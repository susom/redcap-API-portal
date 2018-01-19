$(document).ready(function(){
  //onload specially look for "..._start_ts"
  //ajax it and remove it
  $("#customform input[name$='start_ts']").each(function(){
    saveFormData($(this));
    $(this).remove();
  });

  //SUBMIT/NEXT
  $(".submits").on("click", "button[role='saverecord']" ,function(){
    $("#customform section.active").each(function(idx){
      //IF THERE IS ANOTHER SECTION THEN ITS A "NEXT" ACTION OTHERWISE, FINAL SUBMIT
      if(checkValidation()){
        return;
      }

      if(checkRequired()){
        return;
      } 

      checkGeneralBranching();

      if($(this).next().length){
        $(".required_message").remove();
        if($(this).hasClass("active")){
          $(this).removeClass("active").addClass("inactive");

          $(this).next().addClass("active", function(){
            var panel_height  = $(this).height();
            $("#customform").height(panel_height);
            $(this).height(panel_height*2);

            //THIS IS SOME BS (MAT CUSTOM RESULTS DISPLAY)
            customMAT_BS($(this));

            //THIS IS SOME REAL BS (GRIT CUSTOM RESULTS DISPLAY)
            customGRIT_BS($(this));

            //THIS IS MORE BS (MET CUSTOM POP UP BEFORE RESULTS)
            customMET_BS($(this));
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
        var next_instrument = $("#customform").data("next");
        var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
        $.ajax({
          url:  dataURL,
          type:'POST',
          data: surveyhash + project,
          success:function(result){
            var customIPAQSCORE = "";
            if(next_instrument){
              location.href="survey.php?sid=" + next_instrument + "&survey_complete=" + instrument_name;
            }else{
              location.href="index.php?survey_complete=" + instrument_name;
            }
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
    if(isGRIT){
      showGRITScoring();
    }
    if(isSleep){
      showSleepScoring();
    }
    if(isIPAQ){
      showIPAQScoring();
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
    // var nextEl  = $(this).closest(".inputwrap").nextAll(':visible:first');
    // if(nextEl && !nextEl.is(".submits") && !nextEl.hasClass("LH")){
    //   var nextpos = nextEl.position();
    //   if(nextpos !== undefined && nextpos.top){
    //     var nexttop       = nextpos.top;
    //     $("#customform").animate({ scrollTop :  nexttop},350);
    //   }
    // }

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
  if(answered_keys.hasOwnProperty(0)){
    var last_answered = answered_keys[completed_count - 1];
  }else{
    var last_answered = "inputwrap";
  }
  var newactive       = $("div."+last_answered).first().closest("section");

  if(newactive.length){
    $("#customform section").removeClass("active");
    var panel         = $("#customform section").index(newactive);
    var panel_height  = newactive.height();
    $("#customform").height(panel_height);
    newactive.addClass("active");
  }else{
    var panel_height  = $("#customform section").first().height();
    $("#customform").height(panel_height);
    $("#customform section").first().addClass("active");
  }
  if($("#customform section").length > 1){
    newactive.height(panel_height*2);
  }

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
  if(isGRIT){
    showGRITScoring();
  }
  if(isSleep){
    showSleepScoring();
  }
  if(isIPAQ){
    showIPAQScoring();
  }

  //BMI POPUP
  $("body").on("click","a.moreinfo",function(){
    var contentid = $(this).data("content");
    // var content   = $("#"+contentid);
    var content   = $(this).parent().next();
    content.slideDown("medium");
  });

  $("body").on("click","a.closeparent",function(){
    // close
    $(this).parent().slideUp("fast");
  });

  //OFFSITE LINKS 
  $("body").on("click","a[target='blank']",function(){
    var newin = openNewWindow($(this));
    return false;
  });

  $("body").on("click","a.offsite",function(e){
    e.preventDefault();
    var link  = $(this).attr("href");
    var newin = openNewWindow($(this),true);
    newin.document.write('<div style="text-align:center"><img src="../assets/img/well_logo.png"/></div>');
    newin.document.write('<h2 style="text-align:center; font-weight:500">You will be directed to your page on <br><b>'+link+'</b><br> in just a few seconds. The Well for Life dashboard is still open on the previous window.</h2>');
    newin.document.write('<p style="text-align:center;">You are now leaving a Stanford Medicine website and are going to a website that is not operated by any entity of Stanford University or Stanford Health Care. We are not responsible for the content or availability of linked sites. If you have any questions or concerns about the products, services, or content offered on linked third party websites, please contact the third party directly.</p>');
    setTimeout(function(){  
      newin.location.href = link;
    },5000);

    return false;
  });

  // this is core functionality to generate the numbers
  // $.fn.roundSlider.prototype.defaults.create = function () {
  //   var o = this.options;
  //   for (var i = o.min; i <= o.max; i += o.step) {
  //     var allValues = ["0 Best", "", "", "","", "", "","", "", "", "", "Average", "", "", "","", "", "","", "", "", "21 Worst"], val = allValues[i];
  //     var angle = this._valueToAngle(i);
  //     var numberTag = this._addSeperator(angle, "rs-custom");
  //     var number = numberTag.children();
  //     number.clone().css({ "width": o.width + this._border(), "margin-top": this._border(true) / -2 }).appendTo(numberTag);
  //     number.removeClass().addClass("rs-number").html(val).rsRotate(-angle);

  //     if (i == o.min) number.css("margin-left", "-35px");
  //     else if (i == o.max) number.css("margin-left", "-25px");
  //   }
  // }

  //for fFS
  $("#psqi_slider").ready(function(){
    // // select the target node
    var target = document.querySelector('#checkmutation');
     
    // create an observer instance
    var observer = new MutationObserver(function(mutations) {
        for(var i in mutations){
          var mutation = mutations[i];
          if(mutation.attributeName == "class"){
            var psqi_score = parseInt($("#psqi_score").text());
            setTimeout(function(){
              $("#psqi_slider").roundSlider({
                sliderType: "min-range",
                handleShape: "square",
                circleShape: "half-top",
                showTooltip: false,
                handleSize: 0,
                radius: 120,
                width: 14,
                min: 0,
                max: 21,
                value: psqi_score
              });
            },1000);
              
            // later, you can stop observing
            observer.disconnect();
            break;
          }
        }
    });
    // configuration of the observer:
    var config = { attributes: true, childList: true, characterData: true }
     
    // pass in the target node, as well as the observer options
    observer.observe(target, config);
  });
});

function openNewWindow(_this,offsite){
  var link      = offsite ? "" : _this.attr("href");
  var winwidth  = Math.round(.85 * $( window ).width() );
  var winheight = Math.round(.85 * $( window ).height() );

  var newwin    = window.open(link,'targetWindow','toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width='+winwidth+', height=' + winheight);
  return newwin;
}

function isEmpty(v){
  return v == null || v == undefined;
}

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
        var reqmsg  = $("<div>").addClass("required_message alert alert-danger").html("<ul><li>You have left some fields empty.  If this was intentional please click Submit/Next again or go back and provide the missing information.</li></ul>");
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
      console.log("result from save:",result);

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

function getIPAQScores(){
  console.log("sheieeeeee");
  var ipaq_job_vigorous_day       = parseInt($("#ipaq_job_vigorous_day :selected").val()) || 0;
  var ipaq_job_vigorous_hr        = parseInt($("#ipaq_job_vigorous_hr :selected").val())  || 0;
  var ipaq_job_vigorous_min       = parseInt($("#ipaq_job_vigorous_min :selected").val()) || 0;
  var ipaq_job_moderate_day       = parseInt($("#ipaq_job_moderate_day :selected").val()) || 0;
  var ipaq_job_moderate_hr        = parseInt($("#ipaq_job_moderate_hr :selected").val())  || 0;
  var ipaq_job_moderate_min       = parseInt($("#ipaq_job_moderate_min :selected").val()) || 0;
  var ipaq_job_walk_day           = parseInt($("#ipaq_job_walk_day :selected").val())     || 0;
  var ipaq_job_walk_day_hr        = parseInt($("#ipaq_job_walk_day_hr :selected").val())  || 0;
  var ipaq_job_walk_day_min       = parseInt($("#ipaq_job_walk_day_min :selected").val()) || 0;

  var walking_met                 = 3.3 * ipaq_job_walk_day * (ipaq_job_walk_day_hr*60 + ipaq_job_walk_day_min); //3.3 * walking minutes * walking days at work
  var moderate_met                = 4.0 * ipaq_job_moderate_day * (ipaq_job_moderate_hr*60 + ipaq_job_moderate_min); //4.0 * moderate-intensity activity minutes * moderate-intensity days at work
  var vigorous_met                = 8.0 * ipaq_job_vigorous_day * (ipaq_job_vigorous_hr*60 + ipaq_job_vigorous_min); //8.0 * vigorous-intensity activity minutes * vigorous-intensity days at work
  var total_work_met              = walking_met + moderate_met + vigorous_met; //sum of Walking + Moderate + Vigorous MET-minutes/week scores at work

  var ipaq_bike_day               = parseInt($("#ipaq_bike_day :selected").val()) || 0;
  var ipaq_bike_hr                = parseInt($("#ipaq_bike_hr :selected").val())  || 0;
  var ipaq_bike_min               = parseInt($("#ipaq_bike_min :selected").val()) || 0;
  var ipaq_walk_day               = parseInt($("#ipaq_walk_day :selected").val()) || 0;
  var ipaq_walk_hr                = parseInt($("#ipaq_walk_hr :selected").val())  || 0;
  var ipaq_walk_min               = parseInt($("#ipaq_walk_min :selected").val()) || 0;

  var walking_trans               = 3.3 * ipaq_walk_day * (ipaq_walk_hr*60 + ipaq_walk_min);
  var cycle_trans                 = 6.0 * ipaq_bike_day * (ipaq_bike_hr*60 + ipaq_bike_min);
  var total_trans                 = walking_trans + cycle_trans;

  var ipaq_vigorous_day           = parseInt($("#ipaq_vigorous_day :selected").val())       || 0;
  var ipaq_vigorous_hr            = parseInt($("#ipaq_vigorous_hr :selected").val())        || 0;
  var ipaq_vigorous_min           = parseInt($("#ipaq_vigorous_min :selected").val())       || 0;
  var ipaq_moderate_day           = parseInt($("#ipaq_moderate_day :selected").val())       || 0;
  var ipaq_moderate_hr            = parseInt($("#ipaq_moderate_hr :selected").val())        || 0;
  var ipaq_moderate_min           = parseInt($("#ipaq_moderate_min :selected").val())       || 0;
  var ipaq_moderate_other_day     = parseInt($("#ipaq_moderate_other_day :selected").val()) || 0;
  var ipaq_moderate_other_hr      = parseInt($("#ipaq_moderate_other_hr :selected").val())  || 0;
  var ipaq_moderate_other_min     = parseInt($("#ipaq_moderate_other_min :selected").val()) || 0;

  var domestic_vig                = 5.5 * ipaq_vigorous_day * (ipaq_vigorous_hr*60 + ipaq_vigorous_min);
  var domestic_mod                = 4 * ipaq_moderate_day * (ipaq_moderate_hr*60 + ipaq_moderate_min);
  var domestic_other              = 3 * ipaq_moderate_other_day * (ipaq_moderate_other_hr*60 + ipaq_moderate_other_min);
  var total_domestic              = domestic_vig + domestic_mod +  domestic_other;

  var ipaq_walk_leisure_day       = parseInt($("#ipaq_walk_leisure_day :selected").val())     || 0;
  var ipaq_walk_leisure_hr        = parseInt($("#ipaq_walk_leisure_hr :selected").val())      || 0;
  var ipaq_walk_leisure_min       = parseInt($("#ipaq_walk_leisure_min :selected").val())     || 0;
  var ipaq_moderate_leisure_day   = parseInt($("#ipaq_moderate_leisure_day :selected").val()) || 0;
  var ipaq_moderate_leisure_hr    = parseInt($("#ipaq_moderate_leisure_hr :selected").val())  || 0;
  var ipaq_moderate_leisure_min   = parseInt($("#ipaq_moderate_leisure_min :selected").val()) || 0;
  var ipaq_vigorous_leisure_day   = parseInt($("#ipaq_vigorous_leisure_day :selected").val()) || 0;
  var ipaq_vigorous_leisure_hr    = parseInt($("#ipaq_vigorous_leisure_hr :selected").val())  || 0;
  var ipaq_vigorous_leisure_min   = parseInt($("#ipaq_vigorous_leisure_min :selected").val()) || 0;

  var leisure_walk                = 3.3 * ipaq_walk_leisure_day * (ipaq_walk_leisure_hr*60 + ipaq_walk_leisure_min);
  var leisure_mod                 = 4 * ipaq_moderate_leisure_day * (ipaq_moderate_leisure_hr*60 + ipaq_moderate_leisure_min);
  var leisure_vig                 = 8 * ipaq_vigorous_leisure_day * (ipaq_vigorous_leisure_hr*60 + ipaq_vigorous_leisure_min);
  var total_leisure               = leisure_walk + leisure_mod +  leisure_vig;

  var total_walking               = walking_met + walking_trans + leisure_walk;
  var total_moderate              = moderate_met + domestic_mod + domestic_other + leisure_mod  + cycle_trans + domestic_vig;
  var total_vigorous              = vigorous_met + leisure_vig;
  var total_overall               = total_walking + total_moderate + total_vigorous;
  
  return {
     "ipaq_total_walking"  : Math.round(total_walking)
    ,"ipaq_total_moderate" : Math.round(total_moderate)
    ,"ipaq_total_vigorous" : Math.round(total_vigorous)
    ,"ipaq_total_overall"  : Math.round(total_overall)
  };
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

function customMET_BS(_this){
  if(_this.find("#met_results").length > 0){
    var reqmsg  = $("<div>").addClass("required_message alert alert-info").html("<ul><li>"+MET_DATA_DISCLAIM+"</li></ul>");
    reqmsg.append($("<button>").addClass("btn btn-alert").text("Close"));
    reqmsg.click(function(){
      $("#met_results").addClass("disclaimed");
    });
    $("body").append(reqmsg);
  }
  return;
}

function customMAT_BS(_this){
  var time = 3000;
  if(_this.find("#mat_results").length > 0){
    _this.find(".dead").each(function(){
      var closure_this = $(this);
      setTimeout( function(){ 
        closure_this.addClass("goGray"); 
      }, time);
      time += 500;
    })
  }
  return;
}

function customGRIT_BS(_this){
  var closure_this  = _this.find("#grit_results");
  var animtime      = closure_this.data("animation-time"); 
  if(closure_this.length > 0){
    setTimeout( function(){ 
        closure_this.addClass("pushing").addClass("animate", function(){
          setTimeout(function(){
            closure_this.removeClass("pushing");
            closure_this.addClass("showflags");
          }, animtime*1000);
        }); 
      }, 500);
  }
  return;
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
        // console.log(result);
      }
    });

    var nextSection = $("#customform section:eq(1)");
    var dataURL         = "MET_detail.php?gender=" + ughgender + "&metscore=" + METScore + "&age=" + age+"&uselang="+uselang;
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

        //THE PROMPTS
        if(isSmoker == 1){
          $("#met_smoking .yes").show();
        }else{
          $("#met_smoking .no").show();
        }

        var pa_show = "pa_" + PA_level;
        $("#met_pa ."+pa_show).show();

        if(age > 39 && age <= 59){
          $("#met_aging, #met_aging .old").show();
        }else if(age > 59){
          $("#met_aging, #met_aging .really_old").show();
        }else{
          $("#met_aging").hide();
        }

        var a_fill_1 = Math.round((18.5/703) * Math.pow(height, 2));
        var a_fill_2 = Math.round((24.9/703) * Math.pow(height, 2));
        var a_fill_3 = Math.round(weight - a_fill_2);

        $("#met_bmi .your_height").text(parseInt(foot) + "' " +  parseInt(inch) + "\"");
        $("#met_bmi .your_weight").text(weight + " lb");
        $("#met_bmi .your_bmi").text(bmi.toFixed(1));
        $("#met_bmi .healthy_weight_min").text(a_fill_1 + " lb");
        $("#met_bmi .healthy_weight_max").text(a_fill_2 + " lb");
        $("#met_bmi .lose_weight").text(a_fill_3 + " lb");
        if(bmi <= 18.5){
          $("#met_bmi .bmi_b").show();
        }else if(bmi > 18.5 && bmi <= 24.9){
          $("#met_bmi .bmi_c").show();
        }else if(bmi > 25 && bmi <= 29.9){
          $("#met_bmi .bmi_d").show();
        }else{
          //30+
          $("#met_bmi .bmi_e").show();
        }
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
    var nextSection = $("#customform section").last().prev().prev();
    
    // then ajax to compute the score
    var dataURL = "MAT_assessment.php";
    $.ajax({
      url:  dataURL,
      type:'POST',
      data: "&mat_answers=" + JSON.stringify(mat_map),
      success:function(result){
        var data = JSON.parse(result);
        var matscore  = data.value;

        if(matscore < 40){
            var picperc = 7;
            var desc = mat_score_desc[40];
        }else if(matscore < 50){
            var picperc = 5;
            var desc = mat_score_desc[50];
        }else if(matscore < 60){
            var picperc = 3;
            var desc = mat_score_desc[60];
        }else{
            var picperc = 0;
            var desc = mat_score_desc[70];
        }

        if(uselang == "sp"){
          $("#pa_info_sheet").attr("src","pa_info_sheet_sp.jpg");
        }


        if($("#mat_results").length > 0){
          $("#mat_results").remove();
        }
        var results     = $("<div id='mat_results'><div id='matscore'></div><div id='mat_pic'><ul><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li></ul></div><div id='mat_text'></div>");
        nextSection.find("h2").after(results);
        for(var i = 0;i < picperc; i++){
          $("#mat_pic li:eq("+i+")").addClass("dead");          
        }
        
        $("#mat_text").text(desc);
      }
    });

    // then ajax to compute the score
    var dataURL         = "survey.php?mat=1";
    var instrument_name = $("#customform").attr("name");
    var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
    $.ajax({
      url:  dataURL,
      type:'POST',
      data: project + "&mat_answers=" + JSON.stringify(mat_map),
      success:function(result){        
        //THIS JUST STORES IS 
      }
    },function(err){
      console.log("ERRROR");
      console.log(err);
    });
  }
}

function showTCMScoring(){
  var tcm_required_flat =  _.uniq(_.flatten(tcm_req));
  var all_answers       = $("#customform").serializeArray();
  var user_answers      =  _.filter(all_answers, function(obj){
    return obj.value !== "" && obj.value !== null;
  });
  var user_ans_flat = _.pluck(user_answers,"name");
  var compare       = _.intersection(user_ans_flat, tcm_required_flat);
  var difference    = _.difference(tcm_required_flat, compare);

  if($("#customform input[name='tcm_gender']:checked").val() !== "") {    
    var nextSection = $("#customform section:last").prev();
    var dataURL     = "TCM_bodytype.php?uselang="+uselang;

    $.ajax({
      url:  dataURL,
      type:'POST',
      data: "&tcm_answers=" + JSON.stringify($("#customform").serializeArray()),
      success:function(result){
        if($("#tcm_results").length > 0){
          $("#tcm_results").remove();
        }
        nextSection.find("h2").after(result);
        
        $(".constitution dt").click(function(){
          if($(this).next("dd").is(":visible")){
            $(this).next("dd").slideUp();
          }else{
            $(this).next("dd").slideDown();
          }
          return false;
        });
      }
    });
  }else{
  }
}

function showGRITScoring(){
  var all_answers = $("#customform").serializeArray();
  var nextSection = $("#customform section:last").prev();
  var dataURL     = "GRIT_sisyphus.php";

  $.ajax({
    url:  dataURL,
    type:'POST',
    data: "&grit=" + JSON.stringify($("#customform").serializeArray()) + "&gender=" +  all_completed["core_gender"],
    success:function(result){
      if($("#grit_results").length > 0){
        $("#grit_results").remove();
      }
      nextSection.find("h2").after(result);
    }
  });
}

function showSleepScoring(){
  var all_answers = $("#customform").serializeArray();
  var nextSection = $("#customform section:last").prev();
  nextSection.attr("id","checkmutation");

  var dataURL     = "SLEEP_PSQI.php";
  $.ajax({
    url:  dataURL,
    type:'POST',
    data: "&sleep=" + JSON.stringify(all_answers),
    success:function(result){
      var sleepTitle    = $("#checkmutation h2").clone();
      
      if($("#psqi_display_results").length){
        $("#psqi_display_results").empty();
        $("#psqi_display_results").append(result);
      }else{
        var psqi_container = $("<div id='psqi_display_results'>");
        nextSection.find("h2").after(psqi_container);
      }
      // $("#checkmutation").find("h2","#psqi_results").not(".submits").remove();
      // $("#checkmutation").prepend(sleepTitle);
      // $("#checkmutation #psqi_results").remove();
      // nextSection.find("h2").after(result);

      var PSQI_SCORE      = $("#psqi_score").text();
      var dataURL         = "survey.php?sleep=1";
      var instrument_name = $("#customform").attr("name");
      var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
      $.ajax({
        url:  dataURL,
        type:'POST',
        data: project + "&psqi_score=" + PSQI_SCORE,
        success:function(result){
          // console.log(result);
        }
      });
    }
  });
}

function showIPAQScoring(){
  var nextSection     = $("#customform section:last").prev();
  var ipaqScores      = getIPAQScores();
  
  var dataURL         = "survey.php";
  var instrument_name = $("#customform").attr("name");
  var project         = "&project=" + $("#customform").data("project") + "&sid=" + instrument_name ;
  var dataURL         = "survey.php?ipaq=1";
  $.ajax({
    url:  dataURL,
    type:'POST',
    data: project + "&ipaq_scores=" + JSON.stringify(ipaqScores),
    success:function(result){
      if($("#ipaq_results").length > 0){
        $("#ipaq_results").remove();
      }

      console.log(ipaqScores);
      var results   = $("<div>").attr("id","ipaq_results");
      var dl        = $("<dl>");
      // for(var i in ipaqScores){
      //   // var hiddeninput = $("<input>").attr("name",i).attr("id",i).attr("type","hidden").val(ipaqScores[i]);
      //   // $("#customform").append(hiddeninput);
      //   var tit = i.replace(/_/g," ").toUpperCase();
      //   var dt = $("<dt>").text(tit);
      //   var dd = $("<dd>").text(ipaqScores[i]);
      //   dl.append(dt);
      //   dl.append(dd);
      // }

      var success_msg  = "<h3>Your physical activity MET-minutes/week score is: <b>"+ipaqScores["ipaq_total_overall"]+"</b></h3>";
      results.append(success_msg);
      // results.append(dl);

      nextSection.find("h2").after(results);
    }
  });
  return;
}

// Find Left Boundry of the Screen/Monitor
function FindLeftScreenBoundry(){
    // Check if the window is off the primary monitor in a positive axis
    // X,Y                  X,Y                    S = Screen, W = Window
    // 0,0  ----------   1280,0  ----------
    //     |          |         |  ---     |
    //     |          |         | | W |    |
    //     |        S |         |  ---   S |
    //      ----------           ----------
    if (window.leftWindowBoundry() > window.screen.width)
    {
        return window.leftWindowBoundry() - (window.leftWindowBoundry() - window.screen.width);
    }

    // Check if the window is off the primary monitor in a negative axis
    // X,Y                  X,Y                    S = Screen, W = Window
    // 0,0  ----------  -1280,0  ----------
    //     |          |         |  ---     |
    //     |          |         | | W |    |
    //     |        S |         |  ---   S |
    //      ----------           ----------
    // This only works in Firefox at the moment due to a bug in Internet Explorer opening new windows into a negative axis
    // However, you can move opened windows into a negative axis as a workaround
    if (window.leftWindowBoundry() < 0 && window.leftWindowBoundry() > (window.screen.width * -1))
    {
        return (window.screen.width * -1);
    }

    // If neither of the above, the monitor is on the primary monitor whose's screen X should be 0
    return 0;
}

window.leftScreenBoundry = FindLeftScreenBoundry;
