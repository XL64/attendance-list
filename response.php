<?php
require_once("../../../wp-config.php");
if(file_exists(ABSPATH . "wp-content/plugins/attendance-list/lang.php")) {
	include (ABSPATH . "wp-content/plugins/attendance-list/lang.php");
} else {
	echo "Attendance List error: language file not found.";
}
require_once(ABSPATH . "wp-content/plugins/attendance-list/functions.php");
header('Content-Type: text/html; charset='.get_option('blog_charset').'');


echo al_AjaxVote($_POST);
?>