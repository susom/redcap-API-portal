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
   public   $username;
   public   $username_raw;
   private  $password;
   private  $password_raw;
   
   public   $username_matches = array();    // Array to store record indexes for username matches
   public   $email_matches    = array();    // Array to store record indexes for email matches
   
   public   $error = '';                 // a place to hold error messages
   public   $authenticated_user_id = NULL;  // If the credentials are valid, set the user id here
   public   $new_user_id;             // The ID of the new user that was created
   
   public   $suspended;
   public   $email_verified;
   
   // When launched, provide current credentails
   public function __construct($user, $pass = NULL, $email = NULL, $first = NULL, $last = NULL, $zip = NULL, $city = NULL, $state = NULL, $age = NULL, $lang = NULL){
      $this->username_raw  = trim($user);
      $this->username      = sanitize($user);
      $this->email         = sanitize($email);
      $this->firstname     = sanitize(trim($first));
      $this->lastname      = sanitize(trim($last));
      $this->zip           = sanitize(trim($zip));
      $this->city          = sanitize(trim($city));
      $this->state         = sanitize(trim($state));
      $this->age           = sanitize(trim($age));
      $this->lang          = sanitize(trim($lang));
      
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
      foreach ($this->username_matches as $id => $record){
         // Trim the database-stored password
         $record_salt_pass = $record[getRF('password')];         
         // Take the first 25 characters as the salt
         $record_salt = substr($record_salt_pass,0,25);
         // Take the remaining characters as the password
         $record_pass = substr($record_salt_pass,25); //trim($record[getRF('password')]);
         logIt("Comparing $id:  salt=$record_salt / pass=$record_pass", "DEBUG");
         if (!empty($record_pass) && !empty($record_salt)){
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
   public function loadRecords(){
      $params = array(
         'fields' => array(REDCAP_FIRST_FIELD, getRF('username'), getRF('password'))
      );
      $result = RC::callApi($params, true, REDCAP_API_URL, REDCAP_API_TOKEN);

      // Scan records for email and username matches and to set nextId
      $new_id = 1;
      $username_matches = $email_matches = array();

      foreach ($result as $idx => $record){
         $id = $record[REDCAP_FIRST_FIELD];
         if (is_numeric($id)  && $id >= $new_id)                        $new_id                 = $id+1; //GUESS THE NEXT AUTOINCREME
         
         if (empty($record[getRF('username')]) && empty($record[getRF('email')]))   continue;
         if (!empty($record[getRF('username')]) && $this->username  == sanitize($record[getRF('username')]))   $username_matches[$id]  = $record;
         if (!empty($record[getRF('email')]) && $this->email     == sanitize($record[getRF('email')]))         $email_matches[$id]     = $record;
      }
      $this->next_user_id     = $new_id;
      $this->username_matches = $username_matches;
      $this->email_matches    = $email_matches;
      // print "RA:LOAD RECORDS<pre>".print_r($result,true)."</pre>";
   }
   
   // Create a new user in the REDCap project
   public function createNewUser($pass, $verifymail = true) {
      if (self::usernameExists()) {
         $this->error = "Username already exists."; // Don't create a user if they already exist!
         return false;
      }
      if (empty($pass)) {
         $this->error = "Password is required."; // Missing password 
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
         getRF('firstname')   => ucfirst($this->firstname),
         getRF('lastname')    => $this->lastname,
         getRF('zip')         => $this->zip,
         getRF('city')        => $this->city,
         getRF('state')       => $this->state,
         getRF('age')         => $this->age,
         getRF('email')       => $this->email,
         getRF('created_ts')  => date('Y-m-d H:i:s')
      );
      // Add event if longitudinal
      if (REDCAP_PORTAL_EVENT !== NULL) $data['redcap_event_name'] = REDCAP_PORTAL_EVENT;

      logIt("CREATE NEW USER WITH DATA:".print_r($data,true), "DEBUG");
      $result = RC::writeToApi($data, array('returnContent'=>'ids'), REDCAP_API_URL, REDCAP_API_TOKEN);

      $new_user_id = is_array($result) ? current($result) : null;

      if (is_numeric($new_user_id)) {
         $this->new_user_id = $new_user_id;

         if($verifymail){
            $newuser = new RedcapPortalUser($new_user_id);
            $newuser->createEmailToken();
            $newuser->emailEmailToken();
         }
      } else {
         logIt("Error creating new user: " . print_r($result,true), "ERROR");
         $this->error = "Error creating user via API";
      }
      logIt("CREATE NEW USER RESULT:".json_encode($result), "DEBUG");
      return $new_user_id;
   }
}
?>
