<?php $this->includeTemplate('includes/header.inc'); ?>
<table class="m" align="center">
	<tr>
		<td class="l0" rowspan="2">
			<div class="l1">
				<?php $this->includeTemplate('includes/leftPanel.inc'); ?>
			</div>
		</td>
		<td class="m0" colspan="2">
			<div id="middle_panel">
				<?php
					if($PageTopic)
					{
						?><h1><?php echo $PageTopic ?></h1><br /><?php
					}
					if($MenuBar)
					{
						echo $MenuBar;
					}
					$this->includeTemplate($TemplateBody);
				?>
			</div>
		</td>
		<td class="r0">
			<div id="right_panel">
				<?php $this->includeTemplate('includes/rightPanel.inc'); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td class="footer_left">
			<div style="width:294px;text-align:center">Get <b><u>FREE TURNS</u></b> for voting if(you see the star.</div>
			<?php
				foreach($VoteSites as $VoteSite)
					echo $VoteSite;
				?>
		</td>
		<td class="footer_right">
			<?php $this->includeTemplate('includes/copyright.inc'); ?>
		</td>
	</tr>
</table>
<?php $this->includeTemplate('includes/footer.inc'); ?>