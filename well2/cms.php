<?php
require_once("../models/config.php");
include("models/inc/checklogin.php");

$lang_req     = isset($_GET["lang"]) ? "?lang=".$_GET["lang"] : "";
$pg_title     = "$websiteName";
$body_classes = "cms";

$API_URL      = SurveysConfig::$projects["ADMIN_CMS"]["URL"];
$API_TOKEN    = SurveysConfig::$projects["ADMIN_CMS"]["TOKEN"];
if(!empty($_POST) && isset($_POST["action"])){
  if($_POST["action"] == "newevent"){
    unset($_POST["submit"]);
    unset($_POST["action"]);
    unset($_POST["loc"]);
    unset($_POST["cat"]);

    //import the record
    $ts   = date('Y-m-d H:i:s');
    $data = array(
         "well_cms_create_ts" => $ts
        ,"well_cms_update_ts" => $ts
        ,"id" => "whatever_required_but_wont_be_used"
      );
    foreach($_POST as $key => $val){
      $data[$key] = $val;
    }
    if(!isset($data["well_cms_active"])){
      $data["well_cms_active"] = "0";
    }
    $result = RC::writeToApi($data, array("forceAutoNumber" => "true", "returnContent" => "auto_ids", "overwriteBehavior" => "overwite", "type" => "flat"), $API_URL, $API_TOKEN);

    //import the picture file
    $split  = explode(",",$result[0]);
    $new_id = $split[0];
    RC::writeFileToApi($_FILES["well_cms_pic"], $new_id, "well_cms_pic", null, $API_URL, $API_TOKEN);
  }elseif($_POST["action"] == "delete"){
    if(!empty($_POST["id"])){
      $data = array(
           "action"       => "delete"
          ,"content"      => "record"
          ,"records"      => array($_POST["id"])
        );
      $result = RC::callApi($data, array(), $API_URL, $API_TOKEN);
    }
    exit;
  }elseif($_POST["action"] == "edit"){
    if(!empty($_POST["id"])){
      $data[]   = array(
           "record"     => $_POST["id"]
          ,"field_name" => $_POST["field_name"]
          ,"value"      => $_POST["value"]
        );
      $result   = RC::writeToApi($data, array("format" => "json", "overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
    
      $data[]   = array(
           "record"     => $_POST["id"]
          ,"field_name" => "well_cms_update_ts"
          ,"value"      => date('Y-m-d H:i:s')
      );
      $result   = RC::writeToApi($data, array("format" => "json", "overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
    }
    exit;
  }elseif($_POST["action"] == "edit_img"){
    if(!empty($_POST["id"])){
      RC::writeFileToApi($_FILES["well_cms_pic"], $_POST["id"], "well_cms_pic", null, $API_URL, $API_TOKEN);
      
      $data[]   = array(
           "record"     => $_POST["id"]
          ,"field_name" => "well_cms_update_ts"
          ,"value"      => date('Y-m-d H:i:s')
      );
      $result   = RC::writeToApi($data, array("format" => "json", "overwriteBehavior" => "overwite", "type" => "eav"), $API_URL, $API_TOKEN);
    }
  }
}

// DEFAULT VALUES
$loc          = isset($_REQUEST["loc"]) ? $_REQUEST["loc"] : "1";
$cat          = isset($_REQUEST["cat"]) ? $_REQUEST["cat"] : "1";

$types        = array(0 => "Events", 1 => "Monthly Goals", 99 => "Others");
$locs         = array(1 => "US", 2 => "Taiwan");

include("../models/inc/gl_header.php");
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
        $mon_display  = array(3,4,5,8,11);
        $evt_display  = array(9,6,3,4,5,8,11);
        
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
              <?php
              $trs            = array();
              $monthly_active = false;

              foreach($events as $event){
                $eventpic   = "";
                $recordid   = $event["id"];
                $file_curl  = RC::callFileApi($recordid, "well_cms_pic", null, $API_URL,$API_TOKEN);
                if(strpos($file_curl["headers"]["content-type"][0],"image") > -1){
                  $split    = explode("; ",$file_curl["headers"]["content-type"][0]);
                  $mime     = $split[0];
                  $split2   = explode('"',$split[1]);
                  $imgname  = $split2[1];
                  $eventpic = '<img class="event_img" src="data:'.$mime.';base64,' . base64_encode($file_curl["file_body"]) . '">';
                }
                $selected = array("Yes" => "", "No" => "");

                $trs[]    = "<tr data-id='$recordid' class='editable'>";
                $active   = $event["well_cms_active"] ? "Yes" : "No";
                $selected[$active] = "selected";

                if($cat == 0){
                  $trs[] = "<td class='order'><input type='number' name='well_cms_displayord' value='".$event["well_cms_displayord"] ."'/></td>";
                  $trs[] = "<td class='link'><input type='text' name='well_cms_event_link' value='".$event["well_cms_event_link"] ."'/></td>";
                }else{
                  if(!$monthly_active && $active == "Yes"){
                    $monthly_active = true;
                  }
                }

                $trs[] = "<td class='subject'><input type='text' name='well_cms_subject' value='".$event["well_cms_subject"]  ."'/></td>";
                $trs[] = "<td class='content'><textarea name='well_cms_content'>".$event["well_cms_content"]."</textarea></td>";
                $trs[] = "<td class='pic'>$eventpic";
                $trs[] = "<form class='edit_img' action='cms.php' method='post' enctype='multipart/form-data'>";
                $trs[] = "<input type='hidden' name='action' value='edit_img'/>";
                $trs[] = "<input type='hidden' name='id' value='$recordid'/>";
                $trs[] = "<input type='file' name='well_cms_pic'/>";
                $trs[] = "</form>";
                $trs[] = "</td>";
                $trs[] = "<td class='active'><select name='well_cms_active'>";
                $trs[] = "<option value='0' ".$selected["No"].">No</option>";
                $trs[] = "<option value='1' ".$selected["Yes"].">Yes</option>";
                $trs[] = "</select></td>";
                $trs[] = "<td class='updated'>".$event["well_cms_update_ts"]."</td>";
                $trs[] = "<td class='editbtns'><a href='#' class='deleteid btn btn-danger' data-id='".$event["id"]."'>Delete</a></td>";
                $trs[] = "</tr>";
              }
              echo implode("\n",$trs);
              ?>
          </tbody>
          <tfoot>
            <tr class="addnew">
            <td colspan="<?php echo count($display) + 1 ?>">
              <a id="additem" href="#">+ add item to <?php echo $types[$cat] ?></a>
              
              <form id="newevent" action="cms.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="newevent"/>
                <input type="hidden" name="well_cms_loc" value="<?php echo $loc?>"/>
                <input type="hidden" name="well_cms_catagory" value="<?php echo $cat?>"/>
                <input type="hidden" name="loc" value="<?php echo $loc?>"/>
                <input type="hidden" name="cat" value="<?php echo $cat?>"/>
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
                      $checked  = array("y" => "", "n" => "");
                      $disabled = "";
                      if($cat == 1 && $varid == "well_cms_active" && $monthly_active){
                        $checked["n"] = "checked";
                        $disabled = "disabled=true";
                      }else{
                        $checked["y"] = "checked";
                      }
                      $fields[] = "<label name='$varid'>";
                      $fields[] = "<span>$label</span>";
                      $fields[] = "<em><input name='$varid' type='radio' ".$checked["y"]." $disabled value='1'> Yes</em>";
                      $fields[] = "<em><input name='$varid' type='radio' ".$checked["n"]." $disabled value='0'> No</em>";
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
                      if($varid == "well_cms_pic"){
                        $fields[] = "<i>WxH must be 150x150px</i>";
                      }
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
          </tfoot>
        </table>
      </div>  
    </div>
  </div>
</div>
<?php 
include("../models/inc/gl_footer.php");
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
    return false;
  });
  $(".deleteid").click(function(){
    if(confirm("Confirm deletion of this event")){
      var id_to_delete = $(this).data("id");
      $.ajax({
        url : "cms.php",
        type: 'POST',
        data: "action=delete&id=" + id_to_delete,
        success:function(result){
          $("tr[data-id='"+id_to_delete+"']").fadeOut();
        }
      });
    }
    return false;
  });
  $("#ed_items tbody :input[name!='well_cms_pic']").change(function(){
    var el = $(this);
    var id_to_edit = $(this).parents("tr").data("id");
    var field_name = $(this).attr("name");
    var value      = $(this).val();
    $.ajax({
      url : "cms.php",
      type: 'POST',
      data: "action=edit&id=" + id_to_edit + "&field_name=" + field_name + "&value=" + value,
      success:function(result){
        el.addClass("edited");
        setTimeout(function(){
          el.removeClass("edited");
        },1000);
      }
    });
    return false;
  });
  $("#ed_items tbody :input[name='well_cms_pic']").change(function(){
    $(this).parents("form").submit();
    console.log($(this).parents("form"));
    return false;
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
    font-size:77%;
    vertical-align: top;
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

  .newevent_item label i{
    color:red;
    font-weight:normal;
    font-style:italic;
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

  .content textarea,
  .active select,
  .order input,
  .link input,
  .subject input {
    width:100%;
    border:1px solid transparent;
    padding:0 5px; 
    background:none;
    cursor:pointer;
    height:30px;
  }
  
  .content textarea:focus,
  .active select:focus,
  .order input:focus,
  .link input:focus,
  .subject input:focus{
    border:1px solid yellow;
    box-shadow:0 0 5px 5px yellow;
  }
  
  .content textarea.edited,
  .active select.edited,
  .order input.edited,
  .link input.edited,
  .subject input.edited{
    border:1px solid yellow;
    box-shadow:0 0 3px 5px lightgreen;
  }

  .content textarea {
    padding:4px 5px;
  }
  .active select{
    width:50px;
  }
  .order input {
    width:40px;
  }

  
  #ed_items th {
    vertical-align: bottom
  }
  .order{
    width:58px;
  }
  .active {
    width:68px;
  }
  .actions{
    width:82px;
  }


  .well .event_img {
    max-width:200px;
  }
</style>
