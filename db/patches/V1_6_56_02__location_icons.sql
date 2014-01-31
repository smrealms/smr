/* adding some icons */

#Fed Beacon
UPDATE location_type lt SET lt.location_image = 'images/beacon.png' WHERE lt.location_type_id = 201;
UPDATE location_type lt SET lt.location_image = 'images/beacon_als.png' WHERE lt.location_type_id = 203;
UPDATE location_type lt SET lt.location_image = 'images/beacon_cre.png' WHERE lt.location_type_id = 204;
UPDATE location_type lt SET lt.location_image = 'images/beacon_hum.png' WHERE lt.location_type_id = 205;
UPDATE location_type lt SET lt.location_image = 'images/beacon_ikt.png' WHERE lt.location_type_id = 206;
UPDATE location_type lt SET lt.location_image = 'images/beacon_sal.png' WHERE lt.location_type_id = 207;
UPDATE location_type lt SET lt.location_image = 'images/beacon_the.png' WHERE lt.location_type_id = 208;
UPDATE location_type lt SET lt.location_image = 'images/beacon_wq.png' WHERE lt.location_type_id = 209;
UPDATE location_type lt SET lt.location_image = 'images/beacon_nij.png' WHERE lt.location_type_id = 210;

#HQs
UPDATE location_type lt SET lt.location_image = 'images/government.png' WHERE lt.location_type_id = 101;
UPDATE location_type lt SET lt.location_image = 'images/government_als.png' WHERE lt.location_type_id = 103;
UPDATE location_type lt SET lt.location_image = 'images/government_cre.png' WHERE lt.location_type_id = 104;
UPDATE location_type lt SET lt.location_image = 'images/government_hum.png' WHERE lt.location_type_id = 105;
UPDATE location_type lt SET lt.location_image = 'images/government_ikt.png' WHERE lt.location_type_id = 106;
UPDATE location_type lt SET lt.location_image = 'images/government_sal.png' WHERE lt.location_type_id = 107;
UPDATE location_type lt SET lt.location_image = 'images/government_the.png' WHERE lt.location_type_id = 108;
UPDATE location_type lt SET lt.location_image = 'images/government_wq.png' WHERE lt.location_type_id = 109;
UPDATE location_type lt SET lt.location_image = 'images/government_nij.png' WHERE lt.location_type_id = 110;

#Weapon Shops
UPDATE location_type lt SET lt.location_image = 'images/weapon_shop.png' WHERE lt.location_image = 'images/weapon_shop.gif';

#Allrenches
UPDATE location_type lt SET lt.location_image = 'images/hardware2.png' WHERE lt.location_image = 'images/hardware.png';

#Allships
UPDATE location_type lt SET lt.location_image = 'images/shipdealer.png' WHERE lt.location_image = 'images/shipdealer.gif';