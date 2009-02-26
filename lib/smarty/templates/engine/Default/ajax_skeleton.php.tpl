<?xml version="1.0" encoding="ISO-8859-1"?>
<pagecontent>
	<time>
		{$timeDisplay|pagetrimwhitespace|escape}
	</time>
	<runtime>
		{$ScriptRuntime|pagetrimwhitespace|escape}
	</runtime>
	{if !$AJAX_DISABLE_CONTENT_REFRESH}
		{capture assign=HtmlContent}
			{if $PageTopic}<h1>{$PageTopic}</h1><br />{/if}
			{if $MenuBar}{$MenuBar}{/if}
			{include_template template=$TemplateBody assign=Template}{include file=$Template}
		{/capture}
		{if !$HtmlContent|checkdisableajax}
			<htmlcontent>
				{$HtmlContent|pagetrimwhitespace|escape}
			</htmlcontent>
		{/if}
	{/if}
	{if isset($GameID)}
		<rightpanelhtml>
			{capture assign=HtmlContent}
				{include_template template="includes/rightPanel.inc" assign=Template}{include file=$Template}
			{/capture}
			{$HtmlContent|pagetrimwhitespace|escape}
		</rightpanelhtml>
	{/if}
</pagecontent>