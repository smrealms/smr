		<script type="text/javascript" src="js/smr15.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		{if $js}
			<script type="text/javascript" src="{$js}"></script>
		{/if}
		{if $AJAX_ENABLE_REFRESH}
			<script type="text/javascript">window.onload=function(){ldelim}startRefresh('{$AJAX_ENABLE_REFRESH}');{rdelim}</script>
		{/if}
	</body>
</html>