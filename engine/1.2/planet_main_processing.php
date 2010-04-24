<?php

//we need to see if they are actually still on the planet.
if ($player->land_on_planet == 'FALSE')
    forward(create_container("skeleton.php", "current_sector.php"));
else
    forward(create_container("skeleton.php", "planet_main.php"));

?>