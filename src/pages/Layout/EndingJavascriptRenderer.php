<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

use Smr\Account;
use Smr\Globals;
use Smr\Template;

class EndingJavascriptRenderer {

	public static function render(Template $template, Account $ThisAccount): void {
		?>
		<script src="/js/jquery.hotkeys.js"></script>
		<script src="/js/ajax.js"></script>

		<?php
		foreach ($template->jsSources as $src) { ?>
			<script src="<?php echo $src; ?>"></script><?php
		}

		foreach ($template->jsAlerts as $string) {
			?>alert(<?php echo json_encode($string, JSON_THROW_ON_ERROR); ?>);<?php
		}

		if ($template->listjsInclude !== null) { ?>
			<script src="<?php echo LISTJS_URL; ?>"></script>
			<script src="/js/listjs_include.js"></script>
			<script>
				listjs.<?php echo $template->listjsInclude; ?>();
			</script><?php
		}

		if ($template->addRaceRadarChartJS !== null) { ?>
			<script src="https://cdn.plot.ly/plotly-1.58.2.min.js"></script>
			<script>
				createRaceRadarChart(<?php echo $template->addRaceRadarChartJS?>);
			</script><?php
		}

		$AvailableLinks = Globals::getAvailableLinks(); ?>
		<script>$(function(){<?php
			if ($template->ajaxRefreshInterval !== false) { ?>
				initRefresh('<?php echo $template->ajaxRefreshInterval; ?>');<?php
			}
			foreach ($AvailableLinks as $LinkName => $AvailableLink) {
				$Hotkeys = $ThisAccount->getHotkeys($LinkName);
				foreach ($Hotkeys as $Hotkey) {
					?>$(document).bind('keydown', '<?php echo addslashes($Hotkey); ?>', followLink('<?php echo $AvailableLink; ?>'));<?php
				}
			} ?>
		})</script>
		<?php
	}

}
