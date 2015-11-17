<section>
  <h2 class="headline">Register for this Study</h2>
  <p>Let's get started!  To begin you'll need to register for this study.</p>          
  <form id="getstarted" action="register.php" class="form-horizontal" method="POST" role="form">
    <div class="form-group">
      <label for="email" class="control-label col-sm-2">Your Name:</label>
      <div class="col-sm-5"> 
        <input type="text" class="form-control" name="firstname" id="firstname" placeholder="First Name">
      </div>
      <div class="col-sm-5"> 
        <input type="text" class="form-control" name="lastname" id="lastname" placeholder="Last Name">
      </div>
    </div>
    <div class="form-group">
      <label for="username" class="control-label col-sm-2">Your Email:</label>
      <div class="col-sm-10"> 
        <input type="email" class="form-control" name="username" id="username" placeholder="Email Address" <?=(!is_null($bad_login) ? "value='$bad_login'" : "")?> >
      </div>
    </div>
    <div class="form-group">
      <label for="password" class="control-label col-sm-2">New Password:</label>
      <div class="col-sm-10"> 
        <input type="password" class="form-control" name="password" id="password" >
      </div>
    </div>
    <div class="form-group">
      <label for="confirmpassword" class="control-label col-sm-2">Password Again:</label>
      <div class="col-sm-10"> 
        <input type="password" class="form-control" name="confirmpassword" id="confirmpassword" >
      </div>
    </div>

    <div class="form-group">
      <label for="zip" class="control-label col-sm-2">Your Zip Code:</label>
      <div class="col-sm-10"> 
        <input type="number" class="form-control" name="zip" id="zip" placeholder="Zip Code">
      </div>
    </div>

    <div class="form-group">
      <span class="control-label col-sm-2"></span>
      <div class="col-sm-10"> 
        <label><input checked type="checkbox"> <em>Receive updates about this and future studies.  You can opt-out at anytime.</em></label>
      </div>
    </div>

    <div class="form-group">
      <span class="control-label col-sm-2"></span>
      <div class="col-sm-10"> 
        <div class="g-recaptcha" data-sitekey="6LcEIQoTAAAAAE5Nnibe4NGQHTNXzgd3tWYz8dlP"></div>
        <button type="submit" class="btn btn-primary" name="submit_new_user"  value="true">Register for the Study</button>
      </div>
    </div>
  </form>
  <script>
    $('#getstarted').validate({
      rules: {
        username: {
          <?php echo $username_validation ?>
        },
        password: {
          required: true
        },
        zip: {
          required: true
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
  </script>
</section>