  <form id="getstarted" action="register.php" class="form-horizontal" method="POST" role="form">
    <h2>Register for this Study</h2>
    <div class="form-group">
      <label for="email" class="control-label col-sm-3">Your Name:</label>
      <div class="col-sm-4"> 
        <input type="text" class="form-control" name="firstname" id="firstname" placeholder="First Name" value="<?php echo (isset($fname) ? $fname : "") ?>">
      </div>
      <div class="col-sm-4"> 
        <input type="text" class="form-control" name="lastname" id="lastname" placeholder="Last Name" value="<?php echo (isset($lname) ? $lname : "") ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="username" class="control-label col-sm-3">Your Email:</label>
      <div class="col-sm-8"> 
        <input type="email" class="form-control" name="username" id="username" placeholder="Email Address" value="<?php echo (isset($email) ? $email : "") ?>">
      </div>
    </div>
    <div class="form-group">
      <label for="usernametoo" class="control-label col-sm-3">Re-enter Email:</label>
      <div class="col-sm-8"> 
        <input type="email" class="form-control" name="usernametoo" id="usernametoo" placeholder="Email Address" >
      </div>
    </div>

    <div class="form-group">
      <label for="zip" class="control-label col-sm-3">Your Location:</label>
      <div class="col-sm-2"> 
        <input type="number" class="form-control zip" name="zip" id="zip" placeholder="Zip">
      </div>

      <div class="col-sm-4"> 
        <input type="text" class="form-control city" name="city" id="city" placeholder="City">
      </div>
      <div class="col-sm-2"> 
        <select name="state" class="form-control" id="state">
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
      
    </div>

    <aside class="eligibility">
      <fieldset class="eli_one">
        <div class="form-group">
          <label class="control-label col-sm-6">Do you plan to continue living in Santa Clara County for the next 12 months or longer?</label>
          <div class="col-sm-2"> 
            <label><input name="nextyear" type="radio" value="1"> Yes</label>
          </div>

          <div class="col-sm-2"> 
            <label><input name="nextyear" type="radio" value="0"> No</label>
          </div>
        </div>
      </fieldset>

      <fieldset class="eli_two">
        <div class="form-group">
          <label class="control-label col-sm-6">Are you 18 years old or older?</label>
          <div class="col-sm-2"> 
            <label><input name="oldenough" type="radio" value="1"> Yes</label>
          </div>
          <div class="col-sm-2"> 
            <label><input name="oldenough" type="radio" value="0"> No</label>
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-6">What is your birth year?</label>
          <div class="col-sm-4"> 
            <select name="birthyear" class="form-control" id="birthyear">
            <?php
              $thisyear = date("Y");
              for($i=0; $i < 100 ; $i++){
                $cutoff = ($i == 18 ? "selected" : "");
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
        <em>By clicking the Submit.  you agree to be contacted about WELL related studies and information.</em>
      </div>
    </div>
    <div class="form-group">
      <span class="control-label col-sm-3"></span>
      <div class="col-sm-8"> 
        <!-- <div class="g-recaptcha" data-sitekey="6LcEIQoTAAAAAE5Nnibe4NGQHTNXzgd3tWYz8dlP"></div> -->
        <button type="submit" class="btn btn-primary" name="submit_new_user"  value="true">Submit</button>
        <input type="hidden" name="submit_new_user" value="true"/>
        <input type="hidden" name="optin" value="true"/>
      </div>
    </div>
    <div class="form-group">
      <span class="control-label col-sm-3"></span>
      <div class="col-sm-8"> 
      <a href="login.php" class="showlogin">Already Registered?</a>
      </div>
    </div>
  </form>

  <script>
    var eligible_map    = <?php echo $eligible_map ?>;
    var eligible_zips   = [<?php echo implode(",",$eligible_zips) ?>];

    var zip_to_city     = {};
    for(var i in eligible_map){
      for (var n in eligible_map[i]){
        zip_to_city[eligible_map[i][n]] = i;
      }
    }

    $("#zip,#city").keyup(function(){
      var locationcheck = $(this).val().toUpperCase();
      var showeligible  = false;

      if(locationcheck != ""){        
        if( ($(this).hasClass("zip") && eligible_zips.indexOf(parseInt(locationcheck)) > -1 ) ) {
          $("#city").val(zip_to_city[parseInt(locationcheck)]);
          showeligible = true;
        }

        if( $(this).hasClass("city") && eligible_map.hasOwnProperty(locationcheck) ) {
          if( eligible_map[locationcheck].length == 1 ){
            $("#zip").val(eligible_map[locationcheck][0]);
          }else{
            var possible_zips = eligible_map[locationcheck];
            var lengthcheck   = eligible_map[locationcheck][0].toString().length;
            var common_nums   = [];
            for(var i = 0; i < lengthcheck; i++){
              for(var n in possible_zips){
                var a_zip       = possible_zips[n].toString();
                if(n == 0){
                  common_nums[i]  = a_zip[i];
                }else{
                  if(common_nums[i] !== a_zip[i]){
                    common_nums.pop();
                    break;
                    break;
                  }
                }
              }
            }
            var haszip = $("#zip").val();
            if(eligible_map[locationcheck].indexOf(parseInt(haszip)) < 0){
              $("#zip").val(common_nums.join("")).focus();
            }
          }
          showeligible = true;
        }
      }

      if(showeligible){
        $(".eligibility").slideDown("medium");
      }
    });

    $("input[name='nextyear']").click(function(){
      if($(this).val() == 1) {
        $(".eli_two").slideDown("medium");
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
          required: function(){
            return !$("#zip").val();
          }
        },
        zip: {
          required: function(){
            return !$("#city").val();
          }
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