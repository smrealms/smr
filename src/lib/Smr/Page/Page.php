<?php declare(strict_types=1);

namespace Smr\Page;

use Smr\AbstractPlayer;
use Smr\Session;
use Smr\Template;

/**
 * A container that holds data needed to create a new page.
 */
class Page {

	/**
	 * Used to skip redirect hooks at the beginning of page processing.
	 */
	public bool $skipRedirect = false;

	/**
	 * Used to allow a page to be processed from an AJAX call.
	 */
	public bool $allowAjax = false;

	/**
	 * Storage to remember if we need to display the Under Attack message.
	 */
	protected bool $underAttack = false;

	/**
	 * Template file associated with page (for display pages only).
	 */
	public string $file = '';

	/**
	 * Defines if the page is is always available, or if it is invalid after one
	 * use (i.e. if you get a back button error when navigating back to it).
	 * Only relevant to pages stored as links in the session.
	 */
	public function isLinkReusable(): bool {
		// Pages are single-use unless explicitly whitelisted by ReusableTrait
		return false;
	}

	/**
	 * Determine if we should show the player that they are under attack,
	 * since it needs to persist across ajax updates.
	 */
	public function showUnderAttack(AbstractPlayer $player, bool $ajax): bool {
		// Only ever change the stored value from false -> true so that the under
		// attack warning persists for the lifetime of this Page.
		if ($player->isUnderAttack()) {
			$this->underAttack = true;
		}

		// Don't modify the player state in an ajax call so that the next real
		// page load will also show if the player is under attack (to avoid brief
		// warning flashes if the ajax call occurs just before a real page load).
		if (!$ajax) {
			$player->setUnderAttack(false);
		}

		return $this->underAttack;
	}

	/**
	 * Forward to the page identified by this container.
	 */
	public function go(): never {
		if (defined('OVERRIDE_FORWARD') && OVERRIDE_FORWARD === true) {
			overrideForward($this);
		}
		Session::getInstance()->setCurrentVar($this);
		do_voodoo();
	}

	/**
	 * Create an HREF (based on a random SN) to link to this page.
	 * The container is saved in the Smr\Session under this SN so that on
	 * the next request, we can grab the container out of the Smr\Session.
	 */
	public function href(bool $forceFullURL = false): string {
		// We need to clone this instance in case it is modified after being added
		// to the session links. This would not be necessary if Page was readonly.
		$sn = Session::getInstance()->addLink(clone $this);

		$href = '?sn=' . $sn;
		if ($forceFullURL === true || $_SERVER['SCRIPT_NAME'] !== LOADER_URI) {
			return LOADER_URI . $href;
		}
		return $href;
	}

	/**
	 * Process this page by executing the associated file.
	 */
	public function process(): void {
		if ($this instanceof PlayerPage) {
			$this->build(Session::getInstance()->getPlayer(), Template::getInstance());
		} elseif ($this instanceof PlayerPageProcessor) {
			$this->build(Session::getInstance()->getPlayer());
		} elseif ($this instanceof AccountPage) {
			$this->build(Session::getInstance()->getAccount(), Template::getInstance());
		} elseif ($this instanceof AccountPageProcessor) {
			$this->build(Session::getInstance()->getAccount());
		}
	}

}
