<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="{$lang}" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="{$lang}" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="{$lang}" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="{$lang}" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="{$lang}" class="no-js"> <!--<![endif]-->

<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	{assign var="REV" value="1.0.0.8" nocache}

	<!-- Bootstrap 5 - No IE support -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
		  rel="stylesheet"
		  integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
		  crossorigin="anonymous">

	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
			integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p"
			crossorigin="anonymous"></script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
			integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF"
			crossorigin="anonymous"></script>

	<link rel="stylesheet"
		  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">

	<!-- Login CSS -->
	<link rel="stylesheet"
		  type="text/css"
		  href="styles/resource/css/login/main.css?v={$REV}">

	<link rel="stylesheet"
		  type="text/css"
		  href="styles/resource/css/base/jquery.fancybox.css?v={$REV}">

	<link rel="stylesheet"
		  type="text/css"
		  href="styles/resource/css/login/icon-font/style.css?v={$REV}">

	<link rel="stylesheet"
		  href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600"
		  type="text/css">

	<!-- Favicons -->
	<link rel="shortcut icon"
		  href="/styles/resource/favicon/favicon.ico"
		  type="image/x-icon">

	<link rel="apple-touch-icon"
		  sizes="180x180"
		  href="/styles/resource/favicon/apple-touch-icon.png">

	<link rel="icon"
		  type="image/png"
		  sizes="32x32"
		  href="/styles/resource/favicon/favicon-32x32.png">

	<link rel="icon"
		  type="image/png"
		  sizes="16x16"
		  href="/styles/resource/favicon/favicon-16x16.png">

	<link rel="manifest"
		  href="/styles/resource/favicon/site.webmanifest">

	<!-- SEO -->
	<title>{$LNG.seoTitleIndex}</title>

	<meta name="description"
		  content="{$LNG.seoDescriptionIndex}">

	<meta name="keywords"
		  content="{$LNG.seoKeywordsIndex}">

	<meta name="robots"
		  content="index, follow">

	<!-- Canonical -->
	<link rel="canonical"
		  href="index.php?lang={$lang}">

	<!-- Sprachversionen -->
	<link rel="alternate" hreflang="de" href="index.php?lang=de">
	<link rel="alternate" hreflang="en" href="index.php?lang=en">
	<link rel="alternate" hreflang="es" href="index.php?lang=es">
	<link rel="alternate" hreflang="fr" href="index.php?lang=fr">
	<link rel="alternate" hreflang="pl" href="index.php?lang=pl">
	<link rel="alternate" hreflang="pt" href="index.php?lang=pt">
	<link rel="alternate" hreflang="ru" href="index.php?lang=ru">
	<link rel="alternate" hreflang="tr" href="index.php?lang=tr">

	<link rel="alternate"
		  hreflang="x-default"
		  href="index.php?lang=en">

	<!-- Open Graph -->
	<meta property="og:title"
		  content="{$LNG.seoTitleIndex}">

	<meta property="og:type"
		  content="website">

	<meta property="og:url"
		  content="index.php?lang={$lang}">

	<meta property="og:locale"
		  content="{$lang}">

	<meta property="og:description"
		  content="{$LNG.seoDescriptionIndex}">

	<meta property="og:image"
		  content="styles/resource/images/meta.png">

	<!--[if lt IE 9]>
	<script src="scripts/base/html5.js"></script>
	<![endif]-->

	<script src="scripts/base/jquery.js?v={$REV}"></script>
	<script src="scripts/base/jquery.cookie.js?v={$REV}"></script>
	<script src="scripts/base/jquery.fancybox.js?v={$REV}"></script>
	<script src="scripts/login/main.js"></script>

	<script>
		{if isset($code)}
			var loginError = {$code|json};
		{/if}
	</script>

	{block name="script"}{/block}
</head>

<body id="{if isset($smarty.get.page)}{$smarty.get.page|htmlspecialchars|default:'overview'}{/if}"
	  class="{$bodyclass} login">

	<div id="page">