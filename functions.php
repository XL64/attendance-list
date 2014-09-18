<?php
/** FUNCTIONS */
function al_UserData($id) {
	global $wpdb, $table_prefix;
	$name = $wpdb->get_var("SELECT display_name FROM ".$wpdb->users." WHERE ID='" . intval($id) . "' LIMIT 1");
	if ($name!="") return $name;
	else return false;
}

function al_GetList($id, $onlyactive=true) {
	global $wpdb, $table_prefix;
	$list=array();
	
	$cond="";
	if($onlyactive) $cond=" AND vote <> 2";
	
	$data = $wpdb->get_results("SELECT user, vote FROM ".$table_prefix."attendance_list WHERE post = " .  intval($id) .$cond. " ORDER BY date ASC");
	if(count($data)>0 && is_array($data)) {
		foreach($data as $r) {
			if(al_UserData($r->user))
				$list[$r->user] = $r->vote;
		}
		return $list;
	}
	return false;
}

function al_GetListNegative($id) {
	global $wpdb, $table_prefix;
	$list=array();
	
	$data = $wpdb->get_results("SELECT user, vote FROM ".$table_prefix."attendance_list WHERE post = " .  intval($id) . " AND vote = 2 ORDER BY date ASC");
	if(count($data)>0 && is_array($data)) {
		foreach($data as $r) {
			if(al_UserData($r->user))
				$list[$r->user] = $r->vote;
		}
		return $list;
	}
	return false;
	
}

function al_VoteName($vote) {
	global $al_lang;
	if($vote==1) return $al_lang[vote1];
	if($vote==2) return $al_lang[vote2];
	if($vote==3) return $al_lang[vote3];
	return "";
}

function al_AddVote($post, $vote) {
	global $wpdb, $current_user, $table_prefix;
	
	if ($current_user->ID > 0 && $post > 0 && $vote > 0) {
		$list = al_GetList($post, false);
		//check, if user already on list
		if (empty($list[$current_user->ID])) {
			$res=$wpdb->query(sprintf("INSERT INTO `".$table_prefix."attendance_list` (`post`, `user`, `vote`, `date`) VALUES (%d, %d, %d, %d)",
			intval($post),
			intval($current_user->ID),
			intval($vote),
			time()
			));
		} else {
			$res=$wpdb->query(sprintf("UPDATE `".$table_prefix."attendance_list` SET `vote`=%d, `date`=%d WHERE post=%d AND user=%d LIMIT 1",
			intval($vote),
			time(),
			intval($post),
			intval($current_user->ID)
			));
		}
		if($res) return true;
	}
	return false;
}

function al_AddCss(){
	echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/attendance-list/css/style.css" type="text/css" media="screen"  />'; 
}

function al_AjaxVote($vars) {
	global $wpdb, $current_user, $al_lang;

	$res=al_AddVote(intval($vars["al_postid"]), intval($vars["al_vote"]));
	return al_DrawList(intval($vars["al_postid"]), intval($current_user->ID));
}

function al_DrawList($id=0, $uid=0) {
	global $post, $current_user, $al_lang;
	if($id==0) $id=$post->ID;
	$list = al_GetList($id);
	$listneg = al_GetListNegative($id);
	
	$draw='<table class="al_table" id="al_table_'.$post->ID.'"><tr><td colspan="3">';
	$draw.=$al_lang['listheader'].'</td></tr>';
	
	if(count($list)>0 && is_array($list)) {
		$counter=1;
		foreach($list as $k=>$v) {
			if($uid == $k) $feature=' class="featured"';
			else $feature='';
			$draw.='<tr'.$feature.'><td class="small">'.$counter.'.</td><td>'.al_UserData($k).'</td><td><em>'.al_VoteName($v).'</em></td></tr>';
			$counter++;
		}
	}
	
	if(count($listneg)>0 && is_array($listneg)) {
		$counter=1;
		if(count($list)>0 && is_array($list)) $draw.='<tr><td colspan="3">&nbsp;</td></tr>';
		foreach($listneg as $k=>$v) {
			if($uid == $k) $feature=' class="featured"';
			else $feature='';
			$draw.='<tr'.$feature.'><td class="small noattends">'.$counter.'.</td><td class="noattends">'.al_UserData($k).'</td><td class="noattends"><em>'.al_VoteName($v).'</em></td></tr>';
			$counter++;
		}
	}
	
	$draw.='</table>';
	
	return $draw;
}
?>