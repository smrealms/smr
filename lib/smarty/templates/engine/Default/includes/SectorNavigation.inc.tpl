{if $Sectors}
	<div class="cs_box">
		<div class="scan">
			{if isset($Sectors.Up.MoveLink)}
				<a href="{$Sectors.Up.MoveLink}" class="{$Sectors.Up.Class}">
					<div class="move_up move_text move_hover"><p>{$Sectors.Up.ID}</p></div>
				</a>
				{if $ThisShip->hasScanner()}
					<a href="{$Sectors.Up.ScanLink}">
						<div class="scan_up scan_hover scan_text_hor"><p>SCAN</p></div>
					</a>
				{/if}
			{else}
				<div class="move_up move_text"></div>
				{if $ThisShip->hasScanner()}
					<div class="scan_up scan_hover"></div>
				{/if}
			{/if}
			
			
			{if isset($Sectors.Left.MoveLink)}
				<a href="{$Sectors.Left.MoveLink}" class="{$Sectors.Left.Class}">
					<div class="move_left move_text move_hover"><p>{$Sectors.Left.ID}</p></div>
				</a>
				{if $ThisShip->hasScanner()}
					<a href="{$Sectors.Left.ScanLink}">
						<div class="scan_left scan_hover scan_text_vert"><p>S<br />C<br />A<br />N</p></div>
					</a>
				{/if}
			{else}
				<div class="move_left move_text"></div>
				{if $ThisShip->hasScanner()}
					<div class="scan_left scan_hover"></div>
				{/if}
			{/if}
			
			
			<a href="{$ThisSector->getCurrentSectorHREF()}" class="dgreen">
				<div class="cs_mid move_text move_hover"><p>{$ThisSector->getSectorID()}</p></div>
			</a>
			
			
			{if isset($Sectors.Right.MoveLink)}
				<a href="{$Sectors.Right.MoveLink}" class="{$Sectors.Right.Class}">
					<div class="move_right move_text move_hover"><p>{$Sectors.Right.ID}</p></div>
				</a>
				{if $ThisShip->hasScanner()}
					<a href="{$Sectors.Right.ScanLink}">
						<div class="scan_right scan_hover scan_text_vert"><p>S<br />C<br />A<br />N</p></div>
					</a>
				{/if}
			{else}
				<div class="move_right move_text"></div>
				{if $ThisShip->hasScanner()}
					<div class="scan_right scan_hover"></div>
				{/if}
			{/if}
			
			{if isset($Sectors.Down.MoveLink)}
				<a href="{$Sectors.Down.MoveLink}" class="{$Sectors.Down.Class}">
					<div class="move_down move_text move_hover"><p>{$Sectors.Down.ID}</p></div>
				</a>
				{if $ThisShip->hasScanner()}
					<a href="{$Sectors.Down.ScanLink}">
						<div class="scan_down scan_hover scan_text_hor"><p>SCAN</p></div>
					</a>
				{/if}
			{else}
				<div class="move_down move_text"></div>
				{if $ThisShip->hasScanner()}
					<div class="scan_down scan_hover"></div>
				{/if}
			{/if}
			
			{if isset($Sectors.Warp.MoveLink)}
				<a href="{$Sectors.Warp.MoveLink}" class="{$Sectors.Warp.Class}">
					<div class="move_warp move_text move_hover"><p>{$Sectors.Warp.ID}</p></div>
				</a>
				{if $ThisShip->hasScanner()}
					<a href="{$Sectors.Warp.ScanLink}">
						<div class="scan_warp scan_hover scan_text_vert"><p>S<br />C<br />A<br />N</p></div>
					</a>
				{/if}
				{*
					{else}
						<div class="move_down move_text">&nbsp;</div>
						{if $ThisShip->hasScanner()}
							<div class="scan_down scan_hover"></div>
						{/if}
				*}
			{/if}
		</div>
	</div>
{/if}