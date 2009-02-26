<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	<title>{$Title} {$GameName}</title>
	<meta http-equiv="pragma" content="no-cache">
	<link rel="stylesheet" type="text/css" href="{$CSSLink}">
	<!--[if IE]>
		<link rel="stylesheet" type="text/css" href="ie_specific.css">
	<![endif]-->
	<!--<script type="text/javascript" src="js/smr.js"></script>-->
	<script type="text/javascript" src="js/smr15.js"></script>
	<script type="text/javascript" src="js/ajax.js"></script>
	{if $js}
		<script type="text/javascript" src="{$js}"></script>
	{/if}
	{if $AJAX_ENABLE_REFRESH}
		<script type="text/javascript">window.onload=function(){ldelim}startRefresh('{$AJAX_ENABLE_REFRESH}');{rdelim}</script>
	{/if}
	<style type="text/css">
		body {ldelim}
			font-size:{$fontSize}%;
		{rdelim}
	</style>
	{if isset($HeaderTemplateInclude)}
		{include_template template=$HeaderTemplateInclude assign=Template}{include file=$Template}
	{/if}
	</head>
	<body {$bodyTag}>