<?php declare(strict_types=1);

/**
 * @var Smr\Template $this
 * @var string $SubmitHREF
 * @var int $MinRank
 * @var int $MaxRank
 * @var array<int, array{Class: string, SectorID: int, Value: int}> $TopTen
 * @var array<int, array{Class: string, SectorID: int, Value: int}> $TopCustom
 */

?>
<div class="center">
	<p>Here are the most deadly Sectors!</p>
	<?php $this->includeTemplate('includes/SectorKillList.inc.php', ['Rankings' => $TopTen]); ?>

	<form method="POST" action="<?php echo $SubmitHREF; ?>">
		<p>
			<input type="number" name="min_rank" value="<?php echo $MinRank; ?>" size="3" class="center">&nbsp;-&nbsp;
			<input type="number" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" class="center">&nbsp;
			<input type="submit" name="action" value="Show" />
		</p>
	</form>

	<?php $this->includeTemplate('includes/SectorKillList.inc.php', ['Rankings' => $TopCustom]); ?>
</div>
