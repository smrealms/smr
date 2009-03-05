<?php echo '<?xml version="1.0" encoding="ISO-8859-1"?>'; ?>
<pagecontent>
	<tod><?php echo htmlentities($this->trimWhiteSpace($timeDisplay)) ?></tod>
	<runtime><?php echo htmlentities($this->trimWhiteSpace($ScriptRuntime)) ?></runtime><?php
	echo $AjaxXML;
/*	if(!$AJAX_DISABLE_CONTENT_REFRESH)
	{
		$this->startCapture();
		if($PageTopic)
		{ ?>
			<h1><?php echo $PageTopic ?></h1><br /><?php
		}
		if($MenuBar)
		{
			echo $MenuBar;
		}
		$this->includeTemplate($TemplateBody);
		$HtmlContent =& $this->stopCapture();
		if(!$this->checkDisableAJAX($HtmlContent)&&!$this->echoHtmlForAjax($HtmlContent))
		{ ?>
			<middle_panel>
				<?php echo htmlentities($this->trimWhiteSpace($HtmlContent)) ?>
			</middle_panel><?php
		}
	} ?>
	<?php 
		$this->startCapture();
		$this->includeTemplate('includes/rightPanel.inc');
		$HtmlContent =& $this->stopCapture();
		$this->echoHtmlForAjax($HtmlContent);*/
	?>
</pagecontent>