<?xml version="1.0" encoding="ISO-8859-1"?>
<pagecontent>
	<tod>
		{$timeDisplay|pagetrimwhitespace|escape}
	</tod>
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
			<middle_panel>
				{$HtmlContent|pagetrimwhitespace|escape}
			</middle_panel>
		{/if}
	{/if}
	<right_panel>
		{capture assign=HtmlContent}
			{include_template template="includes/rightPanel.inc" assign=Template}{include file=$Template}
		{/capture}
		{$HtmlContent|pagetrimwhitespace|escape}
	</right_panel>
</pagecontent>