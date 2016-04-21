<?php
require_once("../models/config.php");

//POSTING DATA TO REDCAP API
if(isset($_REQUEST["ajax"])){

  print_r("pretend it saved");
  exit;
  if(isset($_REQUEST["surveycomplete"])){
    $result = RC::callApi(array(
        "hash"    => $_REQUEST["hash"], 
        "format"  => "csv"
      ), true, $custom_surveycomplete_API, REDCAP_API_TOKEN);
    exit;
  }

  //WRITE TO API
  //ADD OVERIDE PARAMETER 
  $data = array();
  foreach($_POST as $field_name => $value){
    if($value === 0){
      $value = "0";
    }

    if($value == ""){
      $value = NULL;
    }

    $data[] = array(
      "record"            => $_SESSION[SESSION_NAME]["user"]->id,
      "redcap_event_name" => $_SESSION[SESSION_NAME]["survey_context"]["event"],
      "field_name"        => $field_name,
      "value"             => $value
    );
    $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), REDCAP_API_URL, REDCAP_API_TOKEN);
  }
  exit;
}

//REDIRECT USERS THAT ARE NOT LOGGED IN
if(!isUserLoggedIn()) {
  $destination = $websiteUrl."login.php";
  header("Location: " . $destination);
  exit;
}else{
  //if they are logged in and active
  //find survey completion and go there?
  // GET SURVEY LINKS
  include("../models/inc/surveys.php");
  include("inc/classes/Survey.php");
}


$active_survey = null;
foreach($surveys as $survey){
  if($survey["survey_complete"]){
    continue;
  }else{
    $survey_data    = $surveys[$survey["instrument_name"]];
    //LOAD UP THE SURVEY PRINTER HERE
    $active_survey  = new Survey($survey_data);

    //ON SURVEY PAGE STORE THIS FOR USE WITH THE AJAX EVENTS 
    $_SESSION[SESSION_NAME]["survey_context"] = array("event" => $survey_data["event"]);
    break;
  }
}


$shownavsmore   = true;
$survey_active  = ' ';
$profile_active = ' ';
$game_active    = ' class="active"';
$pg_title       = "Profile : $websiteName";
$body_classes   = "dashboard profile";
include("inc/gl_head.php");
?>
  <section class="vbox">
    <?php
      include("inc/gl_header.php");
    ?>
    <section>
      <section class="hbox stretch">
        <?php
          include("inc/gl_sidenav.php");
        ?>
        <section id="content">
          <section class="hbox stretch">
            <section>
              <section class="vbox">
                <section class="scrollable padder">
                  <section class="row m-b-md">
                    <h2></h2>
                  </section>
                  <div class="row">
                    <div class="col-sm-1">&nbsp;</div>
                    <div id="wof_game" class="col-sm-10">
                      <h1 class="title">Well Being Paradise!</h1>
                      
                      <?php
                      if($active_survey){
                      ?>
                      <div id="survey_series" class="col-sm-6">
                        <div id="current_survey">
                          <h3><?php echo $active_survey->surveyname ?></h3>
                          <?
                          $num_questions = array_filter($active_survey->raw, function($item){
                            $hidden = (strpos($item["field_annotation"],"HIDDEN") > -1 ? false : true);
                            $desc   = ($item["field_type"] == "descriptive" ? false : true);
                            return ($hidden && $desc);
                          });

                          $num_questions  = count($num_questions);
                          for($i = 1; $i <= $num_questions; $i++){
                            $curclass = ($i == 1 ? " class='current'" : "");
                            echo "<span $curclass>$i</span>";
                          }
                          ?>
                        </div>
                        <div id="current_question" name="<?php echo $survey["instrument_name"];?>">
                          <?php
                            //PRINT OUT THE HTML FOR THIS SURVEY
                            $active_survey->printGameHTML();
                          ?>
                        </div>
                      </div>

                      <div id="gameboard" class="col-sm-6">
                        <div id="board">
                          <div id="totalpoints">Total Points : <b>0</b></div>
                        </div>
                        <form id="letterpicker">
                          <div id="guesses">
                            <div id="guesscount">
                              Guesses Available:  <b>0</b>
                              <i>Answer questions to get more guesses</i>
                            </div>
                            <div id="guessvalue">
                              Guess Point Value:  +<b>10</b>
                              <i>Early guesses are worth more, chose wisely!</i>
                            </div>
                          </div>

                          <button id="pickit" class="btn btn-info">Guess Letter</button>
                        </form>
                      </div>
                      <?php
                      }else{
                        echo "<h3>You don't have any surveys to take at the moment.</h3>"  ;                      
                      } 
                      ?>
                    </div>
                  </div>
                </section>
              </section>
            </section>
            <?php
              include("inc/gl_slideout.php");
            ?>
          </section>
          <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen,open" data-target="#nav,html"></a>
        </section>
      </section>
    </section>
  </section>
<?php
include("inc/gl_foot.php");
?>
<script>
var surveyhash = '<?php echo $active_survey->hash["hash"] ?>';
function saveFormData(elem){
  var dataDump = "game.php?ajax=1";

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
      // setTimeout(function(){
      //   $(".hasLoading").removeClass("hasLoading");
      // },250);
    }
  });
}

function makeGameBoard(secretmessage){
  var letters_per_row = 10;
  var msglen  = secretmessage.length;
  var rows    = Math.ceil(msglen/letters_per_row);
  var filler  = (rows*letters_per_row) - msglen;

  var gameboard = $("<div id='flipboard'></div>");
  for(var i = 0; i < msglen; i++){
    var letter = secretmessage[i].toUpperCase();
    var fc = $("<div class='flip-container'></div>").addClass(letter);
    var fp = $("<div class='flipper'></div>");
    var fr = $("<div class='front'></div>");
    var ba = $("<div class='back'></div>");

    ba.text(letter);
    fp.append(fr).append(ba);
    fc.append(fp);

    if(secretmessage[i] == " "){
      fr.addClass("space");
    }
    gameboard.append(fc);
  }
  for(var i = 0; i < filler; i++){
    var fc = $("<div class='flip-container'></div>");
    var fp = $("<div class='flipper'></div>");
    var fr = $("<div class='front'></div>");
    var ba = $("<div class='back'></div>");
    gameboard.append(fc);
  }

  $("#board").prepend(gameboard);

  $("#guesscount b").text(0);
  $("#guessvalue b").text(10);
  return;
}

function makeLetterTray(){
  var runes = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
  var tray  = $("<div id='lettertray'></div>");
  for(var i in runes){
    var label   = $("<label>"+runes[i]+"</label>");
    var letter  = $("<input type='checkbox' name='letters' value='"+runes[i]+"'>");
    
    //MAKING A GUESS
    letter.change(function(){
      if($(this).is(":checked")){
        //REMOVE "SELECTED" CLASS FROM ANY SIBLINGS (NOT ALREADY BURNED)
        $(this).parent().siblings().removeClass(function() {
          if(!$(this).hasClass("picked")){
            $(this).find(":input").attr("checked",false);
            return $( this ).attr( "class" );
          }
        });
        $(this).parent().addClass("selected");
      }else{
        $(this).parent().removeClass("selected");
      }  
      return false;
    });

    label.append(letter);
    tray.append(label);
  }

  tray.insertAfter("#guesses");
  return;
}

$(document).ready(function(){
  //SET UP THE SURVEY QUESTIONS
  $("#current_question dl").first().addClass("on");

  $("#survey_questions").submit(function(e){
    e.preventDefault(); //SUBMITTING TWICE FOR SOME REASON
    e.stopImmediatePropagation();

    var active  = $("#current_question dl.on");
    var elem    = active.find(":input"); 

    if(elem.length && ( (elem.is(":text,select,textarea") && elem.val() && elem.val()!== "-") || elem.is(":checked") ) ){
      // saveFormData(elem);

      //INCREMENT AVAILABLE GUESSES
      var guesscount  = parseInt($("#guesscount b").text());
      $("#guesscount b").text(guesscount+1);

      // SHOW NEXT QUESTION      
      var nextq = active.next();
      if(nextq && nextq.is("dl")){
        if(nextq.hasClass("hasBranching")){
          if(!nextq.hasClass("showBranch")){
            nextnextq = nextq.next();
            $("#current_survey span").last().remove();
            nextq.remove();
            nextq = nextnextq;
          }
        }
        nextq.addClass("on");
        active.removeClass("on");

        // SHOW THE Q#
        var nextqnum = $("#current_survey span.current").next();
        $("#current_survey span.current").removeClass("current");
        nextqnum.addClass("current");
      }else{
        //NO MORE NEXT ONE, SO...CALL IT OVER AND REFRESH THE PAGE TO GET THE NEXT SURVEY?
        console.log("no next, so this be last one");

        //SUBMIT ALL THOSE HIDDEN FORMS NOW
        $("#current_question input[type='hidden']").each(function(){
          saveFormData($(this));
        });

        //SUBMIT AN ALL COMPLETE
        //REDIRECT TO HOME WITH A MESSAGE
        var dataURL         = "game.php?ajax=1&surveycomplete=1";
        var instrument_name = $("#current_question").attr("name");
        $.ajax({
          url:  dataURL,
          type:'POST',
          data: surveyhash,
          success:function(result){
            // console.log(result);
            location.href="index.php?survey_complete=" + instrument_name;
          }
        });
      }
    }else{
      console.log("No Answer Submitted.");
    }
    return false;
  });

  //SET UP THE FIRST PUZZLE AND LETTER TRAY
  makeGameBoard("An apple a day keeps the doctor away");
  makeLetterTray();

  //RESOLVING A GUESS
  $("#letterpicker").submit(function(){
    //CHECK IF GUESSES AVAILABLE
    var guesscount  = parseInt($("#guesscount b").text());
    
    if(guesscount > 0){
      //GUESS SUBMITTED, SO LOCK IN GUESS, DISABLE THE INPUT
      if($(this).find(".selected").length){
        //CHECK WHAT CURRENT POINT MULTIPLIER IS
        var pointmult     = parseInt($("#guessvalue b").text());
        var curpoints     = parseInt($("#totalpoints b").text());

        var letter_guess  = $(this).find(".selected").find("input").val();
        $(this).find(".selected").find("input").remove();
        $(this).find(".selected").addClass("picked");
        $(this).find("label").removeClass("selected");

        //NOW RESOLVE THE GUESS
        var letters_matched = $(".flip-container."+letter_guess).length;
        var points_earned   = ["A","E","I","O","U"].indexOf(letter_guess) > -1 ? letters_matched : letters_matched * pointmult;
        
        //DECREMENT GUESSES AND POINT MULTIPLIER
        guesscount--; 
        if(pointmult > 1){
          pointmult--;
        }
        $("#guesscount b").text(guesscount);
        $("#guessvalue b").text(pointmult);
        $("#totalpoints b").text(curpoints+points_earned);

        $(".flip-container."+letter_guess).addClass("rotate");
      }
    }else{
      alert("No guesses available, answer survey questions to get more guesses!");
    }
    return false;
  });
});
</script>
