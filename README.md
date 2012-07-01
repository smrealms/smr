# Installation

## Dependencies
These list the known dependencies, there may be more - please update if you find any!

### Core
* PHP 5.3+
* MySQL 5.1+

### PHP Extensions
* MySQL http://php.net/manual/en/book.mysql.php
* cURL (Facebook login): http://php.net/manual/en/book.curl.php
* JSON (Facebook login): http://php.net/manual/en/book.json.php
* runkit (NPCs): http://php.net/manual/en/book.runkit.php


## Config files
Currently it is required to create installation specific copies of the following files:

* htdocs/config.specific.sample.php -> htdocs/config.specific.php
* lib/Default/SmrSessionMySqlDatabase.class.sample.inc -> lib/Default/SmrSessionMySqlDatabase.class.inc
* lib/Default/SmrMySqlDatabase.class.sample.inc -> lib/Default/SmrMySqlDatabase.class.inc

For "Caretaker" functionality:
* tools/irc/config.specific.sample.php -> tools/irc/config.specific.php

For these files the sample version should provide good hints on what info is required, there are also other sample files but these are generally not required (read: only for supporting old 1.2 databases, you're unlikely to have one of those lying about ;) )


## Filesystem permissions
SMR requires write access to htdocs/upload, you will need to create this folder.

## Database
SMR is using [Flyway](http://code.google.com/p/flyway) to deploy database patches.

1. Download and untar Flyway
`wget http://flyway.googlecode.com/files/flyway-commandline-1.6.1-dist.tar.gz && tar -xvzf flyway-commandline-1.6.1-dist.tar.gz -C /opt`
2. Set the following options in /opt/flyway-commandline-1.6.1/conf/flyway.properties
`
flyway.driver=com.mysql.jdbc.Driver
flyway.url=jdbc:mysql://localhost/smr_live
flyway.user=smr
flyway.password=YOUR_DATABSE_PASSWORD`

3. Download the Java MySQL Connector from http://www.mysql.de/downloads/connector/j
4. Unzip it and put the jar into /opt/flyway-commandline-1.6.1/jars
5. Direct sql folder to SMR patches 
`cd /opt/flyway-commandline-1.6.1 && rm -Rf sql && ln -s <GIT_ROOT_PATH>/db/patches sql`
6. Initialize database `./flyway.sh init` which automatically creates the needed database tables and initializes the version
7. Run all patches `./flyway.sh migrate`

If you start with an existing database you need to follow above steps 1 to 5 and initialize the databse with the following command:
`./flyway.sh -initialVersion=1.6.39 init` which would mean that your current database equals the one from SMR 1.6.39. From this point on you can use `./flyway.sh migrate` to update to latest database.
After creating a user account I would recommend inserting a row into the permission table corresponding to the account you created and with a permission_id of 1 in order to give yourself admin permissions.

In case you need to change database with a new version put a file called `V<VERSION_NUMBER>__NAME.sql` into db/patches folder. One version can have multiple patches.


# Coding Style
This is the coding style that should be used for any new code, although currently not all code currently follows these guidelines (the guidelines are also likely to require extension).

* Opening races should be placed on the same line with a single space before
* Single line if statements should still include braces

	```php
	if (true) {
	}
	```

* Function names should be camelCase, class names should be UpperCamelCase

	```php
	function exampleFunction() {
	
	}
	
	class ExampleClass {
		public function exampleMethod() {
		}
	}
	```

* Associative array indices should be UpperCamelCase

	```php
	$container['SectorID'] = $sectorID;
	```

# SMR-isms
## File inclusion
To include a file use get_file_loc()

```php
require_once(get_file_loc('SmrAlliance.class.inc'));
```

## Links
If possible use a function from Globals or a relevant object to generate links (eg Globals::getCurrentSectorHREF(), $otherPlayer->getExamineTraderHREF()), this is usually clearer and allows hooking into the hotkey system.
To create a link you first create a "container" using the create_container() function from smr.inc declared as

```php
create_container($file, $body = '', array $extra = array(), $remainingPageLoads = null)
```
There are two common usages of this:
- $container = create_container('skeleton.php', $displayOnlyPage) with $displayOnlyPage being something such as 'current_sector.php'
- $container = create_container($processingPage) with $processingPage being something such as 'sector_move_processing.php'.

You can then call SmrSession::getNewHREF($container) to get a HREF which will load the given page or SmrSession::generateSN($container) to get just the sn.
Along with this you can also assign extra values to $container which will be available on the next page under $var

```php
$container = create_container('sector_move_processing.php');
$container['target_sector'] = 1;
$link = SmrSession::getNewHREF($container);
```

## Global variables
All pages are called with the following variables available (there may be more)

### $var
$var contains all information passed using the $container from the previous page.
This *can* be assigned to, but only using SmrSession::updateVar($name, $value)

### $account
For any page loaded whilst logged in this contains the current SmrAccount object and should not be assigned to.

### $player
For any page loaded whilst within a game this contains the current SmrPlayer object and should not be assigned to.

### $ship
For any page loaded whilst within a game this contains the current SmrShip object and should not be assigned to.


## Request variables
For any page which takes input through POST or GET (or other forms?) they should store these values in $var using SmrSession::updateVar() and only access via $var, this is required as when auto-refresh updates the page it will *not* resend these inputs but still requires them to render the page correctly.

## Abstract vs normal classes
This initially started out to be used in the "standard" way for NPCs but that idea has since been discarded.
Now all core/shared "Default" code should be here, with the normal class child implementing game type specific functionality/overrides, for instance "lib/Semi Wars/SmrAccount" which is used to make every account appear to be a "vet" account when playing semi wars.