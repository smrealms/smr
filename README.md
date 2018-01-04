# Installation with docker

## Building an SMR docker image
```
docker build --tag smrealms/smr .
```

## Running a mysql server via docker (optional)

### Start the mysql server
```
docker run \
	--name='smr-mysql' \
	--env='MYSQL_RANDOM_ROOT_PASSWORD=yes' \
	--env='MYSQL_USER=smr' \
	--env='MYSQL_PASSWORD=smr' \
	--env='MYSQL_DATABASE=smr_live' \
	--detach \
	mysql:5.5
```
### Populate the mysql server
```
docker run \
	--rm \
	--link='smr-mysql' \
	--volume="$(pwd)/db/patches:/flyway/sql:ro" \
	shouldbee/flyway \
	-url='jdbc:mysql://smr-mysql/smr_live' \
	-user='smr' \
	-password='smr' \
	init

docker run \
	--rm \
	--link='smr-mysql' \
	--volume="$(pwd)/db/patches:/flyway/sql:ro" \
	shouldbee/flyway \
	-url='jdbc:mysql://smr-mysql/smr_live' \
	-user='smr' \
	-password='smr' \
	migrate
```

## Running SMR via docker

You must change the paths to filled in config files, see the config section below for more info

For production
```
docker run \
	--name="smr" \
	--link='smr-mysql' \
	--publish='80:80' \
	--volume="/path/to/config:/smr/config:ro" \
	--detach \
	smrealms/smr
```
For development (will automatically pick up source changes)
```
docker run \
	--name="smr" \
	--link='smr-mysql' \
	--publish='80:80' \
	--volume="$(pwd)/admin:/smr/admin" \
	--volume="$(pwd)/engine:/smr/engine" \
	--volume="$(pwd)/htdocs:/smr/htdocs" \
	--volume="$(pwd)/lib:/smr/lib" \
	--volume="$(pwd)/templates:/smr/templates" \
	--volume="$(pwd)/config:/smr/config:ro" \
	--detach \
	smrealms/smr
```

## Viewing logs via docker

```
docker logs -f smr
```

# Installation natively

## Dependencies
These list the known dependencies, there may be more - please update if you find any!

### Core
* PHP 5.4+
* MySQL 5.5

### PHP Extensions
* MySQL http://php.net/manual/en/book.mysql.php
* cURL (Facebook login): http://php.net/manual/en/book.curl.php
* JSON (Facebook login): http://php.net/manual/en/book.json.php
* runkit (NPCs): http://php.net/manual/en/book.runkit.php


## Config files
Currently it is required to create installation specific copies of the following files:

* config/config.specific.sample.php -> config/config.specific.php
* config/SmrMySqlSecrets.sample.inc -> config/SmrMySqlSecrets.inc

For "Caretaker" functionality:
* config/irc/config.specific.sample.php -> config/irc/config.specific.php

For npc:
* config/npc/config.specific.sample.php -> config/npc/config.specific.php

For these files the sample version should provide good hints on what info is required, there are also other sample files but these are generally not required (read: only for supporting old 1.2 databases, you're unlikely to have one of those lying about ;) )


## Filesystem permissions
SMR requires write access to htdocs/upload.

## Database
SMR is using [Flyway](http://flywaydb.org) to deploy database patches.

1. Download and untar Flyway

    ```bash
    wget http://repo1.maven.org/maven2/org/flywaydb/flyway-commandline/3.0/flyway-commandline-3.0.tar.gz && tar -xvzf flyway-commandline-3.0.tar.gz -C /opt
    ```

2. Set the following options in /opt/flyway-3.0/conf/flyway.properties
    ```bash
    flyway.url=jdbc:mysql://localhost/smr_live
    flyway.user=smr
    flyway.password=YOUR_DATABASE_PASSWORD
    ```

3. Download the Java MySQL Connector from http://www.mysql.de/downloads/connector/j

4. Unzip it and put the jar into /opt/flyway-3.0/jars

5. Point sql folder to SMR patches

    ```
    cd /opt/flyway-3.0 && rm -Rf sql && ln -s <GIT_ROOT_PATH>/db/patches sql
    ```

6. Initialize database `./flyway.sh init` which automatically creates the needed database tables and initializes the version

7. Run all patches `./flyway.sh migrate`

If you start with an existing database you need to follow above steps 1 to 5 and initialize the database with the following command:
`./flyway.sh -initialVersion=1.6.39 init` which would mean that your current database equals the one from SMR 1.6.39. From this point on you can use `./flyway.sh migrate` to update to latest database.
After creating a user account I would recommend inserting a row into the permission table corresponding to the account you created and with a permission_id of 1 in order to give yourself admin permissions.

In case you need to change database with a new version put a file called `V<VERSION_NUMBER>__NAME.sql` into db/patches folder. One version can have multiple patches.


# Runtime

## Permissions
In order to create an admin account you should first create a standard account via the register form, then add an entry to the "account_has_permission" table for the "account_id" of the created account and "permission_id" 1 (which is the permission to manage admin permissions).
Once you have added this entry the account should now have an "Admin Tools" link on the left whilst logged in, which will allow you to assign any extra permissions to yourself and others.

## Creating a Game
To create a game you will have to have assigned yourself the "1.6 Universe Generator" and then access this link via the admin tools to create the game.
Once you are happy with the game you need to edit the "game" table and set the "enabled" flag for your game to 'TRUE' in order for it to appear in the list of games to join.

# Coding Style
This is the coding style that should be used for any new code, although currently not all code currently follows these guidelines (the guidelines are also likely to require extension).

* Opening races should be placed on the same line with a single space before
* Single line if statements should still include braces

	```php
	if (true) {
	}
	```

* Variable names should be camelCase normally, except when in templates when they should be UpperCamelCase (to enforce some mental separation between the two contexts).

	```php
	$applicationVar = 'value';
	$TemplateVar = 'value';
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
Now all core/shared "Default" code should be in the abstract version, with the normal class child implementing game type specific functionality/overrides, for instance "lib/Semi Wars/SmrAccount" which is used to make every account appear to be a "vet" account when playing semi wars.
