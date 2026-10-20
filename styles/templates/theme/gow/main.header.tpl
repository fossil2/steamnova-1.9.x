<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{block name="title"} - {$uni_name} - {$game_name}{/block}</title>
	<link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">

	{if !empty($goto)}
	<meta http-equiv="refresh" content="{$gotoinsec};URL={$goto}">
	{/if}
	
	<!-- JavaScript Core -->
	<script src="{"./scripts/base/jquery.js"|auto_version}"></script>
	<script src="{"./scripts/base/jquery.ui.js"|auto_version}"></script>
	<!-- <script src="{"./scripts/base/jquery.cookie.js"|auto_version}"></script> -->
	<script src="{"./scripts/base/jquery.validationEngine.js"|auto_version}"></script>
	<script src="{"./scripts/l18n/validationEngine/jquery.validationEngine-{$lang}.js"|auto_version}"></script>

	<!-- CSS Frameworks & Stylesheets -->
	<link rel="stylesheet" href="{"./styles/resource/bootstrap_5.3.3/css/bootstrap.min.css"|auto_version}">
	<link rel="stylesheet" href="{"./styles/resource/bootstrap_5.3.3/bootstrap-icons.css"|auto_version}">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.12/css/all.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>

	<!-- Game Base Styles (Automatisch versioniert) -->
	<link rel="stylesheet" type="text/css" href="{"./styles/resource/css/base/boilerplate.css"|auto_version}">
	<link rel="stylesheet" type="text/css" href="{"./styles/resource/css/base/jquery.css"|auto_version}">
	<link rel="stylesheet" type="text/css" href="{"./styles/resource/css/base/validationEngine.jquery.css"|auto_version}">
	<link rel="stylesheet" type="text/css" href="{"{$dpath}formate.css"|auto_version}">
	<link rel="stylesheet" type="text/css" href="{"{$dpath}global.css"|auto_version}">
	<link rel="stylesheet" type="text/css" href="{"{$dpath}kb.css"|auto_version}">

	<!-- Core JavaScript Variables -->
	<script>
	var ServerTimezoneOffset = {$Offset};
	var serverTime 	= new Date({$date.0}, {$date.1 - 1}, {$date.2}, {$date.3}, {$date.4}, {$date.5});
	var startTime	= serverTime.getTime();
	var localTime 	= serverTime;
	var localTS 	= startTime;
	var Gamename	= document.title;
	var Ready		= "{$LNG.ready}";
	var Skin		= "{$dpath}";
	var Lang		= "{$lang}";
	var head_info	= "{$LNG.fcm_info}";
	var auth		= {$authlevel|default:'0'};
	var days 		= {$LNG.week_day|json|default:'[]'};
	var months 		= {$LNG.months|json|default:'[]'};
	var tdformat	= "{$LNG.js_tdformat}";
	var queryString	= "{$queryString|escape:'javascript'}";
	var isPlayerCardActive	= "{$isPlayerCardActive|json}";
	var relativeTime = Math.floor(Date.now() / 1000);
	var attackListenTime = {$attackListenTime};

	setInterval(function() {
		if(relativeTime < Math.floor(Date.now() / 1000)) {
			serverTime.setSeconds(serverTime.getSeconds()+1);
			relativeTime++;
		}
	}, 1);
	</script>

	<!-- Plugins -->
	<script src="{"./scripts/game/base.js"|auto_version}"></script>
	
	{foreach item=scriptname from=$scripts}
	<script src="{"./scripts/game/{$scriptname}.js"|auto_version}"></script>
	{/foreach}
	
	{if isModuleAvailable($smarty.const.MODULE_ATTACK_ALERT)}
	<script>var attackListenTime = {$attackListenTime};</script>
	<script src="{"./scripts/game/attackAlert.js"|auto_version}"></script>
	{/if}

	<!-- Vendor Component Scripts -->
	<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
	<script src="{"./styles/resource/bootstrap_5.3.3/js/bootstrap.bundle.min.js"|auto_version}"></script>
	<script src="{"./scripts/game/overview.js"|auto_version}"></script>

	<!-- Bootstrap Component Initialization -->
	<script>
	$(() => {
		// Holt die originale Whitelist von Bootstrap 5 (heißt jetzt allowList)
		const myDefaultAllowList = bootstrap.Tooltip.Default.allowList;
		
		// Whitelist-Erweiterungen für das Spiel
		myDefaultAllowList.table = ['class','style'];
		myDefaultAllowList.tbody = [];
		myDefaultAllowList.thead = [];
		myDefaultAllowList.th = ['colspan'];
		myDefaultAllowList.tr = [];
		myDefaultAllowList.td = ['colspan','style'];
		myDefaultAllowList.span = ['class','onclick'];
		myDefaultAllowList.img = ['src','alt','width','height'];
		myDefaultAllowList.form = ['class','action','method'];
		myDefaultAllowList.input = ['type','name','value'];
		myDefaultAllowList.button = ['type','class','onclick','style'];
		myDefaultAllowList.font = ['color'];
		myDefaultAllowList.a = ['href','class','onclick'];
		myDefaultAllowList.br = [];

		// Tooltips aktivieren (Nativer Bootstrap-Aufruf über Vanilla JS)
		const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
		[...tooltipTriggerList].map(el => new bootstrap.Tooltip(el, {
			container: 'body',
			html: true,
			allowList: myDefaultAllowList
		}));

		// Popover aktivieren (Nativer Bootstrap-Aufruf über Vanilla JS)
		const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
		[...popoverTriggerList].map(el => new bootstrap.Popover(el, {
			container: 'body',
			html: true,
			allowList: myDefaultAllowList
		}));
	});
	</script>
</head>