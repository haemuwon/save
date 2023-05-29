<?php
if (!defined('_GNUBOARD_')) exit;

function latest_main($parent, $memberlevel=1, $skin_dir='', $rows=10, $except_table, $subject_len=40, $cache_time=1, $options='')
{
	global $g5;

	if (!$skin_dir || $skin_dir == '') { $skin_dir = 'main_latest'; }

	if ($skin_dir == 'main_latest'){
		$latest_skin_path = G5_SKIN_PATH.'/board/'.$parent.'/'.$skin_dir;
		$latest_skin_url  = G5_SKIN_URL.'/board/'.$parent.'/'.$skin_dir;	
	} else {
		$latest_skin_path = G5_SKIN_PATH.'/latest/'.$skin_dir;
		$latest_skin_url  = G5_SKIN_URL.'/latest/'.$skin_dir;
	}
	

	$cache_fwrite = false;
	if(G5_USE_CACHE) {
		$cache_file = G5_DATA_PATH."/cache/latest-{$bo_table}-{$skin_dir}-{$rows}-{$subject_len}.php";

		if(!file_exists($cache_file)) {
			$cache_fwrite = true;
		} else {
			if($cache_time > 0) {
				$filetime = filemtime($cache_file);
				if($filetime && $filetime < (G5_SERVER_TIME - 3600 * $cache_time)) {
					@unlink($cache_file);
					$cache_fwrite = true;
				}
			}

			if(!$cache_fwrite)
				include($cache_file);
		}
	}

	if(!G5_USE_CACHE || $cache_fwrite) {
		$list = array();
		$sql = "SELECT wr_parent, max(bn_datetime), max(wr_id) as wr_id, bo_table FROM {$g5['board_new_table']}
		WHERE bo_table not in ({$except_table})
		GROUP BY wr_parent, bo_table ORDER BY max(bn_datetime) DESC limit 0, {$rows}"; 
		// 게시글 기준으로 시간순 정렬하여 가장 최근 갱신된 게시글의 마지막 덧글 혹은 글을 rows 수만큼 검색함 (not in 안의 table은 제외)

		$result = sql_query($sql);
		for ($i=0; $row = sql_fetch_array($result); $i++) {
			$board[$i]['bo_table'] = $row['bo_table'];
			//게시판의 테이블명을 불러온다
			
			$sql = " SELECT * FROM {$g5['board_table']} WHERE bo_table = '{$row['bo_table']}' ";
			$tmp_sub = sql_fetch($sql);
			$board[$i]['bo_type'] = get_text($tmp_sub['bo_type']);
			$board[$i]['bo_subject'] = get_text($tmp_sub['bo_subject']);
			$board[$i]['bo_list_level'] = $tmp_sub['bo_list_level'];
			//게시판의 이름을 불러온다

			$tmp_write_table = $g5['write_prefix'] . $row['bo_table'];
			$sql = " SELECT * FROM $tmp_write_table WHERE wr_id = {$row['wr_id']} ";
			$rs = sql_fetch($sql); //write_table에서 검색값 (1줄)
			if($board[$i]['bo_type'] == 'mmb'){
				$board[$i]['href'] = G5_BBS_URL.'/board.php?bo_table='.$board[$i]['bo_table'].'&amp;log='.$rs['wr_parent'].'#c_'.$rs['wr_id'].$qstr;
			} else {
				$board[$i]['href'] = G5_BBS_URL.'/board.php?bo_table='.$board[$i]['bo_table'].'&amp;wr_id='.$rs['wr_id'].$qstr;
			}
			$board[$i]['wr_subject'] = get_text($rs['wr_subject']);
			$board[$i]['wr_name'] = get_text($rs['wr_name']);
			$board[$i]['wr_is_comment'] = $rs['wr_is_comment'];
			$board[$i]['wr_num'] = $rs['wr_num']*-1;
			$board[$i]['wr_comment'] = $rs['wr_comment'];
			$board[$i]['wr_comment_reply'] = get_text($rs['wr_comment_reply']);
			$board[$i]['wr_datetime'] = $rs['wr_datetime'];
			$board[$i]['wr_option'] = $rs['wr_option'];
		}

		if($cache_fwrite) {
			$handle = fopen($cache_file, 'w');
			$cache_content = "<?php\nif (!defined('_GNUBOARD_')) exit;\n\$bo_subject='".$bo_subject."';\n\$list=".var_export($list, true)."?>";
			fwrite($handle, $cache_content);
			fclose($handle);
		}
	}

	ob_start();
	include $latest_skin_path.'/latest.skin.php';
	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

?>
