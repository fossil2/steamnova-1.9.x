{block name="title" prepend}{$LNG.ex_em}{/block}
{block name="content"}
<link rel="stylesheet" href="{$dpath}anomalie.css">
<div id="page_content">
	<div class="card1 itemShow d-flex justify-content-center align-items-start w-100 position-relative border-black">
		<div id="header_text">
			<h2><p>{$LNG.ex_a3}</p></h2>
		</div>
	</div>
	<div class="card2">
		<div id="welcome">{$LNG.ex_a}</div>
		<div class="rewardlist_wrapper">
			<div id="select_one">{$LNG.ex_a1}</div>
			<!-- Zeile 1 -->
			<div class="normalRewards">
				<!-- #### -->
				<div class="col w-150 singleReward">
					<div class="rewardName">{$LNG.find_debri}</div>
					<div class="itemBox">
						<div class="thumbnail">
							<img src="{$dpath}img/anomalie_tf.jpeg">
						</div>
						<div class="box"
							data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true" title="{$LNG.tf_txt}"
							style="background: url({$dpath}img/anomalie_single_bg.png) no-repeat left bottom;">
							<div class="quantity">
								Aktiv
							</div>
						</div>
					</div>
					<div class="selectReward" onclick="toggle_Debris()">
						<a class="mission">{$LNG.ex_a2}</a>
					</div>
				</div>
				<!-- #### -->
				<div class="col w-150 singleReward">
					<div class="rewardName">{$LNG.boni_01}</div>
					<div class="itemBox">
						<div class="thumbnail {if !$bonus} thumbnail2 {/if}">
							<img src="{$dpath}img/anomalie_bonus.jpeg">
						</div>
						<div class="box"
							data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true" title="{$LNG['boni_02.1']}"
							style="background: url({$dpath}img/anomalie_single_bg.png) no-repeat left bottom;">
							<div class="quantity {if !$bonus} quantity2 {/if}">
								{if !$bonus} {$bonus_time} {else} {$LNG['boni_01.1']} {/if}
							</div>
						</div>
					</div>
					<div class="selectReward" onclick="toggle_Bonus()">{if $bonus} {/if}
						<a class="mission {if !$bonus} mission2 {/if}">
							<!-- {if !$bonus} data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true" title="Neuer Bonus ab: $bonus_time2 Uhr" {/if} -->
							{if !$bonus}<strong><span class="countdown2" secs="{$bonus_secs}">{$LNG['boni_01.3']}</span></strong>{else} {$LNG['boni_01.2']} {/if}</a>
					</div>
				</div>
				<!-- #### -->
			</div>
			<!-- Zeile 2 -->
			<div class="normalRewards">
				<!-- #### -->
				<div class="col w-150 singleReward">
					<div class="rewardName">{$LNG.tut_tut}</div>
					<div class="itemBox">
						<div class="thumbnail">
							<img src="{$dpath}img/anomalie_tutorial.jpeg">
						</div>
						<div class="box"
							data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true" title="{$LNG.tut_a01}"
							style="background: url({$dpath}img/anomalie_single_bg.png) no-repeat left bottom;">
							<div class="quantity">
								Aktiv
							</div>
						</div>
					</div>
					<div class="selectReward" onclick="toggle_Tutorial()">
						<a class="mission">{$LNG.ex_a2}</a>
					</div>
				</div>
				<!-- #### -->
				<!-- <div class="col w-150 singleReward">
					<div class="rewardName">{$LNG.find_debri}</div>
					<div class="itemBox">
						<div class="thumbnail">
							<img src="{$dpath}img/anomalie_tf.jpeg" style="width: 100%;height: 100%;">
						</div>
						<div class="box"
							data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true" title="{$LNG.tf_txt}"
							style="background: url({$dpath}img/anomalie_single_bg.png) no-repeat left bottom;">
							<div class="quantity">
								Aktiv
							</div>
						</div>
					</div>
					<div class="selectReward" onclick="toggle_test_tf()">
						<a class="mission">{$LNG.ex_a2}</a>
					</div>
				</div> -->
				<!-- #### -->
			</div>
		</div>
	</div>
</div>

<script>
	function toggle_Debris(){
		window.location = "game.php?page=findDebris";
	}
	function toggle_Bonus(){
		window.location = "game.php?page=Bonus";
	}
	function toggle_Tutorial(){
		window.location = "game.php?page=tutorial";
	}
</script>
{/block}	
