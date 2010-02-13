<dl>
	<dt class="bold">Game:<dt><dd><?php echo $GameName ?></dd>
	<dt class="bold">Task:<dt><dd>Adding galaxies</dd>
	<dt class="bold">Description:<dt><dd style="width:50%;">Each galaxy has a name and a size. Please select the name from the drop down box and the size. All galaxies are quadratic
</dl>

<form name="FORM" method="POST" action="<?php echo $CreateGalaxiesFormHref ?>"><?php
	if($ChooseNumberOfGalaxies)
	{ ?>
			<p>Select number of galaxies<br />you want create!</p>
			<input type="text" name="galaxy_count" value="10" id="InputFields" size="3">&nbsp;&nbsp;&nbsp;<input type="submit" name="action" value="Next >>" id="InputFields"><?php 
	}
	else
	{ ?>
		<p>
			<table cellpadding="5" border="0"><?php
			for($i=0;$i<$NumberOfGalaxies;$i++)
			{ ?>
				<tr>
					<td align="right">Name:</td>
					<td align="left">
						<select name="galaxy[<?php echo $i ?>]" size="1" id="InputFields"><?php
						foreach($GalaxyNames as $GalaxyNumber => $GalaxyName)
						{ ?>
							<option value="<?php echo $GalaxyNumber ?>"<?php if($i == $GalaxyNumber){ ?> selected<?php } ?>>
								<?php echo $GalaxyName ?>
							</option>
						<?php } ?>
						</select>
					</td>
					<td align="right">Size:</td>
					<td align="left"><input type="text" name="size[<?php echo $i ?>]" value="15" size="3" maxlength="3" id="InputFields"></td>
				</tr><?php
			} ?>
			</table>
		</p>
		<input type="submit" name="action" value="Next >>" id="InputFields"><?php
		if($CanSkip)
		{
			?>&nbsp;&nbsp;<input type="submit" name="action" value="Skip >>" id="InputFields"><?php
		}
	} ?>
</form>