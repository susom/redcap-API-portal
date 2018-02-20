<?php 
require_once("models/config.php"); 
include("models/inc/checklogin.php");

$navon          = array("home" => "", "reports" => "", "game" => "");
$nav            = isset($_REQUEST["nav"]) ? $_REQUEST["nav"] : "home";
$navon[$nav]   = "on";
$API_URL        = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN      = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];

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

$active_survey = null;
// foreach($surveys as $survey){
//   if($survey["survey_complete"]){
//     continue;
//   }else{
//     $survey_data    = $surveys[$survey["instrument_name"]];
//     //LOAD UP THE SURVEY PRINTER HERE
//     $active_survey  = new Survey($survey_data);

//     //ON SURVEY PAGE STORE THIS FOR USE WITH THE AJAX EVENTS 
//     $_SESSION[SESSION_NAME]["survey_context"] = array("event" => $survey_data["event"]);
//     break;
//   }
// }


$pageTitle = "Well v2 Game";
$bodyClass = "game";
include_once("models/inc/gl_head.php");
?>
    <div class="main-container">
        <div class="main wrapper clearfix">
            <article>
                <div id="wof_game">
                  <h1 class="title" title="Well Being Paradise!">
                    <span></span>
                    <div class="stats">
                      <div id="guesscount">
                        <h4>Spins</h4>
                        <b>0</b> 
                        <i>Answer questions to get more spins</i>
                      </div>
                      <div id="totalpoints">
                        <h4>Total Prize</h4>
                        <b>0</b>
                      </div>
                      <div id="solved">
                        <h4>Puzzles Solved</h4>
                        <b>0</b>
                      </div>
                    </div>
                  </h1>
                  
                  <?php
                  if($active_survey || 1){
                  ?>
                  <!-- <div id="survey_series" class="col-sm-6">
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
                        // $active_survey->printGameHTML();
                      ?>
                    </div>
                  </div> -->

                  <div id="gameboard" class="col-sm-6">
                    <div id="board">
                      <i>A clue about the puzzle?</i>
                      <a href="#" id="solveit" class="btn btn-success">I'd like to solve the puzzle!</a>
                      <a href="#" id="newgame" class="btn btn-success">Start New Game</a>
                    </div>
                    <div id="bigwheel" class="centered">
                      <div id="status_label">loading...</div>
                      <canvas id="drawing_canvas"></canvas>
                    </div>
                    <form id="letterpicker">
                    </form>
                  </div>
                  <?php
                  }else{
                    echo "<h3>You don't have any surveys to take at the moment.</h3>"  ;                      
                  } 
                  ?>
                </div>
            </article>
        </div> <!-- #main -->
    </div> <!-- #main-container -->
<?php 
include_once("models/inc/gl_foot.php");
?>
<script src="//cdnjs.cloudflare.com/ajax/libs/p2.js/0.6.0/p2.min.js"></script>
<script>
  var surveyhash = '<?php echo !empty($active_survey) ? $active_survey->hash["hash"] : "" ?>';
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
  
  window.activepuzzle;
  function makeGameBoard(secretmessage){
    window.activepuzzle = secretmessage;
    var letters_per_row = 10;
    var msglen          = secretmessage.length;
    var rows            = Math.ceil(msglen/letters_per_row);
    var filler          = (rows*letters_per_row) - msglen;

    //remove old gameboard if there
    $("#flipboard").remove();

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
    // for(var i = 0; i < filler; i++){
    //   var fc = $("<div class='flip-container'></div>");
    //   var fp = $("<div class='flipper'></div>");
    //   var fr = $("<div class='front'></div>");
    //   var ba = $("<div class='back'></div>");
    //   gameboard.append(fc);
    // }

    $("#board").prepend(gameboard);

    $("#guesscount b").text(1000);
    $("#guessvalue b").text(10);
    return;
  }

  function makeLetterTray(){
    var runes   = ["B","C","D","F","G","H","J","K","L","M","N","P","Q","R","S","T","V","W","X","Y","Z"];
    var vowels  = ["A","E","I","O","U"];
    
    //remove existing old trays.
    $("#lettertray,#voweltray").remove();

    var tray    = $("<div id='lettertray'><h4>Pick a Letter and press the 'Guess Letter' Button</h4><h5 id='guessvalue'>Each matching letter will be worth <b>10</b> points</h5></div>");
    for(var i in runes){
      var label   = $("<label>"+runes[i]+"</label>");
      var letter  = $("<input type='checkbox' name='letters' value='"+runes[i]+"'>");
      
      var btn     = $("button[clicked=true]").attr("id");
     
      //MAKING A GUESS
      letter.change(function(){
        if($(this).is(":checked")){
          //REMOVE "SELECTED" CLASS FROM ANY SIBLINGS (NOT ALREADY BURNED)
          
          // if(btn == "solveit"){
          //   if(!$(this).hasClass("picked")){
          //     $(this).find(":input").attr("checked",false);
          //   }
          // }else{
            $(this).parent().siblings().removeClass(function() {
              if(!$(this).hasClass("picked")){
                $(this).find(":input").attr("checked",false);
                return $( this ).attr( "class" );
              }
            });
          // }
          
          $(this).parent().addClass("selected");
        }else{
          $(this).parent().removeClass("selected");
        }  
        return false;
      });

      label.append(letter);
      tray.append(label);
    }
    $("#letterpicker").append(tray);
    tray.append($("<button id='pickit' class='btn btn-info'>Guess Letter</button>"));
    $("#letterpicker").append($("<h2>OR</h2>"));

    var tray    = $("<div id='voweltray'><h4>Pick a Vowel and press the 'Buy Vowel' Button</h4><h5>Reveal all matching vowels for a flat cost of -10 points</h5></div>");
    for(var i in vowels){
      var label   = $("<label>"+vowels[i]+"</label>");
      var letter  = $("<input type='checkbox' name='vowels' value='"+vowels[i]+"'>");
      
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
    $("#letterpicker").append(tray);
    tray.append($("<button id='buyit' class='btn btn-info'>Buy a Vowel</button>"));
    return;
  }

  function resetLetters(){
    $(".picked").each(function(){
      var el      = $(this);
      var letter  = el.text();
      el.append($("<input type='checkbox' name='letters' value='"+letter+"'/>"));
      el.removeClass("picked");
    });

    return;
  }

  function revealPuzzle(_cb){
    var elems = count = $(".flip-container").length;

    $(".flip-container").each(function(){
      var el = $(this);
      setTimeout(function(){
        el.addClass("rotate");
      },500);

      if (!--count){
        setTimeout(_cb,3000);
      };
    });
    
  }

  function newGame(phrase){
    makeGameBoard(phrase);
    makeLetterTray();
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
    var phrasing = [
       "An apple a day keeps the doctor away"
      ,"Laughter is the best medicine"
      ,"Let food be thy medicine, and medicine be thy food"
    ];
    
    var rando    = Math.floor(Math.random() * phrasing.length);
    newGame(phrasing[rando]);
    phrasing.splice(rando,1);

    $("#letterpicker button.btn").click(function() {
        $("#letterpicker button.btn").removeAttr("clicked");
        $(this).attr("clicked", "true");
    });

    window.wheelSpun = false;
    //RESOLVING A GUESS
    $("#letterpicker").submit(function(event){
      var btn = $("button[clicked=true]").attr("id"); 

      //CHECK IF GUESSES AVAILABLE
      var guesscount  = parseInt($("#guesscount b").text()); 
      if(guesscount > 0){


        //GUESS SUBMITTED, SO LOCK IN GUESS, DISABLE THE INPUT
        if($(this).find(".selected").length){
          $(this).find(".selected").each(function(el){

            var el = $(this);

            //CHECK WHAT CURRENT POINT MULTIPLIER IS
            var pointmult     = parseInt($("#guessvalue b").text());
            var curpoints     = parseInt($("#totalpoints b").text());

            var letter_guess  = el.find("input").val();
            el.removeClass("selected");
            el.find("input").attr("checked",false);

            //NOW RESOLVE THE GUESS
            var letters_matched = $(".flip-container."+letter_guess).length;
            var points_earned   = ["A","E","I","O","U"].indexOf(letter_guess) > -1 ? letters_matched : letters_matched * pointmult;

            if(["A","E","I","O","U"].indexOf(letter_guess) > -1){
              if(curpoints < 250){
                alert("You don't have enough to buy a vowel");
                return false;
              }
              //vowels should cost
              var points_earned = -250;
            }else{
              if(!window.wheelSpun){
                alert("Spin the Wheel First!");
                return false;
              }
              if(letters_matched){
                PlaySound("Ding.mp3");
              }else{
                PlaySound("Buzzer.mp3");
              }
              var points_earned = letters_matched * pointmult;
            }

            el.find("input").remove();
            el.addClass("picked");
            //DECREMENT GUESSES
            guesscount--; 
            
            $("#guesscount b").text(guesscount);
            $("#guessvalue b").text(pointmult);
            $("#lettertray h5 b").text(pointmult);
            $("#totalpoints b").text(curpoints+points_earned);

            $(".flip-container."+letter_guess).addClass("rotate");
            window.wheelSpun = false;
          });
        }else{
          alert("Pick a Letter First");
        }
      }else{
        alert("No guesses available, answer survey questions to get more guesses!");
      }
      return false;
    });

    $("#solveit").click(function(){
      var solve = prompt("Solve the Puzzle!");
      if (solve.toLowerCase() == window.activepuzzle.toLowerCase()) {
          PlaySound("solved.mp3");
          spawnPartices();
          statusLabel.innerHTML = 'You\'ve Solved the Puzzle!';

          var solved = parseInt($("#solved b").text() );
          solved++;
          $("#solved b").text(solved);

          revealPuzzle(function(){
            resetLetters();
          });
      }else{
          PlaySound("Buzzer.mp3");
          statusLabel.innerHTML = 'Sorry, that is not the right answer.';
      }
      return false;
    });

    $("#newgame").click(function(){
      var rando    = Math.floor(Math.random() * phrasing.length);
      newGame(phrasing[rando]);
      phrasing.splice(rando,1);
      return false;
    });
  });
</script>
<link rel="stylesheet" type="text/css" href="assets/css/wheel_of_fortune.css">
<script src="assets/js/wheel_of_fortune.js"></script>
<script>
const TWO_PI  = Math.PI * 2;
const HALF_PI = Math.PI * 0.5;

var colors    = [ "#000000" 
                , "#C080FF"
                , "#FFFF00"
                , "#00C0FF"
                , "#ffffff"
                , "#FF0000"
                , "#C080FF"
                , "#FF80E0"
                , "#00FF00"
                , "#FFC000"
                , "#DFDFDF"
                , "#C080FF"
                , "#00FF00"
                , "#FFFF00"
                , "#FF0000"
                , "#00C0FF"
                , "#FFC000"
                , "#C080FF"
                , "#FFFF00"
                , "#FF80E0"
                , "#FF0000"
                , "#00C0FF"
                , "#00FF00"
                , "#FF80E0"
                ];

var points    = [ "Bankrupt"
                , 100
                , 200
                , 300
                , 400
                , 500
                , 600
                , 700
                , 800
                , 900
                , 1000
                // , 1100
                // , 1200
                // , 1300
                // , 1400
                // , 1500
                // , 170
                // , 18
                // , 19
                // , 20
                // , 21
                // , 22
                // , 23
                ];

// canvas settings
var viewWidth     = 430,
    viewHeight    = 430,
    viewCenterX   = viewWidth * 0.5,
    viewCenterY   = viewHeight * 0.5,
    drawingCanvas = document.getElementById("drawing_canvas"),
    ctx,
    timeStep      = (1/60),
    time          = 0;

var ppm             = 24, // pixels per meter
    physicsWidth    = viewWidth  / ppm,
    physicsHeight   = viewHeight / ppm,
    physicsCenterX  = physicsWidth  * 0.5,
    physicsCenterY  = physicsHeight * 0.5;

var world;

var wheel,
    arrow,
    mouseBody,
    mouseConstraint;

var arrowMaterial,
    pinMaterial,
    contactMaterial;

var wheelSpinning = false,
    wheelStopped  = true;

var particles     = [];

var statusLabel   = document.getElementById('status_label');

window.onload = function() {
    initDrawingCanvas();
    initPhysics();
    requestAnimationFrame(loop);
    statusLabel.innerHTML = 'Click or Drag to give it a good spin!';
};
</script>