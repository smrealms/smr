-- bump base construction exp due to change in scaling formula
UPDATE planet_can_build SET exp_gain=100 WHERE planet_type_id=1 AND construction_id=1;
UPDATE planet_can_build SET exp_gain=200 WHERE planet_type_id=1 AND construction_id=2;
UPDATE planet_can_build SET exp_gain=600 WHERE planet_type_id=1 AND construction_id=3;
UPDATE planet_can_build SET exp_gain=100 WHERE planet_type_id=1 AND construction_id=4;
UPDATE planet_can_build SET exp_gain=100 WHERE planet_type_id=2 AND construction_id=1;
UPDATE planet_can_build SET exp_gain=200 WHERE planet_type_id=2 AND construction_id=2;
UPDATE planet_can_build SET exp_gain=200 WHERE planet_type_id=2 AND construction_id=3;
UPDATE planet_can_build SET exp_gain=100 WHERE planet_type_id=2 AND construction_id=4;
UPDATE planet_can_build SET exp_gain=100 WHERE planet_type_id=3 AND construction_id=1;
UPDATE planet_can_build SET exp_gain=200 WHERE planet_type_id=3 AND construction_id=2;
UPDATE planet_can_build SET exp_gain=600 WHERE planet_type_id=3 AND construction_id=3;
UPDATE planet_can_build SET exp_gain=100 WHERE planet_type_id=3 AND construction_id=4;
