{if $ThisPlayer->hasPlottedCourse()}
	{assign var=PlottedCourse value=$ThisPlayer->getPlottedCourse()}
	{assign var=NextSector value=$ThisSector->getSector($ThisPlayer->getGameID(),$PlottedCourse.NextSector,$ThisPlayer->getAccountID())}
	<table class="nobord" width="100%">
		<tr>
			<td{if $ThisShip->hasScanner()} rowspan="2"{/if}>{$PlottedCourse.CourseString}<br />
				({$PlottedCourse.Length} sectors)
			</td>
			<td class="top right">
				<div class="buttonA">
					<a class="buttonA" href="{$NextSector->getCurrentSectorHREF()}">&nbsp; Follow Course ({$PlottedCourse.NextSector}) &nbsp;</a>
				</div>
			</td>
		</tr>
		{if $ThisShip->hasScanner()}
			<tr>
				<td class="top right">
					<div class="buttonA">
						<a class="buttonA" href="{$NextSector->getScanSectorHREF()}">&nbsp; Scan Course ({$PlottedCourse.NextSector}) &nbsp;</a>
					</div>
				</td>
			</tr>
		{/if}
	</table>
{/if}