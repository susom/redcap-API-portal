$(document).ready(function(){
  //LAUNCH IT INITIALLY TO CHECK IF PAGE HAS BRANCHING
  checkGeneralBranching();

  function updateProgressBar(ref, perc){
    //UPDATE SURVEY PROGERSS BAR
    ref.attr("data-original-title",perc).css("width",perc);
    return;
  }

  function checkRequired(){
    //ANNOY USERS IF THEY DIDNT FILL OUT A FORM ITEM, PER SECTION!
    var required_fields = $("#customform section.active .required");
    var req_missing     = false;

    required_fields.each(function(){
      if( $(this).is(":visible") && (    ($(this).find(":input").is(':text')  && $(this).find(":input").val().length == 0)
          || ($(this).find(":input").is('select') && $(this).find(":input").val() == "-")
          || ($(this).find(":input").is(':radio') && $(this).find(":input:checked").length == 0) )
        ){
        //ONLY SHOW THE ANNOYING MESSAGE ONCE
        if( !$("#customform section.active").hasClass("annoying_message") ){
          req_missing = true;

          $("#customform section.active").addClass("annoying_message")
          var reqmsg  = $("<div>").addClass("required_message alert alert-danger").html("<ul><li>You have left some fields empty.  If this was intentional please click Submit/Next again or go back and provide the missing information.<li></ul>");
          reqmsg.append($("<button>").addClass("btn btn-alert").text("Close"));
          $("body").append(reqmsg);
          return;
        }
      }
    });                

    return req_missing;
  }

  function checkValidation(){
    var validation_choices  = [ "date" ,"email" ,"integer" ,"number" ,"phone" ,"time" ,"zipcode" ,"date_dmy", "date_mdy", "date_ymd", "datetime_dmy", "datetime_mdy", "datetime_ymd", "datetime_seconds_dmy" ,"datetime_seconds_mdy", "datetime_seconds_ymd" ];
    var verifyjs            = $("#customform section.active").find(".notifyjs-container");
    if(verifyjs.is(":visible")){
      return true;
    }

    return false;        
  }

  function saveFormData(elem){
    var dataURL = "survey.php?ajax=1";
    var for_branch_name = elem.prop("name");
    var for_branch_val  = elem.val();

    //FOR CHECKBOX TYPES
    if(elem.is(":checkbox")){
      //REDCAP SEES THESE DIFFERENTLY, MUST TEMPORARILY ALTER INPUT ATTRIBUTES TO SUBMIT PROPERLY
      var optioncode  = elem.val();
      for_branch_val  = optioncode;
      var oldname     = elem.prop("name");
      var chkbx_name  = oldname + "___" + optioncode;;
      var isChecked   = elem.is(":checked") ? 1 : 0;

      elem.prop("name", chkbx_name);
      elem.prop("checked",true);
      elem.val(isChecked);
    }

    if(!elem.val()){
      elem.val(null);
    }

    //NOW UPDATE THE INMEMORY COMPLETED THING AND RUN THE PAGE BRANCHING CHECK
    all_completed[for_branch_name] = for_branch_val;
    checkGeneralBranching();

    //CHECK PROJECT
    var project = "&project=" + $("#customform").data("project");
    $.ajax({
      url:  dataURL,
      type:'POST',
      data: elem.serialize() + project,
      success:function(result){
        // console.log("result from save:",result);

        if(elem.is(":checkbox")){
          //GOTTA RESET THE checkbox properties haha
          elem.prop("name",oldname);
          elem.val(optioncode);

          if(!isChecked){
            elem.prop("checked",false);
          }
        } 

        //REMOVE THE SPINNER
        setTimeout(function(){
          $(".hasLoading").removeClass("hasLoading");
        },450);
      }
    });
  }
  //SUBMIT/NEXT
  $("button[role='saverecord']").click(function(){
    $("#customform section.active").each(function(idx){
      //IF THERE IS ANOTHER SECTION THEN ITS A "NEXT" ACTION OTHERWISE, FINAL SUBMIT
      if(checkValidation()){
        return;
      }

      if(checkRequired()){
        return;
      }

      if($(this).next().length){
        $(".required_message").remove();
        if($(this).hasClass("active")){
          $(this).removeClass("active").addClass("inactive");

          $(this).next().addClass("active", function(){
            var panel_height  = $(this).height();
            $("#customform").height(panel_height);
            $(this).height(panel_height*2);
          });
          $("#customform").animate({ scrollTop : 0}, function(){});
          return false;
        }
      }else{
        //SUBMIT ALL THOSE HIDDEN FORMS NOW
        $("#customform input[type='hidden']").each(function(){
          saveFormData($(this));
        });

        //SUBMIT AN ALL COMPLETE
        //REDIRECT TO HOME WITH A MESSAGE
        var dataURL         = "survey.php?ajax=1&surveycomplete=1";
        var instrument_name = $("#customform").attr("name");
        var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
        $.ajax({
          url:  dataURL,
          type:'POST',
          data: surveyhash + project,
          success:function(result){
            location.href="index.php?survey_complete=" + instrument_name;
          }
        });
      }    
    });
    return false;
  });

  //INPUT CHANGE ACTIONS
  $("#customform :input").change(function(){
    // console.log($(this));
    //SAVE JUST THIS INPUTS DATA
    $(this).closest(".inputwrap").find(".q_label").addClass("hasLoading");
    
    saveFormData($(this));

    //IF CONDITIONS MET THEN SHOW
    if(isMET){
      showMETScoring();
    }
    if(isMAT){
      showMATScoring($(this));
    }
    if(isTCM){
      showTCMScoring();
    }

    //THE REST IS JUST FIGURING OUT THIS PROGRESS BAR
    var completed_count = 0;
    var total_questions = 0;
    for(var i in form_metadata){
      //UPDATE THE user_answer FIELD IN form_metadata
      if(form_metadata[i]["field_name"] == $(this).attr("name")){
        form_metadata[i]["user_answer"] = $(this).val();
      }

      //NOW DO A RUNNING COUNT
      if(form_metadata[i]["field_type"] !== "descriptive"){
        if(form_metadata[i]["branching_logic"] == ""){
          total_questions++;
        }

        if(form_metadata[i]["user_answer"] !== ""){
          completed_count++;
          if(form_metadata[i]["branching_logic"] !== ""){
            total_questions++;
          }
        }
      }
    }

    //IF THERES A NEXT QUESTION SCROLL DOWN TO IT!
    var nextEl  = $(this).closest(".inputwrap").nextAll(':visible:first');
    if(nextEl && !nextEl.is(".submits") && !nextEl.hasClass("LH")){
      var nextpos = nextEl.position();
      if(nextpos !== undefined && nextpos.top){
        var nexttop       = nextpos.top;
        $("#customform").animate({ scrollTop :  nexttop},350);
      }
    }

    //UPDATE THE PROGRESS BAR 
    var pbar              = $(".progress-bar");
    var percent_complete  = Math.round((completed_count/total_questions)*100,2) + "%";
    updateProgressBar(pbar, percent_complete);
    return;
  }); 

  //SET THE INTIAL PROGRESS BAR
  var pbar              = $(".progress-bar");
  var percent_complete  = Math.round((completed_count/total_questions)*100,2) + "%";
  updateProgressBar(pbar, percent_complete);

  //FIND THE PAGE OF THE LAST QUESTION SAVED AND JUMP TO THAT PANEL
  var answered_keys     = Object.keys(user_completed); 
  var last_answered     = answered_keys[completed_count - 1];
  var newactive         = $("div."+last_answered).closest("section");
  if(newactive.length){
    $("#customform section").removeClass("active");
    var panel         = $("#customform section").index(newactive);
    var panel_height  = newactive.height();
    $("#customform").height(panel_height);
    newactive.addClass("active").height(panel_height*2);
  }else{
    var panel_height  = $("#customform section").first().height();
    $("#customform").height(panel_height);
    $("#customform section").first().addClass("active").height(panel_height*2);
  }

  //CUSTOM SCORING FOR MET / MAT / TCM SURVEYS
  var mat_map = {
     "mat_walkonground"          : {"vid" : "Flat_NoRail_Slow" , "value" : null } 
    ,"mat_walkonground_fast"     : {"vid" : "Flat_NoRail_Fast" , "value" : null } 
    ,"mat_jogonground"           : {"vid" : "Flat_NoRail_Jog" , "value" : null } 
    ,"mat_walkincline_handrail"  : {"vid" : "Ramp_12Pcnt_Rail_Med" , "value" : null } 
    ,"mat_walkincline"           : {"vid" : "Ramp_12Pcnt_NoRail_Med" , "value" : null } 
    ,"mat_stepover_lowhurdle"    : {"vid" : "Walk_Hurdles_1" , "value" : null } 
    ,"mat_walkincline_tern"      : {"vid" : "Terrain_4" , "value" : null } 
    ,"mat_walkincline_tern_fast" : {"vid" : "Terrain_5" , "value" : null } 
    ,"mat_walkup3_handrail"      : {"vid" : "Stairs_3Step_1Foot_Rail_MedSlo2" , "value" : null } 
    ,"mat_walkdn3"               : {"vid" : "DownStairs_3Step_2Foot_NoRail_Slow" , "value" : null } 
    ,"mat_walkup3_carry"         : {"vid" : "Bag_Stairs_3Step_1Foot_NoRail_2_3" , "value" : null } 
    ,"mat_walkup9_carry"         : {"vid" : "TWObag_stairs_9step_1foot_norail" , "value" : null } 
  };

  var tcm_req = [
     ['tcm_energy','tcm_optimism','tcm_weight','tcm_stool','tcm_loosestool','tcm_stickystool']
    ,['tcm_energy','tcm_voice','tcm_panting','tcm_tranquility','tcm_colds','tcm_pasweat']
    ,['tcm_handsfeet_cold','tcm_cold_aversion','tcm_sensitive_cold','tcm_cold_tolerant','tcm_pain_eatingcold','tcm_sleepwell']
    ,['tcm_handsfeet_hot','tcm_face_hot','tcm_dryskin','tcm_dryeyes','tcm_constipated','tcm_drylips']
    ,['tcm_sleepy','tcm_sweat','tcm_oily_forehead','tcm_eyelid','tcm_snore','tcm_naturalenv']
    ,['tcm_frustrated','tcm_nose','tcm_acne','tcm_bitter','tcm_ribcage','tcm_scrotum']
    ,['tcm_forget','bruises_skin','tcm_capillary_cheek','tcm_complexion','tcm_darkcircles','tcm_bodyframe']
    ,['tcm_depressed','tcm_anxious','tcm_melancholy','tcm_scared','tcm_suspicious','tcm_breastpain']
    ,['tcm_sneeze','tcm_cough','tcm_allergies','tcm_hives','tcm_skin_red']
  ];
  var tcm_required_flat =  _.uniq(_.flatten(tcm_req));

  //CUSTOM WORK FOR MET AND MAT SURVEY
  if(isMAT){
    var initcheck = $("#customform").serializeArray();
    for(var i in initcheck){
      var fieldname = initcheck[i].name;
      var fieldval  = initcheck[i].value;
      if(mat_map.hasOwnProperty(fieldname)){
        mat_map[fieldname]["value"] = fieldval;
      }
    }    
    showMATScoring();
  }

  if(isMET){
    showMETScoring();
  }

  if(isTCM){
    showTCMScoring();
  }

  function getBMI(met_weight_pound, met_height_total_inch){
    var BMI = (met_weight_pound * 703)/(Math.pow(met_height_total_inch,2));
    return BMI;
  }

  function getMETScore(gender,age,bmi,isSmoker,PA_level){
    //HARD CONSTANTS
    PA_SCORE = [];
    if(gender == "male"){
      PA_SCORE[1] = 0;
      PA_SCORE[2] = 0.37;
      PA_SCORE[3] = 0.51;
      PA_SCORE[4] = 1.03;
      PA_SCORE[5] = 1.48;
    }else{
      PA_SCORE[1]   = 0;
      PA_SCORE[2]   = 0.27;
      PA_SCORE[3]   = 0.36;
      PA_SCORE[4]   = 0.77;
      PA_SCORE[5]   = 1.22;
    }
    phys_act_score = PA_SCORE[PA_level];
    
    //LINEAR WEIGHTs
    var x_age    = gender == "male" ? .16   : .10  ;
    var x_bmi    = gender == "male" ? .32   : .20  ;
    var x_smoker = gender == "male" ? .41   : .29  ;
    var x_const  = gender == "male" ? 17.26 : 12.77;

    var MetScore = (age*x_age) - .002*(Math.pow(age,2)) - (bmi*x_bmi) + phys_act_score - x_smoker*isSmoker + x_const;
    return Math.round(MetScore*100)/100;
  }

  function showMETScoring(){
    //GATHER ALL AND IF THEY ARE ALL FILLED OUT SHOW THE SCORE
    var age       = $('#met_age').val();

    var foot      = $('#met_height_ft :selected').val();
    var inch      = $('#met_height_inch :selected').val();
    var weight    = $('#met_weigh_pound :selected').val();
    var height    = parseInt(foot)*12 + parseInt(inch);

    var bmi       = getBMI(weight, height);
    var gender    = $('.met_gender input:checked').val();
    var ughgender = gender == 2 || gender == 4 ? "female" : "male";
    var isSmoker  = $('.met_smoker input:checked').val();
    var PA_level  = $('.met_pa_level input:checked').val();
    if(age > 0 && bmi > 0 && !isEmpty(gender) && !isEmpty(isSmoker) && !isEmpty(PA_level)) {
      var METScore    =  getMETScore(ughgender,age,bmi,isSmoker,PA_level);
      
      var dataURL         = "survey.php?met=1";
      var instrument_name = $("#customform").attr("name");
      var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
      $.ajax({
        url:  dataURL,
        type:'POST',
        data: project + "&met_score=" + METScore,
        success:function(result){

        }
      });

      var nextSection = $("#customform section:eq(1)");
      var dataURL         = "MET_detail.php?gender=" + ughgender + "&metscore=" + METScore + "&age=" + age;
      $.ajax({
        url:  dataURL,
        type:'POST',
        data: null,
        success:function(result){
          if($("#met_results").length > 0){
            $("#met_results").remove();
          }
          nextSection.find("h2").after(result);
          $("#met_desc").data("")
          $("#met_score").text(METScore);
        }
      });
    }
  }

  function showMATScoring(qinput){
    var mat_complete  = true;

    if(qinput){
      //single input, stuff value into object 
      var fieldname = qinput.attr("name");
      var fieldval  = qinput.val();
      if(mat_map.hasOwnProperty(fieldname)){
        mat_map[fieldname]["value"] = fieldval;   
      }
    }

    for(var prop in mat_map){
      //check to see if all the questions are complete
      if(!mat_map[prop]["value"]){
        mat_complete = false;
      }
    }

    if(mat_complete) {
      // then ajax to compute the score
      var dataURL         = "survey.php?mat=1";
      var instrument_name = $("#customform").attr("name");
      var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
      
      var nextSection = $("#customform section.active").next();
      $.ajax({
        url:  dataURL,
        type:'POST',
        data: project + "&mat_answers=" + JSON.stringify(mat_map),
        success:function(result){
          // console.log(result);
          var data      = JSON.parse(result);
          var matscore  = data.value;
          
          if(matscore < 40){
              var picperc = "sixsix";
              var desc = "In the next 4 years, 6.6 out of 10 people with your score are going to lose the ability to do active things they enjoy or value."
          }else if(matscore < 50){
              var picperc = "fivetwo";
              var desc = "In the next 4 years, 5.2 out of 10 people with your score are going to lose the ability to do active things they enjoy or value."
          }else if(matscore < 60){
              var picperc = "threefive";
              var desc = "In the next 4 years, 3.5 people out of 10 are going to lose the ability to do the active things they enjoy or value."
          }else{
              var picperc = "hundo";
              var desc = "Your functional capacity and physical mobility are excellent! Keep up the good work!"
          }

          if($("#mat_results").length > 0){
            $("#mat_results").remove();
          }
          var results     = $("<div id='mat_results'><div id='matscore'></div><div id='mat_pic'></div><div id='mat_text'</div>");
          nextSection.find("h2").after(results);

          $("#mat_pic, #mat_results").addClass(picperc);
          $("#mat_text").text(desc);
        }
      });
    }
  }

  function showTCMScoring(){
    var all_answers   = $("#customform").serializeArray();
    var user_answers  =  _.filter(all_answers, function(obj){
      return obj.value !== "" && obj.value !== null;
    });
    var user_ans_flat = _.pluck(user_answers,"name");
    var compare       = _.intersection(user_ans_flat, tcm_required_flat);
    var difference    = _.difference(tcm_required_flat, compare);
    if(!difference.length) {
      var nextSection = $("#customform section:last").prev();
      var dataURL     = "TCM_bodytype.php";
      $.ajax({
        url:  dataURL,
        type:'POST',
        data: "&tcm_answers=" + JSON.stringify($("#customform").serializeArray()),
        success:function(result){
          if($("#tcm_results").length > 0){
            $("#tcm_results").remove();
          }
          nextSection.find("h2").after(result);
        }
      });
    }else{
      // console.log(difference);
    }
  }

  function isEmpty(v){
    return v == null || v == undefined;
  }
});