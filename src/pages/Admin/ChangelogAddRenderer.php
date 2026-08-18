<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Pages\Account\ChangelogViewRenderer;

class ChangelogAddRenderer {

	public static function renderEmpty(): void {
		?>
			Must add an initial version in the database first!<?php
	}

	/**
	 * @param array<array{version: string, went_live: ?string, changes: array<array{title: string, message: string}>}> $Versions
	 * @param array{version: string, went_live: string, changes: array<array{title: string, message: string}>} $FirstVersion
	 */
	public static function render(
		string $ChangeTitle,
		string $ChangeMessage,
		string $AffectedDb,
		ChangelogAddProcessor $AddPage,
		array $FirstVersion,
		array $Versions,
	): void {
		ChangelogViewRenderer::render(ContinueHREF: null, Versions: [$FirstVersion]);
		?>

		<ul>
			<li>
				<form method="POST" action="<?php echo $AddPage->href(); ?>">
					<table>
						<tr>
							<td colspan="2"><small>Title:</small></td>
						</tr>
						<tr>
							<td colspan="2"><input type="text" name="change_title" value="<?php echo htmlentities($ChangeTitle); ?>" style="width:400px;" required></td>
						</tr>
						<tr>
							<td><small>Message (BBCode):</small></td>
							<td><small>Affected Database:</small></td>
						</tr>
						<tr>
							<td><textarea spellcheck="true" name="change_message" style="width:400px;height:50px;" required><?php echo htmlentities($ChangeMessage); ?></textarea></td>
							<td><textarea spellcheck="true" name="affected_db" style="width:200px;height:50px;"><?php echo htmlentities($AffectedDb); ?></textarea></td>
						</tr>
						<tr>
							<td></td>
							<td class="right">
								<?php echo $AddPage->actionPreview->html(); ?>&nbsp;
								<?php echo $AddPage->actionAdd->html(); ?>
							</td>
						</tr>
					</table>
				</form>
			</li>
		</ul>

		<?php
		ChangelogViewRenderer::render(ContinueHREF: null, Versions: $Versions);

	}

}
