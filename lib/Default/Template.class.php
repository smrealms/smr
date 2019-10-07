<?php

class Template {
	private $data = array();
	private $captures = array('');
	private $currentCaptureID = 0;
	private $ignoreMiddle = false;
	private $nestedIncludes = 0;
	private $ajaxJS = array();
	protected $jsAlerts = array();
	private $displayCalled = false;
	private $listjsInclude = null;
	private $jsSources = [];

	public function __destruct() {
		if (!$this->displayCalled && !empty($this->data)) {
			error_log('Template destroyed before displaying the following assigned keys: ' . join(', ', array_keys($this->data)));
		}
	}

	public function hasTemplateVar($var) {
		return isset($this->data[$var]);
	}
	
	public function assign($var, $value) {
		if (!isset($this->data[$var])) {
			$this->data[$var] = $value;
		} else {
			// We insist that template variables not change once they are set
			throw new Exception("Cannot re-assign template variable '$var'!");
		}
	}
	
	public function unassign($var) {
		unset($this->data[$var]);
	}

	/**
	 * Displays the template HTML. Stores any ajax-enabled elements for future
	 * comparison, and outputs modified elements in XML for ajax if requested.
	 */
	public function display($templateName, $outputXml = false) {
		// If we already started output buffering before calling `display`,
		// we may have unwanted content in the buffer that we need to remove
		// before we send the Content-Type headers below.
		// Skip this for debug builds to help discover offending output.
		if (!ENABLE_DEBUG) {
			if (ob_get_length() > 0) {
				ob_clean();
			}
		}
		ob_start();
		$this->includeTemplate($templateName);
		$this->captures[$this->currentCaptureID] .= ob_get_clean();
		$output = join('', $this->captures);
		$this->trimWhiteSpace($output);

		$ajaxEnabled = ($this->data['AJAX_ENABLE_REFRESH'] ?? false) !== false;
		if ($ajaxEnabled) {
			$ajaxXml =& $this->convertHtmlToAjaxXml($output, $outputXml);
			if ($outputXml) {
				/* Left out for size: <?xml version="1.0" encoding="ISO-8859-1"?>*/
				$output = '<all>' . $ajaxXml . '</all>';
			}
			SmrSession::saveAjaxReturns();
		}

		// Now that we are completely done processing, we can output
		if ($outputXml) {
			header('Content-Type: text/xml; charset=utf-8');
		} else {
			header('Content-Type: text/html; charset=utf-8');
		}
		echo $output;

		// Record that display was called for error-checking in dtor
		$this->displayCalled = true;
	}
	
	
	protected function getTemplateLocation($templateName) {
		$templateDir = TEMPLATES_DIR;
		if (isset($this->data['ThisAccount']) && is_object($this->data['ThisAccount']) && $this->data['ThisAccount'] instanceof SmrAccount) {
			$templateDir .= $this->data['ThisAccount']->getTemplate() . '/';
		} else {
			$templateDir .= 'Default/';
		}

		if (SmrSession::hasGame()) {
			$gameDir = Globals::getGameType(SmrSession::getGameID()) . '/';
		} else {
			$gameDir = 'Default/';
		}

		if (file_exists($templateDir . 'engine/' . $gameDir . $templateName)) {
			return $templateDir . 'engine/' . $gameDir . $templateName;
		} elseif (file_exists($templateDir . 'engine/Default/' . $templateName)) {
			return $templateDir . 'engine/Default/' . $templateName;
		} elseif (file_exists(TEMPLATES_DIR . 'Default/engine/' . $gameDir . $templateName)) {
			return TEMPLATES_DIR . 'Default/engine/' . $gameDir . $templateName;
		} elseif (file_exists(TEMPLATES_DIR . 'Default/engine/Default/' . $templateName)) {
			return TEMPLATES_DIR . 'Default/engine/Default/' . $templateName;
		} elseif (file_exists($templateDir . 'admin/' . $gameDir . $templateName)) {
			return $templateDir . 'admin/' . $gameDir . $templateName;
		} elseif (file_exists($templateDir . 'admin/Default/' . $templateName)) {
			return $templateDir . 'admin/Default/' . $templateName;
		} elseif (file_exists(TEMPLATES_DIR . 'Default/admin/' . $gameDir . $templateName)) {
			return TEMPLATES_DIR . 'Default/admin/' . $gameDir . $templateName;
		} elseif (file_exists(TEMPLATES_DIR . 'Default/admin/Default/' . $templateName)) {
			return TEMPLATES_DIR . 'Default/admin/Default/' . $templateName;
		} elseif (file_exists($templateDir . $templateName)) {
			return $templateDir . $templateName;
		} elseif (file_exists(TEMPLATES_DIR . 'Default/' . $templateName)) {
			return TEMPLATES_DIR . 'Default/' . $templateName;
		} else {
			throw new Exception('No template found for ' . $templateName);
		}
	}
	
	protected function includeTemplate($templateName, array $assignVars = null) {
		if ($this->nestedIncludes > 15) {
			throw new Exception('Nested more than 15 template includes, is something wrong?');
		}
		foreach ($this->data as $key => $value) {
			$$key = $value;
		}
		if ($assignVars !== null) {
			foreach ($assignVars as $key => $value) {
				$$key = $value;
			}
		}
		$this->nestedIncludes++;
		require($this->getTemplateLocation($templateName));
		$this->nestedIncludes--;
	}
	
	protected function startCapture() {
		$this->captures[$this->currentCaptureID] .= ob_get_contents();
		ob_clean();
		$this->currentCaptureID++;
		$this->captures[$this->currentCaptureID] = '';
	}
	
	protected function &stopCapture() {
		$captured =& $this->captures[$this->currentCaptureID];
		unset($this->captures[$this->currentCaptureID]);
		$captured .= ob_get_contents();
		ob_clean();
		$this->currentCaptureID--;
		return $captured;
	}
	
	protected function checkDisableAJAX($html) {
		return preg_match('/<input' . '[^>]*' . '[^(submit)(hidden)(image)]' . '[^>]*' . '>/i', $html) != 0;
	}
	
	protected function trimWhiteSpace(&$html) {
		// Pull out the script blocks
		/*	preg_match_all("!<script[^>]*?>.*?</script>!is", $source, $match);
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
		$this->replaceTrimHolder("@@@SMARTY:TRIM:SPAN@@@", $_span_blocks, $html);
		
		// replace textarea blocks
		$this->replaceTrimHolder("@@@SMARTY:TRIM:TEXTAREA@@@", $_textarea_blocks, $html);
		
		// replace pre blocks
		$this->replaceTrimHolder("@@@SMARTY:TRIM:PRE@@@", $_pre_blocks, $html);
		
		// replace script blocks
//		$this->replaceTrimHolder("@@@SMARTY:TRIM:SCRIPT@@@",$_script_blocks, $html);
	}
	protected function replaceTrimHolder($search_str, $replace, &$subject) {
		$_len = strlen($search_str);
		$_pos = 0;
		for ($_i = 0, $_count = count($replace); $_i < $_count; $_i++) {
			if (($_pos = strpos($subject, $search_str, $_pos)) !== false) {
				$subject = substr_replace($subject, $replace[$_i], $_pos, $_len);
			} else {
				break;
			}
		}
	}
	
	protected function doDamageTypeReductionDisplay(&$damageTypes) {
		if ($damageTypes == 3) {
			echo ', ';
		} else if ($damageTypes == 2) {
			echo ' and ';
		}
		$damageTypes--;
	}

	protected function doAn($wordAfter) {
		$char = strtoupper($wordAfter[0]);
		if ($char == 'A' || $char == 'E' || $char == 'I' || $char == 'O' || $char == 'U') {
			echo 'an';
		} else {
			echo 'a';
		}
	}

	/**
	 * Sets a listjs_include.js function to call at the end of the HTML body.
	 */
	public function setListjsInclude($func) {
		$this->listjsInclude = $func;
	}

	/*
	 * EVAL is special (well, will be when needed and implemented in the javascript).
	 */
	public function addJavascriptForAjax($varName, $obj) {
		if ($varName == 'EVAL') {
			if (!isset($this->ajaxJS['EVAL'])) {
				return $this->ajaxJS['EVAL'] = $obj;
			}
			return $this->ajaxJS['EVAL'] .= ';' . $obj;
		}
		
		if (isset($this->ajaxJS[$varName])) {
			throw new Exception('Trying to set javascript val twice: ' . $varName);
		}
		return $this->ajaxJS[$varName] = json_encode($obj);
	}
	
	protected function addJavascriptAlert($string) {
		if (!SmrSession::addAjaxReturns('ALERT:' . $string, $string)) {
			$this->jsAlerts[] = $string;
		}
	}

	/**
	 * Registers a JS target for inclusion at the end of the HTML body.
	 */
	protected function addJavascriptSource($src) {
		array_push($this->jsSources, $src);
	}

	protected function &convertHtmlToAjaxXml($str, $returnXml) {
		if (empty($str)) {
			return '';
		}

		// To get inner html, we need to construct a separate DOMDocument.
		// See PHP Bug #76285.
		$getInnerHTML = function(DOMNode $node) {
			$dom = new DOMDocument();
			$dom->formatOutput = false;
			foreach ($node->childNodes as $child) {
				$dom->appendChild($dom->importNode($child, true));
			}
			// Trim to remove trailing newlines
			return trim(@$dom->saveHTML());
		};

		$xml = '';
		$dom = new DOMDocument();
		$dom->loadHTML($str);
		$xpath = new DOMXpath($dom);
		$ajaxSelectors = array('//span[@id]', '//*[contains(@class,"ajax")]');
		foreach ($ajaxSelectors as $selector) {
			$matchNodes = $xpath->query($selector);
			foreach ($matchNodes as $node) {
				$id = $node->getAttribute('id');
				$inner = $getInnerHTML($node);
				if (!SmrSession::addAjaxReturns($id, $inner) && $returnXml) {
					$xml .= '<' . $id . '>' . xmlify($inner) . '</' . $id . '>';
				}
			}
		}

		if (!$this->ignoreMiddle) {
			$mid = $dom->getElementById('middle_panel');

			$doAjaxMiddle = true;
			if ($mid === null) {
				// Skip if there is no middle_panel.
				$doAjaxMiddle = false;
			} else {
				// Skip if middle_panel has ajax-enabled children.
				$domMid = new DOMDocument();
				$domMid->appendChild($domMid->importNode($mid, true));
				$xpathMid = new DOMXpath($domMid);
				foreach ($ajaxSelectors as $selector) {
					if (count($xpathMid->query($selector)) > 0) {
						$doAjaxMiddle = false;
						break;
					}
				}
			}

			if ($doAjaxMiddle) {
				$inner = $getInnerHTML($mid);
				if (!$this->checkDisableAJAX($inner)) {
					$id = $mid->getAttribute('id');
					if (!SmrSession::addAjaxReturns($id, $inner) && $returnXml) {
						$xml .= '<' . $id . '>' . xmlify($inner) . '</' . $id . '>';
					}
				}
			}
		}

		$js = '';
		foreach ($this->ajaxJS as $varName => $JSON) {
			if (!SmrSession::addAjaxReturns('JS:' . $varName, $JSON) && $returnXml) {
				$js .= '<' . $varName . '>' . xmlify($JSON) . '</' . $varName . '>';
			}
		}
		if ($returnXml && count($this->jsAlerts) > 0) {
			$js = '<ALERT>' . json_encode($this->jsAlerts) . '</ALERT>';
		}
		if (strlen($js) > 0) {
			$xml .= '<JS>' . $js . '</JS>';
		}
		return $xml;
	}
	
	public function ignoreMiddle() {
		$this->ignoreMiddle = true;
	}
}
