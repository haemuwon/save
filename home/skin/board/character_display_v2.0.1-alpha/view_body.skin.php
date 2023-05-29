<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 

$wr_option1 = json_decode($write['wr_1_txt'], true);
$wr_option2 = json_decode($write['wr_2_txt'], true);

$body_cnt = sql_fetch("select count(*) as cnt from {$g5['character_body_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}'");
$body_cnt = $body_cnt['cnt'];

$check_body = true;

if ($body_cnt > 0) {
	$check_body_cnt = sql_fetch("select count(*) as cnt from {$g5['character_body_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bd_use = '1'");
	if ($check_body_cnt['cnt'] > 0) $check_body = false;
}
?>
<article id="glide-box" class="article-closet">
	<div class="glide">
		<div class="glide-track" data-glide-el="track">
			<ul class="glide-slides article-closet__list">
			<?php
				if($wr_option1['body']) {
					$bd_id = "";
					$bd_name = "Default";
					$bd_url = $wr_option1['body'];

					$caption = $check_body ? "NOW ON" : "SKIN";
			?>
				<li class="glide-slide article-closet__list__item animate-onload">
					<article class="article-cloth">
						<div class="article-cloth__thumb" onclick="changeCloth('<?php echo $bd_url; ?>')">
							<div class="article-cloth__thumb__img" style="background-image: url('<?php echo $bd_url; ?>');"></div>
						</div>
						<div class="article-cloth__content">
							<div class="article-cloth__caption <?php if ($check_body) echo "ch-txt-point"; ?>"><?php echo $caption; ?></div>
							<div class="article-cloth__name"><?php echo $bd_name; ?></div>
							<?php if ($update_href && !$check_body) {?>
							<ul class="article-cloth__control">
								<li>
									<a class="article-cloth__control__btn ch-bg-point" href="javascript:fn_set_chBody('set', '<?php echo $bo_table; ?>', '<?php echo $wr_id; ?>', '', '<?php echo $bd_url; ?>');">적용</a>
								</li>
							</ul>
							<?php } ?>
						</div>
					</article>
				</li>
			<?php
				}
				$body_list = sql_query("select * from {$g5['character_body_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' order by bd_id asc");
				for($i=0; $bd = sql_fetch_array($body_list); $i++) {
					$bd_id = $bd['bd_id'];
					$bd_name = $bd['bd_name'];
					$bd_url = $bd['bd_url'];

					$caption = $bd['bd_use'] ? "NOW ON" : "SKIN";

					$thumb = "";
					if ($bd['bd_secret']==1) $thumb = "style=\"background: linear-gradient(180deg, var(--background-dark), {$wr_option2['color']} 150%)\"";
					else $thumb = "style=\"background-image: url('{$bd_url}')\"";
			?>
				<li class="glide-slide article-closet__list__item animate-onload">
					<article class="article-cloth">
						<div class="article-cloth__thumb" onclick="changeCloth('<?php echo $bd_url; ?>', <?php echo $bd['bd_secret']; ?>)">
							<div class="article-cloth__thumb__img" <?php echo $thumb; ?> ></div>
						</div>
						<div class="article-cloth__content">
						<div class="article-cloth__caption <?php if ($bd['bd_use']) echo "ch-txt-point"; ?>"><?php echo $caption; ?></div>
						<div class="article-cloth__name"><?php echo $bd_name; ?></div>
							<?php if ($update_href) {?>
							<ul class="article-cloth__control">
								<?php if (!$bd['bd_use']) {?>
								<li>
									<a class="article-cloth__control__btn ch-bg-point" href="javascript:fn_set_chBody('set', '<?php echo $bo_table; ?>', '<?php echo $wr_id; ?>', '<?php echo $bd_id; ?>', '<?php echo $bd_url; ?>');">적용</a>
								</li>
								<?php } ?>
								<li>
									<a class="article-cloth__control__btn" href="javascript:fn_set_chBody('del', '<?php echo $bo_table; ?>', '<?php echo $wr_id; ?>', '<?php echo $bd_id; ?>');">삭제</a>
								</li>
							</ul>
							<?php } ?>
						</div>
					</article>
				</li>
			<?php } ?>
			</ul>
		</div>
		<!-- <div class="glide-nb" data-glide-el="controls">
			<a class="glide-arrow glide__arrow--left" data-glide-dir="<"><i class="fas fa-caret-left"></i></a>
			<a class="glide-arrow glide__arrow--right" data-glide-dir=">"><i class="fas fa-caret-right"></i></a>
		</div> -->
	</div>
</article>
<?php if (isset($wr_option1['mini']) && $wr_option1['mini']) { // Add mini here ?>
<div class="article-mini">
	<div class="article-mini__container animate-onload">
		<img class="article-mini__img" src="<?php echo $wr_option1['mini']; ?>" />
	</div>
</div>
<?php } ?>

<script>
function changeCloth(path, spoiler) {
	if (spoiler) {
		if(!confirm("스포일러 요소가 있습니다. 정말 열람하시겠습니까?")){
			exit
		}
	}
	let newCloth = '<div class="view__body__area-visual__img-holder"><img class="view__body__area-visual__img" src="'+path+'" /></div>';
	$(".view__body__area-visual__container").removeClass("on").html(newCloth);
	setTimeout(() => {
		$(".view__body__area-visual__container").addClass("on");
		measureImage();
		floatingVisuals();
	}, 500);
};

<? if($update_href) { ?>
	var control_idx = "";
	var originam_url = "<?=$wr_option1['body']?>";

	function fn_set_chBody(type, bo_table, wr_id, control_idx, bd_url = null) {
		var url = skin_url;
		var formData = new FormData();
		formData.append("bo_table", bo_table);
		formData.append("wr_id", wr_id);
		formData.append("bd_id", control_idx);

		if(type == 'del') {
			if(confirm("해당 데이터를 정말 삭제하시겠습니까?")) {
				// $('.loading').addClass('mask');
				$.ajax({
					cache : false,
					url : url + "/proc/del_body.php", // 요기에
					type : 'POST',
					processData: false,
					contentType: false,
					data : formData, 
					success: function(data) {
						// Toss
						var response = data;
						$('.view__body__container-closet').empty().append(response);
						changeCloth(originam_url);
					},
					error: function(data, status, err) {},
					complete: function() { 
						// Complete
						$(".view__body__container-closet .animate-onload").addClass("on");
					}
				});
			} 
		}
		if(type == 'set') {
			if(confirm("해당 데이터를 기본 전신으로 설정하시겠습니까?")) {
				$.ajax({
					cache : false,
					url : url + "/proc/set_body.php", // 요기에
					type : 'POST',
					processData: false,
					contentType: false,
					data : formData, 
					success: function(data) {
						// $('#control_body_box').empty();
						var response = data;
						$('.view__body__container-closet').empty().append(response);
						changeCloth(bd_url);
					},
					error: function(data, status, err) {},
					complete: function() { 
						// Complete
						$(".view__body__container-closet .animate-onload").addClass("on");
					}
				});
			}
		}

	}
<? } ?>
</script>