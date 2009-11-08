<?php if($Sectors)
{ ?>
	<div class="cs_box">
		<div class="scan">
			<?php
			if(isset($Sectors['Up']['MoveLink']))
			{ ?>
				<a href="<?php echo $Sectors['Up']['MoveLink'] ?>" class="<?php echo $Sectors['Up']['Class'] ?>">
					<div class="move_up move_text move_hover"><span><?php echo $Sectors['Up']['ID'] ?></span></div>
				</a><?php
				if($ThisShip->hasScanner())
				{ ?>
					<a href="<?php echo $Sectors['Up']['ScanLink'] ?>">
						<div class="scan_up scan_hover scan_text_hor"><span>SCAN</span></div>
					</a><?php
				}
			}
			else
			{ ?>
				<div class="move_up move_text"></div>
				<?php if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_up scan_hover"></div><?php
				}
			}
			
			
			if(isset($Sectors['Left']['MoveLink']))
			{ ?>
				<a href="<?php echo $Sectors['Left']['MoveLink'] ?>" class="<?php echo $Sectors['Left']['Class'] ?>">
					<div class="move_left move_text move_hover"><span><?php echo $Sectors['Left']['ID'] ?></span></div>
				</a><?php
				if($ThisShip->hasScanner())
				{ ?>
					<a href="<?php echo $Sectors['Left']['ScanLink'] ?>">
						<div class="scan_left scan_hover scan_text_vert"><span>S<br />C<br />A<br />N</span></div>
					</a><?php
				}
			}
			else
			{ ?>
				<div class="move_left move_text"></div>
				<?php if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_left scan_hover"></div><?php
				}
			}?>
			
			
			<a href="<?php echo $ThisSector->getCurrentSectorHREF() ?>" class="dgreen">
				<div class="cs_mid move_text move_hover"><span><?php echo $ThisSector->getSectorID() ?></span></div>
			</a>
			
			
			<?php
			if(isset($Sectors['Right']['MoveLink']))
			{ ?>
				<a href="<?php echo $Sectors['Right']['MoveLink'] ?>" class="<?php echo $Sectors['Right']['Class'] ?>">
					<div class="move_right move_text move_hover"><span><?php echo $Sectors['Right']['ID'] ?></span></div>
				</a><?php
				if($ThisShip->hasScanner())
				{ ?>
					<a href="<?php echo $Sectors['Right']['ScanLink'] ?>">
						<div class="scan_right scan_hover scan_text_vert"><span>S<br />C<br />A<br />N</span></div>
					</a><?php
				}
			}
			else
			{ ?>
				<div class="move_right move_text"></div><?php
				if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_right scan_hover"></div><?php
				}
			}
			

			if(isset($Sectors['Down']['MoveLink']))
			{ ?>
				<a href="<?php echo $Sectors['Down']['MoveLink'] ?>" class="<?php echo $Sectors['Down']['Class'] ?>">
					<div class="move_down move_text move_hover"><span><?php echo $Sectors['Down']['ID'] ?></span></div>
				</a><?php
				if($ThisShip->hasScanner())
				{ ?>
					<a href="<?php echo $Sectors['Down']['ScanLink'] ?>">
						<div class="scan_down scan_hover scan_text_hor"><span>SCAN</span></div>
					</a><?php
				}
			}
			else
			{ ?>
				<div class="move_down move_text"></div>
				<?php if($ThisShip->hasScanner())
				{ ?>
					<div class="scan_down scan_hover"></div><?php 
				}
			}
			

			if(isset($Sectors['Warp']['MoveLink']))
			{ ?>
				<a href="<?php echo $Sectors['Warp']['MoveLink'] ?>" class="<?php echo $Sectors['Warp']['Class'] ?>">
					<div class="move_warp move_text move_hover"><span><?php echo $Sectors['Warp']['ID'] ?></span></div>
				</a><?php
				if($ThisShip->hasScanner())
				{ ?>
					<a href="<?php echo $Sectors['Warp']['ScanLink'] ?>">
						<div class="scan_warp scan_hover scan_text_vert"><span>S<br />C<br />A<br />N</span></div>
					</a><?php
				}
			}?>
		</div>
	</div>
<?php
}
?>