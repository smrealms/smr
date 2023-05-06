<?php declare(strict_types=1);

use Smr\Globals;

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Sector $ThisSector
 * @var Smr\Ship $ThisShip
 * @var array<string, array{ID: int, Class: string}> $Sectors
 */

if ($Sectors) { ?>
	<div class="secNavBox">
		<div class="<?php if ($ThisShip->hasScanner()) { ?>scan<?php } else { ?>no_scan<?php } ?>">
			<?php
			if ($Sectors['Up']['ID'] !== 0) { ?>
				<div class="move_up move_text move_hover" id="moveUp">
					<a href="<?php echo Globals::getCurrentSectorMoveHREF($ThisPlayer, $Sectors['Up']['ID']); ?>" class="<?php echo $Sectors['Up']['Class']; ?>">
						<?php echo $Sectors['Up']['ID']; ?>
					</a>
				</div><?php
				if ($ThisShip->hasScanner()) { ?>
					<div class="scan_up scan_hover scan_text_hor">
						<a href="<?php echo Globals::getSectorScanHREF($ThisPlayer, $Sectors['Up']['ID']); ?>">
							SCAN
						</a>
					</div><?php
				}
			} else { ?>
				<div class="move_up move_text"></div>
				<?php if ($ThisShip->hasScanner()) {
					?><div class="scan_up scan_hover scan_text_hor"></div><?php
				}
			}

			if ($Sectors['Left']['ID'] !== 0) { ?>
				<div class="move_left move_text move_hover" id="moveLeft">
					<a href="<?php echo Globals::getCurrentSectorMoveHREF($ThisPlayer, $Sectors['Left']['ID']); ?>" class="<?php echo $Sectors['Left']['Class']; ?>">
						<?php echo $Sectors['Left']['ID']; ?>
					</a>
				</div><?php
				if ($ThisShip->hasScanner()) { ?>
					<div class="scan_left scan_hover scan_text_vert">
						<a href="<?php echo Globals::getSectorScanHREF($ThisPlayer, $Sectors['Left']['ID']); ?>">
							S<br />C<br />A<br />N
						</a>
					</div><?php
				}
			} else { ?>
				<div class="move_left move_text"></div><?php
				if ($ThisShip->hasScanner()) {
					?><div class="scan_left scan_hover scan_text_vert"></div><?php
				}
			}

			if ($ThisShip->hasScanner()) {
				$ThisSector->getSectorScanHREF($ThisPlayer);
			} ?>

			<div class="cs_mid move_text move_hover">
				<a href="<?php echo Globals::getCurrentSectorHREF(); ?>" class="currentSector">
					<?php echo $ThisSector->getSectorID(); ?>
				</a>
			</div>


			<?php
			if ($Sectors['Right']['ID'] !== 0) { ?>
				<div class="move_right move_text move_hover" id="moveRight">
					<a href="<?php echo Globals::getCurrentSectorMoveHREF($ThisPlayer, $Sectors['Right']['ID']); ?>" class="<?php echo $Sectors['Right']['Class']; ?>">
						<?php echo $Sectors['Right']['ID']; ?>
					</a>
				</div><?php
				if ($ThisShip->hasScanner()) { ?>
					<div class="scan_right scan_hover scan_text_vert">
						<a href="<?php echo Globals::getSectorScanHREF($ThisPlayer, $Sectors['Right']['ID']); ?>">
							S<br />C<br />A<br />N
						</a>
					</div><?php
				}
			} else { ?>
				<div class="move_right move_text"></div><?php
				if ($ThisShip->hasScanner()) {
					?><div class="scan_right scan_hover scan_text_vert"></div><?php
				}
			}

			if ($Sectors['Down']['ID'] !== 0) { ?>
				<div class="move_down move_text move_hover" id="moveDown">
					<a href="<?php echo Globals::getCurrentSectorMoveHREF($ThisPlayer, $Sectors['Down']['ID']); ?>" class="<?php echo $Sectors['Down']['Class']; ?>">
						<?php echo $Sectors['Down']['ID']; ?>
					</a>
				</div><?php
				if ($ThisShip->hasScanner()) { ?>
					<div class="scan_down scan_hover scan_text_hor">
						<a href="<?php echo Globals::getSectorScanHREF($ThisPlayer, $Sectors['Down']['ID']); ?>">
							SCAN
						</a>
					</div><?php
				}
			} else { ?>
				<div class="move_down move_text"></div>
				<?php if ($ThisShip->hasScanner()) {
					?><div class="scan_down scan_hover scan_text_hor"></div><?php
				}
			}

			if ($Sectors['Warp']['ID'] !== 0) { ?>
				<div class="move_warp move_text move_hover" id="moveWarp">
					<a href="<?php echo Globals::getCurrentSectorMoveHREF($ThisPlayer, $Sectors['Warp']['ID']); ?>" class="<?php echo $Sectors['Warp']['Class']; ?>">
						<?php echo $Sectors['Warp']['ID']; ?>
					</a>
				</div><?php
				if ($ThisShip->hasScanner()) { ?>
					<div class="scan_warp scan_hover scan_text_vert">
						<a href="<?php echo Globals::getSectorScanHREF($ThisPlayer, $Sectors['Warp']['ID']); ?>">
							S<br />C<br />A<br />N
						</a>
					</div><?php
				}
			}?>
		</div>
	</div><?php
}
