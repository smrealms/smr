<div class="center">
	<p>Here are the most deadly Sectors!</p>
	<?php $this->includeTemplate('includes/SectorKillList.inc.php', array('Rankings' => $TopTen)); ?>

	<form method="POST" action="<?php echo $SubmitHREF; ?>">
		<p>
			<input type="number" name="min_rank" value="<?php echo $MinRank; ?>" size="3" class="center">&nbsp;-&nbsp;
			<input type="number" name="max_rank" value="<?php echo $MaxRank; ?>" size="3" class="center">&nbsp;
			<input type="submit" name="action" value="Show" />
		</p>
	</form>

	<?php $this->includeTemplate('includes/SectorKillList.inc.php', array('Rankings' => $TopCustom)); ?>
</div>
