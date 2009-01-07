<table width="100%">
	<tr>
		<td>
			{if !$isFirefox}
				<a class="button" href="http://www.spreadfirefox.com/node&id=216853&t=219" target="firefox">
					<img alt="Get Firefox!" title="Get Firefox!" src="images/firefoxSmall.gif">
				</a>
			{else}
				&nbsp;
			{/if}
		</td>
		<td class="right">
			<div align="right">
				SMR {$Version}&copy;2007-{$CurrentYear} Page and SMR<br />
				Kindly Hosted by <a href="http://www.fem.tu-ilmenau.de/index.php?id=93&amp;L=1" target="fem">FeM</a><br />
				Script runtime: <span id="runtime">{$ScriptRuntime}</span> seconds<br />
				<a href="imprint.html">[Imprint]</a>
			</div>
		</td>
	</tr>
</table>