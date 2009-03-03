<table width="100%">
	<tr>
		<td><?php
			if(!$isFirefox)
			{ ?>
				<a class="button" href="http://www.spreadfirefox.com/node&id=216853&t=219" target="firefox">
					<img alt="Get Firefox!" title="Get Firefox!" src="images/firefoxSmall.gif">
				</a>
			<?php
			}
			else
			{ ?>
				&nbsp;<?php
			} ?>
		</td>
		<td class="right">
			<div align="right">
				SMR <?php echo $Version ?>&copy;2007-<?php echo $CurrentYear ?> Page and SMR<br />
				Kindly Hosted by <a href="http://www.fem.tu-ilmenau.de/index.php?id=93&amp;L=1" target="fem">FeM</a><br />
				Script runtime: <span id="runtime"><?php echo $ScriptRuntime ?></span> seconds<br />
				<a href="imprint.html">[Imprint]</a>
			</div>
		</td>
	</tr>
</table>