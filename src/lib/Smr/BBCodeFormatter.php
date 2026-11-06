<?php declare(strict_types=1);

namespace Smr;

use Nbbc\BBCode;
use Smr\Chess\ChessGame;
use Smr\Pages\Player\AllianceInviteAcceptProcessor;
use Smr\Pages\Player\AllianceMotd;
use Throwable;
use function create_link;
use function linkCombatLog;
use function parseBoolean;

class BBCodeFormatter {

	private static ?self $instance = null;
	private readonly BBCode $parser;
	private int $gameID;
	private bool $noLinks;

	public function __construct() {
		$this->parser = new BBCode();
		$this->parser->setEnableSmileys(false);
		$this->parser->removeRule('wiki');
		$this->parser->removeRule('img');
		$this->parser->setURLTarget('_blank');
		$this->parser->setURLTargetable('override');
		$this->parser->setEscapeContent(false); // don't escape HTML, for News etc.

		// Add [verbatim] tag, needed for displaying other tags
		$this->parser->addRule('verbatim', [
			'content' => BBCode::BBCODE_VERBATIM,
			'simple_start' => '<tt>',
			'simple_end' => '</tt>',
			'allow_in' => ['block'],
		]);

		$smrRule = [
			'mode' => BBCode::BBCODE_MODE_CALLBACK,
			'method' => $this->renderTag(...),
			'class' => 'link',
			'allow_in' => ['listitem', 'block', 'columns', 'inline'],
			'end_tag' => BBCode::BBCODE_PROHIBIT,
			'content' => BBCode::BBCODE_PROHIBIT,
		];
		foreach (['combatlog', 'player', 'alliance', 'race', 'servertimetouser', 'chess', 'sector', 'join_alliance'] as $tag) {
			$this->parser->addRule($tag, $smrRule);
		}
	}

	public static function getInstance(): self {
		return self::$instance ??= new self();
	}

	public function format(string $message, ?int $gameID = null, bool $noLinks = false): string {
		$this->gameID = $gameID ?? Session::getInstance()->getGameID();
		$this->noLinks = $noLinks;

		// Do a full parse if we find the start of a BBCode tag
		if (str_contains($message, '[')) {
			return $this->parser->parse($message);
		}
		// Otherwise just convert newlines
		return nl2br($message, true);
	}

	/**
	 * Converts a BBCode tag into some other text depending on the tag and value.
	 * This is called in two stages: first with action BBCODE_CHECK (where the
	 * returned value must be a boolean) and second, if the first check passes,
	 * with action BBCODE_OUTPUT.
	 *
	 * @param array<string, string> $tagParams
	 */
	public function renderTag(
		BBCode $bbParser,
		int $action,
		string $tagName,
		string $default,
		array $tagParams,
		string $tagContent,
	): string|bool {
		$session = Session::getInstance();
		$linked = !$this->noLinks
			&& $session->hasGame()
			&& $this->gameID === $session->getGameID();
		try {
			switch ($tagName) {
				case 'combatlog':
					if ($action === BBCode::BBCODE_CHECK) {
						return is_numeric($default);
					}
					$logID = (int)$default;
					return linkCombatLog($logID);

				case 'player':
					if ($action === BBCode::BBCODE_CHECK) {
						return is_numeric($default);
					}
					$playerNumber = (int)$default;
					$player = Player::getPlayerByPlayerNumber($playerNumber, $this->gameID);
					$showAlliance = isset($tagParams['showalliance'])
						? parseBoolean($tagParams['showalliance'])
						: false;
					if ($linked) {
						return $player->getLinkedDisplayName($showAlliance);
					}
					return $player->getDisplayName($showAlliance);

				case 'alliance':
					if ($action === BBCode::BBCODE_CHECK) {
						return is_numeric($default);
					}
					$allianceID = (int)$default;
					$alliance = Alliance::getAlliance($allianceID, $this->gameID);
					if ($linked && $alliance->getAllianceID() === $session->getPlayer()->getAllianceID()) {
						$container = new AllianceMotd($alliance->getAllianceID());
						return create_link($container, $alliance->getAllianceDisplayName());
					}
					return $alliance->getAllianceDisplayName($linked);

				case 'race':
					$raceNameID = $default;
					foreach (Race::getAllNames() as $raceID => $raceName) {
						if ((is_numeric($raceNameID) && (int)$raceNameID === $raceID)
							|| $raceNameID === $raceName) {
							if ($action === BBCode::BBCODE_CHECK) {
								return true;
							}
							$raceLinked = !$this->noLinks && $this->gameID === $session->getGameID();
							$player = $session->hasGame() ? $session->getPlayer() : null;
							return Player::getColouredRaceNameOrDefault($raceID, $player, $raceLinked);
						}
					}
					break;

				case 'servertimetouser':
					if ($action === BBCode::BBCODE_CHECK) {
						return true;
					}
					$time = strtotime($default);
					if ($time !== false) {
						$time += $session->getAccount()->getOffset() * 3600;
						return date($session->getAccount()->getDateTimeFormat(), $time);
					}
					break;

				case 'chess':
					if ($action === BBCode::BBCODE_CHECK) {
						return is_numeric($default);
					}
					$chessGameID = (int)$default;
					$chessGame = ChessGame::getChessGame($chessGameID);
					return '<a href="' . $chessGame->getPlayGameHREF()
						. '">chess game (' . $chessGame->getChessGameID() . ')</a>';

				case 'sector':
					if ($action === BBCode::BBCODE_CHECK) {
						return is_numeric($default);
					}
					$sectorID = (int)$default;
					$sectorTag = '<span class="sectorColour">#' . $sectorID . '</span>';
					if (!$this->noLinks
						&& $session->hasGame()
						&& $this->gameID === $session->getGameID()
						&& Sector::sectorExists($this->gameID, $sectorID)) {
						$href = Globals::getPlotCourseHREF(
							$session->getPlayer()->getSectorID(),
							$sectorID,
						);
						return '<a href="' . $href . '">' . $sectorTag . '</a>';
					}
					return $sectorTag;

				case 'join_alliance':
					if ($action === BBCode::BBCODE_CHECK) {
						return is_numeric($default);
					}
					$allianceID = (int)$default;
					$alliance = Alliance::getAlliance($allianceID, $this->gameID);
					$container = new AllianceInviteAcceptProcessor($allianceID);
					return '<div class="buttonA"><a class="buttonA" href="'
						. $container->href() . '">Join '
						. $alliance->getAllianceDisplayName() . '</a></div>';
			}
		} catch (Throwable) {
			// An invalid BBCode reference should display as its original text.
		}

		if ($action === BBCode::BBCODE_CHECK) {
			return false;
		}
		return htmlspecialchars($tagParams['_tag']) . $tagContent . htmlspecialchars($tagParams['_endtag']);
	}

}
