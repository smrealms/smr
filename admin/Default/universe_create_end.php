	<?php

$PHP_OUTPUT.=('<h1>CREATE UNIVERSE - FINISH</h1>');

$PHP_OUTPUT.=('<p>Congratulations!</p>');
$PHP_OUTPUT.=('<p>You successfully created a universe with all the stuff in it.<br />It still needs to be approved by MrSpock (noone will be able to join before that)</p>');
$game_id = $var['game_id'];
$db->query('REPLACE INTO alliance (alliance_id, game_id, alliance_name, alliance_description, alliance_password, leader_id, `mod`) VALUES ' .
			'(1,'.$game_id.',\'Game Admins\',\'One Alliance to Rule Them All\',\'*\',1,\'We are the Admins!\')');
			
// Newbie Help Alliance

$db->query('REPLACE INTO alliance (alliance_id, game_id, alliance_name, alliance_description, alliance_password, leader_id, `mod`) VALUES ' .
			'(302,'.$game_id.',\'Newbie Help Alliance\',\'Newbie Help Alliance\',\'*\','.ACCOUNT_ID_NHL.',\'Alliance message board includes tips and FAQs.\')');
$withPerDay = ALLIANCE_BANK_UNLIMITED;
$removeMember = TRUE;
$changePass = TRUE;
$changeMOD = TRUE;
$changeRoles = TRUE;
$planetAccess = TRUE;
$exemptWith = TRUE;
$mbMessages = TRUE;
$sendAllMsg = TRUE;
$db->query('REPLACE INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg) ' .
			'VALUES (302, '.$game_id.', 1, \'Leader\', '.$withPerDay.', '.$db->escapeString($removeMember).', '.$db->escapeString($changePass).', '.$db->escapeString($changeMOD).', '.$db->escapeString($changeRoles).', '.$db->escapeString($planetAccess).', '.$db->escapeString($exemptWith).', '.$db->escapeString($mbMessages).', '.$db->escapeString($sendAllMsg).')');
$withPerDay = ALLIANCE_BANK_UNLIMITED;
$removeMember = FALSE;
$changePass = FALSE;
$changeMOD = FALSE;
$changeRoles = FALSE;
$planetAccess = TRUE;
$exemptWith = FALSE;
$mbMessages = FALSE;
$sendAllMsg = FALSE;
$db->query('REPLACE INTO alliance_has_roles (alliance_id, game_id, role_id, role, with_per_day, remove_member, change_pass, change_mod, change_roles, planet_access, exempt_with, mb_messages, send_alliance_msg) ' .
			'VALUES (302, '.$game_id.', 2, \'New Member\', '.$withPerDay.', '.$db->escapeString($removeMember).', '.$db->escapeString($changePass).', '.$db->escapeString($changeMOD).', '.$db->escapeString($changeRoles).', '.$db->escapeString($planetAccess).', '.$db->escapeString($exemptWith).', '.$db->escapeString($mbMessages).', '.$db->escapeString($sendAllMsg).')');
$db->query('REPLACE INTO player_has_alliance_role (game_id, account_id, role_id, alliance_id) VALUES ('.$game_id.', '.ACCOUNT_ID_NHL.', 1,302)');

// NHA default topics

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 1, \'Read this first!\')');
$text = 'This alliance message board contains pretty much everything you need to know to get going in the game, and hopefully learn some of the skills you will need to do well and stay alive. Here are some basic tips that you should start thinking about right from the start, all of which are dealt with in more detail elsewhere on the message board:<br />
<br />
1) When you log off, always make sure you are safe.<br />
2) Don\\\'t raid ports.<br />
3) Do not create more than one account.<br />
4) Talk to other players ingame or in IRC chat.<br />
5) Stay in the racial galaxies as much as possible.<br />
6) Learn to use Merchant\\\'s Guide to the Universe.<br />
7) Contact Newbie Help Leader for help, advice or with any questions you have.<br />
<br />
8) Most of all - have fun out there!';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 1, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 2, \'What the Newbie Help Alliance (NHA) can do for you\')');
$text = 'There are a number of ways this alliance can help you as a new player, and you are welcome to make use of as many or as few of these as you wish:<br />
<br />
1) Access to one or more experienced veteran players who will give you accurate and unbiased advice. Most players in SMR will help you if you need it, but the veterans in this alliance are here specifically to help you.<br />
<br />
2) An extensive alliance message board with all the basics you need to know. Alliance members are encouraged to use the message board to ask their own questions - if you are wondering about something, chances are that other new players would like to know the answer too.<br />
<br />
3) Maps. A short time into every game, this alliance will usually have full maps of the game universe. However, keep in mind that maps will change slightly during the course of a game as ports get upgraded and busted down.<br />
<br />
4) If you stay in NHA, stay active, try to learn, and keep in touch with the Newbie Help Leader then this alliance can be a stepping stone to the more established alliances. Every game I get alliance leaders asking me to recommend newbies who are active and have potential.<br />
<br />
5) Newbie Help Leader will work with each of you individually on specific questions and game goals if you want. If resources allow it (I am dependent on my own trading income for cash) I try to reward players for achieving the game goals they work towards. I also try to make sure that members have at least a mid-level tradeship after they have been killed, although I try to make sure that they have learned from their mistakes before buying replacement ships (to be fair to the rest of the players in the game, you can no longer get cash or new ships after reaching fledgling status, although you are welcome to stay in the alliance as long as you like).';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 2, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 3, \'Turns\')');
$text = 'Many of the basic actions performed in SMR cost turns, the most common examples being moving, trading &amp; attacking. One of the keys to success in the game is good turn management, no matter what you are busing the turns to accomplish. If you are a trader, you want to get as much cash and xp as possible per turn used. If you are a hunter, you want to spend as many turns as possible efficiently locating targets and getting kills, and as few as possible chasing traders around without getting the final trigger shot off. In an alliance, you will often be expected to save turns for op\\\'s, where it is often crucial to have plenty of alliance members show up with plenty of turns.<br />
<br />
Turns are accumulated constantly, whether you are logged in or not, and are a product of ship speed and game speed. When you click the Trader link on the left of your screen, one of the things you will see is how many turns you get per hour in the ship you are in, and also the maximum number of turns you can accumulate in the current game. It is important to manage your turns carefully - make sure to always leave yourself enough turns to get back to somewhere you can park safely (preferably with some to spare in case you run into trouble on the way). It can sometimes be a good idea to save up a large number of turns so you can use them all in one session, but be aware that this is not always possible or even ideal. You should try to avoid reaching the maximum number of turns, since you will then stop getting more and will basically be wasting the turns you would usually have accumulated.<br />
<br />
A good way to get a few extra turns and help the game at the same time is to click on the voting links at the bottom of your screen. Voting for SMR helps our rankings, which brings more players to the game - making it better for all of us.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 3, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 4, \'How to trade\')');
$text = 'In SMR, trading is the easiest and most fundamental way to accumulate both cash and experience points. The basic principles of trading are easy, but getting really good at it takes time and experience.<br />
<br />
The basic idea of trading is that you buy goods from a port that sells them, transport them to a reciprocal port that buys them, and sell them there for a profit. If you make a good trade, you will also accumulate experience points for the trades. Reciprocal ports for a trade good are ones which can be matched up to create a trade route; for example if a port sells wood then all ports that buy wood are reciprocal ports and can potentially be used to create a "wood" traderoute. A good traderoute is one which can be traded in both directions (different goods of course), and one which makes good profits and/or experience. More on that later.<br />
<br />
There are 12 tradegoods on the game, divided into low mid and high level goods. Three of the tradegoods are so-called illegals and can only be traded if your alignment in -100 or lower. The low level goods are wood, food, ore, precious metals and slaves (illegal); the mid levels are machinery, textiles, circuitry and weapons (illegal); and the high level goods are computers, luxury items and narcotics (illegal). Generally speaking, higher level goods are more rare, and will lead to better profits and often more experience points.<br />
<br />
The value and efficiency of a traderoute between 2 (or more) ports is determined by the multipliers for the goods traded, where the multipliers are determined by the distance to the nearest reciprocal port (note that the port you are actually using is irrelevant, it is the nearest port that counts). For example, if a port is buying computers from me and the nearest port selling computers is 3 sectors away then the multiplier for selling those comps is 3x. The multiplier determines the profit you will make and the maximum experience you can gain, the higher the multiplier, the better the trade. <br />
<br />
The experience you get from a trade depends on your relations with that race, and on how good a deal you make. With perfect relations (1000) you will automatically be offered the best possible deal by a port and will not need to bargain at all. At less than 1000 relations, you get experience depending on how well you bargain, where bargaining means offering a little less than the port asks for when you are buying and asking for a little more than the port is offering when you are selling. relations go up with successful trades, and they go down when a port refuses the bargain you offer them. If you are trading with your own race, it is often a good idea not to worry too much about making the best bargains at first and try to get maximum relations as quickly as possible.<br />
<br />
One of the major contributing factors to how well you can trade is the ship you are using. Profit and experience depend on the number of goods you are able to trade which depends in turn on the number of cargo holds you have and the speed of your ship. A general guide to how efficient a tradeship is is to multiply the number of holds by the ship speed to give you the trade potential. It is important to note, however, that trade potential is not the only important factor in choosing a tradeship. For example, the planetary super freighter is a very efficient trader with good defenses but is very slow and therefore very inefficient if you have to travel a lot between traderoutes and parking spots; whereas the Interstellar trader has pretty good trade potential and a jump drive to allow you to travel easily but has very weak defenses and is easily killed.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 4, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 5, \'Safe trading\')');
$text = 'This topic is extensive, and there is no substitute for playing the game, probably dying a few times, and learning through experience. However, it doesn\\\'t hurt to keep a few tips and tricks in mind:<br />
<br />
1) Choose your route. The best routes are also likely to be the least safe, since that is where most hunters will look for their targets. It is sometimes better to trade a slightly less lucrative route and stay alive, especially if you know there are hunters online in the area or you have seen a lot of deaths in the news on the better routes.<br />
<br />
2) Vary your trading. Good hunters will look for trading patterns and use them to catch their prey. Try to vary your trading times and your traderoutes to stay unpredictable.<br />
<br />
3) Use forces. If you have a ship that is force capable (i.e. a ship that can carry scouts, drones and/or mines), you should always use them when you trade. Ideally, you should have a scout+mine combo in your port sectors, and sometimes also other scouts and mines strategically placed around and along your route. The advantage of the scout+mine combo is that it warns you when someone enters your sector, plus it makes intruders load an extra screen before they can get to target you. If you see a scout message icon (top right of screen) you should immediately hit current sector, immediately run if you see a ship that was not there before (do not stop to examine it), run a few sectors to get clear, then get to safety if necessary.<br />
<br />
Always pick up your forces when you are done trading, and use just scouts or just mines if your ship can only carry those.<br />
<br />
4) Watch the CPL. Before and during trading, kepp an eye on the current player list and the news, and look for hunters who you think may come for you. It is sometimes better to ;eave trading for another time, but at least stay alive.<br />
<br />
5) Use local map. It can sometimes be useful to enter your port sectors using local map, especially if you suspect there may be trouble close by. This allows you to see ships sitting in neighbouring sectors waiting to ambush you, but it only works if you have a scanner.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 5, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 6, \'Get a scanner\')');
$text = ' 	If your ship can equip with a scanner, then get one and learn how to use it. Scanners are very useful for moving around safely and for gathering information about local sectors. Some examples:<br />
<br />
Scanners can warn you of nearby ships on local map (see safe trading thread).<br />
<br />
Scanners can warn you of cloaked ships. Scanning a sector (from a neighbouring sector) will give you a reading on the number of ships there. Enemy ships scan as their defensive value x 10. For example, if you scan a sector and get a reading of 300, then enter and see only a planetary super freighter there (def value of 15) then you know there is a 150 scan unaccounted for, which means a cloaked ship. At this point, you will probably want to get out of there fast! (Note that ship scans can sometimes also be off due to Illusion generator ships pretending to be something they are not).<br />
<br />
Scanners will give you force readings. Scanning a neighbouring sector will tell you the total number of forces in the sector (scouts scan as 1, drones as 2, mines as 3). This allows you to avoid sectors with heavy force scans (which usually means a lot of mines) when navigating.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 6, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 7, \'Logging off safely\')');
$text = 'Before logging off, it is very important to ALWAYS check that your are as safe as you can be. Until you join a major alliance, you should all be parking safely in federally protected space every time you log off. Before leaving SMR, ALWAYS check your main screen protection message and/or click the "Trader" link to make sure you are in fact protected.<br />
<br />
The two things that can prevent you from having federal protection are carrying illegal goods (slaves, weapons or narcotics) or having an attack rating that is too high. At neutral alignment, that is +/-149, you can park safely with an attack rating of 3 or less. The attack rating you can park with increases with increasing alignment and decreases with decreasing alignment.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 7, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 8, \'Merchant\\\'s Guide to the Universe\')');
$text = 'MGU, as everyone calls it, is an extremely valuable tool to use alongside SMR. Details on how to get the software (as well as instructions and discussions) can be found on the SMR Webboard at http://smrcnn.smrealms.de/viewforum.php?f=32<br />
<br />
Basically, after you have installed MGU, you need to download your game maps using the link on the left side of the SMR page, and save them into your MGU directory. From MGU, you can then open the game maps and access the map information to do may useful things. MGU functions include things like finding traderoutes (listed by experience or cash), finding locations, plotting arming routes, finding safe course plots, etc.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 8, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');
	
// remove newbie gals
//$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 9, \'Racial galaxies\')');
//$text = 'As long as you are ranked newbie or beginner, players ranked fledgling or above cannot see you in the racial galaxies. This makes these galaxies considerably safer for you, and I recommend avoiding too much time spent outside them.';
//$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 9, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 10, \'Respect for other players\')');
$text = 'SMR has a very active and social player community, and one thing that will get noticed is how you treat your fellow players. If you send polite messages to other players asking for advice or just to make some contacts, they\'ll remember your name, maybe mention you to their teammates, and you have taken the first step to becoming a respected player. However, if you, for example, react to being killed (it happens to everyone, get used to it) by sending an abusive and angry message then you will quickly lose the respect of your fellow players.<br />
<br />
If you do get killed, it can be useful to politely message your killer and ask what you did wrong - most veteran players will be more than happy to advise you on how to avoid the same thing happening again, plus it shows them that you want to learn how to improve at the game. If you get a kill, it is considered impolite to send messages gloating over the fact, although some veteran players will sometimes message newbies they have killed with offers of cash to get them back on their feet or advice on how to avoid the same thing happening again.<br />
<br />
There are all kinds of ways to play and enjoy this game, different players choose to emphasize different aspects of gameplay - but whatever choices you make, the bottom line is that if you treat your fellow players (both allies and enemies) with respect, then they will respect you.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 10, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');
	
$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 11, \'Talk to the players\')');
$text = 'SMR has a very active community, and it is always a good idea to talk to the other players. You can do this in the #smr chatroom, or by messaging them ingame. Most players will be happy to talk to you or help you if you send them polite messages.<br />
<br />
It is also a good idea to talk to veteran players, especially alliance leaders, about their alliances and what they look for in their team members. You probably won\\\'t be asked to join a major alliance right away, but many of them have training alliances and they are always looking for active players who are willing to learn and contribute to an alliance.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 11, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 12, \'The Webboard\')');
$text = 'The SMR webboard (often referred to simply as the WB) is full of all kinds of advice and discussions, and I recommend stopping by to take a look every so often. It will all seem a bit much at first, but start with the sections that seem most useful to you and you will quickly learn to recognize what is important and what is not.<br />
<br />
You should also contribute to the webboard if you have an opinion or something you feel needs discussed, but please use the search function before starting new topics to make sure you are not repeating what someone else has posted somewhere else.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 12, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');



	
$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, '.ACCOUNT_ID_NHL.', \'Alignment\')');
$text = ' 	Alignment has a couple of functions, the main ones being for trading purposes and determining which restricted ships and weapons you can buy.<br />
<br />
If you are evil (alignment -100 or lower) you can trade evil goods, buy underground ships (Thief, Assasin, Death Cruiser which are all cloaked) and buy the underground level 5 weapon (nuke). If you are neutral (between -99 and 99 alignment) you can become evil by signing up as a gang member at Underground HQ. Evil players cannot enter federal (racial) HQ\\\'s.<br />
<br />
If you are good (alignment 100 or higher) you can buy federal ships (Federal Discovery, Warrant and Ultimatum which all have jump drive and take half damage from forces) and buy the federal level 5 weapon (holy hand grenade). If you are neutral (between -99 and 99 alignment) you can become good by deputizing at any racial HQ. Good players cannot enter the underground HQ.<br />
<br />
Alignment also affects the attack ratings you can have and still be federally protected in fed space. At neutral alignment you can park with an attack rating of 3, and the protected rating goes up 1 for every +150 alignment and down 1 for every -150 alignment. You are always protected with an attack rating of zero.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, '.ACCOUNT_ID_NHL.', 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 14, \'Watching the news and CPL\')');
$text = 'Whether you are a trader or a hunter, it is very valuable to know as much as possible about who is currently active in the game, and where they might be. Two of the resources you need to learn to use, but also know the limitations of, are the news and the current player list (CPL).<br />
<br />
Reading the news before you trade can be vlauable in letting you know which hunters are currently active in the game, even if they don\\\'t show on the CPL. If there has been a recent kill near your traderoute, or a hunter that knows where you like to trade has recen tly been active, it is often a good idea to wait and trade another time.<br />
<br />
The CPL will let you know who has recently accessed the database, and also how many players are "lurking" (logged into the game, but not moving). It is a good idea to check the CPL for hunters you believe are a threat to you before you trade, and also every so often while you trade (especially if you are trading over a scout drone).';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 14, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 15, \'IRC chat\')');
$text = ' 	It is very helpful to learn to use IRC chat often while playing this game. The first step is to simply use the IRC chat link on the left of your screen and just spend some time in the #smr room getting to know some of the players. This is also a good time to ask questions - not just about technical or tactical aspects of the game, but about what the players like about the game, about the alliances, about pretty much anything. Getting to know the players and the community is part of getting to know SMR.<br />
<br />
When you end up in an alliance, playing with a team, you will find that they pretty much all use dedicated alliance chatrooms that they use for conducting alliance operations. These rooms are also a place to simplky hang out and chat, which is a great way to get to know your teammates.<br />
<br />
In time, if you are able to do so, it is useful to install an IRC client on the computer(s) you use. mIRC is free and works well. If you have trouble getting your IRC client to work, there are instructions and help to be found on the webboard.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 15, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 16, \'Avoiding mines\')');
$text = 'Enemy mines are something you will encounter on a regular basis in SMR, and there are a couple of things that can help prevent you dying to them.<br />
<br />
1) If you hit a mined sector, you always have a safe way out. Going back the way you came to your green highlighted sector will spare you from hitting any more mines.<br />
<br />
2) Use a scanner. If you are working in an area where there are mines and trying to avoid them or work around them, scanning potential sectors to move to will give you an idea of how heavily mined they are - a higher force scan usually means more mines.<br />
<br />
3) Work from UNO. If you are travelling a long way, it is often a very good idea not to plot your route directly from A to B, but to plan it so that you pass UNO shops along the way - this will allow you to refill your defenses as you go if you take some damage. This is especially important when travelling through large neutral galaxies.<br />
<br />
4) Use your maps. If you encounter heavily mined sectors, retreat to safety, then take the time to look carefully at your maps to see if the area you were in is close to something an alliance would want to protect (usually a galaxy warp or a Combat Accessories shop). Then plan a new route avoinding this area.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 16, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 17, \'Port raiding\')');
$text = 'It\\\'s simple - don\\\'t do it! Raiding small ports will gain you nothing and will often get you killed, and raiding big ports is impossible without a well-armed fleet of warships.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 17, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 18, \'Operations guide\')');
$text = ' 	http://smrcnn.smrealms.de/viewtopic.php?t=3922<br />
<br />
Once you graduate to alliances that are more active in the bigger picture of a game, you will want to take part in alliance operations. SMR op\\\'s come in all kinds of shapes &amp; sizes and flavours, and involve things like territory wars, planet busts, port raids and fleet battles.<br />
<br />
The link I posted here is a rough guide to what you might expect, and what will be expected of you, in alliance op\\\'s.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 18, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 19, \'Multiple accounts\')');
$text = 'Multiple accounts are NOT permitted in SMR. The game has admins who actively look for multiple accounts and suspicious activity. If you are caught playing more than one account, you risk losing all your accumulated stats and being banned from the game.<br />
<br />
One of the results of this strict policy on multiple accounts is that you should try to avoid logging into your account from any computer also used by other SMR players. Even though you are not actually playing more than one account, it can seem as though you are when the connection logs are checked.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 19, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 20, \'Moving on to a new alliance\')');
$text = 'When you feel you are ready to move on from this alliance to a new one, it is a good idea to take the time to talk to the alliance leaders out there, When you do, don\\\'t be afraid to talk to the major alliances as well as the small ones - they probably won\\\'t offer you a spot right away, but they can give good advive and it also puts your name on their radar for future games.<br />
<br />
There are a number of things it is a good idea to ask alliance leaders about, to give you an idea what they look for in a member and (more importantly) to help you decide what kind of alliance you want to join.<br />
<br />
1) What are the goals of the alliance - both in the current game, and in the future?<br />
2) What does the alliance expect from its team members?<br />
3) What kind of resources does the alliance offer its members (resources can be planets to park on, cash reserves, an active chatroom, advice or teaching from veteran players, etc).<br />
4) What aspects of the game does the alliance emphasize (territory wars? hunting? trading?) and how active are they?<br />
5) Since you are still learning, what can you expect to learn from flying with a given alliance.<br />
<br />
There is no right time to leave this alliance and join a new one, but I recommend that you do not jump hastily or blindly into a random small alliance. Apart from anything else, some of the small ones are made up of new players who have as little game knowledge as you and are not doing anything remotely organized or coordinated.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 20, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');
		
$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 21, \'Experience\')');
$text = 'There are many ways to play SMR, and many different aspects of the game to enjoy - but experience points are important whether you play with a high ranking as a goal in itself, or whether you see it as just another tool to help achieve other goals.<br />
<br />
Experience (xp) is gained through both trading and combat, but trading is by far the most effective and efficient way to accumulate experience (especially early in a game). Refer to the trading section on this message board for details, but the best way to get lots of experience fast is to trade long routes with perfect trading relations. This is not always possible, and often dangerous (and you lose significant amounts of xp when you die), so it is often a good idea to look for decent traderoutes but stay off the best ones.<br />
<br />
The main benefits of experience are:<br />
1) Higher weapon accuracy when you fire, and lower weapon accuracy for your opponents.<br />
2) The ability to cloak from lower ranked players.<br />
3) Demonstrating the ability to accumulate and keep a good experience level. This last benefit is especially important for new players since climbing the rankings and avoiding too many deaths will get you noticed.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 21, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 22, \'Ships\')');
$text = 'Everything you do in SMR is done while you are flying some kind of ship - it can be anything from an escape pod to a huge IkThorne mothership, but you are always the pilot of something. In addition to the neutral ships, each race also has unique ships that only race members can purchase. You can find the SMR shiplist here:<br />	
<a href="http://www.smrealms.de/ship_list.php target="_blank">http://www.smrealms.de/ship_list.php</a> <br />
Additional information relating to ships can be found in the <a href="http://wiki.smrealms.de/" target="_blank">SMR wiki</a><br />
<br />
It is important to choose ships to match your budget and your needs, and the biggest or most expensive ships are not always the best ones for all purposes.<br />
<br />
Make sure you are buying the right ship before you hand over your cash to the ship dealer since you only get a partial refund for the ship you are selling, which means that you lose some money every time you switch ships.<br />
<br />
Ships fall into a few basic categories, and I will try to give you a basic overview here - but I encourage everyone to look at the shiplist themselves, experiment with different ships, and talk to experienced players about what they recommend buying.<br />
<br />
Trade ships: This class of ships emphasizes trading capacity over fighting ability. They generally tend to be relatively slow, have good cargo capacity, very limited combat ability, and varying defense. These ships are basically only used for trading, and should run from pretty much any fight.<br />
<br />
Warships: These are the largest and most heavily armed ships in the game, and are ideal for combat situations where you expect someone (other ships) or something (ports and planets) to be firing back at you. This class of ships inculdes the racial warships and the Federal Ultimatum.<br />
<br />
Hunting ships: These combat vessels are slightly smaller than the warships but are usually faster and have the added ability to carry scout drones for gathering information and tracking other ships. Hunters are useful for killing tradeships or other hunters, but should generally avoid combat with warships.<br />
<br />
In addition to these basic classes, many ships are especially good at specific things like clearing mines, transporting forces or exploring the universe.<br />
<br />
Some of the ship concepts you should be familiar with:<br />
- Cost. Unless you have a lot of spare cash, buy a ship to suit your budget. The most expensive ship is not always the best choice, especially if buying it leaves you with no cash reserves.<br />
- Defense. Ship defense is made up of shields, armour and combat drones. The higher the defense, the harder you are to kill so more is better.<br />
- Offensive capability. Ships attack (and defend) using weapons and combat drones. Weapons are mounted on the limited number of hardpoints (HP) a ship has, meaning that more HPs give a higher offensive capability.<br />
- Speed. Ship speed determines how fast your ship will accumulate turns. Faster ships gain more turns per hour and vice versa.<br />
- Hardware. Most ships can use one or more hardware items, each of which adds certain capabilities to the ship. These are Scanners (see scanner thread), Cloaks (invisibility from lower ranked traders when activated), Illusion Generators (the ability to disguise your ship), Jump Drives (allows you to jump from place to place without travelling through the sectors between), and Drone Scramblers (improved defense against combat drones).<br />
- Restricted ships. There are restrictions on who can buy certain ships. These include racial restrictions, top racial restrictions (the top racial ships cannot be purchased until you reach fledgling status), and alignment restrictions (Underground and Federal ships require you to be evil or good respectively, Federal ships also take half damage from forces).';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 22, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 23, \'Weapons\')');
$text = 'SMR has a wide variety of weapons with which to arm your ships, and information on weapon capabilities can be found here:<br />
<br />
<a href="http://www.smrealms.de/weapon_list.php">http://www.smrealms.de/weapon_list.php</a><br />>
<br />
There are a few things to consider when choosing weapons, and the weapon combination that works for one player may not be suited to the style or the needs of another.<br />
<br />
- Cost. If your resources are limited (especially if you need to sell your weapons each time you log off) then buying the expensive big guns is not always the best choice. If you shop around, you can often get decent firepower relatively cheaply.<br />
- Damage type. Weapon damage is classified by armour/shield damage, and a good weapon setup generally has these pretty evenly balanced. However, be aware that it is sometimes preferable to load up on weapons with a specific damage type in specific circumstances (for example, forces are killed by armour damage weapons so these are preferable for mineclearing).<br />
- Accuracy. Weapon accuracy (modified by trader level) determines how likely it is that your weapon will hit your target on any shot. In ship to ship combat, higher ranked traders get accuracy benefits over their lower ranked opponents. There is (and always has been since the dawn of this game) an ongoing debate about when it is better to use either high accuracy low damage weapons or low accuracy high damage weapons.<br />
- Rating. There are restrictions on how many weapons of certain ratings you are allowed to arm yourself with. Weapon rating is determined by accuracy and damage, and higher is better. You are allowed up to one level 5 weapon, up to 2 level 4 weapons, and up to 3 level 3 weapons.<br />
- Availability.Weapon choice when arming is sometimes determined by the availability of certain weapons. Racial weapons are only available to the owner race or races that have peaceful relations with them; weapons may be sold at weapon shops that are not safely accessible; or the number of turns it would take to acquire all the weapons you would like may be too high.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 23, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');

$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 24, \'Planets and Terrritory\')');
$text = 'Except for your ship and what it carries, a planet is the only thing in SMR that you can claim for yourself or your alliance. This means that planets and planet galaxies are often the main focus of the rivalries and wars between alliances.<br />
<br />
At the beginning of every game planets are weak and planet galaxies are easily accessible, but as a game progresses planets become stronger (the strongest planets will regularly kill attacking warships even when being attacked by a full fleet) and some galaxies (alliance territory) become heavily fortified by minefields. Planets become parking spots for fully armed warships as well as being used to generate cash, and therefore become attractive targets for enemy alliances.<br />
<br />
Kill counts and experience are important measures of success, but many veteran players feel that the only true measure of success in alliance wars is the ability to hold and/or take territory from enemy alliances.<br />
<br />
It is important to note that weakly defended planets should NOT be considered safe parking spots.';
$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 24, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');
				
//$db->query('REPLACE INTO alliance_thread_topic (game_id, alliance_id, thread_id, topic) VALUES ('.$game_id.', 302, 1, 'Welcome to SMR')');
//$text = '';
//$db->query('REPLACE INTO alliance_thread (game_id, alliance_id, thread_id, reply_id, text, sender_id, time) VALUES('.$game_id.', 302, 1, 1, '.$db->escapeString($text).', '.ACCOUNT_ID_NHL.', '.TIME.')');
	
?>