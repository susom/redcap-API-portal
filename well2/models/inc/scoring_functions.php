<?php

function checkMinimumForShortScore($missing_data_keys){
  $skip_score = 0;
  foreach($missing_data_keys as $missing_key => $junk){
    switch($missing_key){
      //PURPOSE AND MEANING
      case "core_contribute_doing":
      case "core_satisfied_yourself":
      case "core_money_needs":
      case "core_religious_beliefs":
      case "core_engage_oppo":
      case "core_fitness_level":
        $skip_score++;
        break;
      case "core_important_energy":
      case "core_deal_whatever":
      case "core_joyful":
      case "core_worried":
        $skip_score = $skip_score + .5;
        break;
      case "core_lack_companionship":
      case "core_people_upset":
      case "core_energized_help":
        $skip_score = $skip_score + .333;
        break;
      case "core_lpaq":
      case "core_smoke_100":
      case "core_sleep_quality":
        $skip_score = $skip_score + .2;
        break;
      case "core_vegatables_intro_v2":
      case "core_sugar_intro_v2":
        $skip_score = $skip_score + .1;
        break;
    }
  }

  if( empty($missing_data_keys["core_bngdrink_female_freq"]) && empty($missing_data_keys["core_bngdrink_male_freq"]) ){
    $skip_score = $skip_score + .2;
  }

  if($skip_score > 3){
    return false;
  }
  return true;
}

function getShortScores($arm_answers){
  $scores = array();
  foreach($arm_answers as $arm => $answers){
    $scores[$arm] = getShortScore($answers);
  }
  return $scores;
}

function getShortScore($answers){
  // $answers = array_filter($answers);
  $score  = array();

  if(empty($answers)){
    return array();
  }

  //SOCIAL CONNECTEDNESS
  //
  $sc_a   = isset($answers["core_lack_companionship"]) ? 5/3 * ((6 - $answers["core_lack_companionship"])/5) : 0;
  $sc_b   = isset($answers["core_people_upset"]) ? 5/3 * ((6 - $answers["core_people_upset"])/5) : 0;
  $sc_c   = isset($answers["core_energized_help"]) ? 5/3 * ($answers["core_energized_help"]/5) : 0;
  $score["soc_con"] = $sc_a + $sc_b + $sc_c;


  if(isset($answers["core_vegatables_intro_v2"])){
	  //Lifestyle BEHAVIORS
	  $veg_ar = array(
	    1 => array(0,0,1),
	    2 => array(2,4,6),
	    3 => array(8,9,10,10)
	  );
	  $veg_score = 0;

	  if(isset($answers["core_vegatables_intro_v2"])){
	    $veg_a  = $answers["core_vegatables_intro_v2"];
	    $veg_b  = $answers["core_vegetables_intro_v2_" . $veg_a];
	    $veg_score = (($veg_ar[$veg_a][$veg_b])/10) * .5;
	  }
  }elseif(isset($answers["core_vegatables_intro"])){
  	  $veg_ar = array(
	    0 => 0,
	    1 => 8,
	    2 => 9,
	    3 => 9
	  );
	  $veg_score = $answers["core_vegatables_intro"] > 3 ? 10 : $veg_ar[$answers["core_vegatables_intro"]];
  }

  if(isset($answers["core_sugar_intro_v2"])){
	  $sugar_ar = array(
	    1 => array(10,9,8),
	    2 => array(6,4,1),
	    3 => array(0,0,0,0)
	  );
	  $sug_score = 0;
	  if(isset($answers["core_sugar_intro_v2"])){
	    $sug_a  = $answers["core_sugar_intro_v2"];
	    $sug_b  = $answers["core_sugar_intro_v2_" . $sug_a];
	    $sug_score = (($sugar_ar[$sug_a][$sug_b])/10) * .5;
	  }
  }elseif(isset($answers["core_sugar_intro"])){
	  $sug_score = $answers["core_sugar_intro"] == 0 ? 10 : 0;
  }

  $dietscore  = $veg_score + $sug_score;

  $smokescore = 0;
  if(isset($answers["core_smoke_100"])){
    $smokecfn = $answers["core_smoke_100"];
    $smok_frq = isset($answers["core_smoke_freq"]) ? $answers["core_smoke_freq"] : 0;
    if($smok_frq === 3){
      $smokecfn = 2;
    }
    $smokecfn++;
    $smokescore   = (4 - $smokecfn)/3;
  }
  
  $lpaqscore    = isset($answers["core_lpaq"]) ? $answers["core_lpaq"]/6 : 0;
  $slepscore    = isset($answers["core_sleep_quality"]) ? $answers["core_sleep_quality"]/4 : 0;

  $bng          = isset($answers["core_bngdrink_female_freq"]) ? $answers["core_bngdrink_female_freq"] : 0;
  $bng          = isset($answers["core_bngdrink_male_freq"]) ?  $answers["core_bngdrink_male_freq"] : $bng;
  $bng++;
  $bngscore     = (3 - $bng)/2;

  $score["lif_beh"] = $bngscore + $slepscore + $lpaqscore + $smokescore + $dietscore;

  //STRESS AND RESILIENCE
  $sr_a     = isset($answers["core_important_time"]) ? ((6 - $answers["core_important_time"])/5) * 2.5 : 0;
  $sr_b     = isset($answers["core_deal_whatever"]) ? ($answers["core_deal_whatever"]/5) * 2.5 : 0;
  $score["stress_res"]  = $sr_a + $sr_b;

  //EXPERIENCE OF EMOTIONS
  $eom_a    = isset($answers["core_joyful"]) ? ($answers["core_joyful"]/5) * 2.5 : 0;
  $eom_b    = isset($answers["core_worried"]) ? ((6 - $answers["core_worried"])/5) * 2.5 : 0;
  $score["exp_emo"]     = $eom_a + $eom_b;

  //PHYSICAL HEALTH
  $score["phys_health"] = isset($answers["core_fitness_level"]) ? $answers["core_fitness_level"] * (5/6) : 0;

  //PURPOSE AND MEANING
  $score["purp_mean"]   = isset($answers["core_contribute_doing"]) ? $answers["core_contribute_doing"] : 0;

  //SENSE OF SELF
  $score["sens_self"]   = isset($answers["core_satisfied_yourself"]) ? $answers["core_satisfied_yourself"] : 0;

  //FINANCIAL SECURITY/SATISFACTION
  $score["fin_sat"]     = isset($answers["core_money_needs"]) ? $answers["core_money_needs"] * (5/6) : 0;

  //SPIRITUALITY AND RELIGION
  $score["spirit_rel"]  = isset($answers["core_religious_beliefs"]) ? $answers["core_religious_beliefs"] : 0;

  //EXPLORATION AND CREATIVITY
  $score["exp_cre"]     = isset($answers["core_engage_oppo"]) ? $answers["core_engage_oppo"] : 0;

  return $score;
}

function printWELLComparison($eventarm, $user_score, $other_score){
  global $loggedInUser, $lang, $all_completed;

  $user_score       = !empty($user_score) ? round(array_sum($user_score)) : array();
  $user_score_txt   = !empty($user_score) ? $lang["USERS_SCORE"] . " : " . $user_score . "/50" : $lang["NOT_ENOUGH_USER_DATA"];
  $user_bar         = ($user_score*100)/70;

  $other_score      = !empty($other_score) ? round(array_sum($other_score)) : array();
  $other_score_txt  = !empty($other_score) ? $lang["OTHERS_SCORE"] . " : " . $other_score . "/50" : $lang["NOT_ENOUGH_OTHER_DATA"];
  $other_bar        = ($other_score*100)/70;
  
  // $armtime          = ucfirst(str_replace("_"," ",str_replace("_arm_1","",$eventarm)));
  //TODO , short arm uses diet_start_ts_v2, long arm uses your_feedback_ts?
  $armtime          = substr($all_completed["diet_start_ts_v2"],0,strpos($all_completed["diet_start_ts_v2"],"-"));
  
  echo "<div class='well_scores'>";
  echo "<div class='well_score user_score'><span style='width:$user_bar%'></span><b>$user_score_txt</b></div>";
  echo "<div class='well_score other_score'><span style='width:$other_bar%'></span><b>$other_score_txt</b></div>";
  echo "<h4>$armtime</h4>";  
  echo "</div>";
}

function printWELLOverTime($user_scores){
  global $loggedInUser, $lang;

  $year_css = "year";
  $arm_year = substr($loggedInUser->consent_ts,0,strpos($loggedInUser->consent_ts,"-"));

  echo "<div class='well_scores'>";
  foreach($user_scores as $arm => $score){
    $user_score       = !empty($score) ? round(array_sum($score)) : array();
    $user_score_txt   = !empty($user_score) ? ($user_score/50)*100 . "%" : $lang["NOT_ENOUGH_OTHER_DATA"];
    $user_bar         = !empty($user_score) ? ($user_score*100)/50 : "0%";
    echo "<div class='well_score user_score $year_css'><span style='width:$user_bar%'><i>$arm_year</i></span><b>$user_score_txt</b></div>";
    
    //TODO IS THIS OK?
    $year_css = $year_css . "x";
    $arm_year++;
  }
  echo "<div class='anchor'>
    <span class='zero'>0% (".$lang["LOWER_WELLBEING"].")</span>
    <span class='fifty'>50%</span>
    <span class='hundred'> (".$lang["HIGHER_WELLBEING"].") 100%</span>
  </div>";
  echo "</div>";
}

function getAvgWellScoreOthers($others_scores){
  $sum = 0;
  foreach($others_scores as $user){
    $sum = $sum + intval($user["well_score"]);
  }

  return round($sum/count($others_scores));
}

function getLongScores($domain_fields, $user_completed_fields){
  // 10 domains
  // Each domain counts for 10 points
  // Total score is 100

  $score  = array();
  foreach($domain_fields as $domain => $fields){
    switch($domain){
      case "Exploration and Creativity" :
      case "Spirituality and Religion" :
      case "Financial Security" :
        $denom = $domain == "Financial Security" ? 6 : 5;
        $field = array_pop($fields);
        $score[$domain] = 10*$user_completed_fields[$field]/$denom;
      break;
            
      case "Physical Health" :
        $domain_items = array();
        foreach($fields as $field){
          $denom          = $field == "core_fitness_level" ? 6 : 5;
          if($field == "core_interfere_life"){
            $domain_items[] = (6-$user_completed_fields[$field])/$denom;
          }else{
            $domain_items[] = $user_completed_fields[$field]/$denom;
          }
        }
        $score[$domain] = 2*array_sum($domain_items);
      break;
      
      case "Purpose and Meaning" :
      case "Sense of Self" :
        $domain_items = array();
        foreach($fields as $field){
          $denom = 5;
          $domain_items[] = $user_completed_fields[$field]/$denom;
        }
        $weight = $domain == "Sense of Self" ? 2 : 5;
        $score[$domain] = $weight*array_sum($domain_items);
      break;
      
      case "Experience of Emotions" :
        $domain_items = array();
        foreach($fields as $field){
          $denom = 5;
          if($field == "core_drained" || $field == "core_frustrated" || $field == "core_hopeless" || $field == "core_sad" || $field == "core_worried"){
            $domain_items[] = (6-$user_completed_fields[$field])/$denom;
          }else{
            $domain_items[] = $user_completed_fields[$field]/$denom;
          }
        }
        $score[$domain] = (10/11)*(array_sum($domain_items));
      break;
      
      case "Stress and Resilience" :
        $domain_items = array();
        foreach($fields as $field){
          $denom = 5;
          if($field == "core_important_time" || $field == "core_overwhelm_difficult" || $field == "core_important_energy"){
            $domain_items[] = (6-$user_completed_fields[$field])/$denom;
          }else{
            $domain_items[] = $user_completed_fields[$field];
          }
        }
        $score[$domain] = (10/14)*(array_sum($domain_items));
      break;
      
      case "Social Connectedness" :
        $domain_items = array();
        foreach($fields as $field){
          $denom = 5;
          if($field == "core_lack_companionship" || $field == "core_left_out" || $field == "core_isolated_others" || $field == "core_drained_helping" || $field == "core_people_upset" || $field == "core_meet_expectations"){
            $domain_items[] = (6-$user_completed_fields[$field])/$denom;
          }else{
            $domain_items[] = $user_completed_fields[$field]/$denom;
          }
        }
        $score[$domain] = (10/13)*(array_sum($domain_items));
      break;
      
      case "Lifestyle Behaviors" :
        $domain_items = array();

        //physical activity
        $domain_items[]         = 2*$user_completed_fields["core_lpaq"]/6;

        //sleep
        $core_sleep_total       = 60*$user_completed_fields["core_sleep_hh"] + $user_completed_fields["core_sleep_mm"];
        $core_fallasleep_min    = (8-$user_completed_fields["core_fallasleep_min"])/7;
        $core_fallasleep        = (6-$user_completed_fields["core_fallasleep"])/5;
        $core_wokeup_early      = (6-$user_completed_fields["core_wokeup_early"])/5;
        $core_wokeup_unrefresh  = (6-$user_completed_fields["core_wokeup_unrefresh"])/5;
        $domain_items[]         = 0; 
        
        //diet
        $diet_score = array();
        if(isset($user_completed_fields["core_veg_intro_v2"])){
          $temp_ar = array(
            1 => array(0,0,1),
            2 => array(2,4,6),
            3 => array(8,9,10,10)
          );
          $primary_var    = "core_veg_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_fruit_intro_v2"])){
          $temp_ar = array(
            1 => array(0,0,1),
            2 => array(2,4,6),
            3 => array(8,10,10,10)
          );
          $primary_var    = "core_fruit_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_grain_intro_v2"])){
          $temp_ar = array(
            1 => array(0,0,1),
            2 => array(2,4,6),
            3 => array(8,10,10,8)
          );
          $primary_var    = "core_grain_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_bean_intro_v2"])){
          $temp_ar = array(
            1 => array(0,1,2),
            2 => array(4,6,8),
            3 => array(9,10,10,10)
          );
          $primary_var    = "core_bean_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_sweet_intro_v2"])){
          $temp_ar = array(
            1 => array(10,9,8),
            2 => array(6,4,1),
            3 => array(0,0,0,0)
          );
          $primary_var    = "core_sweet_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_meat_intro_v2"])){
          $temp_ar = array(
            1 => array(10,10,8),
            2 => array(6,4,2),
            3 => array(1,0,0,0)
          );
          $primary_var    = "core_meat_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_nuts_intro_v2"])){
          $temp_ar = array(
            1 => array(0,1,2),
            2 => array(4,6,8),
            3 => array(10,10,8,6)
          );
          $primary_var    = "core_nuts_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_sodium_intro_v2"])){
          $temp_ar = array(
            1 => array(10,9,9),
            2 => array(6,4,2),
            3 => array(1,0,0,0)
          );
          $primary_var    = "core_sodium_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_sugar_intro_v2"])){
          $temp_ar = array(
            1 => array(10,9,8),
            2 => array(6,4,1),
            3 => array(0,0,0,0)
          );
          $primary_var    = "core_sugar_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_fish_intro_v2"])){
          $temp_ar = array(
            1 => array(0,4,7),
            2 => array(10,10,10),
            3 => array(10,10,10,10)
          );
          $primary_var    = "core_fish_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_cook_intro_v2"])){
          $temp_ar = array(
            1 => array(0,1,1),
            2 => array(2,4,6),
            3 => array(8,10,10,10)
          );
          $primary_var    = "core_cook_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }

        if(isset($user_completed_fields["core_fastfood_intro_v2"])){
          $temp_ar = array(
            1 => array(10,8,5),
            2 => array(2,0,0),
            3 => array(0,0,0,0)
          );
          $primary_var    = "core_fastfood_intro_v2";
          $secondary_var  = $primary_var . "_" . $user_completed_fields[$primary_var];
          $diet_score[$secondary_var]   = isset($user_completed_fields[$secondary_var]) ? $temp_ar[$user_completed_fields[$primary_var]][$user_completed_fields[$secondary_var]] : 0;
        }
        $domain_items[] = array_sum($diet_score)/60;

        //alchohol
        $domain_items[] = ((isset($user_completed_fields["core_bngdrink_male_freq"]) && $user_completed_fields["core_bngdrink_male_freq"] == 1) || (isset($user_completed_fields["core_bngdrink_female_freq"]) && $user_completed_fields["core_bngdrink_female_freq"]) ) ? 0 : 2;

        //smoking
        $domain_items[] = ( $user_completed_fields["core_smoke_100"] == 0 || ($user_completed_fields["core_smoke_100"] == 1 && $user_completed_fields["core_smoke_freq"] == 1) ) ? 2 : 0;
        $score[$domain] = array_sum($domain_items);
      break;
    }
  }
  return $score;
}

function checkMinimumForLongScore(){
  // Missing 30% more items in a domain/or subdomain -> no domain score
  // Missing any domain score -> no SWLS score
}


