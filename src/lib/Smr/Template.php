<?php declare(strict_types=1);

namespace Smr;

use DOMDocument;
use DOMNode;
use DOMXPath;
use Exception;
use Globals;
use Smr\Container\DiContainer;
use Smr\Session;
use SmrAccount;

class Template {
	private array $data = [];
	private bool $ignoreMiddle = false;
	private int $nestedIncludes = 0;
	private array $ajaxJS = [];
	protected array $jsAlerts = [];
	private bool $displayCalled = false;
	private ?string $listjsInclude = null;
	private array $jsSources = [];

	/**
	 * Return the Smr\Template in the DI container.
	 * If one does not exist yet, it will be created.
	 * This is the intended way to construct this class.
	 */
	public static function getInstance() : self {
		return DiContainer::get(self::class);
	}

	public function __destruct() {
		if (!$this->displayCalled && !empty($this->data)) {
			error_log('Template destroyed before displaying the following assigned keys: ' . join(', ', array_keys($this->data)));
		}
	}

	public function hasTemplateVar(string $var) : bool {
		return isset($this->data[$var]);
	}

	public function assign(string $var, mixed $value) : void {
		if (!isset($this->data[$var])) {
			$this->data[$var] = $value;
		} else {
			// We insist that template variables not change once they are set
			throw new Exception("Cannot re-assign template variable '$var'!");
		}
	}

	public function unassign(string $var) : void {
		unset($this->data[$var]);
	}

	/**
	 * Displays the template HTML. Stores any ajax-enabled elements for future
	 * comparison, and outputs modified elements in XML for ajax if requested.
	 */
	public function display(string $templateName, bool $outputXml = false) : void {
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
		$output = ob_get_clean();

		$ajaxEnabled = ($this->data['AJAX_ENABLE_REFRESH'] ?? false) !== false;
		if ($ajaxEnabled) {
			$ajaxXml = $this->convertHtmlToAjaxXml($output, $outputXml);
			if ($outputXml) {
				/* Left out for size: <?xml version="1.0" encoding="ISO-8859-1"?>*/
				$output = '<all>' . $ajaxXml . '</all>';
			}
			$session = Session::getInstance();
			$session->saveAjaxReturns();
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


	protected function getTemplateLocation(string $templateName) : string {
		$templateDir = TEMPLATES_DIR;
		if (isset($this->data['ThisAccount']) && is_object($this->data['ThisAccount']) && $this->data['ThisAccount'] instanceof SmrAccount) {
			$templateDir .= $this->data['ThisAccount']->getTemplate() . '/';
		} else {
			$templateDir .= 'Default/';
		}

		$session = Session::getInstance();
		if ($session->hasGame()) {
			$gameDir = Globals::getGameType($session->getGameID()) . '/';
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

	protected function includeTemplate(string $templateName, array $assignVars = null) : void {
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

	/**
	 * Check if the HTML includes input elements where the user is able to
	 * input data (i.e. we don't want to AJAX update a form that they may
	 * have already started filling out).
	 */
	protected function checkDisableAJAX(string $html) : bool {
		return preg_match('/<input (?![^>]*(submit|hidden|image))/i', $html) != 0;
	}

	protected function doDamageTypeReductionDisplay(int &$damageTypes) : void {
		if ($damageTypes == 3) {
			echo ', ';
		} elseif ($damageTypes == 2) {
			echo ' and ';
		}
		$damageTypes--;
	}

	protected function doAn(string $wordAfter) : void {
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
	public function setListjsInclude(string $func) : void {
		$this->listjsInclude = $func;
	}

	/*
	 * EVAL is special (well, will be when needed and implemented in the javascript).
	 */
	public function addJavascriptForAjax(string $varName, mixed $obj) {
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

	protected function addJavascriptAlert(string $string) : void {
		$session = Session::getInstance();
		if (!$session->addAjaxReturns('ALERT:' . $string, $string)) {
			$this->jsAlerts[] = $string;
		}
	}

	/**
	 * Registers a JS target for inclusion at the end of the HTML body.
	 */
	protected function addJavascriptSource(string $src) : void {
		array_push($this->jsSources, $src);
	}

	protected function convertHtmlToAjaxXml(string $str, bool $returnXml) : string {
		if (empty($str)) {
			return '';
		}

		$session = Session::getInstance();

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
		$xpath = new DOMXPath($dom);
		$ajaxSelectors = array('//span[@id]', '//*[contains(@class,"ajax")]');
		foreach ($ajaxSelectors as $selector) {
			$matchNodes = $xpath->query($selector);
			foreach ($matchNodes as $node) {
				$id = $node->getAttribute('id');
				$inner = $getInnerHTML($node);
				if (!$session->addAjaxReturns($id, $inner) && $returnXml) {
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
				$xpathMid = new DOMXPath($domMid);
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
					if (!$session->addAjaxReturns($id, $inner) && $returnXml) {
						$xml .= '<' . $id . '>' . xmlify($inner) . '</' . $id . '>';
					}
				}
			}
		}

		$js = '';
		foreach ($this->ajaxJS as $varName => $JSON) {
			if (!$session->addAjaxReturns('JS:' . $varName, $JSON) && $returnXml) {
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

	public function ignoreMiddle() : void {
		$this->ignoreMiddle = true;
	}
}
