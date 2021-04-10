<?php declare(strict_types=1);

/**
 * A container that holds data needed to create a new page.
 *
 * This class acts like an array, whose keys define the page properties.
 * Then we can either create an HREF so that it can be accessed by a future
 * http request (via the Smr\Session), or forwarded to within the same request.
 */
class Page extends ArrayObject {

	private const ALWAYS_AVAILABLE = 999999;

	// Defines the number of pages that can be loaded after
	// this page before the links on this page become invalid
	// (i.e. before you get a back button error).
	private const URL_DEFAULT_REMAINING_PAGE_LOADS = array(
			'alliance_broadcast.php' => self::ALWAYS_AVAILABLE,
			'alliance_forces.php' => self::ALWAYS_AVAILABLE,
			'alliance_list.php' => self::ALWAYS_AVAILABLE,
			'alliance_message_view.php' => self::ALWAYS_AVAILABLE,
			'alliance_message.php' => self::ALWAYS_AVAILABLE,
			'alliance_mod.php' => self::ALWAYS_AVAILABLE,
			'alliance_option.php' => self::ALWAYS_AVAILABLE,
			'alliance_pick.php' => self::ALWAYS_AVAILABLE,
			'alliance_remove_member.php' => self::ALWAYS_AVAILABLE,
			'alliance_roster.php' => self::ALWAYS_AVAILABLE,
			'beta_functions.php' => self::ALWAYS_AVAILABLE,
			'bug_report.php' => self::ALWAYS_AVAILABLE,
			'cargo_dump.php' => self::ALWAYS_AVAILABLE,
			'course_plot.php' => self::ALWAYS_AVAILABLE,
			'changelog_view.php' => self::ALWAYS_AVAILABLE,
			'chat_rules.php' => self::ALWAYS_AVAILABLE,
			'chess_play.php' => self::ALWAYS_AVAILABLE,
			'combat_log_list.php' => self::ALWAYS_AVAILABLE,
			'combat_log_viewer.php' => self::ALWAYS_AVAILABLE,
			'current_sector.php' => self::ALWAYS_AVAILABLE,
			'configure_hardware.php' => self::ALWAYS_AVAILABLE,
			'contact.php' => self::ALWAYS_AVAILABLE,
			'council_embassy.php' => self::ALWAYS_AVAILABLE,
			'council_list.php' => self::ALWAYS_AVAILABLE,
			'council_politics.php' => self::ALWAYS_AVAILABLE,
			'council_send_message.php' => self::ALWAYS_AVAILABLE,
			'council_vote.php' => self::ALWAYS_AVAILABLE,
			'current_players.php' => self::ALWAYS_AVAILABLE,
			'donation.php' => self::ALWAYS_AVAILABLE,
			'feature_request_comments.php' => self::ALWAYS_AVAILABLE,
			'feature_request.php' => self::ALWAYS_AVAILABLE,
			'forces_list.php' => self::ALWAYS_AVAILABLE,
			'forces_mass_refresh.php' => self::ALWAYS_AVAILABLE,
			'hall_of_fame_player_new.php' => self::ALWAYS_AVAILABLE,
			'hall_of_fame_player_detail.php' => self::ALWAYS_AVAILABLE,
			'leave_newbie.php' => self::ALWAYS_AVAILABLE,
			'logoff.php' => self::ALWAYS_AVAILABLE,
			'map_local.php' => self::ALWAYS_AVAILABLE,
			'message_view.php' => self::ALWAYS_AVAILABLE,
			'message_send.php' => self::ALWAYS_AVAILABLE,
			'news_read_advanced.php' => self::ALWAYS_AVAILABLE,
			'news_read_current.php' => self::ALWAYS_AVAILABLE,
			'news_read.php' => self::ALWAYS_AVAILABLE,
			'planet_construction.php' => self::ALWAYS_AVAILABLE,
			'planet_defense.php' => self::ALWAYS_AVAILABLE,
			'planet_financial.php' => self::ALWAYS_AVAILABLE,
			'planet_main.php' => self::ALWAYS_AVAILABLE,
			'planet_ownership.php' => self::ALWAYS_AVAILABLE,
			'planet_stockpile.php' => self::ALWAYS_AVAILABLE,
			'planet_list.php' => self::ALWAYS_AVAILABLE,
			'planet_list_financial.php' => self::ALWAYS_AVAILABLE,
			'preferences.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_experience.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_alliance_vs_alliance.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_experience.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_player_profit.php' => self::ALWAYS_AVAILABLE,
			'rankings_race_death.php' => self::ALWAYS_AVAILABLE,
			'rankings_race_kills.php' => self::ALWAYS_AVAILABLE,
			'rankings_race.php' => self::ALWAYS_AVAILABLE,
			'rankings_sector_kill.php' => self::ALWAYS_AVAILABLE,
			'rankings_view.php' => self::ALWAYS_AVAILABLE,
			'trader_bounties.php' => self::ALWAYS_AVAILABLE,
			'trader_relations.php' => self::ALWAYS_AVAILABLE,
			'trader_savings.php' => self::ALWAYS_AVAILABLE,
			'trader_search_result.php' => self::ALWAYS_AVAILABLE,
			'trader_search.php' => self::ALWAYS_AVAILABLE,
			'trader_status.php' => self::ALWAYS_AVAILABLE,
			'weapon_reorder.php' => self::ALWAYS_AVAILABLE,
			//Processing pages
			'alliance_message_add_processing.php' => self::ALWAYS_AVAILABLE,
			'alliance_message_delete_processing.php' => self::ALWAYS_AVAILABLE,
			'alliance_pick_processing.php' => self::ALWAYS_AVAILABLE,
			'chess_move_processing.php' => self::ALWAYS_AVAILABLE,
			'toggle_processing.php' => self::ALWAYS_AVAILABLE,
			//Admin pages
			'account_edit.php' => self::ALWAYS_AVAILABLE,
			'album_moderate.php' => self::ALWAYS_AVAILABLE,
			'box_view.php' => self::ALWAYS_AVAILABLE,
			'changelog.php' => self::ALWAYS_AVAILABLE,
			'comp_share.php' => self::ALWAYS_AVAILABLE,
			'form_open.php' => self::ALWAYS_AVAILABLE,
			'ip_view_results.php' => self::ALWAYS_AVAILABLE,
			'ip_view.php' => self::ALWAYS_AVAILABLE,
			'permission_manage.php' => self::ALWAYS_AVAILABLE,
			'word_filter.php' => self::ALWAYS_AVAILABLE,
			//Uni gen
			'1.6/check_map.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_locations.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_planets.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_ports.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_sector_details.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_sectors.php' => self::ALWAYS_AVAILABLE,
			'1.6/universe_create_warps.php' => self::ALWAYS_AVAILABLE,
		);

	/**
	 * Create a new Page object.
	 * This is the standard method to package linked pages and the data to
	 * accompany them.
	 */
	public static function create($file, $body = '', Page|array $extra = [], $remainingPageLoads = null) : self {
		if ($extra instanceof Page) {
			// to avoid making $container a reference to $extra
			$extra = $extra->getArrayCopy();
		}
		$container = new Page($extra);
		$container['url'] = $file;
		$container['body'] = $body;
		if ($remainingPageLoads !== null) {
			$container['RemainingPageLoads'] = $remainingPageLoads;
		}
		return $container;
	}

	/**
	 * Create a copy of a Page object.
	 * This may be useful for reusing a Page object without modifying the
	 * original.
	 */
	public static function copy(Page $other) : self {
		return clone $other;
	}

	/**
	 * Forward to the page identified by this container.
	 */
	public function go() : void {
		if (defined('OVERRIDE_FORWARD') && OVERRIDE_FORWARD === true) {
			overrideForward($this);
			return;
		}
		Smr\Session::getInstance()->setCurrentVar($this);
		do_voodoo();
	}

	/**
	 * Transfer $var[$source] into this container with new name $dest.
	 * If $dest is not specified, keep the index named $source.
	 */
	public function addVar(string $source, string $dest = null) : void {
		$var = Smr\Session::getInstance()->getCurrentVar();

		// transfer this value to next container
		if (!isset($var[$source])) {
			throw new Exception('Could not find "' . $source. '" in var!');
		}
		if ($dest === null) {
			$dest = $source;
		}
		$this[$dest] = $var[$source];
	}

	/**
	 * Create an HREF (based on a random SN) to link to this page.
	 * The container is saved in the Smr\Session under this SN so that on
	 * the next request, we can grab the container out of the Smr\Session.
	 */
	public function href(bool $forceFullURL = false) : string {

		// We need to make a clone of this object for two reasons:
		// 1. The object saved in the session is not modified if we use this
		//    object to create more links.
		// 2. Any additional links we create using this object do not inherit
		//    the metadata properties that we add here, which would falsely
		//    represent some other page.
		// Ideally this would not be necessary, but the usage of this method
		// would need to change globally first (no Page re-use).
		$copy = self::copy($this);

		if (!isset($copy['RemainingPageLoads'])) {
			$pageURL = $copy['url'] == 'skeleton.php' ? $copy['body'] : $copy['url'];
			$copy['RemainingPageLoads'] = self::URL_DEFAULT_REMAINING_PAGE_LOADS[$pageURL] ?? 1; // Allow refreshing
		}

		// 'CommonID' MUST be unique to a specific action. If there will
		// be two different outcomes from containers given the same ID then
		// problems will likely arise.
		$copy['CommonID'] = $this->getCommonID();
		$sn = Smr\Session::getInstance()->addLink($copy);

		if ($forceFullURL === true || stripos($_SERVER['REQUEST_URI'], 'loader.php') === false) {
			return '/loader.php?sn=' . $sn;
		}
		return '?sn=' . $sn;
	}

	/**
	 * Returns a hash of the contents of the container to identify when two
	 * containers are equivalent (apart from page-load tracking metadata, which
	 * we strip out to prevent false differences).
	 */
	private function getCommonID() : string {
		$commonContainer = $this->getArrayCopy();
		unset($commonContainer['RemainingPageLoads']);
		unset($commonContainer['PreviousRequestTime']);
		unset($commonContainer['CommonID']);
		// NOTE: This ID will change if the order of elements in the container
		// changes. If this causes unnecessary SN changes, sort the container!
		return md5(serialize($commonContainer));
	}

	/**
	 * Process this page by executing the 'url' and 'body' files.
	 * Global variables are included here for convenience (and should be
	 * synchronized with `do_voodoo`).
	 */
	public function process() : void {
		global $lock, $var;
		if ($this['url'] != 'skeleton.php') {
			require(get_file_loc($this['url']));
		}
		if ($this['body']) {
			require(get_file_loc($this['body']));
		}
	}

}
