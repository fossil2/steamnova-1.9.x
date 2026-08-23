{block name="title" prepend}{$LNG.ex_em}{/block}

{block name="content"}

<style>
#page_content {
	width: 100%;
	max-width: 100%;
	box-sizing: border-box;
}

/* ===============================
   Header / großes Anomalie-Bild
   =============================== */
.card1 {
	position: relative;
	overflow: hidden;
	min-height: 165px;

	background:
		url('{$dpath}images/anomalie.jpeg')
		no-repeat center center;

	background-size: cover;

	border: 1px solid rgba(58, 118, 178, 0.40);
	border-radius: 10px 10px 0 0;

	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.03),
		0 0 8px rgba(45,120,200,0.13),
		0 3px 8px rgba(0,0,0,0.30);
}

#header_text {
	position: absolute;
	top: 0;
	left: 0;

	width: 100%;
	padding: 7px 10px;

	box-sizing: border-box;

	background: rgba(10,14,20,0.68);

	border-bottom: 1px solid rgba(70,130,190,0.18);

	text-align: center;
}

#header_text h2,
#header_text p {
	margin: 0;
	padding: 0;

	color: #d7e2ef;

	font-size: 14px;
	font-weight: 700;

	text-shadow:
		0 1px 2px rgba(0,0,0,0.85);
}

/* ===============================
   Hauptbereich
   =============================== */
.card2 {
	padding: 14px;

	background: linear-gradient(
		180deg,
		rgba(22,29,40,0.98) 0%,
		rgba(15,20,30,0.98) 52%,
		rgba(9,13,21,1) 100%
	);

	border-left: 1px solid rgba(58,118,178,0.40);
	border-right: 1px solid rgba(58,118,178,0.40);
	border-bottom: 1px solid rgba(58,118,178,0.40);

	border-radius: 0 0 10px 10px;

	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.03),
		0 0 8px rgba(45,120,200,0.10),
		0 3px 8px rgba(0,0,0,0.30);
}

#welcome {
	margin: 0 0 6px 0;

	text-align: center;

	color: #d7e2ef;

	font-size: 14px;
	font-weight: 700;
}

#select_one {
	margin-bottom: 12px;

	text-align: center;

	color: #b9c7d6;

	font-size: 11px;
}

.rewardlist_wrapper {
	width: 100%;
}

/* ===============================
   Zeilen
   =============================== */
.normalRewards {
	display: flex;
	justify-content: center;
	flex-wrap: wrap;

	gap: 12px;

	margin-bottom: 12px;
}

/* ===============================
   Karten
   =============================== */
.singleReward {
	width: 230px;
	max-width: 230px;

	padding: 12px;

	box-sizing: border-box;

	background: linear-gradient(
		180deg,
		rgba(27,35,48,0.98) 0%,
		rgba(17,23,34,0.98) 55%,
		rgba(10,15,24,1) 100%
	);

	border: 1px solid rgba(58,118,178,0.40);
	border-radius: 10px;

	box-shadow:
		0 5px 14px rgba(0,0,0,0.28),
		0 0 7px rgba(45,120,200,0.10),
		inset 0 1px 0 rgba(255,255,255,0.03);

	transition:
		border-color 0.18s ease,
		box-shadow 0.18s ease,
		transform 0.18s ease;
}

.singleReward:hover {
	border-color: rgba(84,155,220,0.65);

	box-shadow:
		0 7px 16px rgba(0,0,0,0.32),
		0 0 8px rgba(45,120,200,0.16),
		inset 0 1px 0 rgba(255,255,255,0.04);

	transform: translateY(-1px);
}

.rewardName {
	margin-bottom: 10px;

	text-align: center;

	color: #d7e2ef;

	font-size: 13px;
	font-weight: 700;
}

/* ===============================
   Bilder
   =============================== */
.itemBox {
	display: flex;
	justify-content: center;
	align-items: center;

	position: relative;

	margin-bottom: 12px;
}

.thumbnail {
	position: relative;

	overflow: hidden;

	border-radius: 8px;

	border: 1px solid rgba(58,118,178,0.40);

	box-shadow:
		0 2px 6px rgba(0,0,0,0.35);
}

.thumbnail img {
	display: block;

	width: 86px;
	height: 86px;

	object-fit: cover;
}

.thumbnail2 {
	opacity: 0.55;
	filter: grayscale(0.35);
}

/* ===============================
   Statusleiste Bild
   =============================== */
.box {
	position: absolute;

	bottom: 0;
	left: 50%;
	transform: translateX(-50%);

	width: 86px;
	height: 20px;

	background: rgba(10,14,20,0.90);

	border-radius: 0 0 8px 8px;
}

.quantity {
	width: 100%;

	box-sizing: border-box;

	padding: 2px 4px;

	text-align: right;

	color: #9dff00;

	font-size: 10px;
	font-weight: 700;

	white-space: nowrap;

	text-shadow:
		0 1px 2px rgba(0,0,0,0.85);
}

.quantity2 {
	color: #ffd600;
}

/* ===============================
   Buttons
   =============================== */
.selectReward {
	display: flex;
	justify-content: center;
}

.mission {
	display: inline-flex;
	align-items: center;
	justify-content: center;

	min-width: 90px;
	min-height: 28px;

	padding: 4px 12px;

	box-sizing: border-box;

	background: linear-gradient(
		180deg,
		rgba(39,83,126,0.96) 0%,
		rgba(25,57,90,0.98) 100%
	);

	border: 1px solid rgba(84,155,220,0.55);
	border-radius: 6px;

	color: #dce9f7;

	font-size: 11px;
	font-weight: 700;

	text-decoration: none;

	cursor: pointer;

	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.05),
		0 2px 4px rgba(0,0,0,0.28);

	transition:
		background 0.18s ease,
		border-color 0.18s ease,
		box-shadow 0.18s ease;
}

.mission:hover {
	background: linear-gradient(
		180deg,
		rgba(48,102,155,0.98) 0%,
		rgba(29,70,111,1) 100%
	);

	border-color: rgba(98,177,240,0.78);

	color: #ffffff;

	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.06),
		0 0 7px rgba(45,120,200,0.18);
}

/* deaktiviert / Countdown */
.mission2 {
	background: linear-gradient(
		180deg,
		rgba(70,70,70,0.96) 0%,
		rgba(42,42,42,0.98) 100%
	);

	border-color: rgba(120,120,120,0.55);

	color: #c8c8c8;

	cursor: default;
}

.mission2:hover {
	background: linear-gradient(
		180deg,
		rgba(70,70,70,0.96) 0%,
		rgba(42,42,42,0.98) 100%
	);

	border-color: rgba(120,120,120,0.55);

	color: #c8c8c8;

	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.04),
		0 2px 4px rgba(0,0,0,0.28);
}

@media only screen and (max-width: 700px) {
	.normalRewards {
		flex-direction: column;
		align-items: center;
	}

	.singleReward {
		width: 100%;
		max-width: 260px;
	}
}
</style>


<div id="page_content">


	<!-- ===============================
	     HEADER
	     =============================== -->

	<div class="card1 itemShow d-flex justify-content-center align-items-start w-100 position-relative">

		<div id="header_text">
			<h2>
				<p>{$LNG.ex_a3}</p>
			</h2>
		</div>

	</div>


	<!-- ===============================
	     CONTENT
	     =============================== -->

	<div class="card2">

		<div id="welcome">
			{$LNG.ex_a}
		</div>

		<div class="rewardlist_wrapper">

			<div id="select_one">
				{$LNG.ex_a1}
			</div>


			<!-- ================= ZEILE 1 ================= -->

			<div class="normalRewards">


				<!-- TRÜMMERFELD -->

				<div class="col w-150 singleReward">

					<div class="rewardName">
						{$LNG.find_debri}
					</div>

					<div class="itemBox">

						<div class="thumbnail">

							<img
								src="{$dpath}img/anomalie_tf.jpeg"
								alt=""
							>

						</div>

						<div
							class="box"
							data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true"
							title="{$LNG.tf_txt}"
						>

							<div class="quantity">
								Aktiv
							</div>

						</div>

					</div>


					<div
						class="selectReward"
						onclick="toggle_Debris()"
					>

						<a class="mission">
							{$LNG.ex_a2}
						</a>

					</div>

				</div>


				<!-- BONUS -->

				<div class="col w-150 singleReward">

					<div class="rewardName">
						{$LNG.boni_01}
					</div>

					<div class="itemBox">

						<div class="thumbnail {if !$bonus}thumbnail2{/if}">

							<img
								src="{$dpath}img/anomalie_bonus.jpeg"
								alt=""
							>

						</div>

						<div
							class="box"
							data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true"
							title="{$LNG['boni_02.1']}"
						>

							<div class="quantity {if !$bonus}quantity2{/if}">

								{if !$bonus}

									{$bonus_time}

								{else}

									{$LNG['boni_01.1']}

								{/if}

							</div>

						</div>

					</div>


					<div
						class="selectReward"
						{if $bonus}
							onclick="toggle_Bonus()"
						{/if}
					>

						<a class="mission {if !$bonus}mission2{/if}">

							{if !$bonus}

								<strong>

									<span
										class="countdown2"
										secs="{$bonus_secs}"
									>
										{$LNG['boni_01.3']}
									</span>

								</strong>

							{else}

								{$LNG['boni_01.2']}

							{/if}

						</a>

					</div>

				</div>


			</div>


			<!-- ================= ZEILE 2 ================= -->

			<div class="normalRewards">


				<!-- TUTORIAL -->

				<div class="col w-150 singleReward">

					<div class="rewardName">
						{$LNG.tut_tut}
					</div>

					<div class="itemBox">

						<div class="thumbnail">

							<img
								src="{$dpath}img/anomalie_tutorial.jpeg"
								alt=""
							>

						</div>

						<div
							class="box"
							data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true"
							title="{$LNG.tut_a01}"
						>

							<div class="quantity">
								Aktiv
							</div>

						</div>

					</div>


					<div
						class="selectReward"
						onclick="toggle_Tutorial()"
					>

						<a class="mission">
							{$LNG.ex_a2}
						</a>

					</div>

				</div>


				<!-- COLLECT MINES -->

				{if $collect_mines_active}

				<div class="col w-150 singleReward">

					<div class="rewardName">
						{$LNG['cm_collect_mines_submit']}
					</div>


					<div class="itemBox">

						<div class="thumbnail {if !$collect_mine_ready}thumbnail2{/if}">

							<img
								src="{$dpath}img/anomalie_secure.jpeg"
								alt=""
							>

						</div>


						<div
							class="box"
							data-bs-toggle="tooltip"
							data-bs-placement="bottom"
							data-bs-html="true"
							title="Sichert alle Rohstoffe auf den aktiven Planeten"
						>

							<div class="quantity {if !$collect_mine_ready}quantity2{/if}">

								{if !$collect_mine_ready}

									{$collect_mine_time}

								{elseif $collect_mine_dm_cost > 0}

									{$collect_mine_dm_cost} DM

								{else}

									Kostenlos

								{/if}

							</div>

						</div>

					</div>


					<div
						class="selectReward"
						{if $collect_mine_ready}
							onclick="toggle_CollectMines()"
						{/if}
					>

						<a class="mission {if !$collect_mine_ready}mission2{/if}">

							{if !$collect_mine_ready}

								<strong>

									<span
										class="countdown2"
										secs="{$collect_mine_secs}"
									>
										{$collect_mine_time}
									</span>

								</strong>

							{else}

								{$LNG.ex_a2}

							{/if}

						</a>

					</div>

				</div>

				{/if}


			</div>

		</div>

	</div>

</div>


<script>
function toggle_Debris()
{
	window.location = "game.php?page=findDebris";
}

function toggle_Bonus()
{
	window.location = "game.php?page=Bonus";
}

function toggle_Tutorial()
{
	window.location = "game.php?page=tutorial";
}

function toggle_CollectMines()
{
	window.location = "game.php?page=collectMines&from=anomalie";
}
</script>

{/block}