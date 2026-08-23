{block name="title" prepend}{$LNG.lm_empire}{/block}

{block name="content"}

<style>
.imperium-wrapper {
	width: 100%;
	max-width: 100%;
	min-width: 0;
	box-sizing: border-box;

	background: linear-gradient(
		180deg,
		rgba(22, 29, 40, 0.98) 0%,
		rgba(15, 20, 30, 0.98) 52%,
		rgba(9, 13, 21, 1) 100%
	);

	border: 1px solid rgba(58, 118, 178, 0.40);
	border-radius: 10px;

	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.03),
		0 0 8px rgba(45,120,200,0.13),
		0 3px 8px rgba(0,0,0,0.35);

	overflow: hidden;
}

.imperium-title {
	padding: 8px 10px;

	text-align: center;

	color: #ffd600;

	font-size: 13px;
	font-weight: 700;

	background: rgba(18, 25, 36, 0.96);

	border-bottom: 1px solid rgba(70, 130, 190, 0.18);
}

.imperium-tabs {
	display: flex;
	flex-wrap: wrap;

	gap: 8px;

	padding: 10px;

	border-bottom: 1px solid rgba(70, 130, 190, 0.18);
}

.imperium-tab {
	padding: 6px 10px;

	cursor: pointer;

	color: #d7e2ef;

	background: linear-gradient(
		180deg,
		rgba(34, 42, 56, 0.98) 0%,
		rgba(20, 26, 38, 0.98) 52%,
		rgba(11, 16, 25, 1) 100%
	);

	border: 1px solid rgba(58, 118, 178, 0.35);
	border-radius: 7px;

	font-size: 11px;
	font-weight: 600;

	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.04),
		0 2px 4px rgba(0,0,0,0.25);

	transition:
		background 0.18s ease,
		border-color 0.18s ease,
		box-shadow 0.18s ease;
}

.imperium-tab:hover {
	background: linear-gradient(
		180deg,
		rgba(52, 72, 98, 1) 0%,
		rgba(28, 45, 68, 1) 50%,
		rgba(16, 28, 46, 1) 100%
	);

	border-color: rgba(84, 155, 220, 0.75);

	box-shadow:
		inset 0 1px 0 rgba(255,255,255,0.08),
		0 0 8px rgba(45,120,200,0.22);
}

.imperium-tab.active {
	background: linear-gradient(
		180deg,
		rgba(28, 78, 125, 1) 0%,
		rgba(16, 53, 92, 1) 52%,
		rgba(10, 33, 62, 1) 100%
	);

	border-color: rgba(88, 170, 235, 0.90);

	color: #ffd600;
}

.imperium-scroll {
	width: 100%;
	max-width: 100%;
	min-width: 0;

	overflow-x: auto;
	overflow-y: hidden;
}

.imperium-scroll::-webkit-scrollbar {
	height: 6px;
}

.imperium-scroll::-webkit-scrollbar-track {
	background: rgba(10, 14, 20, 0.90);
}

.imperium-scroll::-webkit-scrollbar-thumb {
	background: rgba(58, 118, 178, 0.55);
	border-radius: 6px;
}

.imperium-scroll::-webkit-scrollbar-thumb:hover {
	background: rgba(84, 155, 220, 0.75);
}

.imperium-table {
	width: max-content;
	min-width: 100%;

	margin: 0;

	border-collapse: separate;
	border-spacing: 0;

	background: rgba(14, 18, 26, 0.96);

	color: #ffd600;
}

.imperium-table td,
.imperium-table th {
	padding: 5px 7px;

	border-right: 1px solid rgba(70, 130, 190, 0.15);
	border-bottom: 1px solid rgba(70, 130, 190, 0.15);

	white-space: nowrap;
}

.imperium-table th {
	background: rgba(18, 25, 36, 0.96);

	color: #ffd600;
	font-weight: 700;
}

.imperium-table td:first-child,
.imperium-table th:first-child {
	position: sticky;
	left: 0;

	z-index: 4;

	background: rgba(17, 22, 32, 0.99);
}

.imperium-table a {
	color: #ffd600;
	text-decoration: none;
}

.imperium-table a:hover {
	color: #ffffff;
	text-decoration: underline;
}

.imperium-planet {
	min-width: 115px;
	text-align: center;
}

.imperium-planet-img {
	width: 76px;
	height: 76px;

	object-fit: cover;

	border-radius: 8px;

	border: 1px solid rgba(58, 118, 178, 0.38);

	box-shadow:
		0 2px 6px rgba(0,0,0,0.35),
		0 0 6px rgba(45,120,200,0.10);

	transition:
		border-color 0.18s ease,
		box-shadow 0.18s ease,
		transform 0.18s ease;
}

.imperium-planet-img:hover {
	border-color: rgba(84, 155, 220, 0.75);

	box-shadow:
		0 0 8px rgba(45,120,200,0.20),
		0 3px 8px rgba(0,0,0,0.35);

	transform: translateY(-1px);
}

.imperium-total {
	font-size: 36px;

	color: #ffd600;

	text-align: center;
}

.imperium-income {
	color: #00ff66;
}

.imperium-section {
	display: none;
}

.imperium-section.active {
	display: table-row-group;
}

.imperium-section-title {
	text-align: center;
}
</style>


<script>
$(document).ready(function() {

	$('.imperium-tab').on('click', function() {

		const section = $(this).data('section');

		$('.imperium-tab').removeClass('active');
		$(this).addClass('active');

		$('.imperium-section').removeClass('active');

		if (section !== 'overview') {
			$('.imperium-section[data-section="' + section + '"]')
				.addClass('active');
		}
	});

});
</script>


<div class="imperium-wrapper">

	<div class="imperium-title">
		{$LNG.lv_imperium_title}
	</div>

	<div class="imperium-tabs">

	<div class="imperium-tab active" data-section="overview">
		{$LNG.lm_overview}
	</div>

	<div class="imperium-tab" data-section="resources">
		{$LNG.lv_resources}
	</div>

	<div class="imperium-tab" data-section="buildings">
		{$LNG.lv_buildings}
	</div>

	<div class="imperium-tab" data-section="technology">
		{$LNG.lv_technology}
	</div>

	<div class="imperium-tab" data-section="fleet">
		{$LNG.lv_ships}
	</div>

	<div class="imperium-tab" data-section="defense">
		{$LNG.lv_defenses}
	</div>

	<div class="imperium-tab" data-section="missiles">
		{$LNG.tech.500}
	</div>

</div>
	
	
	


	<div class="imperium-scroll">

		<table class="imperium-table fs-12">


			<!-- PLANETENKOPF - IMMER SICHTBAR -->

			<tbody class="imperium-head">

				<tr>

					<td style="width:110px;">
						{$LNG.lv_planet}
					</td>

					<td class="imperium-total" style="width:90px;">
						&Sigma;
					</td>

					{foreach $planetList.image as $planetID => $image}

						<td class="imperium-planet">

							<a href="game.php?page=overview&amp;cp={$planetID}">

								<img
									class="imperium-planet-img"
									src="{$dpath}planeten/{$image}.jpg"
									alt=""
								>

							</a>

						</td>

					{/foreach}

				</tr>


				<tr>

					<td>
						{$LNG.lv_name}
					</td>

					<td>
						{$LNG.lv_total}
					</td>

					{foreach $planetList.name as $name}
						<td>{$name}</td>
					{/foreach}

				</tr>


				<tr>

					<td>
						{$LNG.lv_coords}
					</td>

					<td>
						-
					</td>

					{foreach $planetList.coords as $coords}

						<td>

							<a href="game.php?page=galaxy&amp;galaxy={$coords.galaxy}&amp;system={$coords.system}">

								[{$coords.galaxy}:{$coords.system}:{$coords.planet}]

							</a>

						</td>

					{/foreach}

				</tr>


				<tr>

					<td>
						{$LNG.lv_fields}
					</td>

					<td>
						-
					</td>

					{foreach $planetList.field as $field}

						<td>
							{$field.current} / {$field.max}
						</td>

					{/foreach}

				</tr>

			</tbody>


			<!-- ROHSTOFFE -->

			<tbody
				class="imperium-section"
				data-section="resources"
			>

				<tr>

					<th
						class="imperium-section-title"
						colspan="{$colspan}"
					>
						{$LNG.lv_resources}
					</th>

				</tr>


				{foreach $planetList.resource as $elementID => $resourceArray}

					<tr>

						<td>

							<a
								href="#"
								onclick="return Dialog.info({$elementID});"
							>
								{$LNG.tech.$elementID}
							</a>

						</td>


						<td>

							{array_sum($resourceArray)|number}

							{if in_array($elementID, array(901,902,903))}

								<span class="imperium-income">

									{array_sum($planetList.resourcePerHour[$elementID])|number}/h

								</span>

							{/if}

						</td>


						{foreach $resourceArray as $planetID => $resource}

							<td>

								{$resource|number}

								{if in_array($elementID, array(901,902,903))
									&& $planetList.planet_type[$planetID] == 1}

									<span class="imperium-income">

										{$planetList.resourcePerHour[$elementID][$planetID]|number}/h

									</span>

								{/if}

							</td>

						{/foreach}

					</tr>

				{/foreach}

			</tbody>


			<!-- GEBÄUDE -->

			<tbody
				class="imperium-section"
				data-section="buildings"
			>

				<tr>

					<th
						class="imperium-section-title"
						colspan="{$colspan}"
					>
						{$LNG.lv_buildings}
					</th>

				</tr>


				{foreach $planetList.build as $elementID => $buildArray}

					<tr>

						<td>

							<a
								href="#"
								onclick="return Dialog.info({$elementID})"
							>
								{$LNG.tech.$elementID}
							</a>

						</td>

						<td>
							{array_sum($buildArray)|number}
						</td>


						{foreach $buildArray as $planetID => $build}

							<td>
								{$build|number}
							</td>

						{/foreach}

					</tr>

				{/foreach}

			</tbody>


			<!-- FORSCHUNG -->

			<tbody
				class="imperium-section"
				data-section="technology"
			>

				<tr>

					<th
						class="imperium-section-title"
						colspan="{$colspan}"
					>
						{$LNG.lv_technology}
					</th>

				</tr>


				{foreach $planetList.tech as $elementID => $tech}

					<tr>

						<td>

							<a
								href="#"
								onclick="return Dialog.info({$elementID})"
							>
								{$LNG.tech.$elementID}
							</a>

						</td>

						<td>
							{$tech|number}
						</td>


						{foreach $planetList.name as $name}

							<td>
								{$tech|number}
							</td>

						{/foreach}

					</tr>

				{/foreach}

			</tbody>


			<!-- SCHIFFE -->

			<tbody
				class="imperium-section"
				data-section="fleet"
			>

				<tr>

					<th
						class="imperium-section-title"
						colspan="{$colspan}"
					>
						{$LNG.lv_ships}
					</th>

				</tr>


				{foreach $planetList.fleet as $elementID => $fleetArray}

					<tr>

						<td>

							<a
								href="#"
								onclick="return Dialog.info({$elementID})"
							>
								{$LNG.tech.$elementID}
							</a>

						</td>


						<td>
							{array_sum($fleetArray)|number}
						</td>


						{foreach $fleetArray as $planetID => $fleet}

							<td>
								{$fleet|number}
							</td>

						{/foreach}

					</tr>

				{/foreach}

			</tbody>


			<!-- VERTEIDIGUNG -->

			<tbody
				class="imperium-section"
				data-section="defense"
			>

				<tr>

					<th
						class="imperium-section-title"
						colspan="{$colspan}"
					>
						{$LNG.lv_defenses}
					</th>

				</tr>


				{foreach $planetList.defense as $elementID => $fleetArray}

					<tr>

						<td>

							<a
								href="#"
								onclick="return Dialog.info({$elementID})"
							>
								{$LNG.tech.$elementID}
							</a>

						</td>


						<td>
							{array_sum($fleetArray)|number}
						</td>


						{foreach $fleetArray as $planetID => $fleet}

							<td>
								{$fleet|number}
							</td>

						{/foreach}

					</tr>

				{/foreach}

			</tbody>


			<!-- RAKETEN -->

			<tbody
				class="imperium-section"
				data-section="missiles"
			>

				<tr>

					<th
						class="imperium-section-title"
						colspan="{$colspan}"
					>
						{$LNG.tech.500}
					</th>

				</tr>


				{foreach $planetList.missiles as $elementID => $fleetArray}

					<tr>

						<td>

							<a
								href="#"
								onclick="return Dialog.info({$elementID})"
							>
								{$LNG.tech.$elementID}
							</a>

						</td>


						<td>
							{array_sum($fleetArray)|number}
						</td>


						{foreach $fleetArray as $planetID => $fleet}

							<td>
								{$fleet|number}
							</td>

						{/foreach}

					</tr>

				{/foreach}

			</tbody>


		</table>

	</div>

</div>

{/block}