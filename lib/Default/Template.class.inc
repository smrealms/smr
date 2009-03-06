<?php
require_once(LIB.'simplehtmldom/simple_html_dom.php');
class Template
{
	protected $data = array();
	protected $ajax = array();
	protected $currentAjaxID=false;
	protected $captures=array();
	protected $currentCaptureID=0;
	protected $ignoreMiddle=false;
	
	protected $doAjaxMiddle=true;
	
	public function __construct()
	{
	}
	
	public function assign($var,$value)
	{
		$this->data[$var] =& $value;
	}
	
	public function assignByRef($var,&$value)
	{
		$this->data[$var] =& $value;
	}
	
	public function display($templateName,$forAjax=false)
	{
		if($forAjax)
			header('Content-Type: text/xml');
		ob_clean();
		ob_start();
		$this->includeTemplate($templateName);
		$this->captures[$this->currentCaptureID].=ob_get_clean();
		$output='';
		foreach($this->captures as $captured)
		{
			$output.=$captured;
		}
		$ajaxXml =& $this->convertHtmlToAjaxXml($output);
		if($forAjax)
		{
			/* Left out for size: <?xml version="1.0" encoding="ISO-8859-1"?>*/
			$ajaxXml='<all>'.$ajaxXml.'</all>';
			echo $this->trimWhiteSpace($ajaxXml);
		}
		else
			echo $this->trimWhiteSpace($output);
		SmrSession::saveAjaxReturns();
	}
	
	
	protected function getTemplateLocation($templateName)
	{
		if (SmrSession::$game_id > 0)
		{
			$stored_dir = Globals::getGameType(SmrSession::$game_id) . '/';
			$gameDir = $stored_dir;
		} else $gameDir = 'Default/';
		if (file_exists(TEMPLATES_DIR . 'engine/' . $gameDir . $templateName)) return TEMPLATES_DIR . 'engine/' . $gameDir . $templateName;
		elseif (file_exists(TEMPLATES_DIR . 'engine/' . 'Default/' . $templateName)) return TEMPLATES_DIR . 'engine/' . 'Default/' . $templateName;
		elseif (file_exists(TEMPLATES_DIR . 'admin/' . $gameDir . $templateName)) return TEMPLATES_DIR . 'admin/' . $gameDir . $templateName;
		elseif (file_exists(TEMPLATES_DIR . 'admin/' . 'Default/' . $templateName)) return TEMPLATES_DIR . 'admin/' . 'Default/' . $templateName;
		elseif (file_exists(TEMPLATES_DIR .  $templateName)) return TEMPLATES_DIR . $templateName;
		else return TEMPLATES_DIR.'default.inc';//return $file_name;
	}
	
	protected function includeTemplate($templateName, array $assignVars = null)
	{
		foreach($this->data as $key => $value)
		{
			$$key=&$value;
			unset($value);
		}
		if($assignVars!==null)
		{
			foreach($assignVars as $key => $value)
			{
				$$key=&$value;
				unset($value);
			}
		}
		require_once($this->getTemplateLocation($templateName));
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
	
	protected function startAJAXCapture($id)
	{
		if($this->currentAjaxID!==false)
			throw new Exception('Already capturing for AJAX');
		$this->currentAjaxID=$id;
		$this->startCapture();
	}
	
	protected function &storeAJAXCapture()
	{
		if($this->currentAjaxID===false)
			throw new Exception('Not capturing for AJAX');
		$this->ajax[$this->currentAjaxID] = $this->stopCapture();
		echo $this->ajax[$this->currentAjaxID];
		$this->currentAjaxID=false;
	}
	
	protected function echoAjaxXml()
	{
		foreach($this->ajax as $element => $content)
		{
			if(trim($content)!='')
				echo '<'.$element.'>'.htmlentities($this->trimWhiteSpace($content)).'</'.$element.'>';
		}
	}
	
	protected function checkDisableAJAX(&$html)
	{
		return preg_match('/<input'.'[^>]*'.'[^(submit)(hidden)(image)]'.'[^>]*'.'>/i', $html)!=0;
	}
	
	protected function &trimWhiteSpace(&$html)
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
		$source = preg_replace("!<pre[^>]*?>.*?</pre>!is",
			'@@@SMARTY:TRIM:PRE@@@', $html);
		
		// Pull out the textarea blocks
		preg_match_all("!<textarea[^>]*?>.*?</textarea>!is", $html, $match);
		$_textarea_blocks = $match[0];
		$source = preg_replace("!<textarea[^>]*?>.*?</textarea>!is",
			'@@@SMARTY:TRIM:TEXTAREA@@@', $html);
		
		// remove all leading spaces, tabs and carriage returns NOT
		// preceeded by a php close tag.
		$html = preg_replace('/[\s]+/', ' ', $html);
		$html = trim(preg_replace('/> </', '><', $html));
		
		// replace textarea blocks
		$this->replaceTrimHolder("@@@SMARTY:TRIM:TEXTAREA@@@",$_textarea_blocks, $html);
		
		// replace pre blocks
		$this->replaceTrimHolder("@@@SMARTY:TRIM:PRE@@@",$_pre_blocks, $html);
		
		// replace script blocks
//		$this->replaceTrimHolder("@@@SMARTY:TRIM:SCRIPT@@@",$_script_blocks, $html);
		
		return $html;
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
	
	protected function doDamageTypeReductionDisplay($damageTypes)
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
	
	protected function &convertHtmlToAjaxXml(&$str)
	{
		$xml='';
		$str =& $this->trimWhiteSpace($str);
		$html = new simple_html_dom();
		$html->load($str);
		$searches = array('span[id]','[class=ajax]');
		foreach($searches as $search)
		{
			$spans = $html->find($search);
			foreach($spans as $span)
			{
				$id = $span->getAttribute('id');
				$inner = $span->innertext();
				if(!SmrSession::addAjaxReturns($id,$inner))
					$xml.= '<'.$id.'>'.htmlentities($inner). '</'.$id.'>';
			}
		}
		if($this->doAjaxMiddle && !$this->ignoreMiddle)
		{
			$mid=$html->find('div#middle_panel');
			if(!empty($mid))
			{
				$mid=$mid[0];
				$inner = $mid->innertext();
				if(!$this->checkDisableAJAX($inner))
				{
					$id = $mid->getAttribute('id');
					if(!SmrSession::addAjaxReturns($id,$inner))
						$ajaxXml.= '<'.$id.'>'.htmlentities($inner). '</'.$id.'>';
				}
			}
		}
		return $xml;
	}
	protected function startMiddle()
	{
		$this->startCapture();
	}
	protected function endMiddle()
	{
		$middle=$this->stopCapture();
		$this->captures[$this->currentCaptureID].=$middle;
		$html =& new simple_html_dom();
		$html->load($middle);
		$searches = array('span[id]','[class=ajax]');
		foreach($searches as $search)
		{
			$spans = $html->find($search);
			if(!empty($spans))
				$this->doAjaxMiddle=false;
		}
	}
	
	public function ignoreMiddle()
	{
		$this->ignoreMiddle=true;
	}
}
?>