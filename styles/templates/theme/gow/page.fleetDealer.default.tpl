{block name="title" prepend}{$LNG.lm_fleettrader}{/block}

{block name="content"}

<style>
.fleetdealer-wrapper {
	width: 100%;
	box-sizing: border-box;
	background: linear-gradient(
		180deg,
		rgba(22, 29, 40, 0.98) 0%,
		rgba(15, 20, 30, 0.98) 52%,
		rgba(9, 13, 21, 1) 100%
	);
	border: 1px solid rgba(58, 118, 178, 0.40);
	border-radius: 8px;
	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.03),
		0 0 8px rgba(45,120,200,0.13),
		0 3px 8px rgba(0,0,0,0.30);
	overflow: hidden;
}

.fleetdealer-title {
	padding: 6px 8px;
	background: rgba(18, 25, 36, 0.96);
	border-bottom: 1px solid rgba(70,130,190,0.18);
	color: #ffd600;
	font-size: 12px;
	font-weight: 700;
}

.fleetdealer-body {
	display: flex;
	align-items: flex-start;
	gap: 18px;
	padding: 14px;
}

.fleetdealer-image {
	flex: 0 0 145px;
}

.fleetdealer-image img {
	display: block;
	width: 145px;
	height: 145px;
	object-fit: cover;
	border-radius: 10px; /* vorher z.B. 6px */
	border: 1px solid rgba(58,118,178,0.40);
	box-shadow:
		0 2px 6px rgba(0,0,0,0.35),
		0 0 6px rgba(45,120,200,0.10);
}

#traderHead {
	margin: 0;
	color: #ffd600;
	font-size: 18px;   /* kleiner */
	line-height: 1.2;
	font-weight: 500;
}

.fleetdealer-select select {
	min-width: 150px;
	padding: 4px 6px;
	background: rgba(14,18,26,0.96);
	border: 1px solid rgba(58,118,178,0.45);
	border-radius: 5px;
	color: #ffd600;
	font-size: 12px;   /* kleiner */
}

.fleetdealer-count {
	display: flex;
	align-items: center;
	flex-wrap: wrap;
	gap: 5px;
	margin: 8px 0 12px;
	color: #ffd600;
	font-size: 12px;   /* kleiner */
}

.fleetdealer-count input[type="text"] {
	width: 155px;
	padding: 4px 6px;
	background: rgba(14,18,26,0.96);
	border: 1px solid rgba(58,118,178,0.45);
	border-radius: 5px;
	color: #ffd600;
	text-align: center;
	font-size: 12px;   /* kleiner */
}

.fleetdealer-count button {
	padding: 4px 7px;
	background: linear-gradient(
		180deg,
		rgba(34,42,56,0.98) 0%,
		rgba(20,26,38,0.98) 100%
	);
	border: 1px solid rgba(58,118,178,0.45);
	border-radius: 5px;
	color: #ffd600;
	cursor: pointer;
	font-size: 12px;   /* kleiner */
}

.fleetdealer-line {
	margin: 10px 0;
	color: #ffd600;
	line-height: 1.5;
	word-break: normal;
	font-size: 12px;   /* kleiner */
}

.fleetdealer-submit input {
	padding: 5px 10px;
	background: linear-gradient(
		180deg,
		rgba(39,83,126,0.96) 0%,
		rgba(25,57,90,0.98) 100%
	);
	border: 1px solid rgba(84,155,220,0.55);
	border-radius: 5px;
	color: #ffd600;
	cursor: pointer;
	font-size: 12px;   /* kleiner */
}

.fleetdealer-charge {
	margin-top: 8px;
	color: #ffd600;
	font-size: 12px;   /* kleiner */
}

@media only screen and (max-width: 700px) {
	.fleetdealer-body {
		flex-direction: column;
	}

	.fleetdealer-image {
		flex: none;
	}

	.fleetdealer-top {
		flex-direction: column;
	}

	#traderHead {
		font-size: 21px;
	}
}
</style>


<form action="game.php?page=fleetDealer" method="post" autocomplete="off">

	<input type="hidden" name="mode" value="send">

	<div class="fleetdealer-wrapper">

		<div class="fleetdealer-title">
			{$LNG.ft_head}
		</div>

		<div class="fleetdealer-body">

			<div class="fleetdealer-image">
				<img
					id="img"
					alt=""
					data-src="{$dpath}gebaeude/"
				>
			</div>

			<div class="fleetdealer-content">

				<div class="fleetdealer-top">

					<h2 id="traderHead"></h2>

					<div class="fleetdealer-select">
						<select
							class="text-center text-yellow"
							name="shipID"
							id="shipID"
							onchange="updateVars()"
						>
							{foreach $shipIDs as $shipID}
								<option value="{$shipID}">
									{$LNG.tech.$shipID}
								</option>
							{/foreach}
						</select>
					</div>

				</div>


				<div class="fleetdealer-count">

					<span>{$LNG.ft_count}:</span>

					<input
						type="text"
						id="count"
						name="count"
						value="0"
						autocomplete="off"
						onkeyup="Total();"
					>

					<button
						type="button"
						onclick="MaxShips();"
					>
						{$LNG.ft_max}
					</button>

				</div>


				<div class="fleetdealer-line">

					{$LNG.tech.901}:
					<span id="metal" style="font-weight:800;"></span>

					&bull;

					{$LNG.tech.902}:
					<span id="crystal" style="font-weight:800;"></span>

					&bull;

					{$LNG.tech.903}:
					<span id="deuterium" style="font-weight:800;"></span>

					&bull;

					{$LNG.tech.921}:
					<span id="darkmatter" style="font-weight:800;"></span>

				</div>


				<div class="fleetdealer-line">

					{$LNG.ft_total}:

					{$LNG.tech.901}:
					<span id="total_metal" style="font-weight:800;"></span>

					&bull;

					{$LNG.tech.902}:
					<span id="total_crystal" style="font-weight:800;"></span>

					&bull;

					{$LNG.tech.903}:
					<span id="total_deuterium" style="font-weight:800;"></span>

					&bull;

					{$LNG.tech.921}:
					<span id="total_darkmatter" style="font-weight:800;"></span>

				</div>


				<div class="fleetdealer-submit">

					<input
						class="text-center"
						type="submit"
						value="{$LNG.ft_absenden}"
					>

				</div>


				<div class="fleetdealer-charge">

					{$LNG.ft_charge}: {$Charge}%

				</div>

			</div>

		</div>

	</div>

</form>


{block name="script" append}

<script src="scripts/game/fleettrader.js"></script>

<script>
var CostInfo = {$CostInfos|json};
var Charge = {$Charge};

$(function(){
	updateVars();
});
</script>

{/block}

{/block}