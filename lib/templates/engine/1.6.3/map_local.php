<div align="center">
	Local Map of the Known <span class="big bold">
	<?php echo $GalaxyName ?>
	</span> Galaxy.<br />
	<?php if(isset($Error)) echo $Error; ?>
	<br />
	<a id="status" onClick="toggleM();">Mouse Zoom is <?php if($isZoomOn){ ?>On<?php }else{ ?>Off<?php } ?>.  Click to toggle.</a>
</div><br />

<?php $this->includeTemplate('includes/SectorMap.inc'); ?>