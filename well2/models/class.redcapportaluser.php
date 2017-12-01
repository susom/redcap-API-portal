<?php
/*
   This class can be used in two cases:

   1) When registering a new user

      A new RedcapAuth is instantiated with username and password
      if $this->username_exists == false it is valid to create a new user
      call createNewUser() to get the user ID - then instantiate a new user object...

   2) When logging in and verifying credentials

*/

// The logged in user - stored in the $_SESSION
class RedcapPortalUser
{
   public $errors = array();
   public $user_id = Null; // User ID in REDCAP_FIRST_FIELD
   public $mail_failure = false; // Used to record

// public $username;
// public $password;
// public $active
// public $suspended;
// public $email;
// public $email_ver;
// public $email_ver_ts;
// public $email_act_token;
// public $pass_reset_req_ts;
// public $pass_reset_token;
// public $pass_security_question;
// public $pass_security_answer;
// public $created_ts;
// public $last_login_ts;
// public $log;

   public $log_entry = array();  // Place to store log_entry that can be written to the log

   function __construct($user_id) {
      if (empty($user_id)) {
         logIt("REDCap user created with missing user_id!","ERROR");
         addSessionAlert("Error accessing user information.  Please try again later.");
         logout();die();
      }
      $this->user_id = $user_id;

      // Load the user attributes from API
      self::loadUser();
   }

   #################### STATUS ###################

   function getEmail() {
      return empty($this->email) ? false : $this->email;
   }

   function isActive() {
      return $this->active == 1;
   }

   // Is the user suspended
   function isSuspended() {
      return $this->suspended == 1;
   }

   // Return T/F depending on whether email is validated
   function isEmailVerified() {
      return $this->email_verified == 1;
   }

   // Returns T/F if token is set (which may be false for new users)
   function isEmailTokenSet() {
      return !empty($this->email_act_token);
   }

   // Creates a new email verification token and writes it to the database
   function createEmailToken() {
      $this->email_act_token = generateRandomString(10,false,true);
      $this->log_entry[] = "Created email verification token";

      logIt("DEBUG: CreateEmailToken: " . json_encode($this), "DEBUG");

      // Update token
      return self::updateUser(array(
         getRF('email_act_token') => $this->email_act_token
      ));
   }

   // Send email with link for token
   function emailEmailToken() {
      global $websiteUrl, $websiteName, $mail_templates_dir;
      $mail = new userPieMail();

      //Build the activation message
      $activation_message = lang("ACTIVATION_MESSAGE",array($websiteUrl,$this->email_act_token,$this->user_id));

      //Define more if you want to build larger structures
      $hooks = array(
         "searchStrs" => array("#ACTIVATION-MESSAGE","#ACTIVATION-KEY","#USERNAME#"),
         "subjectStrs" => array($activation_message,$this->email_act_token,$this->firstname)
      );
      //logIt("Hooks: " . print_r($hooks,true), "DEBUG");

      // Build the template - Optional, you can just use the sendMail function to message
      $mail_templ = isset($_SESSION["use_lang"]) && file_exists($mail_templates_dir . "new-registration-".$_SESSION["use_lang"].".txt") ? "new-registration-".$_SESSION["use_lang"].".txt" : "new-registration-en.txt" ;
      
      if(!$mail->newTemplateMsg($mail_templ,$hooks)) {
         logIt("Error building rew-registration email template", "ERROR");
         $this->mail_failure = true;
      } else {
         // Send the mail. Specify users email here and subject.
         // SendMail can have a third parementer for message if you do not wish to build a template.
         $mail_subj = $_SESSION["use_lang"] == "sp" ? "$websiteName Verificación de su correo electrónico" : "$websiteName Email Verification";
         $mail_body = $_SESSION["use_lang"] == "sp" ? mb_convert_encoding($this->email,"utf-8","auto") : $this->email;
         $encoding  = $_SESSION["use_lang"] !== "en" ? "utf-8" : "iso-8859-1";
         if(!$mail->sendMail($mail_body,$mail_subj,NULL,$encoding))
         {
            logIt("Error sending email: " . print_r($mail,true), "ERROR");
            $this->mail_failure = true;
         } else {
            // Update email_act_sent_ts
            $this->log_entry[] = "Verification email sent.";
            self::updateUser(array(
               getRF('email_act_sent_ts')=>date('Y-m-d H:i:s')
            ));
         }
      }
   }

   // Returns time since last activation email
   function getMinSinceLastActivationEmail() {
      $now_ts = new DateTime();
      $last_sent_ts = new DateTime($this->email_act_sent_ts);
      $min_ago = ($now_ts->getTimestamp() - $last_sent_ts->getTimestamp()) / 60;

      //DEBUG - what happens when email_act_sent_ts is null?
      logIt("email_act_sent_ts: {$this->email_act_sent_ts} / min_ago: $min_ago", "DEBUG");
      return $min_ago;
   }

   // Is the supplied token equal to the stored one
   function isEmailTokenValid($token) {
      return (!empty($token) && $this->email_act_token == $token);
   }

   function setEmailVerified() {
      $this->log_entry[] = "Email verified (setting ts)";

      // Update token
      return self::updateUser(array(
         getRF('email_verified')    => 1,
         getRF('email_verified_ts') => date('Y-m-d H:i:s')
      ), array('overwriteBehavior'=>'overwrite'));
   }

   function setActive() {
      $this->log_entry[] = "Activating user";
      // Update
      return self::updateUser(array(
         getRF('active')      => 1,
         getRF('consent_ts')  => date('Y-m-d H:i:s'),
         getRF('email_act_token')   => ''
      ), array('overwriteBehavior'=>'overwrite'));
   }

   // Return username
   function getUsername() {
      return $this->username;
   }


   // Create a new password reset token and update the timestamp
   function createPassResetToken() {
      $this->pass_reset_token = generateRandomString(20);
      $this->pass_reset_req_ts = date('Y-m-d H:i:s');
      $this->log_entry[] = "Creating password reset token";

      return self::updateUser(array(
         getRF('pass_reset_token') => $this->pass_reset_token,
         getRF('pass_reset_req_ts') => $this->pass_reset_req_ts
      ));
   }

   // Return hashed password
   function getPasswordHash() {
      return $this->password;
   }

   // The salt is the first 25 characters of the password hash
   function getSalt() {
      return substr($this->password,0,25);
   }

   // Take a new password already hashed and save it
   function updatePassword($password_hash) {
      $this->log_entry[] = "Updating password";
      $result = self::updateUser(array(
         getRF('password') => $password_hash,
      ));
   }

   /* Was there a pending password change
      $pass_reset_req_ts is set and $pass_reset_token is generated when requested
      $pass_reset_req_ts should be null and token should be null if not active
   */
   function isPasswordResetPending() {
      if (!empty($this->pass_reset_req_ts)) {
         return true;
      } else {
         return false;
      }
   }

   function updatePasswordReset($q_field, $a_field, $q, $a) {
      //$this->log_entry[] = "Updating password recovery options";
      self::updateUser(array(
         getRF($q_field) => htmlentities($q),
         getRF($a_field) => htmlentities($a),
      ));
   }

   function isPasswordRecoveryConfigured() {
      global $password_reset_pairs;
      foreach ($password_reset_pairs as $i => $pair) {
         // If any fields are empty, then return false
         if (empty($this->$pair['question']) || empty($this->$pair['answer'])) return false;
      }
      return true;
   }


   ################# ACTION METHODS ######################

   // Remove any password reset features
   function clearPasswordReset() {
      $result = self::updateUser(array(
         getRF('pass_reset_req_ts')       => '',
         getRF('pass_reset_token')  => ''
      ), array('overwriteBehavior'=>'overwrite'));
      clearSessionPassResetAttempt();
   }


   function timeout() {
      //logIt('Timeout called','DEBUG');
      logout("Session timed out",true);
   }

   function addLogEntry($msg) {
      $this->log_entry[] = $msg;
   }

   // Update the user record with supplied data (adding log)
   function updateUser($data = array(), $extra_params=array(), $flushLog = true) {
      // Add record ID if not already there
      $data[REDCAP_FIRST_FIELD] = $this->user_id;

      // Add event if longitudinal
      if (REDCAP_PORTAL_EVENT !== NULL) $data['redcap_event_name'] = REDCAP_PORTAL_EVENT;

      logIt("updateUser data1:".print_r($data,true), "DEBUG");

      if ($flushLog && count($this->log_entry > 0)) {
         $newLog = array(getRF('log') => implode("\n",$this->log_entry));
         $data = array_merge($data,$newLog);
         //$this->log_entry = array();
      }

      //logIt("updateUser data2:".print_r($data,true), "DEBUG");
      $result = RC::writeToApi($data, $extra_params, REDCAP_API_URL, REDCAP_API_TOKEN);
      if (isset($result['error'])) {
         logIt('Error updating User: ' . $result['error'] . " with: " . print_r($data,true));
         return false;
      }
      //logIt("updateUser result:".print_r($result,true), "DEBUG");

      // Flush the log
      if ($flushLog) $this->log_entry = array();

      // Reload the session user from the API
      self::refreshUser();
      return true;   //$result;
   }

   // This function re-loads the user object from the database and should be called whenever a successful updateUser has finished
   function refreshUser() {
      self::loadUser();
   }


   // Pull data from REDCap project
   public function loadUser() {
      global $redcap_field_map;

      // Strip off checkbox endings and reduce duplicates in case multiple different checkboxes options are being used
      // For example checkbox___1 and checkbox___2 become checkbox in the field array
      $fields = array_values(array_unique(preg_replace('/___\d+/','',$redcap_field_map)));
      $params = array(
         'records' => array($this->user_id),
         'fields' => $fields
      );
      $result = RC::callApi($params, true, REDCAP_API_URL, REDCAP_API_TOKEN);
      //print "DEBUG: PARAMS: <pre>".print_r($params,true)."</pre>";
      //print "DEBUG: LOAD USER: <pre>".print_r($result,true)."</pre>";

      // THIS MESSES THINGS UP FOR multi ARM deals
      // if (count($result) != 1) {
      //    $this->errors[] = "Unable to load specified user (" . $this->user_id . ")";
      //    return false;
      // }

      // Load results into this object
      // $user = current($result);
      $user = $result[0];

      //logIt("Loaded User: " . json_encode($user), "DEBUG");

      foreach ($redcap_field_map as $k => $v) {
         $this->$k = $user[$v];
      }

      //THINK ABOUT THIS - FLAIR FOR first PArticipants
      $user["elite"] = $user["id"] < 500 ? true : false;
      
      //logIt("This: " . json_encode($this), "DEBUG");

      // TBD Sanitize any of the loaded variables?
      return true;
   }
}

?>
