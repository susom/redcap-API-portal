<?php
require_once("models/config.php");
$lang_req     = isset($_GET["lang"]) ? "?lang=".$_GET["lang"] : "";
$pg_title     = "$websiteName";
$body_classes = "cms";


if(!empty($_POST) && isset($_POST["action"]) && $_POST["action"] == "newevent"){
  unset($_POST["submit"]);
  unset($_POST["action"]);

  //import the record
  $API_URL    = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
  $API_TOKEN  = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];
  
  $ts         = date('Y-m-d H:i:s');
  $data = array(
       "well_cms_create_ts" => $ts
      ,"well_cms_update_ts" => $ts
      ,"id" => "whatever_required_but_wont_be_used"
    );

  foreach($_POST as $key => $val){
    $data[$key] = $val;
  }
  $result = RC::writeToApi($data, array("forceAutoNumber" => "true", "returnContent" => "auto_ids", "overwriteBehavior" => "overwite", "type" => "flat"), $API_URL, $API_TOKEN);
  $split  = explode(",",$result[0]);
  $new_id = $split[0];

  //import the picture file
  $file = (function_exists('curl_file_create') ? curl_file_create($_FILES["well_cms_pic"]["name"],$_FILES["well_cms_pic"]["type"],$_FILES["well_cms_pic"]["tmp_name"]) : "@". realpath($_FILES["well_cms_pic"]["tmp_name"]));
  $data = array(
       "record"       => $new_id
      ,"field_name"   => 'well_cms_pic'
      ,"action"       => "import"
      ,"content"      => "file"
      ,"file"         => $file
    );
  $result = RC::writeToApi($data, array("overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
}




include("models/inc/gl_header.php");

// DEFAULT VALUES
$loc          = isset($_GET["loc"]) ? $_GET["loc"] : "1";
$cat          = isset($_GET["cat"]) ? $_GET["cat"] : "1";

$types        = array(0 => "Events", 1 => "Monthly Goals", 99 => "Others");
$locs         = array(1 => "US", 2 => "Taiwan");
?>
<div id="content" class="container" role="main" tabindex="0">
  <div class="row"> 
    <div id="main-content" class="col-md-12" role="main">
      <div id="cms" class="well">
        <h2>WELL Portal Admin CMS</h2>
        <select id='loc'>
          <option value=1 <?php if($loc == 1) echo "selected"?>>US</option>
          <option value=2 <?php if($loc == 2) echo "selected"?>>Taiwan</option>
        </select>
        
        <ul id="folders">
          <li><a href="?cat=1"  data-val=1 class="<?php if($cat == 1) echo "on"?>"><?php echo $types[1] ?></a></li>
          <li><a href="?cat=0"  data-val=0 class="<?php if($cat == 0) echo "on"?>"><?php echo $types[0] ?></a></li>
          <li><a href="?cat=99" data-val=99 class="<?php if($cat == 99) echo "on"?>"><?php echo $types[99] ?></a></li>
        </ul>
        <?php
        $api_url      = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
        $api_token    = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];
        $extra_params = array(
          'content'   => 'metadata',
          'format'    => 'json'
        );
        $results      = RC::callApi($extra_params, true, $api_url, $api_token); 
        $fields       = array_column($results, 'field_name'); 
        $labels       = array_column($results, 'field_label'); 
        
        $mon_display  = array(3,5,4,8,11);
        $evt_display  = array(3,4,5,6,8,9,11);
        
        $extra_params = array(
          'content'   => 'record',
          'format'    => 'json'
        );
        $filterlogic  = array();
        if($loc){
          $filterlogic[] = '[well_cms_loc] = "'.$loc.'"';
        }
        if($cat || $cat === "0"){
          $filterlogic[] = '[well_cms_catagory] = "'.$cat.'"';
        }
        if(count($filterlogic)){
          $extra_params["filterLogic"] = implode(" and ", $filterlogic);
        }
        $events       = RC::callApi($extra_params, true, $api_url, $api_token); 
        ?>
        <table id="ed_items">
          <thead>
            <tr>
              <?php
              $display = $cat == 0 ? $evt_display : $mon_display;
              foreach($display as $item){
                echo "<th>".$labels[$item]."</th>\n";
              }
              ?>
            <th class='actions'></th>
            </tr> 
          </thead>
          <tbody>
              <form id="edit" method="post">
              <?php
              $trs = array();
              foreach($events as $event){
                $trs[] = "<tr>";
                
                if($cat == 0){
                  $trs[] = "<td>".$event["well_cms_subject"]."</td>";
                  $trs[] = "<td>".$event["well_cms_content"]."</td>";
                  $trs[] = "<td>".$event["well_cms_pic"]."</td>";
                  $trs[] = "<td>".$event["well_cms_event_link"]."</td>";
                  $trs[] = "<td>".$event["well_cms_active"]."</td>";
                  $trs[] = "<td>".$event["well_cms_displayord"]."</td>";
                  $trs[] = "<td>".$event["well_cms_update_ts"]."</td>";
                  $trs[] = "<td>buttons</td>";
                }else{
                  $trs[] = "<td>".$event["well_cms_subject"]."</td>";
                  $trs[] = "<td>".$event["well_cms_pic"]."</td>";
                  $trs[] = "<td>".$event["well_cms_content"]."</td>";
                  $trs[] = "<td>".$event["well_cms_active"]."</td>";
                  $trs[] = "<td>".$event["well_cms_update_ts"]."</td>";
                  $trs[] = "<td>buttons</td>";
                }
                $trs[] = "</tr>";
              }
              echo implode("\n",$trs);
              ?>
              </form>
              <tr class="addnew">
              <td colspan="<?php echo count($display) + 1 ?>">
                <a id="additem" href="#">+ add item to <?php echo $types[$cat] ?></a>
                
                <form id="newevent" action="cms.php" method="post" enctype="multipart/form-data">
                  <input type="hidden" name="action" value="newevent"/>
                  <input type="hidden" name="well_cms_loc" value="<?php echo $loc?>"/>
                  <input type="hidden" name="well_cms_catagory" value="<?php echo $cat?>"/>
                  <fieldset>
                  <h3>New <?php echo substr($types[$cat],0,strlen($types[$cat]) - 1) ?> for <?php echo $locs[$loc] ?></h3>
                  <?php
                  $fields     = array();
                  $new_fields = $display;
                  array_pop($new_fields);
                  foreach($new_fields as $idx){
                    $field = $results[$idx];
                    $label = $field["field_label"];
                    $varid = $field["field_name"];
                    $type  = $field["field_type"];
                    $setan = $field["select_choices_or_calculations"];
                    // https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css
                    // https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js

                    $fields[] = "<div class='newevent_item'>";
                    switch($type){
                      case "dropdown":
                        $split = explode("|",$setan);
                        
                        $fields[] = "<label name='$varid'>";
                        $fields[] = "<span>$label</span>";
                        $fields[] = "<select name='$varid'>";
                        foreach($split as $pair){
                          $value_label  = explode(", ",$pair);
                          $key          = trim($value_label[0]); 
                          $val          = trim($value_label[1]); 
                          $fields[]     = "<option value='".$key."' $selected>".$val."</option>";
                        }
                        $fields[] = "</select>";
                        $fields[] = "</label>";
                      break;

                      case "truefalse":
                        $fields[] = "<label name='$varid'>";
                        $fields[] = "<span>$label</span>";
                        $fields[] = "<em><input name='$varid' type='radio' checked value='1'> Yes</em>";
                        $fields[] = "<em><input name='$varid' type='radio' value='0'> No</em>";
                        $fields[] = "</label>";
                      break;

                      case "notes":
                        $fields[] = "<label name='$varid'>";
                        $fields[] = "<span>$label</span>";
                        $fields[] = "<textarea name='$varid'></textarea>";
                        $fields[] = "</label>";
                      break;

                      case "file":
                        $fields[] = "<label name='$varid'>";
                        $fields[] = "<span>$label</span>";
                        $fields[] = "<input type='file' name='$varid'/>";
                        $fields[] = "</label>";
                      break;

                      default: //text
                        if($varid == "well_cms_displayord"){
                          $type = "number";
                          $val  = count($events) + 1;
                        }else{
                          $type = "text";
                          $val  = "";
                        }
                        $fields[] = "<label name='$varid'>";
                        $fields[] = "<span>$label</span>";
                        $fields[] = "<input type='$type' name='$varid' value='$val'/>";
                        $fields[] = "</label>";
                      break;
                    }
                    $fields[] = "</div>";
                  }
                  print(implode("\n",$fields));
                  ?>
                  </fieldset>
                  <input type="submit" name="submit" class="btn btn-success" value="Save to <?php echo $types[$cat]?>"/>
                </form>
              </td>
              </tr>
          </tbody>
         <!--  <tfoot>
            <tr>
            <td colspan="<?php echo count($display) + 1 ?>">
              <a href="#">Sumbit Edits</a>
            </td>
            </tr>
          </tfoot> -->
        </table>
        

        
      </div>  
    </div>
  </div>
</div>
<?php 
include("models/inc/gl_footer.php");
?>
<script>
$(document).ready(function(){
  $("#loc").change(function(){
    var loc       = $(this).val();
    var cat       = $("#folders .on").data("val");
    var link      = window.location.href.split('?')[0];
    location.href = link + "?cat=" + cat + "&loc=" + loc;
  });
  $("#folders a").click(function(){
    var loc       = $("#loc").val();
    var cat       = $(this).data("val");
    var link      = window.location.href.split('?')[0];
    location.href = link + "?cat=" + cat + "&loc=" + loc;
    return false;
  });
  $("#additem").click(function(){
    var el = $(this);
    el.addClass("fadeout");
    setTimeout(function(){
      el.slideUp();
      $("#newevent").slideDown();
    },500);
  });
});
</script>
<style>
  #main-content .well {
    background-size:7%;
    padding-top:88px;
  }
  #cms h2{
    font-size:200%;
    margin-bottom:60px;
  }
  #cms a{
    text-decoration:none;
  }
  #ed_items{
    width:calc(100% - 20px); margin:10px;
    border-top:1px solid #333;
    border-right:1px solid #333;
  }
  #ed_items th, #ed_items td {
    border-bottom:1px solid #333;
    border-left:1px solid #333;
    padding:5px 8px;
  }
  
  #cms tfoot {
    text-align:right;
  }
  #cms tfoot a{
    display:inline-block;
    border:1px solid #ccc;
    border-radius:10px;
    box-shadow:0 0 5px #000;
    text-transform: uppercase;
    background:salmon;
    padding:5px 15px;
    margin:5px;
  }

  #loc {
    float:right;
    margin:68px 10px 0;
  }
  #folders{
    margin:0; padding:10px;
    float:left;
  }
  #folders li {
    display:inline-block;
    list-style:none;
    margin-left:20px;
  }
  #folders li:first-child{
    margin-left:0;
  }
  #folders a{
    display:inline-block;
    text-align:center;
    width:120px;
    height:80px;
    border:1px solid #ccc;
    border-radius:5px;
    box-shadow:1px 1px 3px #ccc;
    line-height:80px;
  }
  #folders a.on{
    background:#efefef;
    box-shadow:1px 1px 3px #333;
  }

  #newevent {
    margin:10px;
    border:1px solid #ccc;
    border-radius:5px;
  }
  #newevent fieldset {
    padding:10px;
  }

  #additem {
    width:200px;
    display:block;
    margin:0 auto;
    font-weight:bold;
    text-decoration: none;
    transition:.5s opacity;
    opacity: 1;
  }
  #additem.fadeout {
    opacity:0;
  }

  #newevent{
    display:none;
  }
  #newevent h3{
    text-transform:capitalize;
    font-size:180%;
    margin-bottom:20px;
  }
  #newevent input[type="submit"]{
    display:block;
    width:200px;
    margin:20px auto;
    border-radius:5px;
  }

  .newevent_item{
    margin-bottom:15px;
  }
  .newevent_item label{
    display:block;
  }
  .newevent_item label span{
    display:block;
  }
  .newevent_item input[type="text"],
  .newevent_item textarea{
    width:50%;
    border-radius:5px;
  }
  .newevent_item label em {
    display:inline-block;
    margin-right:10px;
    font-style:normal;
  }
</style>
