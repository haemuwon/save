<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
if($is_admin){?>
<section id="clap_stat_box">
	<a href="#clap_stat_box" onclick="$('#clap_wrap').slideToggle();return false;" class="ui-btn point small">▶ 통계</a>
	<div id="clap_wrap">
		<div class="clap_container">
		<section class="clap_stat_weekly"> 
			<h3 class="txt-center">지난 <span class="less">10</span><span class="full">14</span>일 (일별)</h3>
			<table>
				<tbody>
			<? 	$clap_wlist=array();
				$h_max=5;
				$cnt=13;
				for($k=0;$k<14;$k++){
					$this_day=date("Y-m-d",strtotime("today - {$k} day"));
					$clap_w=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date,'%Y-%m-%d')='{$this_day}' and cl_val=''");
					if($h_max<$clap_w['sum']) $h_max=$clap_w['sum'];
					$clap_wlist[$cnt]['sum']=$clap_w['sum'];
					$clap_wlist[$cnt]['day']=date("n/j",strtotime($this_day));
					$cnt--;
				} 
				$c_h=100/$h_max;
				for($k=0;$k<14;$k++){
					if($k==0) echo "<tr>";
					?>
					<td <?if($k<4) echo "class='old'";?>>
						<p class="bar highlight" style="height:<?=$c_h * $clap_wlist[$k]['sum']?>%;"><i class="ui-btn small"><?=$clap_wlist[$k]['sum']?></i></p>
						<p class="num"><?=$clap_wlist[$k]['day']?></p>
					</td>
				<? if($k==13) echo "</tr>";}
			?></tbody>
			</table>
		</section> 
		<section class="clap_stat_daily">
			<h3 class="txt-center"><?=G5_TIME_YMD?> (오늘)</h3>
			<table>
				<tbody>
					<? 
					$h_max=5;
					$clap_dlist=array();
					for($k=0;$k<24;$k++){
						$this_hour=G5_TIME_YMD." ".sprintf('%02d',$k);
						$clap_d=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date,'%Y-%m-%d %H')='{$this_hour}' and cl_val=''");
						if ($h_max<$clap_d['sum']) $h_max=$clap_d['sum'];
						$clap_dlist[$k]=$clap_d['sum']; 
					} 
					$c_h=100/$h_max;
					for($k=0;$k<24;$k++) {
						if($k==0) echo "<tr>";
						?>
						<td>
							<p class="bar highlight" style="height:<?=$c_h * $clap_dlist[$k]?>%;"><i class="ui-btn small"><?=$clap_dlist[$k]?></i></p>
							<p class="num"><?=$k?></p>
						</td>
					<?	
						if($k==11) echo "</tr><tr>";
						if($k==23) echo "</tr>";
					}?>
				</tbody>
			</table>
		</section>
		<section class="clap_stat_daily">
			<h3 class="txt-center"><?=date("Y-m-d",strtotime("yesterday"))?> (어제)</h3>
			<table>
				<tbody>
					<? 
					$h_max=5;
					$clap_dlist=array();
					for($k=0;$k<24;$k++){
						$this_hour=date("Y-m-d",strtotime("yesterday"))." ".sprintf('%02d',$k);
						$clap_d=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date,'%Y-%m-%d %H')='{$this_hour}' and cl_val=''");
						if ($h_max<$clap_d['sum']) $h_max=$clap_d['sum'];
						$clap_dlist[$k]=$clap_d['sum']; 
					} 
					$c_h=100/$h_max;
					for($k=0;$k<24;$k++) {
						if($k==0) echo "<tr>";
						?>
						<td>
							<p class="bar highlight" style="height:<?=$c_h * $clap_dlist[$k]?>%;"><i class="ui-btn small"><?=$clap_dlist[$k]?></i></p>
							<p class="num"><?=$k?></p>
						</td>
					<?	
						if($k==11) echo "</tr><tr>";
						if($k==23) echo "</tr>";
					}?>
				</tbody>
			</table>
		</section>	
		<section class="clap_stat_daily">
			<h3 class="txt-center"><?=date("Y-m-d",strtotime("today -2 day"))?> (2일전)</h3>
			<table>
				<tbody>
					<? 
					$h_max=5;
					$clap_dlist=array();
					for($k=0;$k<24;$k++){
						$this_hour=date("Y-m-d",strtotime("today -2 day"))." ".sprintf('%02d',$k);
						$clap_d=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date,'%Y-%m-%d %H')='{$this_hour}' and cl_val=''");
						if ($h_max<$clap_d['sum']) $h_max=$clap_d['sum'];
						$clap_dlist[$k]=$clap_d['sum']; 
					}
					$c_h=100/$h_max;
					for($k=0;$k<24;$k++) {
						if($k==0) echo "<tr>";
						?>
						<td>
							<p class="bar highlight" style="height:<?=$c_h * $clap_dlist[$k]?>%;"><i class="ui-btn small"><?=$clap_dlist[$k]?></i></p>
							<p class="num"><?=$k?></p>
						</td>
					<?	
						if($k==11) echo "</tr><tr>";
						if($k==23) echo "</tr>";
					}?>
				</tbody>
			</table>
		</section>	
		<section class="clap_stat_daily">
			<h3 class="txt-center"><?=date("Y-m-d",strtotime("today -3 day"))?> (3일전)</h3>
			<table>
				<tbody>
					<? 
					$h_max=5;
					$clap_dlist=array();
					for($k=0;$k<24;$k++){
						$this_hour=date("Y-m-d",strtotime("today -3 day"))." ".sprintf('%02d',$k);
						$clap_d=sql_fetch("select sum(cl_cnt) as sum from {$g5['clap_table']} where date_format(cl_date,'%Y-%m-%d %H')='{$this_hour}' and cl_val=''");
						if ($h_max<$clap_d['sum']) $h_max=$clap_d['sum'];
						$clap_dlist[$k]=$clap_d['sum']; 
					}
					$c_h=100/$h_max;
					for($k=0;$k<24;$k++) {
						if($k==0) echo "<tr>";
						?>
						<td>
							<p class="bar highlight" style="height:<?=$c_h * $clap_dlist[$k]?>%;"><i class="ui-btn small"><?=$clap_dlist[$k]?></i></p>
							<p class="num"><?=$k?></p>
						</td>
					<?	
						if($k==11) echo "</tr><tr>";
						if($k==23) echo "</tr>";
					}?>
				</tbody>
			</table>
		</section>	
		</div>
	</div>
</section>	
<?}?>