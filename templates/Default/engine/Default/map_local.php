<table class="nobord fullwidth">
	<tr>
		<td style="width: 10%" class="top">
			<a href="<?php echo $MapExpandHREF; ?>">
				<img class="bottom" src="images/zoom_expand.svg" width="16" height="16" title="Expand Map" />
			</a>&nbsp;
			<a href="<?php echo $MapShrinkHREF; ?>">
				<img class="bottom" src="images/zoom_shrink.svg" width="16" height="16" title="Shrink Map" />
			</a>
		<td style="width: 80%" class="center">
			Local Map of the Known <span class="big bold"><?php echo $GalaxyName ?></span> Galaxy
			<br /><br />
			<?php if(isset($Error)) echo $Error; ?>
		</td>
		<td style="width: 10%"></td>
	</tr>
</table>

<?php
$this->includeTemplate('includes/SectorMap.inc');
$this->includeTemplate('includes/SectorMapOptions.inc');
?>
