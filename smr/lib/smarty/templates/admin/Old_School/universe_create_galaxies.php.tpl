<dl>
	<dt style="font-weight:bold;">Game:<dt><dd>{$GameName}</dd>
	<dt style="font-weight:bold;">Task:<dt><dd>Adding galaxies</d>
	<dt style="font-weight:bold;">Description:<dt><dd style="width:50%;">Each galaxy has a name and a size. Please select the name from the drop down box and the size. All galaxies are quadratic
</dl>

<form name="FORM" method="POST" action="{$CreateGalaxiesFormAction}">
	<input type="hidden" name="sn" value="{$CreateGalaxiesFormSN}">
	{if $ChooseNumberOfGalaxies}
			<p>Select number of galaxies<br>you want create!</p>
			<input type="text" name="galaxy_count" value="10" id="InputFields" size="3">&nbsp;&nbsp;&nbsp;<input type="submit" name="action" value="Next >>" id="InputFields">
	{else}
		<p>
			<table cellpadding="5" border="0">
				{section name=GalaxyLoop start=0 loop=$NumberOfGalaxies step=1}
					<tr>
						<td align="right">Name:</td>
						<td align="left">
							<select name="galaxy[{$smarty.section.GalaxyLoop.index}]" size="1" id="InputFields">
							{foreach from=$GalaxyNames item=GalaxyName key=GalaxyNumber}
								<option value="{$GalaxyNumber}"{if $smarty.section.GalaxyLoop.index == $GalaxyNumber} selected{/if}>
									{$GalaxyName}
								</option>
							{/foreach}
							</select>
						</td>
						<td align="right">Size:</td>
						<td align="left"><input type="text" name="size[{$smarty.section.GalaxyLoop.index}]" value="15" size="3" maxlength="3" id="InputFields"></td>
					</tr>
				{/section}
			</table>
		</p>
		<input type="submit" name="action" value="Next >>" id="InputFields">
		{if $CanSkip}&nbsp;&nbsp;<input type="submit" name="action" value="Skip >>" id="InputFields">{/if}
	{/if}
</form>