{block name="title" prepend}{$LNG.find_deb_title}{/block}
{block name="content"}
<link rel="stylesheet" href="{$dpath}anomalie.css">
<link rel="stylesheet" href="{$dpath}finddebris.css">
<div id="page_content">
	<div class="card1 itemShow d-flex justify-content-center align-items-start w-100 position-relative border-black">
		<div id="header_text">
			<h2><p>{$LNG['ex_ex']} / {$LNG.find_debri}</p></h2>
		</div>
	</div>
	<div class="card2">
		<div class="resource resource_img"></div>
		<div class="content">
			<h2>{$LNG.find_debri}</h2>
			<span class="level">
				<span class="undermark">{$LNG.find_deb_range} {$range} {if $range >1}Systeme{else}System{/if}</span>
			</span>
			<br style="clear: both;height: 0;font-size: 1px;line-height: 0;">
			<div class="wrapper">
				<div class="features">
					<p>{$LNG.tf_txt}
					</p>
					<div>
						{$debris}
					</div>
					<br>
				</div>
			</div>
		</div>
		<div class="description">
			<div class="fieldlist">
				<table width="100%" cellpadding="0" cellspacing="1">
					<tr id="fleetstatusrow">
						<th colspan="6">{$LNG.find_deb_fleet}</th>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
	var MaxFleetSetting = {$user_maxfleetsettings};
	function doit(missionID, planetID){
		$.getJSON("game.php?page=fleetAjax&ajax=1&mission="+missionID+"&planetID="+planetID, function(data)
		{
			$('#slots').text(data.slots);
			if(typeof data.ships !== "undefined")
			{
				$.each(data.ships, function(elementID, value) {
					$('#elementID'+elementID).text(number_format(value));
				});
			}
			var statustable	= $('#fleetstatusrow');
			var messages	= statustable.find("~tr");
			if(messages.length == MaxFleetSetting){
				messages.filter(':last').remove();
			}
			var element		= $('<td />').attr('colspan', 8).attr('class', data.code == 600 ? "success" : "error").text(data.mess).wrap('<tr />').parent();
			statustable.removeAttr('style').after(element);
		});
	}
</script>
{/block}