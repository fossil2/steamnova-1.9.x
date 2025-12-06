{block name="title" prepend}{$LNG.boni_01}{/block}
{block name="content"}
<!-- <link href="{$dpath}bonus.css" as="style"> -->
<link rel="stylesheet" href="{$dpath}anomalie.css">
<link rel="stylesheet" href="{$dpath}bonus.css">
<div id="page_content">
	<div class="card1 itemShow d-flex justify-content-center align-items-start w-100 position-relative border-black">
		<div id="header_text">
			<h2><p>{$LNG['ex_ex']} / {$LNG.boni_01}</p></h2>
		</div>
	</div>
	<div class="card2">
		<div class="resource resource_img"></div>
		<div class="content">
			<h2>{$LNG.boni_01}</h2>
			<span class="level">
				<span class="undermark">{$LNG['boni_03']}</span>
			</span>
			<br style="clear: both;height: 0;font-size: 1px;line-height: 0;">
			<div class="wrapper">
				<div class="features">
					<p>{$LNG.boni_02}
					</p>
					<div>
						{if !$bonus}<span class="fillResource">{$LNG['boni_04.1']}</span>{elseif $bonus}
						<span>{$LNG['boni_04']}</span>
						<ul class="ipiRewardsList">
							<li class="ipiRewardItem">
								<span class="resourceReward resource-0" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" title="<table class='table-tooltip fs-11'><th class='text-start color-yellow' colspan='2'>{$LNG.tech.901}</th></table>"><div>{$bonus_m}</div></span>
							</li>
							<li class="ipiRewardItem">
								<span class="resourceReward resource-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<table class='table-tooltip fs-11'><th class='text-start color-yellow' colspan='2'>{$LNG.tech.902}</th></table>"><div>{$bonus_c}</div></span>
							</li>
							<li class="ipiRewardItem">
								<span class="resourceReward resource-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<table class='table-tooltip fs-11'><th class='text-start color-yellow' colspan='2'>{$LNG.tech.903}</th></table>"><div>{$bonus_d}</div></span>
							</li>
							<li class="ipiRewardItem">
								<span class="resourceReward resource-4" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<table class='table-tooltip fs-11'><th class='text-start color-yellow' colspan='2'>{$LNG.tech.921}</th></table>"><div>{$bonus_dm}</div></span>
							</li>
						</ul>
						{/if}
					</div>
					<br>
				</div>
			</div>
		</div>
		<div class="description">
			<div class="fieldlist">
				{$LNG['boni_05']} {$LNG['boni_06']} &nbsp; &nbsp; <span>{$bonus_time} {$LNG['boni_06.1']}</span> &nbsp; &nbsp; {$LNG['boni_06.2']}
			</div>
		</div>
	</div>
</div>
{/block}