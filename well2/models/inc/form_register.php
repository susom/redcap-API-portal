  <form id="getstarted" action="register.php" class="form-horizontal" method="POST" role="form">
    <input type="hidden" name="lang_req" value="<?php echo $lang_req ?>"/>
    <h2><?php echo lang("ACCOUNT_REGISTER") ?></h2>
    <div class="form-group">
      <label for="email" class="control-label col-sm-3"><?php echo lang("ACCOUNT_YOUR_NAME") ?>:</label>
      <div class="col-sm-4"> 
        <input type="text" class="form-control" name="firstname" id="firstname" placeholder="<?php echo lang("ACCOUNT_FIRST_NAME") ?>" value="<?php echo (isset($fname) ? $fname : "") ?>">
      </div>
      <div class="col-sm-4"> 
        <input type="text" class="form-control" name="lastname" id="lastname" placeholder="<?php echo lang("ACCOUNT_LAST_NAME") ?>" value="<?php echo (isset($lname) ? $lname : "") ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="username" class="control-label col-sm-3"><?php echo lang("ACCOUNT_YOUR_EMAIL") ?>:</label>
      <div class="col-sm-8"> 
        <input type="email" class="form-control" name="username" id="username" placeholder="<?php echo lang("ACCOUNT_EMAIL_ADDRESS") ?>" value="<?php echo (isset($email) ? $email : "") ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="usernametoo" class="control-label col-sm-3"><?php echo lang("ACCOUNT_REENTER_EMAIL") ?>:</label>
      <div class="col-sm-8"> 
        <input type="email" class="form-control" name="usernametoo" id="usernametoo" placeholder="<?php echo lang("ACCOUNT_EMAIL_ADDRESS") ?>" >
      </div>
    </div>

    <div class="form-group">
      <label for="zip" class="control-label col-sm-3"><?php echo lang("ACCOUNT_YOUR_LOCATION") ?>:</label>
      

      <div class="col-sm-4"> 
        <input type="text" class="form-control city" name="city" id="city" placeholder="<?php echo lang("ACCOUNT_CITY") ?>">
      </div>
      <div class="col-sm-2"> 
        <select name="state" class="form-control state" id="state">
          <option value="AL">AL</option>
          <option value="AK">AK</option>
          <option value="AZ">AZ</option>
          <option value="AR">AR</option>
          <option value="CA" selected>CA</option>
          <option value="CO">CO</option>
          <option value="CT">CT</option>
          <option value="DE">DE</option>
          <option value="DC">DC</option>
          <option value="FL">FL</option>
          <option value="GA">GA</option>
          <option value="HI">HI</option>
          <option value="ID">ID</option>
          <option value="IL">IL</option>
          <option value="IN">IN</option>
          <option value="IA">IA</option>
          <option value="KS">KS</option>
          <option value="KY">KY</option>
          <option value="LA">LA</option>
          <option value="ME">ME</option>
          <option value="MD">MD</option>
          <option value="MA">MA</option>
          <option value="MI">MI</option>
          <option value="MN">MN</option>
          <option value="MS">MS</option>
          <option value="MO">MO</option>
          <option value="MT">MT</option>
          <option value="NE">NE</option>
          <option value="NV">NV</option>
          <option value="NH">NH</option>
          <option value="NJ">NJ</option>
          <option value="NM">NM</option>
          <option value="NY">NY</option>
          <option value="NC">NC</option>
          <option value="ND">ND</option>
          <option value="OH">OH</option>
          <option value="OK">OK</option>
          <option value="OR">OR</option>
          <option value="PA">PA</option>
          <option value="RI">RI</option>
          <option value="SC">SC</option>
          <option value="SD">SD</option>
          <option value="TN">TN</option>
          <option value="TX">TX</option>
          <option value="UT">UT</option>
          <option value="VT">VT</option>
          <option value="VA">VA</option>
          <option value="WA">WA</option>
          <option value="WV">WV</option>
          <option value="WI">WI</option>
          <option value="WY">WY</option>
        </select>
      </div>
      
      <div class="col-sm-2"> 
        <input type="number" class="form-control zip" name="zip" id="zip" placeholder="<?php echo lang("ACCOUNT_ZIP") ?>" min="0">
        <select id="zipset"></select>
      </div>
    </div>

    <aside class="eligibility">
      <fieldset class="eli_one">
        <div class="form-group">
          <label class="control-label col-sm-6"><?php echo lang("ACCOUNT_USA_CURRENT") ?></label>
          <div class="col-sm-2"> 
            <label><input name="in_usa" type="radio" value="1"> <?php echo lang("GENERAL_YES") ?></label>
          </div>

          <div class="col-sm-2"> 
            <label><input name="in_usa" type="radio" value="0"> <?php echo lang("GENERAL_NO") ?></label>
          </div>
        </div>
      </fieldset>

      <fieldset class="eli_two">
        <div class="form-group">
          <label class="control-label col-sm-6"><?php echo lang("ACCOUNT_18_PLUS") ?></label>
          <div class="col-sm-2"> 
            <label><input name="oldenough" type="radio" value="1"> <?php echo lang("GENERAL_YES") ?></label>
          </div>
          <div class="col-sm-2"> 
            <label><input name="oldenough" type="radio" value="0"> <?php echo lang("GENERAL_NO") ?></label>
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-6"><?php echo lang("ACCOUNT_BIRTH_YEAR") ?></label>
          <div class="col-sm-4"> 
            <select name="birthyear" class="form-control" id="birthyear">
            <?php
              $thisyear = date("Y") - 18;
              for($i=0; $i < 100 ; $i++){
                $cutoff = ($i == 0 ? "selected" : "");
                echo "<option $cutoff>".($thisyear - $i)."</option>";
              }
            ?>
            </select>
          </div>
        </div>
      </fieldset>
    </aside>
  
    <div class="form-group">
      <span class="control-label col-sm-3"></span>
      <div class="col-sm-8"> 
        <em><?php echo lang("ACCOUNT_AGREE") ?></em>
      </div>
    </div>
    <div class="form-group">
      <span class="control-label col-sm-3"></span>
      <div class="col-sm-8"> 
        <!-- <div class="g-recaptcha" data-sitekey="6LcEIQoTAAAAAE5Nnibe4NGQHTNXzgd3tWYz8dlP"></div> -->
        <button type="submit" class="btn btn-primary" name="submit_new_user"  value="true"><?php echo lang("SUBMIT") ?></button>
        <input type="hidden" name="submit_new_user" value="true"/>
        <input type="hidden" name="optin" value="true"/>
      </div>
    </div>
    <div class="form-group">
      <span class="control-label col-sm-3"></span>
      <div class="col-sm-8"> 
      <a href="login.php" class="showlogin"><?php echo lang("ACCOUNT_ALREADY_REGISTERED") ?></a>
      </div>
    </div>
  </form>
  <style>
  #zipset  { display:none; 
    border:1px solid #ddd;
    height:34px; 
    width:100%; 
  }
  #zip{
    opacity:1;
    transition: .5s opacity;
  }
  #zip.goaway {
    opacity:0;
    position:absolute; 
    z-index:-1;
  }
  </style>
  <script>
    var eligible_map    = <?php echo $eligible_map ?>;
    var eligible_zips   = [<?php echo implode(",",$eligible_zips) ?>];

    var zip_to_city     = {};
    for(var i in eligible_map){
      for (var n in eligible_map[i]){
        zip_to_city[eligible_map[i][n]] = i;
      }
    }

    // $("#zip,#city").blur(function(){
    //   var locationcheck = $(this).val().toUpperCase();
    //   var showeligible  = false;

    //   if(locationcheck != ""){        
    //     if( ($(this).hasClass("zip") && eligible_zips.indexOf(parseInt(locationcheck)) > -1 ) ) {
    //       $("#city").val(zip_to_city[parseInt(locationcheck)]);
    //       showeligible = true;
    //     }

    //     if( $(this).hasClass("city") && eligible_map.hasOwnProperty(locationcheck) ) {
    //       if( eligible_map[locationcheck].length == 1 ){
    //         $("#zip").val(eligible_map[locationcheck][0]);
    //       }else{
    //         var possible_zips = eligible_map[locationcheck];

    //         // console.log(possible_zips);
    //         $("#zip").val(possible_zips[0].toString()).addClass("goaway");
    //         $("#zipset").empty();

    //         for(var n in possible_zips){
    //           var a_zip       = possible_zips[n].toString();
    //           var a_option    = $("<option/>").val(a_zip);
    //           a_option.text(a_zip);
    //           $("#zipset").append(a_option);
    //         }
            
    //         $("#zipset").fadeIn();
    //       }
    //       showeligible = true;
    //     }
    //   }

    //   if(showeligible){
    //     $(".eligibility").slideDown("medium");
    //   }else{
    //     $(".eligibility").slideUp("fast");
    //   }
    // });

    $("#zipset").on("change",function(){
      $(this).hide();
      $("#zip").val($(this).val()).removeClass("goaway");
    });

    $("input[name='in_usa']").click(function(){
      if($(this).val() == 1) {
        $(".eli_two").slideDown("medium");
      }else{
        $(".eli_two").slideUp("fast");
      }
    });

    $('#getstarted').validate({
      rules: {
        firstname:{
          required: true
        },
        lastname:{
          required: true
        },
        username: {
          <?php echo $username_validation ?>
        },
        usernametoo: {
          equalTo: "#username"
        },
        city:{
          required: true
        },
        zip: {
          required: true
        },
        nextyear: {
          required: function(){
            return $(".eligibility").is(':visible');
          }
        },
        oldenough: {
          required: function(){
            return $(".eli_two").is(':visible');
          }
        }

      },
      highlight: function(element) {
        $(element).closest('.form-group').addClass('has-error');
      },
      unhighlight: function(element) {
        $(element).closest('.form-group').removeClass('has-error');
      },
      errorElement: 'span',
      errorClass: 'help-block',
      errorPlacement: function(error, element) {
        if(element.parent('.input-group').length) {
          error.insertAfter(element.parent());
        } else {
          error.insertAfter(element);
        }
      }
    });

    $("#getstarted").submit(function(){
      var formValues = {};
      $.each($(this).serializeArray(), function(i, field) {
          formValues[field.name] = field.value;
      });

      if(formValues.firstname == "" || formValues.lastname == "" || formValues.username == "" || $(this).find(".help-block").length){
        return;
      }

      //ADD LOADING DOTS
      $("button[name='submit_new_user']").append("<img width=50 height=14 src='assets/img/loading_dots.gif'/>")
    });
  </script>