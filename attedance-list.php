<?php
/*
    Copyright 2014       XL64       (email : xl64100@gmail.com)
    Copyright 2010-2014  2inspired  (email : office@2inspired.eu)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin Name: Attendance List (forked by XL)
Plugin URI: https://github.com/XL64/attendance-list
Description: Simple event attendance list. You can add it to any post or page. (Forked from 2inspired 1.1 release http://attendance.2inspired.eu/).
Author: XL
Version: 1.2
Author URI: https://github.com/XL64/
License: GPLv2
*/

if(file_exists(ABSPATH . "wp-content/plugins/attendance-list/lang.php")) {
	include (ABSPATH . "wp-content/plugins/attendance-list/lang.php");
} else {
	echo "Attendance List error: language file not found.";
}

if (!function_exists('add_action')) {
	if (file_exists(ABSPATH.'/wp-load.php')) {
		require_once(ABSPATH.'/wp-load.php');
	} else {
		require_once(ABSPATH.'/wp-config.php');
	}
}

$create_table = "CREATE TABLE IF NOT EXISTS `".$table_prefix."attendance_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `post` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `vote` int(10) unsigned NOT NULL,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `post` (`post`,`user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

If (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
}
If (!dbDelta($create_table)) {
	echo "Attedance List error: db query failed";
}

add_shortcode('attendancelist', 'al_Main');

require_once(ABSPATH . "wp-content/plugins/attendance-list/functions.php");

wp_enqueue_script("jquery");
add_action("wp_head", "al_AddCss");


function al_Main($atts) {
	global $wpdb, $current_user, $al_lang, $post;
	
        $a = shortcode_atts( array(
          'al_id' => $post->ID,
           ), $atts );
	
	$return = '<div id="al_table_cont_'.$a['al_id'].'" class="al_table_cont">';
	
	if($current_user->ID > 0) {
		$return.=	'<table id="al_head_'.$a['al_id'].'"><tr><td class="al_head"><strong>'.$al_lang['question'].'</strong> </td><td class="al_head">' .
				'<a href="#" id="al_vote1_'.$a['al_id'].'" title="1" class="al_btn al_btn_'.$a['al_id'].'">'.$al_lang['vote1'].'</a>&nbsp;' .
				'<a href="#" id="al_vote2_'.$a['al_id'].'" title="2" class="al_btn al_btn_'.$a['al_id'].'">'.$al_lang['vote2'].'</a>&nbsp;' .
				'<a href="#" id="al_vote3_'.$a['al_id'].'" title="3" class="al_btn al_btn_'.$a['al_id'].'">'.$al_lang['vote3'].'</a>&nbsp;&nbsp;&nbsp;' .
				'<span id="al_state_'.$a['al_id'].'" class="al_state"></span></td></tr></table>';


		$wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY user_nicename");	
		$return .= '<table id="al_head_'.$a['al_id'].'"><tr><td class="al_head">';
		/*$return.= '<form name="formulaire">';*/
		$return.= '<select id="boite1" name="boite1" onChange=""> ';
		$return.= '<option selected value="-1">Choisissez un nom</option>  ';
		foreach ( $wp_user_search as $userid ) {

	        	$user_id = (int) $userid->ID;
        		$user_info = get_userdata($user_id);
			if ($user_info->user_login != 'arruanais' &&
			    $user_info->user_login != 'admin'
			   )
			{
				$return .= '<option value="'.$user_id.'">'.$user_info->display_name.'</option> ';
			}
		}
		$return .= '</select>';
		$return .= '</td>';
		//$return .= '<td class="al_head"><strong>'.$al_lang['question2'].'</strong> </td>';
		$return .= '<td class="al_head">' .
				'<a href="#" id="al_vote1_'.$a['al_id'].'" title="1" class="al_btn al_btn2_'.$a['al_id'].'">'.$al_lang['vote1'].'</a>&nbsp;' .
				'<a href="#" id="al_vote2_'.$a['al_id'].'" title="2" class="al_btn al_btn2_'.$a['al_id'].'">'.$al_lang['vote2'].'</a>&nbsp;' .
				'<a href="#" id="al_vote3_'.$a['al_id'].'" title="3" class="al_btn al_btn2_'.$a['al_id'].'">'.$al_lang['vote3'].'</a>&nbsp;&nbsp;&nbsp;' .
				'<span id="al_state2_'.$a['al_id'].'" class="al_state"></span></td></tr></table>';

	} else {
		$return.='<table><tr><td>'.$al_lang['login'].'</td></tr></table>';
	}
	
	$return.='<div id="al_cont_'.$a['al_id'].'">'.al_DrawList($a['al_id']).'</div></div>';
	
	$return .= "<script language='javascript'>
	jQuery(document).ready(function(){
	  jQuery('.al_btn_".$a['al_id']."').click(function() {
	  	jQuery('#al_state_".$a['al_id']."').html('<img src=\"".get_bloginfo('wpurl') ."/wp-content/plugins/attendance-list/img/ajax-loader.gif\" />');
	    param=jQuery(this).attr('title');
	    jQuery.post('".get_bloginfo('wpurl') ."/wp-content/plugins/attendance-list/response.php', { al_vote: param, al_postid:".$a['al_id'].", al_user_ID:".$current_user->ID." }, 
	    function(data){ 
	      if(data) {
			jQuery('#al_cont_".$a['al_id']."').html(data);
			jQuery('#al_state_".$a['al_id']."').html('');
	      }
	    }, 
	    'html');
	    return false;
	  });
	
	   jQuery('.al_btn2_".$a['al_id']."').click(function() {
		var e = document.getElementById('boite1');
		var strUser = e.options[e.selectedIndex].value;
	  	jQuery('#al_state2_".$a['al_id']."').html('<img src=\"".get_bloginfo('wpurl') ."/wp-content/plugins/attendance-list/img/ajax-loader.gif\" />');
	    	param=jQuery(this).attr('title');
	    	jQuery.post('".get_bloginfo('wpurl') ."/wp-content/plugins/attendance-list/response.php', { al_vote: param, al_postid:".$a['al_id'].", al_user_ID: strUser }, 
	    	function(data){ 
	      		if(data) {
				jQuery('#al_cont_".$a['al_id']."').html(data);
				jQuery('#al_state2_".$a['al_id']."').html('');
	      		}
	    	}, 
	    	'html');
	    return false;
	  });
	});
	</script>";
	
	return $return;
}

?>