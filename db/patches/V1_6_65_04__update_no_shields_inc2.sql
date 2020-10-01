-- Replace the Ik-Thorne Cluster Missile with the Ik-Thorne Accoustic Jammer
-- at the No Shields Inc 2 weapon shop.
UPDATE location_sells_weapons SET weapon_type_id = 15 WHERE weapon_type_id = 14 AND location_type_id = 311;
