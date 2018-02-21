<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");

$navon          = array("home" => "", "reports" => "", "game" => "");
$API_URL        = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN      = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];

if(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"]){
  //WRITE TO API
  //ADD OVERIDE PARAMETER
  $formdata = $_POST;

  $data   = array();
  foreach($_POST as $field_name => $value){
    if($value === 0){
      $value = "0";
    }

    if($value == ""){
      $value = NULL;
    }

    $field_name = $redcap_field_map[$field_name];

    //SET IT IN THE SESSION
    $_SESSION[SESSION_NAME]["user"]->{$field_name} = $value;

    $data[] = array(
      "record"            => $_SESSION[SESSION_NAME]["user"]->id,
      "redcap_event_name" => $_SESSION[SESSION_NAME]["survey_context"]["event"],
      "field_name"        => $field_name,
      "value"             => $value,
    );
  }
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), REDCAP_API_URL, REDCAP_API_TOKEN);

  echo json_encode($data);
  exit;
}


$pageTitle = "Well v2 Profile";
$bodyClass = "profile";
include_once("models/inc/gl_head.php");
?>
    <div class="main-container">
        <div class="main wrapper clearfix">
            <article>
                <h3>My Profile</h3>
                  <?php
                    $label_map = array();
                    $label_map["portal_nickname"]       = "Nickname";
                    $label_map["portal_middlename"]     = "Middle Name";
                    $label_map["email"]                 = "Email";
                    $label_map["portal_contact_name"]   = "Contact Name";
                    $label_map["portal_contact_phone"]  = "Contact Phone";
                    $label_map["portal_mail_street"]    = "Street Address";
                    $label_map["portal_apartment_no"]   = "Apartment/Number";
                    $label_map["city"]           = "City";
                    $label_map["state"]          = "State";
                    $label_map["zip"]            = "Zip Code";

                    $profile_info     = $_SESSION["REDCAP_PORTAL"]["user"];
                    $p_joined         = Date("M d Y", strtotime($profile_info->email_verified_ts));
                    $p_pic            = (!$profile_info->portal_pic     ? "-10px -10px"    :$profile_info->portal_pic    );
                    $p_firstname      = (!$profile_info->firstname      ? "First Name"     :$profile_info->firstname      );
                    $p_lastname       = (!$profile_info->lastname       ? "Last Name"      :$profile_info->lastname       );

                    $p_portal_apartment_no  = (!$profile_info->portal_apartment_no ? ""      :$profile_info->portal_apartment_no       );
                    $p_portal_mail_street   = (!$profile_info->portal_mail_street  ? ""      :$profile_info->portal_mail_street        );
                    $p_city                 = (!$profile_info->city                ? ""      :$profile_info->city                      );
                    $p_state                = (!$profile_info->state               ? ""      :$profile_info->state                     );
                    $p_zip                  = (!$profile_info->zip                 ? ""      :$profile_info->zip                       ); 
                  ?>
                  <style>
                  .profile_card figure span { 
                    background:url(assets/img/profile_icons.png) <?php echo $p_pic; ?> no-repeat;
                  }
                  </style>
                  <form class="customform">
                    <div class="profile_card">
                    <figure>
                      <span id="ppic" class="<?php echo $special_user ?>"></span>
                      <figcaption>
                        <b><?php echo $p_firstname . " " . $p_lastname ?></b>
                        <em><?php echo lang("PROFILE_JOINED") ?> : <?php echo $p_joined ?></em>
                      </figcaption>
                    </figure>
                    <ul>
                      <?php
                      $html         = "";
                      foreach($label_map as $item => $field_label){
                        $temp_junk  = array( "Email" => "ACCOUNT_EMAIL_ADDRESS"
                                            ,"Nickname" => "PROFILE_NICKNAME"
                                            ,"Middle Name" => "ACCOUNT_MIDDLE_NAME"
                                            ,"Contact Name" => "PROFILE_CONTACT_NAME"
                                            ,"Contact Phone" => "PROFILE_CONTACT_PHONE"
                                          );
                        $placeholder= lang($temp_junk[$field_label]);
                        $field_name = $redcap_field_map[$item];
                        $value      = $_SESSION["REDCAP_PORTAL"]["user"]->{$item};
                        $validate   = ($item == "email" ? 'data-validate="email"' : '');
                        $validate   = ($item == "portal_contact_phone" ? 'data-validate="phone"' : $validate);
                        $html .= "<li class='$field_name'>\n";
                        $html .= "<input $validate type='text' id='$field_name' name='$item' value='$value' placeholder='$placeholder'/>\n";
                        $html .= "</li>\n";
                        if($item == "portal_contact_phone"){
                          break;
                          //DO THE REST BY HAND , UGH
                        }
                      }
                      print $html;
                      ?>
                      <li>
                      <input type="text" id="portal_mail_street" name="portal_mail_street" value="<?php echo $p_portal_mail_street ?>" placeholder="<?php echo lang("PROFILE_STREET_ADDRESS"); ?>">
                      / <input type="text" id="portal_apartment_no" name="portal_apartment_no" value="<?php echo $p_portal_apartment_no ?>" placeholder="<?php echo lang("PROFILE_APARTMENT"); ?>">
                      </li>
                      <li>
                        <input type="text" id="city" name="city" value="<?php echo $p_city ?>" placeholder="<?php echo lang("ACCOUNT_CITY"); ?>">
                        , <input type="text" id="state" name="state" value="<?php echo $p_state ?>" placeholder="<?php echo lang("ACCOUNT_STATE"); ?>">
                        <input data-validate="number" type="text" id="zip" name="zip" value="<?php echo $p_zip ?>" placeholder="<?php echo lang("ACCOUNT_ZIP"); ?>">
                      </li>
                    </ul>

                    <a href="#" class="btn btn-large block btn-info editprofile"><span><?php echo lang("EDIT_PROFILE"); ?></span> <?php echo lang("PROFILE_EDIT"); ?></a>
                    </div>
                  </form>
            </article>
        </div> <!-- #main -->
    </div> <!-- #main-container -->
<?php 
include_once("models/inc/gl_foot.php");
?>
<div id="picpick">
  <h3>Pic Picker - Click on a Face</h3>
  <img src="images/profile_icons.png"/>
</div>
<script>
function saveFormData(elem){
  var dataDump = "profile.php?ajax=1";

  if(!elem.val()){
    elem.val(null);
  }

  $.ajax({
    url:  dataDump,
    type:'POST',
    data: elem.serialize(),
    success:function(result){
      console.log("Data Saved",result);
      
      //REMOVE THE SPINNER
      setTimeout(function(){
        $(".hasLoading").removeClass("hasLoading");
      },250);
    }
  });
}

$(document).ready(function(){
  //INPUT CHANGE ACTIONS
  $(".customform :input").click(function(){
    if(!$(".profile_card").hasClass("editmode")){
      $(this).blur();
    }
  }); 

  $(".customform :input").change(function(){
    //SAVE JUST THIS INPUTS DATA
    $(this).closest("li").addClass("hasLoading");
    saveFormData($(this));
  }); 

  //EDIT
  $(".editprofile").click(function(){
    $(".profile_card").toggleClass("editmode");
    if($(".profile_card").hasClass("editmode")){
      $(this).find("span").text("Save");
      $(".profile_card input").first().focus();    
    }else{
      $(this).find("span").text("Edit");
      $(".profile_card input").first().blur(); 
    }
    return false; 
  });

  //THE PROFILE PIC PICKER
  $("#ppic").click(function(){
    $("#picpick").show();
    return false;
  });

  function closest(arr, closestTo){
      var closest = Math.max.apply(null, arr); //Get the highest number in arr in case it match nothing.
      for(var i = 0; i < arr.length; i++){ //Loop the array
          if(arr[i] >= closestTo && arr[i] < closest) closest = arr[i]; //Check if it's higher than your number, but lower than your closest value
      }
      return closest; // return the value
  }

  var xar = [113, 330, 547, 764, 981];
  var yar = [150, 417, 680, 935];
  $("#picpick").click(function(e) {
    var offset = $(this).offset();
    var relativeX = (e.pageX - offset.left);
    var relativeY = (e.pageY - offset.top);
    relativeX     = 105 - closest(xar,relativeX);
    relativeY     = 140 - closest(yar,relativeY);
    var picpos    = relativeX+"px "+relativeY+"px";
    var picpos_sm = Math.round(relativeX/4.6)+"px "+Math.round(relativeY/4.6)+"px";
    var picpos_xs = Math.round(relativeX/6)+"px "+Math.round(relativeY/6)+"px";
    var imp       = $("<input>").attr("name","portal_pic");
    imp.val(picpos);

    $("#ppic").css("background-position",picpos);

    $(".thumb.avatar").css("background-position",picpos_sm);
    $(".thumb-sm.avatar").css("background-position",picpos_xs);

    $(this).hide();
    // console.log(picpos);
    saveFormData(imp);
  });

  $(document).on('click', function(event) {
    if (!$(event.target).closest('#picpick').length && $("#picpick").is(":visible")) {
      setTimeout(function(){
        $("#picpick").hide(100,function(){});
      }, 300);
    }
  });
});
</script>