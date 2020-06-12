-- Reduce cost and damage of Level 1 weapons by 30%
UPDATE weapon_type SET cost = 19250, armour_damage = 120 WHERE weapon_name = 'Ik-Thorne Rapid Fire Cannon';
UPDATE weapon_type SET cost = 20000, armour_damage = 50 WHERE weapon_name = 'Anti-Ship Missile (Heat-Seeking)';
UPDATE weapon_type SET cost = 15500, armour_damage = 70 WHERE weapon_name = 'Thevian Rail Gun';
UPDATE weapon_type SET cost = 19250, armour_damage = 70 WHERE weapon_name = 'Anti-Ship Missile';
UPDATE weapon_type SET cost = 15500, armour_damage = 140 WHERE weapon_name = 'Projectile Cannon Lvl 2';
UPDATE weapon_type SET cost = 9500, armour_damage = 50 WHERE weapon_name = 'Projectile Cannon Lvl 1';
UPDATE weapon_type SET cost = 13000, shield_damage = 45 WHERE weapon_name = 'Shield Disruptor';
UPDATE weapon_type SET cost = 9000, shield_damage = 25, armour_damage = 25 WHERE weapon_name = 'Small Laser';
UPDATE weapon_type SET cost = 15000, shield_damage = 40, armour_damage = 20 WHERE weapon_name = 'Nijarin Ion Pulse Phaser';
