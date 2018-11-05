/* changes to ships as per JJ */

-- Battle Cruiser cost to 3628134, speed to 7, HP to 5, Shields to 525, Armounr to 500
UPDATE ship_type SET cost=3628134, speed=7, hardpoint = 5 WHERE ship_type_id = 17;
UPDATE ship_type_support_hardware SET max_amount = 525 WHERE ship_type_id = 17 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 500 WHERE ship_type_id = 17 AND hardware_type_id = 2;

-- Celestial Mercanary HP to 3, Scouts to 5
UPDATE ship_type SET hardpoint = 3 WHERE ship_type_id = 18;
UPDATE ship_type_support_hardware SET max_amount = 5 WHERE ship_type_id = 18 AND hardware_type_id = 5;

-- Celestial Combatant HP to 4, Shields to 400, Armour to 350, Drones to 35, Scouts to 5
UPDATE ship_type SET hardpoint = 4 WHERE ship_type_id = 19;
UPDATE ship_type_support_hardware SET max_amount = 400 WHERE ship_type_id = 19 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 350 WHERE ship_type_id = 19 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 35 WHERE ship_type_id = 19 AND hardware_type_id = 4;
UPDATE ship_type_support_hardware SET max_amount = 5 WHERE ship_type_id = 19 AND hardware_type_id = 5;

-- Federal Discovery HP to 3, Shields to 500, cost to 3335689
UPDATE ship_type SET cost=3335689, hardpoint = 3 WHERE ship_type_id = 20;
UPDATE ship_type_support_hardware SET max_amount = 500 WHERE ship_type_id = 20 AND hardware_type_id = 1;

-- Federal Warrant HP to 5, Shields to 600, Armour to 500, cost to 12026598
UPDATE ship_type SET cost=12026598, hardpoint = 5 WHERE ship_type_id = 21;
UPDATE ship_type_support_hardware SET max_amount = 600 WHERE ship_type_id = 21 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 500 WHERE ship_type_id = 21 AND hardware_type_id = 2;

-- Federal Ultimatum HP to 7, Shields to 700, Armour to 600, cost to 38675738
UPDATE ship_type SET cost=38675738, hardpoint = 7 WHERE ship_type_id = 22;
UPDATE ship_type_support_hardware SET max_amount = 700 WHERE ship_type_id = 22 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 600 WHERE ship_type_id = 22 AND hardware_type_id = 2;

-- Thief Cargo Holds to 150, cost to 10802157
UPDATE ship_type SET cost=10802157 WHERE ship_type_id = 23;
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 23 AND hardware_type_id = 3;

-- Assassin HP to 4, Shields to 500, Armour to 400, Scouts to 10, cost to 12483452
UPDATE ship_type SET cost=12483452, hardpoint = 4 WHERE ship_type_id = 24;
UPDATE ship_type_support_hardware SET max_amount = 500 WHERE ship_type_id = 24 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 400 WHERE ship_type_id = 24 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 24 AND hardware_type_id = 5;

-- Death Cruiser HP to 5, Shields to 800, Scouts to 20, Mines to 125, cost to 25890100
UPDATE ship_type SET cost=25890100, hardpoint = 5 WHERE ship_type_id = 25;
UPDATE ship_type_support_hardware SET max_amount = 800 WHERE ship_type_id = 25 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 20 WHERE ship_type_id = 25 AND hardware_type_id = 5;
UPDATE ship_type_support_hardware SET max_amount = 125 WHERE ship_type_id = 25 AND hardware_type_id = 6;

-- Medium Carrier HP to 1, Shields to 450, Aromur to 100, Drones to 200, Scouts to 10, cost to 1630523
UPDATE ship_type SET cost=1630523, hardpoint = 1 WHERE ship_type_id = 27;
UPDATE ship_type_support_hardware SET max_amount = 450 WHERE ship_type_id = 27 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 27 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 27 AND hardware_type_id = 4;
UPDATE ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 27 AND hardware_type_id = 5;

-- Small Timer Cargo Holds to 100
UPDATE ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 29 AND hardware_type_id = 3;

-- Trip-Maker Shields to 200, Armour to 150, cost to 1354955
UPDATE ship_type SET cost=1354955 WHERE ship_type_id = 30;
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 30 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 30 AND hardware_type_id = 2;

-- Deal-Maker Shields to 250, Armour to 200
UPDATE ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 31 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 31 AND hardware_type_id = 2;

-- Deep-Spacer Shields to 350, Armour to 250
UPDATE ship_type_support_hardware SET max_amount = 350 WHERE ship_type_id = 32 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 32 AND hardware_type_id = 2;

-- Trade-Master speed to 9, Cargo Holds to 200, cost to 12095764
UPDATE ship_type SET speed=9, cost=12095764 WHERE ship_type_id = 33;
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 33 AND hardware_type_id = 3;

-- Leviathan Cargo Holds to 240
UPDATE ship_type_support_hardware SET max_amount = 240 WHERE ship_type_id = 35 AND hardware_type_id = 3;

-- Goliath HP to 4, Armour to 900, Scouts to 0, Mines to 0, cost to 3380866
UPDATE ship_type SET cost=3380866, hardpoint = 4 WHERE ship_type_id = 36;
UPDATE ship_type_support_hardware SET max_amount = 900 WHERE ship_type_id = 36 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 36 AND hardware_type_id = 5;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 36 AND hardware_type_id = 6;

-- Juggernaut HP to 5, Shields to 150, Armour to 1150, cost to 5104130
UPDATE ship_type SET cost=5104130, hardpoint = 5 WHERE ship_type_id = 37;
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 37 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 1150 WHERE ship_type_id = 37 AND hardware_type_id = 2;

-- Devastator speed to 6, HP to 8, Armour to 1800, cost to 11559082
UPDATE ship_type SET speed=6, cost=11559082, hardpoint = 8 WHERE ship_type_id = 38;
UPDATE ship_type_support_hardware SET max_amount = 1800 WHERE ship_type_id = 38 AND hardware_type_id = 2;

-- Ambassador Cargo Holds to 150, cost to 1792187
UPDATE ship_type SET cost=1792187 WHERE ship_type_id = 40;
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 40 AND hardware_type_id = 3;

-- Renaissance Cargo Holds to 90, cost to 606664, scanner to yes
UPDATE ship_type SET cost=606664 WHERE ship_type_id = 41;
UPDATE ship_type_support_hardware SET max_amount = 90 WHERE ship_type_id = 41 AND hardware_type_id = 3;
UPDATE ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 41 AND hardware_type_id = 7;

-- Border Cruiser HP to 4, Shields to 575, Armour to 600, cost to 6887432
UPDATE ship_type SET cost=6887432, hardpoint=4  WHERE ship_type_id = 42;
UPDATE ship_type_support_hardware SET max_amount = 575 WHERE ship_type_id = 42 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 600 WHERE ship_type_id = 42 AND hardware_type_id = 2;

-- Destroyer HP to 6, Shields to 750, Armour to 750, Drones to 100, cost to 13719505
UPDATE ship_type SET cost=13719505, hardpoint=6  WHERE ship_type_id = 43;
UPDATE ship_type_support_hardware SET max_amount = 750 WHERE ship_type_id = 43 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 750 WHERE ship_type_id = 43 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 43 AND hardware_type_id = 4;

-- Rebellious Child speed to 10, HP to 1, Shields to 375, Armour to 75, cost to 273707
UPDATE ship_type SET speed=10, cost=273707, hardpoint=1  WHERE ship_type_id = 45;
UPDATE ship_type_support_hardware SET max_amount = 350 WHERE ship_type_id = 45 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 75 WHERE ship_type_id = 45 AND hardware_type_id = 2;

-- Favoured Offspring Cargo Holds to 200, Scanner to Yes, cost to 2486141
UPDATE ship_type SET cost=2486141 WHERE ship_type_id = 46;
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 46 AND hardware_type_id = 3;
UPDATE ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 46 AND hardware_type_id = 7;

-- Proto Carrier speed to 9, Shields to 400, Armour to 125, Cargo Holds to 120, Drones to 90, Scouts to 10, Mines to 0, cost to 553810
UPDATE ship_type SET speed=9, cost=553810 WHERE ship_type_id = 47;
UPDATE ship_type_support_hardware SET max_amount = 400 WHERE ship_type_id = 47 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 125 WHERE ship_type_id = 47 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 120 WHERE ship_type_id = 47 AND hardware_type_id = 3;
UPDATE ship_type_support_hardware SET max_amount = 90 WHERE ship_type_id = 47 AND hardware_type_id = 4;
UPDATE ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 47 AND hardware_type_id = 5;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 47 AND hardware_type_id = 6;

-- Advanced Carrier Shields to 750, Armour to 100, Drones to 275, cost to 5610264
UPDATE ship_type SET cost=5610264 WHERE ship_type_id = 48;
UPDATE ship_type_support_hardware SET max_amount = 750 WHERE ship_type_id = 48 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 48 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 275 WHERE ship_type_id = 48 AND hardware_type_id = 4;

-- Mother Ship speed to 6, Shields to 1000, cost to 10432759
UPDATE ship_type SET speed=6, cost=10432759 WHERE ship_type_id = 49;
UPDATE ship_type_support_hardware SET max_amount = 1000 WHERE ship_type_id = 49 AND hardware_type_id = 1;

-- Drudge speed to 6, Cargo Holds to 250
UPDATE ship_type SET speed=6 WHERE ship_type_id = 51;
UPDATE ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 51 AND hardware_type_id = 3;

-- Watchful Eye speed to 10, HP to 1, Shields to 75, Armour to 125, Scouts to 100, cost to 135366
UPDATE ship_type SET speed=10, cost=135366, hardpoint=1 WHERE ship_type_id = 52;
UPDATE ship_type_support_hardware SET max_amount = 75 WHERE ship_type_id = 52 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 125 WHERE ship_type_id = 52 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 52 AND hardware_type_id = 5;

-- Predator speed to 8, HP to 4, Shields to 525, Armour to 475, Scouts to 10, cost to 2823300
UPDATE ship_type SET speed=8, cost=2823300, hardpoint=4 WHERE ship_type_id = 53;
UPDATE ship_type_support_hardware SET max_amount = 525 WHERE ship_type_id = 53 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 475 WHERE ship_type_id = 53 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 53 AND hardware_type_id = 5;

-- Ravager HP to 5, Shields to 650, cost to 6707691
UPDATE ship_type SET cost=6707691, hardpoint=5 WHERE ship_type_id = 54;
UPDATE ship_type_support_hardware SET max_amount = 650 WHERE ship_type_id = 54 AND hardware_type_id = 1;

-- Eater of Souls, speed to 7, HP to 6, cost to 13306175
UPDATE ship_type SET speed=7, cost=13306175, hardpoint=6 WHERE ship_type_id = 55;

-- Expediter speed to 9, HP to 0, Cargo Holds to 180, Scouts to 0, Mines to 0
UPDATE ship_type SET speed=9, hardpoint=0 WHERE ship_type_id = 57;
UPDATE ship_type_support_hardware SET max_amount = 180  WHERE ship_type_id = 57 AND hardware_type_id = 3;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 57 AND hardware_type_id = 5;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 57 AND hardware_type_id = 6;

-- Star Ranger speed to 12, HP to 1, Shields to 225, Armour to 200, Scouts to 10, cost to 130923
UPDATE ship_type SET speed=12, cost=130923, hardpoint=1 WHERE ship_type_id = 58;
UPDATE ship_type_support_hardware SET max_amount = 225  WHERE ship_type_id = 58 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 58 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 58 AND hardware_type_id = 5;

-- Bounty Hunter speed to 11, HP to 3, Shields to 550, Armour to 650, cost to 1768634
UPDATE ship_type SET speed=11, cost=1768634, hardpoint=3 WHERE ship_type_id = 59;
UPDATE ship_type_support_hardware SET max_amount = 550  WHERE ship_type_id = 59 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 650 WHERE ship_type_id = 59 AND hardware_type_id = 2;

-- Carapace HP to 5, Shields to 600, Armour to 700, cost to 6045094
UPDATE ship_type SET cost=6045094, hardpoint=5 WHERE ship_type_id = 60;
UPDATE ship_type_support_hardware SET max_amount = 600  WHERE ship_type_id = 60 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 700 WHERE ship_type_id = 60 AND hardware_type_id = 2;

-- Assault Craft HP to 6, Shields to 950, Armour to 950, Drones to 0, Scouts to 0, Mines to 0, cost to 12789862
UPDATE ship_type SET cost=12789862, hardpoint=6 WHERE ship_type_id = 61;
UPDATE ship_type_support_hardware SET max_amount = 950  WHERE ship_type_id = 61 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 950 WHERE ship_type_id = 61 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 61 AND hardware_type_id = 4;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 61 AND hardware_type_id = 5;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 61 AND hardware_type_id = 6;

-- Negotiator HP to 1, Cargo Holds to 40
UPDATE ship_type SET hardpoint=1 WHERE ship_type_id = 63;
UPDATE ship_type_support_hardware SET max_amount = 40  WHERE ship_type_id = 63 AND hardware_type_id = 3;

-- Resistance speed to 8, HP to 2, Scouts to 10, cost to 1156265
UPDATE ship_type SET speed=8, cost=1156265, hardpoint=2 WHERE ship_type_id = 64;
UPDATE ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 64 AND hardware_type_id = 5;

-- Rogue HP to 2, Shields to 425, Armour to 550, cost to 2074860
UPDATE ship_type SET cost=2074860, hardpoint=2 WHERE ship_type_id = 65;
UPDATE ship_type_support_hardware SET max_amount = 425 WHERE ship_type_id = 65 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 550 WHERE ship_type_id = 65 AND hardware_type_id = 2;

-- Blockade Runner speed to 9, HP to 3, Cargo Holds to 175
UPDATE ship_type SET speed=9, hardpoint=3 WHERE ship_type_id = 66;
UPDATE ship_type_support_hardware SET max_amount = 175 WHERE ship_type_id = 66 AND hardware_type_id = 3;

-- Dark Mirage HP to 6, Shields to 825, Armour to 825, Scouts to 0, cost to 10088764
UPDATE ship_type SET cost=10088764, hardpoint=6 WHERE ship_type_id = 67;
UPDATE ship_type_support_hardware SET max_amount = 825 WHERE ship_type_id = 67 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 825 WHERE ship_type_id = 67 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 67 AND hardware_type_id = 5;

-- Retaliation HP to 2, Shields to 150, Armour to 200, cost to 435900
UPDATE ship_type SET cost=435900, hardpoint=2 WHERE ship_type_id = 71;
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 71 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 71 AND hardware_type_id = 2;

-- Vengeance speed to 8, Cargo Holds to 170
UPDATE ship_type SET speed=8 WHERE ship_type_id = 72;
UPDATE ship_type_support_hardware SET max_amount = 170 WHERE ship_type_id = 72 AND hardware_type_id = 3;

-- Retribution speed to 9, HP to 4, Shields to 300, Armour to 300, Drones to 50, cost to 3535800
UPDATE ship_type SET speed=9, cost=3535800, hardpoint=4 WHERE ship_type_id = 73;
UPDATE ship_type_support_hardware SET max_amount = 300 WHERE ship_type_id = 73 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 300 WHERE ship_type_id = 73 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 50 WHERE ship_type_id = 73 AND hardware_type_id = 4;

-- Vindicator HP to 6, Shields to 425, Armour to 400, Drones to 25, cost to 7600000
UPDATE ship_type SET cost=7600000, hardpoint=6 WHERE ship_type_id = 74;
UPDATE ship_type_support_hardware SET max_amount = 425 WHERE ship_type_id = 74 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 400 WHERE ship_type_id = 74 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 25 WHERE ship_type_id = 74 AND hardware_type_id = 4;

-- Fury HP to 7, Shields to 875, cost to 14724001
UPDATE ship_type SET cost=14724001, hardpoint=7 WHERE ship_type_id = 75;
UPDATE ship_type_support_hardware SET max_amount = 875 WHERE ship_type_id = 75 AND hardware_type_id = 1;
