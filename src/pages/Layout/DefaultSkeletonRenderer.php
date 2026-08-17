<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

class DefaultSkeletonRenderer extends AbstractSkeletonRenderer {

public static function render(SkeletonData $data): void {
	$timeDisplay = $data->timeDisplay;

?>
<!DOCTYPE html>
<html>
	<head><?php
		HeadRenderer::render($data->account, $data->gameName); ?>
	</head>
	<body>
		<table class="m centered">
			<tr>
				<td class="l0" rowspan="2">
					<div class="l1">
						<span class="yellow">
							<span id="tod"><?php echo $timeDisplay; ?></span>
						</span>
						<br /><br />
						<?php LeftPanelRenderer::render($data->account, $data->player); ?>
						<br />
					</div>
				</td>
				<td class="m0" colspan="2">
					<div id="middle_panel"><?php
						if ($data->template->pageTopic !== null) {
							?><h1><?php echo $data->template->pageTopic; ?></h1><br /><?php
						}
						MenuRenderer::render($data->template);
						if ($data->template->pageRenderer !== null) {
							($data->template->pageRenderer)();
						} ?>
					</div>
				</td>
				<td class="r0">
					<div id="right_panel">
						<?php
						if ($data->rightPanelData !== null) {
							RightPanelPlayerRenderer::render($data->rightPanelData); ?>
						<br />
							<?php RightPanelShipRenderer::render($data->rightPanelData);
						} ?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="footer_left">
					<?php VoteLinksRenderer::render($data->voteLinks, $data->timeToNextVote); ?>
				</td>
				<td class="footer_right">
					<?php CopyrightRenderer::render($data->version); ?>
				</td>
				<td></td>
			</tr>
		</table>
		<?php EndingJavascriptRenderer::render($data->template, $data->account); ?>
	</body>
</html>
<?php }

}
