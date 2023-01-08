<?php declare(strict_types=1);

namespace Smr;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use Smr\Container\DiContainer;

class Template {

	/** @var array<string, mixed> */
	private array $data = [];
	private int $nestedIncludes = 0;
	/** @var array<string, mixed> */
	private array $ajaxJS = [];
	/** @var array<string> */
	protected array $jsAlerts = [];
	/** @var array<string> */
	protected array $jsSources = [];

	/**
	 * Defines a listjs_include.js function to call at the end of the HTML body.
	 */
	public ?string $listjsInclude = null;

	/**
	 * Return the Smr\Template in the DI container.
	 * If one does not exist yet, it will be created.
	 * This is the intended way to construct this class.
	 */
	public static function getInstance(): self {
		return DiContainer::get(self::class);
	}

	public function hasTemplateVar(string $var): bool {
		return isset($this->data[$var]);
	}

	public function assign(string $var, mixed $value): void {
		if (!isset($this->data[$var])) {
			$this->data[$var] = $value;
		} else {
			// We insist that template variables not change once they are set
			throw new Exception("Cannot re-assign template variable '$var'!");
		}
	}

	public function unassign(string $var): void {
		unset($this->data[$var]);
	}

	/**
	 * Displays the template HTML. Stores any ajax-enabled elements for future
	 * comparison, and outputs modified elements in XML for ajax if requested.
	 */
	public function display(string $templateName, bool $outputXml = false): void {
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
		if ($output === false) {
			throw new Exception('Output buffering is not active!');
		}

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
	}


	protected function getTemplateLocation(string $templateName): string {
		if (isset($this->data['ThisAccount'])) {
			$templateDir = $this->data['ThisAccount']->getTemplate() . '/';
		} else {
			$templateDir = 'Default/';
		}
		$templateDirs = array_unique([$templateDir, 'Default/']);

		foreach ($templateDirs as $templateDir) {
			$filePath = TEMPLATES . $templateDir . 'engine/Default/' . $templateName;
			if (is_file($filePath)) {
				return $filePath;
			}
		}
		foreach ($templateDirs as $templateDir) {
			$filePath = TEMPLATES . $templateDir . $templateName;
			if (is_file($filePath)) {
				return $filePath;
			}
		}
		throw new Exception('No template found for ' . $templateName);
	}

	/**
	 * @param array<string, mixed> $assignVars
	 */
	protected function includeTemplate(string $templateName, array $assignVars = null): void {
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
	protected function checkDisableAJAX(string $html): bool {
		return preg_match('/<input (?![^>]*(submit|hidden|image))/i', $html) != 0;
	}

	protected function doDamageTypeReductionDisplay(int &$damageTypes): void {
		if ($damageTypes == 3) {
			echo ', ';
		} elseif ($damageTypes == 2) {
			echo ' and ';
		}
		$damageTypes--;
	}

	protected function doAn(string $wordAfter): string {
		$char = strtoupper($wordAfter[0]);
		return str_contains('AEIOU', $char) ? 'an' : 'a';
	}

	/*
	 * EVAL is special (well, will be when needed and implemented in the javascript).
	 */
	public function addJavascriptForAjax(string $varName, mixed $obj): string {
		if ($varName == 'EVAL') {
			if (!isset($this->ajaxJS['EVAL'])) {
				return $this->ajaxJS['EVAL'] = $obj;
			}
			return $this->ajaxJS['EVAL'] .= ';' . $obj;
		}

		if (isset($this->ajaxJS[$varName])) {
			throw new Exception('Trying to set javascript val twice: ' . $varName);
		}
		$json = json_encode($obj);
		if ($json === false) {
			throw new Exception('Failed to encode to json: ' . $varName);
		}
		return $this->ajaxJS[$varName] = $json;
	}

	protected function addJavascriptAlert(string $string): void {
		$session = Session::getInstance();
		if (!$session->addAjaxReturns('ALERT:' . $string, $string)) {
			$this->jsAlerts[] = $string;
		}
	}

	/**
	 * Registers a JS target for inclusion at the end of the HTML body.
	 */
	protected function addJavascriptSource(string $src): void {
		$this->jsSources[] = $src;
	}

	protected function convertHtmlToAjaxXml(string $str, bool $returnXml): string {
		if (empty($str)) {
			return '';
		}

		$session = Session::getInstance();

		$getInnerHTML = function(DOMNode $node): string {
			$innerHTML = '';
			foreach ($node->childNodes as $child) {
				$innerHTML .= $child->ownerDocument->saveHTML($child);
			}
			return $innerHTML;
		};

		// Helper function to canonicalize making an XML element,
		// with its inner content properly escaped.
		$xmlify = function(string $id, string $str): string {
			return '<' . $id . '>' . htmlspecialchars($str, ENT_XML1, 'utf-8') . '</' . $id . '>';
		};

		$xml = '';
		$dom = new DOMDocument();
		$dom->loadHTML($str);
		$xpath = new DOMXPath($dom);

		// Use relative xpath selectors so that they can be reused when we
		// pass the middle panel as the xpath query's context node.
		$ajaxSelectors = ['.//span[@id]', './/*[contains(@class,"ajax")]'];

		foreach ($ajaxSelectors as $selector) {
			$matchNodes = $xpath->query($selector);
			if ($matchNodes === false) {
				throw new Exception('XPath query failed for selector: ' . $selector);
			}
			foreach ($matchNodes as $node) {
				if (!($node instanceof DOMElement)) {
					throw new Exception('XPath query returned unexpected DOMNode type: ' . $node->nodeType);
				}
				$id = $node->getAttribute('id');
				$inner = $getInnerHTML($node);
				if (!$session->addAjaxReturns($id, $inner) && $returnXml) {
					$xml .= $xmlify($id, $inner);
				}
			}
		}

		// Determine if we should do ajax updates on the middle panel div
		$mid = $dom->getElementById('middle_panel');
		$doAjaxMiddle = true;
		if ($mid === null) {
			// Skip if there is no middle_panel.
			$doAjaxMiddle = false;
		} else {
			// Skip if middle_panel has ajax-enabled children.
			foreach ($ajaxSelectors as $selector) {
				$matchNodes = $xpath->query($selector, $mid);
				if ($matchNodes === false) {
					throw new Exception('XPath query failed for selector: ' . $selector);
				}
				if (count($matchNodes) > 0) {
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
					$xml .= $xmlify($id, $inner);
				}
			}
		}

		$js = '';
		foreach ($this->ajaxJS as $varName => $JSON) {
			if (!$session->addAjaxReturns('JS:' . $varName, $JSON) && $returnXml) {
				$js .= $xmlify($varName, $JSON);
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

}
