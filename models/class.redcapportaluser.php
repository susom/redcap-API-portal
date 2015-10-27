<?php

/*
   This class can be used in two cases:
   
   1) When registering a new user

      A new RedcapAuth is instantiated with username and password
      if $this->username_exists == false it is valid to create a new user
      call createNewUser() to get the user ID - then instantiate a new user object...
      
   2) When logging in and verifying credentials

*/

// A class used for looking up users and matching credentials.
class RedcapAuth {
   public $username;
   public $username_raw;
   private $password;
   private $password_raw;
   
   public $username_matches = array();    // Array to store record indexes for username matches
   public $email_matches = array();    // Array to store record indexes for email matches
   
   public $error = '';                 // a place to hold error messages
   public $authenticated_user_id = NULL;  // If the credentials are valid, set the user id here
   public $new_user_id;             // The ID of the new user that was created
   
   public $suspended;
   public $email_verified;
   
   // When launched, provide current credentails
   public function __construct($user, $pass = NULL, $email = NULL)
   {
      $this->username_raw = trim($user);
      $this->username = sanitize($user);
      $this->email = sanitize($email);
      
      // Load the record data from the API and get any username matches
      self::loadRecords();
      
      // If the password was supplied, then try to verify it while we are here
      if (!empty($pass)) self::verifyPassword($pass);
   }
   
   public function usernameExists() {
      return count($this->username_matches) !== 0;
   }
   
   public function emailExists() {
      return count($this->email_matches) !== 0;
   }
   
   public function verifyPassword($login_pass) {
      // The password contains 25 characters as a salt and then then generated password by SHA1 encrypting the salt+pass.
      foreach ($this->username_matches as $id => $record)
      {
         // Trim the database-stored password
         $record_salt_pass = $record[getRF('password')];
         
         // Take the first 25 characters as the salt
         $record_salt = substr($record_salt_pass,0,25);
         // Take the remaining characters as the password
         $record_pass = substr($record_salt_pass,25); //trim($record[getRF('password')]);
         logIt("Comparing $id:  salt=$record_salt / pass=$record_pass", "DEBUG");
         if (!empty($record_pass) && !empty($record_salt))
         {
            $generated_pass = generateHash($login_pass, $record_salt);
            logIt("Generating: $generated_pass","DEBUG");
            if ($generated_pass == $record_salt_pass) {
               $this->authenticated_user_id = $id;
               return $id;
            }
         }
      }
      return Null;
   }
   
   // Load all records from project to prepare to validate
   public function loadRecords()
   {
      $params = array(
         'fields' => array(REDCAP_FIRST_FIELD, getRF('username'), getRF('password'), getRF('email'))
      );
      $result = RC::callApi($params);
      
      // Scan records for email and username matches and to set nextId
      $new_id = 1;
      $username_matches = $email_matches = array();
      foreach ($result as $idx => $record)
      {
         $id = $record[REDCAP_FIRST_FIELD];
         if(is_numeric($id) && $id >= $new_id) $new_id = $id+1;
         
         if ($this->username == sanitize($record[getRF('username')])) $username_matches[$id] = $record;
         if ($this->email == sanitize($record[getRF('email')])) $email_matches[$id] = $record;
      }
      $this->next_user_id = $new_id;
      $this->username_matches = $username_matches;
      $this->email_matches = $email_matches;
      //print "RA:LOAD RECORDS<pre>".print_r($result,true)."</pre>";
   }
   
   // Create a new user in the REDCap project
   public function createNewUser($pass)
   {
      if (self::usernameExists())
      {
         $this->error = "Error creating user (CODE 001)"; // Don't create a user if they already exist!
         return false;
      }
      if (empty($pass)) {
         $this->error = "Error creating user (CODE 002)"; // Missing password 
         return false;
      }
      
      // Salt and Hash password
      //$salt = generateRandomString(25, true);
      $password_salt_hash = generateHash($pass);
      //logIt("Hashing $pass with $salt to yield $password_hash","DEBUG");
      
      $data = array(
         REDCAP_FIRST_FIELD   => $this->next_user_id,
         getRF('username')    => $this->username,
         getRF('password')    => $password_salt_hash,
         getRF('email')       => $this->email,
         getRF('created_ts') => date('Y-m-d H:i:s')
      );
      // Add event if longitudinal
      if (REDCAP_PORTAL_EVENT !== NULL) $data['redcap_event_name'] = REDCAP_PORTAL_EVENT;
      
      logIt("CREATE NEW USER WITH DATA:".print_r($data,true), "DEBUG");
      $result = RC::writeToApi($data, array('returnContent'=>'ids'));
      
      $new_user_id = is_array($result) ? current($result) : null;
      
      if (is_numeric($new_user_id))
      {
         $this->new_user_id = $new_user_id;
      }
      else
      {
         logIt("Error creating new user: " . print_r($result,true), "ERROR");
         $this->error = "Error creating user via API";
      }
      logIt("CREATE NEW USER RESULT:".json_encode($result), "DEBUG");
      return $new_user_id;
   }
}


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
   
   function __construct($user_id)
   {
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
      global $websiteUrl, $websiteName;
      $mail = new userPieMail();
      
      //Build the activation message
      $activation_message = lang("ACTIVATION_MESSAGE",array($websiteUrl,$this->email_act_token));
      
      //Define more if you want to build larger structures
      $hooks = array(
         "searchStrs" => array("#ACTIVATION-MESSAGE","#ACTIVATION-KEY","#USERNAME#"),
         "subjectStrs" => array($activation_message,$this->email_act_token,$this->username)
      );
      //logIt("Hooks: " . print_r($hooks,true), "DEBUG");   
         
      // Build the template - Optional, you can just use the sendMail function to message
      if(!$mail->newTemplateMsg("new-registration.txt",$hooks))
      {
         logIt("Error building rew-registration email template", "ERROR");
         $this->mail_failure = true;
      }
      else
      {
         // Send the mail. Specify users email here and subject. 
         // SendMail can have a third parementer for message if you do not wish to build a template.
         if(!$mail->sendMail($this->email,"$websiteName Email Verification"))
         {
            logIt("Error sending email: " . print_r($mail,true), "ERROR");
            $this->mail_failure = true;
         }
         else
         {
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
   
   function setEmailVerified()
   {
      $this->log_entry[] = "Email verified (setting ts)";
      
      // Update token
      return self::updateUser(array(
         getRF('email_verified')    => 1,
         getRF('email_verified_ts') => date('Y-m-d H:i:s'),
         getRF('email_act_token')   => ''
      ), array('overwriteBehavior'=>'overwrite'));
   }
   
   function setActive()
   {
      $this->log_entry[] = "Activating user";
      // Update
      return self::updateUser(array(
         getRF('active') => 1
      ));
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
      }
      else
      {
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
      foreach ($password_reset_pairs as $i => $pair)
      {
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
      
      if ($flushLog && count($this->log_entry > 0))
      {
         $newLog = array(getRF('log') => implode("\n",$this->log_entry));
         $data = array_merge($data,$newLog);
         //$this->log_entry = array();
      }
      
      //logIt("updateUser data2:".print_r($data,true), "DEBUG");
      $result = RC::writeToApi($data, $extra_params);
      if (isset($result['error']))
      {
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
      $result = RC::callApi($params);
      //print "DEBUG: PARAMS: <pre>".print_r($params,true)."</pre>";
      //print "DEBUG: LOAD USER: <pre>".print_r($result,true)."</pre>";
      
      if (count($result) != 1) {
         $this->errors[] = "Unable to load specified user (" . $this->user_id . ")";
         return false;
      }
      
      // Load results into this object
      $user = current($result);
      //logIt("Loaded User: " . json_encode($user), "DEBUG");
      
      foreach ($redcap_field_map as $k => $v) {
         $this->$k = $user[$v];
      }
      
      //logIt("This: " . json_encode($this), "DEBUG");
      
      // TBD Sanitize any of the loaded variables?
      return true;
   }
}

?>
