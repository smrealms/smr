<!DOCTYPE html>
<html>
	<head><?php
		if(!is_object($ThisAccount) || $ThisAccount->isDefaultCSSEnabled()) { ?>
			<link rel="stylesheet" type="text/css" href="<?php echo $CSSLink; ?>">
			<link rel="stylesheet" type="text/css" href="<?php echo $CSSColourLink; ?>" /><?php
		}
		if(isset($ExtraCSSLink)) {
			?><link rel="stylesheet" type="text/css" href="<?php echo $ExtraCSSLink; ?>" /><?php
		} ?>
	    <title>Galaxy Map</title>
	    <meta http-equiv="pragma" content="no-cache">
	    <style type="text/css">
	      body {
	        font-size:<?php echo $ThisAccount->getFontSize(); ?>%;
	      }
	    </style>
    </head>
	
	<body>
		<h1>View Galaxy</h1>
		<br/>
		<form class="standard" name="GalaxyMapForm" method="GET">
			Switch galaxy
			<select name="galaxy_id" onchange="this.form.submit()"><?php
				foreach($GameGalaxies as &$GameGalaxy) { 
					$GalaxyID = $GameGalaxy->getGalaxyID(); ?>
					<option value="<?php echo $GalaxyID; ?>"<?php if($ThisGalaxy->equals($GameGalaxy)){ ?> selected="selected"<?php } ?>>
					<?php echo $GameGalaxy->getName(); ?>
					</option><?php
				} unset($GameGalaxy); ?>
			</select>&nbsp;
			<input type="submit" value="View"/>&nbsp;
		</form>
		<br>
		<form class="standard" name="GalaxyMapJumpTo" method="get">
			Switch sector 
			<input type="text" size="5" maxlength="5" name="sector_id" />&nbsp;
			<input type="submit" value="View" />
		</form>
		
		<p>Galaxy map of the known <b><big><?php echo $GalaxyName; ?></big></b> galaxy.</p>
	
		<?php $this->includeTemplate('includes/SectorMap.inc',array('GalaxyMap'=>true)); ?>
	</body>
</html>