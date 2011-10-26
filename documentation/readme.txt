Coding Guidelines
*****************
written by Michael Kunze
last updated 04/28/2002

Table of Content
----------------

1.	Introduction
2.	Writing Code for SMR
	2.1. General
	2.2. Code Layout
	2.3. Using HTML links
	2.4. Using HTML forms
	2.5. Using SMR submenus
	2.6. Functions to use
	2.7. What to do in ERROR case?


1.	Introduction
================

	This document describes how to write code for SMR. It is advised to follow these guide in each
	way! Otherwise your script can't be used!

2.	Writing Code for SMR
========================

2.1. General
------------


2.2. Code Layout
----------------

	This section descibes how the code has to be look like.
	The opening brace '{' has to be on the same line as the last command. The next lines has
	to be indented by one tabluator (which equals 4 spaces) To minimize loading times always
	use tabulator characters instead of it equal spaces.
	the closing bracket has to be reidented. The block have to be surrounded by one
	empty line (before and after). So typical code would look like:

		if {!empty($action)) {

			doSomtething1();
			doSomtething2();

		}

	If the is only one line leave out the brackets and the empty lines!

		if {!empty($action))
			doSomtething();

	For HTML form elements always use the css class InputFields

		$PHP_OUTPUT.=('<input id="InputFields">');

	Each line that is within a new code block have to be indented by one tabulator!
	It has to equal 4 spaces!


2.3. Using HTML links
---------------------

	Never use the <a href=''></a> to create a link in SMR!
	SEARCH YOUR FILES FOR 'HREF'! If you get a result you made something wrong and didn't
	follow this guide!
	I've created a functionality to transport information from one page to another pager
	saftly through database. To use this functionality you must proceed the following
	guideline. To transport any variables between pages you must create a container object.
	This object is an error that must have at least one element with the URL of the calling
	page. First of all you have to create a new container array. NEVER reuse an old container
	because you never know which information are in that. So the first line would be:

		$container = array();

	This gives you a fresh new array called 'container'.
	Now assign the URL:

		$container['url'] = 'skeleton.php';

	For most pages you have to define the 'body' page. That is the page that will be displayed
	between the menu and the user information. You don't have to care about that.

		$container['body'] = 'game_play.php';

	If you need to transfer any other paramters use

		$container['parameter_name'] = 'parameter_value';

	On the next page (game_play.php) you would have access to a variable called $var. To echo
	the value of this parameter use:

		$PHP_OUTPUT.=($var['parameter_name']);

	You have created the link. It is an object in the memory. To actually echo the link
	into the page you must use the this:

		$PHP_OUTPUT.=create_link($container, 'Play Game');

	The first parameter defines the container we created above. The next parameter defines the
	name of the link that will be echoed on the page. You can provide any text here you need.
	Even images

		$PHP_OUTPUT.=create_link($container, '<img src="game_play.gif">');

	This echos an image to the page. If the user echos this image he will be forwarded to
	game_play.php
	If you don't need any parameters on the next page you can use a much simplier version.

		$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'game_play.php'),
									'Play Game');

	create_container() is a function that always takes two parameters. the url and the body page
	and it returns an array that can be provided as the first parameeter to $PHP_OUTPUT.=create_link()

	In some cases you don't want to echo the link into the page instead just creating it.
	You can use the following syntax

		$link = create_link(create_container('skeleton.php', 'game_play.php'),
											 'Play Game');
		$PHP_OUTPUT.=($link);

	You can see that create_link() simply returns the HTML expression of the link. By the way:
	$PHP_OUTPUT.=create_link() does exactly the above two lines. it creates the link (using create_link()) and
	echos it immediatly.


2.4. Using HTML forms
---------------------

	There is a simular rule for forms. Never transfer any parameters via hidden fields or by
	extending the form url! This is not safe and can be modified by the user.
	First you have to create a container object first with url.

		$container = array();
		$container['url'] = 'message_send_processing.php';

	Put parameters you probably need into the container:

		$container['receiver_id'] = $receiver_id;

	To echo the form use the following lines:

		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=('<textarea name="message" id="InputFields"></textarea><br />');
		$PHP_OUTPUT.=create_submit('Send message');
		$PHP_OUTPUT.=('</form>');

	As you can see the is a method $PHP_OUTPUT.=create_echo_form() that takes a container object. Never forget
	to manually close the form! (See last line)
	For submit fields use the function $PHP_OUTPUT.=create_submit();
	Of course you can use the simplier method here too if you DON'T have any other paramaters
	beside url and body.
	Without transfering the receiver_id in the above example you would use this:

		$PHP_OUTPUT.=create_echo_form(create_container('message_send_processing.php', ''));
		$PHP_OUTPUT.=('<textarea name="message" id="InputFields"></textarea><br />');
		$PHP_OUTPUT.=create_submit('Send message');
		$PHP_OUTPUT.=('</form>');

	The body is empty in the above example. This is valid! There is always an URL needed.
	If the URL equals 'skeleton.php' you have to provide the second parameter as body page.


2.5. Using SMR submenus
------------------------

	First of all u have to create an array that conatins all the links.

		$menu_items = array();

	Create links like described above and push it into the menu_item array

		$menu_items[] = create_link(create_container('skeleton.php', 'game_play.php'),
													  'Play Game');
		$menu_items[] = create_link(create_container('skeleton.php', 'logoff.php'),
													  'Logoff');

	Finally echo the menu into the page

		echo_menu($menu_items);


2.6. Functions to use
---------------------

	This section describes wich functions you should use instead of writing your ow stuff.
	Most of them are only accessible when calling inside of the skeleton.php file.

	To echo topics on the pages always use (skeleton.php only)

		$template->assign('PageTopic','My Topic');

	To echo a form and link always use (skeleton.php only)

		$PHP_OUTPUT.=create_echo_form($conatiner);
		$PHP_OUTPUT.=create_link($conatiner);

	To echo a submit button use (skeleton.php only)

		$PHP_OUTPUT.=create_submit('Create');

	To forward the user to a new page (mostly in processing scripts) use the forward method
	See the above text for description of container object (you can use create_container() here too!)

		forward($container);

	If you have to take turns from a user use the $player->takeTurns(); It also deduct newbie turns!

		$player->takeTurns(3);

	Don't forget to $player->update() after that to make it permanent.
	If you have to safe a VARCHAR string to the database you have to look for ' in the string you
	want to save. This apostroph causes troubles to database while updating tables. Consider using
	the function formatString() from loader.php script. It can AND should be used everywhere.

		$db->query('UPDATE table SET column = ' . formatString($my_column));

	It transfers the string into something that the database can understand. You don't need
	the leading and trailing slashes around the string (will be added automatically)



2.7. What to do in ERROR case?
------------------------------

	In case you have to present the user an error page you must consider what to do.
	If you are in a processing script (that doesn't have any output at all)
	you have to use setError() like

		if ($player->getNewbieTurns() > 0)
			setError('You are under newbie protection!');

	If you are within a script that is loaded from the skeleton.php page
	you must use create_error AND return from current script

		if ($player->getNewbieTurns() > 0) {

	        create_error('You are under newbie protection!');
	        return;

		}

	Always think about using which one is the correct one! There are magnificent differences!