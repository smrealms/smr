<?php declare(strict_types=1);

/**
 * A container that holds data needed to create a new page.
 *
 * This class acts like an array, whose keys define the page properties.
 * Then we can either create an HREF so that it can be accessed by a future
 * http request (via the Smr\Session), or forwarded to within the same request.
 *
 * @extends ArrayObject<string, mixed>
 */
class Page extends ArrayObject {

	private const ALWAYS_AVAILABLE = true;

	// Defines if the page is is always available, or if it is invalid after one
	// use (i.e. if you get a back button error when navigating back to it).
	public const ALWAYS_AVAILABLE_PAGES = [
			'album_edit.php' => self::ALWAYS_AVAILABLE,
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
			'chess.php' => self::ALWAYS_AVAILABLE,
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
			'galactic_post_current.php' => self::ALWAYS_AVAILABLE,
			'hall_of_fame_new.php' => self::ALWAYS_AVAILABLE,
			'hall_of_fame_player_detail.php' => self::ALWAYS_AVAILABLE,
			'leave_newbie.php' => self::ALWAYS_AVAILABLE,
			'logoff.php' => self::ALWAYS_AVAILABLE,
			'map_local.php' => self::ALWAYS_AVAILABLE,
			'message_box.php' => self::ALWAYS_AVAILABLE,
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
			'smr_file_create.php' => self::ALWAYS_AVAILABLE,
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
			'game_leave_processing.php' => self::ALWAYS_AVAILABLE,
			'toggle_processing.php' => self::ALWAYS_AVAILABLE,
			//Admin pages
			'admin/account_edit.php' => self::ALWAYS_AVAILABLE,
			'admin/album_moderate.php' => self::ALWAYS_AVAILABLE,
			'admin/box_view.php' => self::ALWAYS_AVAILABLE,
			'admin/changelog.php' => self::ALWAYS_AVAILABLE,
			'admin/comp_share.php' => self::ALWAYS_AVAILABLE,
			'admin/form_open.php' => self::ALWAYS_AVAILABLE,
			'admin/ip_view_results.php' => self::ALWAYS_AVAILABLE,
			'admin/ip_view.php' => self::ALWAYS_AVAILABLE,
			'admin/permission_manage.php' => self::ALWAYS_AVAILABLE,
			'admin/word_filter.php' => self::ALWAYS_AVAILABLE,
			//Uni gen
			'admin/unigen/check_map.php' => self::ALWAYS_AVAILABLE,
			'admin/unigen/universe_create_locations.php' => self::ALWAYS_AVAILABLE,
			'admin/unigen/universe_create_planets.php' => self::ALWAYS_AVAILABLE,
			'admin/unigen/universe_create_ports.php' => self::ALWAYS_AVAILABLE,
			'admin/unigen/universe_create_sector_details.php' => self::ALWAYS_AVAILABLE,
			'admin/unigen/universe_create_sectors.php' => self::ALWAYS_AVAILABLE,
			'admin/unigen/universe_create_warps.php' => self::ALWAYS_AVAILABLE,
		];

	public readonly bool $reusable;

	/**
	 * @param array<string, mixed> $data
	 */
	protected function __construct(
		public readonly string $file,
		array $data,
		public readonly bool $skipRedirect // to skip redirect hooks at beginning of page processing
	) {
		parent::__construct($data);

		// Pages are single-use unless explicitly whitelisted
		$this->reusable = self::ALWAYS_AVAILABLE_PAGES[$file] ?? false;
	}

	/**
	 * Create a new Page object.
	 * This is the standard method to package linked pages and the data to
	 * accompany them.
	 *
	 * @param self|array<string, mixed> $data
	 */
	public static function create(string $file, self|array $data = [], bool $skipRedirect = false): self {
		if ($data instanceof self) {
			// Extract the data from the input Page
			$data = $data->getArrayCopy();
		}
		return new self($file, $data, $skipRedirect);
	}

	/**
	 * Create a copy of a Page object.
	 * This may be useful for reusing a Page object without modifying the
	 * original.
	 */
	public static function copy(Page $other): self {
		return clone $other;
	}

	/**
	 * Forward to the page identified by this container.
	 */
	public function go(): never {
		if (defined('OVERRIDE_FORWARD') && OVERRIDE_FORWARD === true) {
			overrideForward($this);
		}
		Smr\Session::getInstance()->setCurrentVar($this);
		do_voodoo();
	}

	/**
	 * Transfer $var[$source] into this container with new name $dest.
	 * If $dest is not specified, keep the index named $source.
	 */
	public function addVar(string $source, string $dest = null): void {
		$var = Smr\Session::getInstance()->getCurrentVar();

		// transfer this value to next container
		if (!isset($var[$source])) {
			throw new Exception('Could not find "' . $source . '" in var!');
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
	public function href(bool $forceFullURL = false): string {
		// We need to clone this instance in case it is modified after being added
		// to the session links. This would not be necessary if Page was readonly.
		$sn = Smr\Session::getInstance()->addLink(self::copy($this));

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
		require(get_file_loc($this->file));
	}

}
