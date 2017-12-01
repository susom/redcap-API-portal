<?php
$lang_picked 	= isset($_GET["lang"]) || isset($_SESSION["use_lang"]) ? isset($_GET["lang"]) ? $_GET["lang"] : $_SESSION["use_lang"] : "";
$en_selected	= $lang_picked == "en" ? "selected" : "";
$sp_selected	= $lang_picked == "sp" ? "selected" : "";
$tw_selected	= $lang_picked == "tw" ? "selected" : "";
$cn_selected	= $lang_picked == "cn" ? "selected" : "";
?>
<form onchange="this.submit()" method="get" id="language_select">
<select name="lang">
  <option value="en" <?php echo $en_selected ?>>English</option>
  <option value="sp" <?php echo $sp_selected ?>>Spanish</option>
  <option value="cn" <?php echo $cn_selected ?>>中文简</option>
  <option value="tw" <?php echo $tw_selected ?>>中文繁</option>
</select>
</form>