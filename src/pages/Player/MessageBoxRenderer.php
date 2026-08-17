<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Template;

class MessageBoxRenderer {

	/**
	 * @param array<array{Name: string, HasUnread: bool, MessageCount: int, ViewHref: string, DeleteHref: string}> $MessageBoxes
	 */
	public static function render(Template $template, array $MessageBoxes): void {
		?>
		<p>Please choose your Message folder!</p>

		<table id="folders" class="standard">
			<thead>
				<tr>
					<th class="sort" data-sort="sort_name">Folder</th>
					<th class="sort" data-sort="sort_messages">Messages</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody class="list"><?php
				foreach ($MessageBoxes as $MessageBox) { ?>
					<tr id="<?php echo str_replace(' ', '-', $MessageBox['Name']); ?>" class="ajax<?php if ($MessageBox['HasUnread']) { ?> bold<?php } ?>">
						<td class="sort_name">
							<a href="<?php echo $MessageBox['ViewHref']; ?>"><?php echo $MessageBox['Name']; ?></a>
						</td>
						<td class="sort_messages center yellow"><?php echo $MessageBox['MessageCount']; ?></td>
						<td><a href="<?php echo $MessageBox['DeleteHref']; ?>">Empty Read Messages</a></td>
					</tr><?php
				} ?>
			</tbody>
		</table>
		<?php $template->listjsInclude = 'message_box';

	}

}
