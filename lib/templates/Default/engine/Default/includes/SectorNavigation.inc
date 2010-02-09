<?php if($Sectors)
{ ?>
	<div class="secNavBox">
		<div class="<?php if($ThisShip->hasScanner()){ ?>scan<?php }else{ ?>no_scan<?php } ?>">
			<?php
			if(isset($Sectors['Up']['MoveLink']))
			{ ?>
				<div class="move_up move_text move_hover">
					<a href="<?php echo $Sectors['Up']['MoveLink']; ?>" class="<?php echo $Sectors['Up']['Class']; ?>">
						<?php echo $Sectors['Up']['ID']; ?>
					</a>
				</div><?php
				if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_up scan_hover scan_text_hor">
						<a href="<?php echo $Sectors['Up']['ScanLink']; ?>">
							SCAN
						</a>
					</div><?php
				}
			}
			else
			{ ?>
				<div class="move_up move_text"></div>
				<?php if($ThisShip->hasScanner())
				{
					?><div class="scan_up scan_hover scan_text_hor"></div><?php
				}
			}
			
			
			if(isset($Sectors['Left']['MoveLink']))
			{ ?>
				<div class="move_left move_text move_hover">
					<a href="<?php echo $Sectors['Left']['MoveLink']; ?>" class="<?php echo $Sectors['Left']['Class']; ?>">
						<?php echo $Sectors['Left']['ID']; ?>
					</a>
				</div><?php
				if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_left scan_hover scan_text_vert">
						<a href="<?php echo $Sectors['Left']['ScanLink']; ?>">
							S<br />C<br />A<br />N
						</a>
					</div><?php
				}
			}
			else
			{ ?>
				<div class="move_left move_text"></div>
				<?php if($ThisShip->hasScanner())
				{
					?><div class="scan_left scan_hover scan_text_vert"></div><?php
				}
			}?>
			
			
			<div class="cs_mid move_text move_hover">
				<a href="<?php echo $ThisSector->getCurrentSectorHREF(); ?>" class="dgreen">
					<?php echo $ThisSector->getSectorID(); ?>
				</a>
			</div>
			
			
			<?php
			if(isset($Sectors['Right']['MoveLink']))
			{ ?>
				<div class="move_right move_text move_hover">
					<a href="<?php echo $Sectors['Right']['MoveLink']; ?>" class="<?php echo $Sectors['Right']['Class']; ?>">
						<?php echo $Sectors['Right']['ID']; ?>
					</a>
				</div><?php
				if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_right scan_hover scan_text_vert">
						<a href="<?php echo $Sectors['Right']['ScanLink']; ?>">
							S<br />C<br />A<br />N
						</a>
					</div><?php
				}
			}
			else
			{ ?>
				<div class="move_right move_text"></div><?php
				if($ThisShip->hasScanner())
				{
					?><div class="scan_right scan_hover scan_text_vert"></div><?php
				}
			}
			

			if(isset($Sectors['Down']['MoveLink']))
			{ ?>
				<div class="move_down move_text move_hover">
					<a href="<?php echo $Sectors['Down']['MoveLink']; ?>" class="<?php echo $Sectors['Down']['Class']; ?>">
						<?php echo $Sectors['Down']['ID']; ?>
					</a>
				</div><?php
				if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_down scan_hover scan_text_hor">
						<a href="<?php echo $Sectors['Down']['ScanLink']; ?>">
							SCAN
						</a>
					</div><?php
				}
			}
			else
			{ ?>
				<div class="move_down move_text"></div>
				<?php if($ThisShip->hasScanner())
				{
					?><div class="scan_down scan_hover scan_text_hor"></div><?php 
				}
			}
			

			if(isset($Sectors['Warp']['MoveLink']))
			{ ?>
				<div class="move_warp move_text move_hover">
					<a href="<?php echo $Sectors['Warp']['MoveLink']; ?>" class="<?php echo $Sectors['Warp']['Class']; ?>">
						<?php echo $Sectors['Warp']['ID']; ?>
					</a>
				</div><?php
				if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_warp scan_hover scan_text_vert">
						<a href="<?php echo $Sectors['Warp']['ScanLink']; ?>">
							S<br />C<br />A<br />N
						</a>
					</div><?php
				}
			}?>
		</div>
	</div><?php
} ?>