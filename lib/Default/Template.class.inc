<?php

class Template
{
	protected $data = array();
//	protected $ajax = array();
//	protected $currentAjaxID=false;
	protected $captures=array('');
	protected $currentCaptureID=0;
	protected $ignoreMiddle=false;
	protected $nestedIncludes = 0;
	
	public function __construct()
	{
	}
	
	public function hasTemplateVar($var)
	{
		return isset($this->data[$var]);
	}
	
	public function assign($var,$value)
	{
		$this->data[$var] =& $value;
	}
	
	public function unassign($var)
	{
		unset($this->data[$var]);
	}
	
	public function assignByRef($var,&$value)
	{
		$this->data[$var] =& $value;
	}
	
	public function display($templateName,$forAjax=false)
	{
		@ob_clean();
		ob_start();
		$this->includeTemplate($templateName);
		$this->captures[$this->currentCaptureID].=ob_get_clean();
		$output='';
		foreach($this->captures as $captured)
		{
			$output.=$captured;
		}
		$this->trimWhiteSpace($output);
		if($this->data['AJAX_ENABLE_REFRESH'])
		{
			$ajaxXml =& $this->convertHtmlToAjaxXml($output,USING_AJAX);
			if($forAjax)
			{
				header('Content-Type: text/xml; charset: utf-8');
				/* Left out for size: <?xml version="1.0" encoding="ISO-8859-1"?>*/
				$ajaxXml='<all>'.$ajaxXml.'</all>';
				echo $ajaxXml;
			}
			else
			{
				header('Content-Type: text/html; charset: utf-8');
				echo $output;
			}
			SmrSession::saveAjaxReturns();
		}
		else
			echo $output;
	}
	
	
	protected function getTemplateLocation($templateName)
	{
		$templateDir = TEMPLATES_DIR;
		if (isset($this->data['ThisAccount'])&&is_object($this->data['ThisAccount'])&&$this->data['ThisAccount'] instanceof SmrAccount) $templateDir .= $this->data['ThisAccount']->getTemplate() . '/';
		else $templateDir .= 'Default/';

		if (SmrSession::$game_id > 0) $gameDir = Globals::getGameType(SmrSession::$game_id) . '/';
		else $gameDir = 'Default/';

		if (file_exists($templateDir . 'engine/' . $gameDir . $templateName)) return $templateDir . 'engine/' . $gameDir . $templateName;
		elseif (file_exists($templateDir . 'engine/Default/' . $templateName)) return $templateDir . 'engine/Default/' . $templateName;
		elseif (file_exists(TEMPLATES_DIR . 'Default/engine/' . $gameDir . $templateName)) return TEMPLATES_DIR . 'Default/engine/' . $gameDir . $templateName;
		elseif (file_exists(TEMPLATES_DIR . 'Default/engine/Default/' . $templateName)) return TEMPLATES_DIR . 'Default/engine/Default/' . $templateName;
		elseif (file_exists($templateDir . 'admin/' . $gameDir . $templateName)) return $templateDir . 'admin/' . $gameDir . $templateName;
		elseif (file_exists($templateDir . 'admin/Default/' . $templateName)) return $templateDir . 'admin/Default/' . $templateName;
		elseif (file_exists(TEMPLATES_DIR . 'Default/admin/' . $gameDir . $templateName)) return TEMPLATES_DIR . 'Default/admin/' . $gameDir . $templateName;
		elseif (file_exists(TEMPLATES_DIR . 'Default/admin/Default/' . $templateName)) return TEMPLATES_DIR . 'Default/admin/Default/' . $templateName;
		elseif (file_exists($templateDir .  $templateName)) return $templateDir . $templateName;
		elseif (file_exists(TEMPLATES_DIR . 'Default/' . $templateName)) return TEMPLATES_DIR . 'Default/' . $templateName;
		elseif (file_exists($templateDir .  'default.inc')) return $templateDir.'default.inc';
		else return TEMPLATES_DIR.'Default/default.inc';//return $file_name;
	}
	
	protected function includeTemplate($templateName, array $assignVars = null)
	{
		if($this->nestedIncludes > 15)
			throw new Exception('Nested more than 15 template includes, is something wrong?');
		foreach($this->data as $key => &$value)
		{
			$$key=&$value;
			unset($value);
		}
		if($assignVars!==null)
		{
			foreach($assignVars as $key => &$value)
			{
				$$key=&$value;
				unset($value);
			}
		}
		$this->nestedIncludes++;
		require($this->getTemplateLocation($templateName));
		$this->nestedIncludes--;
	}
	
	protected function startCapture()
	{
		$this->captures[$this->currentCaptureID].=ob_get_contents();
		ob_clean();
		$this->currentCaptureID++;
	}
	
	protected function &stopCapture()
	{
		$captured =& $this->captures[$this->currentCaptureID];
		unset($this->captures[$this->currentCaptureID]);
		$captured .= ob_get_contents();
		ob_clean();
		$this->currentCaptureID--;
		return $captured;
	}
	
//	protected function startAJAXCapture($id)
//	{
//		if($this->currentAjaxID!==false)
//			throw new Exception('Already capturing for AJAX');
//		$this->currentAjaxID=$id;
//		$this->startCapture();
//	}
//
//	protected function &storeAJAXCapture()
//	{
//		if($this->currentAjaxID===false)
//			throw new Exception('Not capturing for AJAX');
//		$this->ajax[$this->currentAjaxID] = $this->stopCapture();
//		echo $this->ajax[$this->currentAjaxID];
//		$this->currentAjaxID=false;
//	}
//
//	protected function echoAjaxXml()
//	{
//		foreach($this->ajax as $element => $content)
//		{
//			if(trim($content)!='')
//				echo '<'.$element.'>'.xmlify($this->trimWhiteSpace($content)).'</'.$element.'>';
//		}
//	}
	
	protected function checkDisableAJAX(&$html)
	{
		return preg_match('/<input'.'[^>]*'.'[^(submit)(hidden)(image)]'.'[^>]*'.'>/i', $html)!=0;
	}
	
	protected function trimWhiteSpace(&$html)
	{
		// Pull out the script blocks
		/*    preg_match_all("!<script[^>]*?>.*?</script>!is", $source, $match);
		 $_script_blocks = $match[0];
		 $source = preg_replace("!<script[^>]*?>.*?</script>!is",
		 '@@@SMARTY:TRIM:SCRIPT@@@', $source);
		 */
		// Pull out the pre blocks
		preg_match_all("!<pre[^>]*?>.*?</pre>!is", $html, $match);
		$_pre_blocks = $match[0];
		$html = preg_replace("!<pre[^>]*?>.*?</pre>!is",
			'@@@SMARTY:TRIM:PRE@@@', $html);
		
		// Pull out the textarea blocks
		preg_match_all("!<textarea[^>]*?>.*?</textarea>!is", $html, $match);
		$_textarea_blocks = $match[0];
		$html = preg_replace("!<textarea[^>]*?>.*?</textarea>!is",
			'@@@SMARTY:TRIM:TEXTAREA@@@', $html);
		
		// remove all leading spaces, tabs and carriage returns NOT
		// preceeded by a php close tag.
		$html = preg_replace('/[\s]+/', ' ', $html);
		
		// Pull out the span> <span blocks
		preg_match_all("!</span> <span!is", $html, $match);
		$_span_blocks = $match[0];
		$html = preg_replace("!</span> <span!is",
			'@@@SMARTY:TRIM:SPAN@@@', $html);
		
		$html = trim(preg_replace('/> </', '><', $html));
		
		// replace span blocks
		$this->replaceTrimHolder("@@@SMARTY:TRIM:SPAN@@@",$_span_blocks, $html);
		
		// replace textarea blocks
		$this->replaceTrimHolder("@@@SMARTY:TRIM:TEXTAREA@@@",$_textarea_blocks, $html);
		
		// replace pre blocks
		$this->replaceTrimHolder("@@@SMARTY:TRIM:PRE@@@",$_pre_blocks, $html);
		
		// replace script blocks
//		$this->replaceTrimHolder("@@@SMARTY:TRIM:SCRIPT@@@",$_script_blocks, $html);
	}
	protected function replaceTrimHolder($search_str, $replace, &$subject)
	{
	    $_len = strlen($search_str);
	    $_pos = 0;
	    for ($_i=0, $_count=count($replace); $_i<$_count; $_i++)
	        if (($_pos=strpos($subject, $search_str, $_pos))!==false)
	            $subject = substr_replace($subject, $replace[$_i], $_pos, $_len);
	        else
	            break;
	}
	
	protected function doDamageTypeReductionDisplay(&$damageTypes)
	{
		if($damageTypes == 3)
		{
			echo ', ';
		}
		else if($damageTypes == 2)
		{
			echo ' and ';
		}
		$damageTypes--;
	}
	
	protected function doAn($wordAfter)
	{
		$char = strtoupper($wordAfter[0]);
		if($char == 'A' || $char == 'E' || $char == 'I' || $char == 'O' || $char == 'U')
			echo 'an';
		else
			echo 'a';
	}
	
	protected function &convertHtmlToAjaxXml(&$str,$returnXml)
	{
		if(empty($str))
			return '';

		require_once(LIB.'External/phpQuery/phpQuery.php');
		
		$xml='';
		$html = phpQuery::newDocument($str);
		$searches = array('span[id]','.ajax');
		foreach($searches as $search)
		{
			$spans = $html->find($search);
			foreach($spans as $span)
			{
				$id = $span->getAttribute('id');
				$inner = pq($span)->html();
				if(!SmrSession::addAjaxReturns($id,$inner) && $returnXml)
					$xml.= '<'.$id.'>'.xmlify($inner). '</'.$id.'>';
			}
		}
		if(!$this->ignoreMiddle)
		{
			$doAjaxMiddle=true;
			$searches = array('div#middle_panel span[id]','div#middle_panel .ajax');
			foreach($searches as $search)
			{
				$spans = $html->find($search);
				if(count($spans) > 0)
					$doAjaxMiddle=false;
			}
			if($doAjaxMiddle)
			{
				$mid=$html->find('div#middle_panel');
				if(count($mid) > 0)
				{
					$mid=$mid->get(0);
					$inner = pq($mid)->html();
					if(!$this->checkDisableAJAX($inner))
					{
						$id = $mid->getAttribute('id');
						if(!SmrSession::addAjaxReturns($id,$inner) && $returnXml)
							$xml.= '<'.$id.'>'.xmlify($inner). '</'.$id.'>';
					}
				}
			}
		}
		return $xml;
	}
	
	public function ignoreMiddle()
	{
		$this->ignoreMiddle=true;
	}
}
?>