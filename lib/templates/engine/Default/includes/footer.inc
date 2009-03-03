		<script type="text/javascript" src="js/smr15.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<?php
		if($js)
		{ ?>
			<script type="text/javascript" src="<?php echo $js ?>"></script><?php
		} 
		if($AJAX_ENABLE_REFRESH)
		{ ?>
			<script type="text/javascript">window.onload=function(){startRefresh('<?php echo $AJAX_ENABLE_REFRESH ?>');}</script><?php
		} ?>
	</body>
</html>