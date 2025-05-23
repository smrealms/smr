<?php declare(strict_types=1);

use Smr\Game;

?>

<h1>Create New Game</h1>
<?php $this->includeTemplate('admin/unigen/GameDetails.inc.php', ['ProcessingHREF' => $CreateGalaxiesHREF]); ?>
<br />

<h1>Past Games</h1>
<form method="POST">
	<select name="game_id" required><?php
		foreach (Game::getPastGames() as $game) {
			?><option value="<?php echo $game->getGameID(); ?>"><?php echo $game->getDisplayName(); ?></option><?php
		} ?>
	</select>
	<input type="submit" value="View" name="View" formaction="<?php echo $ViewGameHREF; ?>">
</form>
<br /><?php

if ($CanEditEnabledGames) { ?>
	<h1>Active Games</h1>
	<form method="POST">
		<select name="game_id" required><?php
			foreach (Game::getActiveGames() as $game) {
				?><option value="<?php echo $game->getGameID(); ?>"><?php echo $game->getDisplayName(); ?></option><?php
			} ?>
		</select>
		<input type="submit" value="View" name="View" formaction="<?php echo $ViewGameHREF; ?>">
		<input type="submit" value="Edit" name="Edit" formaction="<?php echo $EditGameHREF; ?>">
	</form>
	<br /><?php
} ?>

<h1>In Development</h1>
<?php
if (count($DevGames) === 0) { ?>
	There are no games in development.<br /><?php
} else { ?>
	<table id="dev-games" class="standard">
		<thead>
			<tr>
				<th>Action</th>
				<th class="sort" data-sort="sort_id">ID</th>
				<th class="sort" data-sort="sort_name">Name</th>
				<th class="sort" data-sort="sort_creator">Creator</th>
				<th class="sort" data-sort="sort_created">Created</th>
				<th class="sort" data-sort="sort_ready">Ready</th>
			</tr>
		</thead>
		<tbody class="list"><?php
			foreach ($DevGames as $DevGame) { ?>
				<tr>
					<td>
						<a class="submitStyle" href="<?php echo $DevGame['ViewHREF']; ?>">View</a><?php
						if ($DevGame['EditHREF'] !== null) { ?>
							<a class="submitStyle" href="<?php echo $DevGame['EditHREF']; ?>">Edit</a><?php
						} ?>
					</td>
					<td class="sort_id"><?php echo $DevGame['ID']; ?></td>
					<td class="sort_name"><?php echo $DevGame['Name']; ?></td>
					<td class="sort_creator"><?php echo $DevGame['Creator']; ?></td>
					<td class="sort_created"><?php echo $DevGame['CreateDate']; ?></td>
					<td class="sort_ready"><?php echo $DevGame['ReadyDate']; ?></td>
					<td><?php
						if ($DevGame['DeleteHREF'] !== null) { ?>
							<a href="<?php echo $DevGame['DeleteHREF']; ?>">
								<img class="bottom" src="images/silk/cross.png" width="16" height="16" alt="Delete Game" title="Delete Game <?php echo $DevGame['ID']; ?>" />
							</a><?php
						} ?>
					</td>
				</tr><?php
			} ?>
		</tbody>
	</table><?php
}

$this->listjsInclude = 'dev_games';
