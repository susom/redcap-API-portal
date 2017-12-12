function isEmpty(v){
  return v == null || v == undefined;
}

function showTCMScoring(all_answers, cb){
  var tcm_req = [
     ['tcm_energy','tcm_optimism','tcm_weight','tcm_stool','tcm_loosestool','tcm_stickystool']
    ,['tcm_energy','tcm_voice','tcm_panting','tcm_tranquility','tcm_colds','tcm_pasweat']
    ,['tcm_handsfeet_cold','tcm_cold_aversion','tcm_sensitive_cold','tcm_cold_tolerant','tcm_pain_eatingcold','tcm_sleepwell']
    ,['tcm_handsfeet_hot','tcm_face_hot','tcm_dryskin','tcm_dryeyes','tcm_constipated','tcm_drylips']
    ,['tcm_sleepy','tcm_sweat','tcm_oily_forehead','tcm_eyelid','tcm_snore','tcm_naturalenv']
    ,['tcm_frustrated','tcm_nose','tcm_acne','tcm_bitter','tcm_ribcage','tcm_scrotum']
    ,['tcm_forget','tcm_bruises_skin','tcm_capillary_cheek','tcm_complexion','tcm_darkcircles','tcm_bodyframe']
    ,['tcm_depressed','tcm_anxious','tcm_melancholy','tcm_scared','tcm_suspicious','tcm_breastpain']
    ,['tcm_sneeze','tcm_cough','tcm_allergies','tcm_hives','tcm_skin_red']
  ];

  var adapter = [];
  for(var name in all_answers){
    var value = all_answers[name];
    adapter.push({"name" : name, "value" : value}); 
  }

  var tcm_required_flat =  _.uniq(_.flatten(tcm_req));
  var user_answers      =  _.filter(adapter, function(obj){
    return obj.value !== "" && obj.value !== null;
  });
  var user_ans_flat = _.pluck(user_answers,"name");
  var compare       = _.intersection(user_ans_flat, tcm_required_flat);
  var difference    = _.difference(tcm_required_flat, compare);
  var dataURL       = "TCM_bodytype.php?&uselang="+uselang;
  $.ajax({
    url:  dataURL,
    type:'POST',
    data: "&tcm_answers=" + JSON.stringify(adapter),
    success:function(result){
      cb(result);
    }
  });
}

// this is core functionality to generate the numbers
$.fn.roundSlider.prototype.defaults.create = function () {
  var o = this.options;
  for (var i = o.min; i <= o.max; i += o.step) {
    var allValues = ["0 Best", "", "", "","", "", "","", "", "", "", "Average", "", "", "","", "", "","", "", "", "21 Worst"], val = allValues[i];
    var angle = this._valueToAngle(i);
    var numberTag = this._addSeperator(angle, "rs-custom");
    var number = numberTag.children();
    number.clone().css({ "width": o.width + this._border(), "margin-top": this._border(true) / -2 }).appendTo(numberTag);
    number.removeClass().addClass("rs-number").html(val).rsRotate(-angle);

    if (i == o.min) number.css("margin-left", "-35px");
    else if (i == o.max) number.css("margin-left", "-25px");
  }
}

function showSleepScoring(all_answers, cb){
  var dataURL     = "SLEEP_PSQI.php";
  
  var adapter     = [];
  for(var name in all_answers){
    var value = all_answers[name];
    adapter.push({"name" : name, "value" : value}); 
  }

  $.ajax({
    url:  dataURL,
    type:'POST',
    data: "&sleep=" + JSON.stringify(adapter),
    success:function(result){
      cb(result);
    }
  });
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
    PA_SCORE[1] = 0;
    PA_SCORE[2] = 0.27;
    PA_SCORE[3] = 0.36;
    PA_SCORE[4] = 0.77;
    PA_SCORE[5] = 1.22;
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

function showMETScoring(all_answers, cb){
  //GATHER ALL AND IF THEY ARE ALL FILLED OUT SHOW THE SCORE
  var age       = all_answers["met_age"];         
  var foot      = all_answers["met_height_ft"];   
  var inch      = all_answers["met_height_inch"]; 
  var weight    = all_answers["met_weigh_pound"]; 
  var height    = parseInt(foot)*12 + parseInt(inch);

  var bmi       = getBMI(weight, height);
  var gender    = all_answers["met_gender"]; 
  var ughgender = gender == 2 || gender == 4 ? "female" : "male";
  var isSmoker  = all_answers["met_smoker"]; 
  var PA_level  = all_answers["met_pa_level"]; 

  if(age > 0 && bmi > 0 && !isEmpty(gender) && !isEmpty(isSmoker) && !isEmpty(PA_level)) {
    var METScore    =  getMETScore(ughgender,age,bmi,isSmoker,PA_level);

    var dataURL         = "MET_detail.php?gender=" + ughgender + "&metscore=" + METScore + "&age=" + age +  "&uselang=" + uselang;
    $.ajax({
      url:  dataURL,
      type:'POST',
      data: null,
      success:function(result){
        var temp = $("<div>").html(result);
        temp.find("#met_score").text(METScore);

        //THE PROMPTS
        if(isSmoker == 1){
          temp.find("#met_smoking .yes").show();
        }else{
          temp.find("#met_smoking .no").show();
        }

        var pa_show = "pa_" + PA_level;
        temp.find("#met_pa ."+pa_show).show();

        if(age > 39 && age <= 59){
          temp.find("#met_aging, #met_aging .old").show();
        }else if(age > 59){
          temp.find("#met_aging, #met_aging .really_old").show();
        }else{
          temp.find("#met_aging").hide();
        }

        var a_fill_1 = Math.round((18.5/703) * Math.pow(height, 2));
        var a_fill_2 = Math.round((24.9/703) * Math.pow(height, 2));
        var a_fill_3 = Math.round(weight - a_fill_2);

        temp.find("#met_bmi .your_height").text(parseInt(foot) + "' " +  parseInt(inch) + "\"");
        temp.find("#met_bmi .your_weight").text(weight + " lb");
        temp.find("#met_bmi .your_bmi").text(bmi.toFixed(1));
        temp.find("#met_bmi .healthy_weight_min").text(a_fill_1 + " lb");
        temp.find("#met_bmi .healthy_weight_max").text(a_fill_2 + " lb");
        temp.find("#met_bmi .lose_weight").text(a_fill_3 + " lb");
        if(bmi <= 18.5){
          temp.find("#met_bmi .bmi_b").show();
        }else if(bmi > 18.5 && bmi <= 24.9){
          temp.find("#met_bmi .bmi_c").show();
        }else if(bmi > 25 && bmi <= 29.9){
          temp.find("#met_bmi .bmi_d").show();
        }else{
          //30+
          temp.find("#met_bmi .bmi_e").show();
        }

        var newinHTML = temp.html();
        cb(newinHTML);
      }
    });
  }
}

function customMET_BS(_this){
  if(_this.find("#met_results").length > 0){
    var reqmsg  = $("<div>").addClass("required_message alert alert-info").html("<ul><li>"+MET_DATA_DISCLAIM+"<li></ul>");
    reqmsg.append($("<button>").addClass("btn btn-alert").text("Close"));
    reqmsg.click(function(){
      $("#met_results").addClass("disclaimed");
    });
    $("body").append(reqmsg);
  }
  return;
}

function showGRITScoring(all_answers,cb){
  var adapter     = [];
  for(var name in all_answers){
    var value = all_answers[name];
    adapter.push({"name" : name, "value" : value}); 
  }

  var dataURL     = "GRIT_sisyphus.php";
  $.ajax({
    url:  dataURL,
    type:'POST',
    data: "&grit=" + JSON.stringify(adapter) + "&gender=" +  all_answers["core_gender"],
    success:function(result){
      cb(result);
    }
  });
}

function customGRIT_BS(_this){
  var closure_this  = _this;
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

function showMATScoring(all_answers,cb){
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

    for(var i in all_answers){
      //single input, stuff value into object 
      var fieldname = i;
      var fieldval  = all_answers[i];
      if(mat_map.hasOwnProperty(fieldname)){
        mat_map[fieldname]["value"] = fieldval;   
      }
    }

    // then ajax to compute the score
    var dataURL = "MAT_assessment.php";
    $.ajax({
      url:  dataURL,
      type:'POST',
      data: "&mat_answers=" + JSON.stringify(mat_map),
      success:function(result){
        console.log(result);
        var data = JSON.parse(result);
        cb(data);
      }
    });
}

function customMAT_BS(_this){
  var time = 2500;
  _this.find(".dead").each(function(){
    var closure_this = $(this);
    setTimeout( function(){ 
      closure_this.addClass("goGray"); 
    }, time);
    time += 500;
  })
  return;
}


