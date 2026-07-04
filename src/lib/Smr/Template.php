<?php declare(strict_types=1);

namespace Smr;

use DOMDocument;
use DOMElement;
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
		return DiContainer::getClass(self::class);
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
	 * @param ?array<string, mixed> $assignVars
	 */
	protected function includeTemplate(string $templateName, ?array $assignVars = null): void {
		if ($this->nestedIncludes > 15) {
			throw new Exception('Nested more than 15 template includes, is something wrong?');
		}
		extract($this->data);
		if ($assignVars !== null) {
			extract($assignVars);
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
		return preg_match('/<input (?![^>]*(submit|hidden|image))/i', $html) !== 0;
	}

	/**
	 * @param ForceTakenDamageData $damageTaken
	 */
	public function displayForceTakenDamage(array $damageTaken, int $kamikaze = 0): string {
		$items = [
			[$damageTaken['NumMines'] - $kamikaze, 'red', 'mine', ''],
			[$damageTaken['NumCDs'], 'red', 'combat drone', ''],
			[$damageTaken['NumSDs'], 'red', 'scout drone', ''],
		];
		return $this->displayDamage($items);
	}

	/**
	 * @param TakenDamageData $damageTaken
	 */
	public function displayTakenDamage(array $damageTaken): string {
		$items = [
			[$damageTaken['Shield'], 'shields', 'shield', ''],
			[$damageTaken['NumCDs'], 'cds', 'combat drone', ''],
			[$damageTaken['Armour'], 'red', 'plate', ' of armour'],
		];
		return $this->displayDamage($items);
	}

	/**
	 * @param array<array{int, string, string, string}> $damageTypes
	 */
	private function displayDamage(array $damageTypes): string {
		$strings = [];
		foreach ($damageTypes as [$damage, $class, $name, $suffix]) {
			if ($damage > 0) {
				$strings[] = '<span class="' . $class . '">' . number_format($damage) . '</span> ' . pluralise($damage, $name, false) . $suffix;
			}
		}
		return format_list($strings);
	}

	protected function doAn(string $wordAfter): string {
		$char = strtoupper($wordAfter[0]);
		return str_contains('AEIOU', $char) ? 'an' : 'a';
	}

	/*
	 * EVAL is special (well, will be when needed and implemented in the javascript).
	 */
	public function addJavascriptForAjax(string $varName, mixed $obj): string {
		if ($varName === 'EVAL') {
			if (!isset($this->ajaxJS['EVAL'])) {
				return $this->ajaxJS['EVAL'] = $obj;
			}
			return $this->ajaxJS['EVAL'] .= ';' . $obj;
		}

		if (isset($this->ajaxJS[$varName])) {
			throw new Exception('Trying to set javascript val twice: ' . $varName);
		}
		$json = json_encode($obj, JSON_THROW_ON_ERROR);
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
		if ($str === '') {
			return '';
		}

		$session = Session::getInstance();

		$getInnerHTML = function(DOMElement $node): string {
			$innerHTML = '';
			$document = $node->ownerDocument;
			foreach ($node->childNodes as $child) {
				$innerHTML .= $document->saveHTML($child);
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

		// Handle libxml errors ourselves to provide more detailed errors
		$orig = libxml_use_internal_errors(true);
		$dom->loadHTML($str);
		$errors = libxml_get_errors();
		libxml_use_internal_errors($orig);
		foreach ($errors as $error) {
			$line = explode("\n", $str)[$error->line - 1];
			$message = 'libxml: ' . $error->message . ' at col ' . $error->column . ' of line: ' . $line;
			if (ENABLE_LIBXML_ERRORS) {
				throw new Exception($message);
			} else {
				error_log($message);
			}
		}

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
		if ($mid !== null) {
			// Skip if middle_panel has ajax-enabled children.
			$doAjaxMiddle = true;
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
			if ($doAjaxMiddle) {
				$inner = $getInnerHTML($mid);
				if (!$this->checkDisableAJAX($inner)) {
					$id = $mid->getAttribute('id');
					if (!$session->addAjaxReturns($id, $inner) && $returnXml) {
						$xml .= $xmlify($id, $inner);
					}
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
			$js = '<ALERT>' . json_encode($this->jsAlerts, JSON_THROW_ON_ERROR) . '</ALERT>';
		}
		if (strlen($js) > 0) {
			$xml .= '<JS>' . $js . '</JS>';
		}
		return $xml;
	}

}
