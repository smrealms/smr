<?php
if(isset($MenuItems))
{ ?>
	<div class="bar1">
		<div>
			<span class="nowrap"><?php
				foreach($MenuItems as $number => $MenuItem)
				{
					if(isset($MenuItem['Link']))
					{
						if ($number > 0)
						{
							?></span> | <span class="nowrap"><?php
						}
						?><a href="<?php echo $MenuItem['Link']; ?>"><?php echo $MenuItem['Text']; ?></a><?php
					}
				}?>
			</span>
		</div>
	</div><br /><?php
} ?>