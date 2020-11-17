[![Travis Build](https://travis-ci.org/smrealms/smr.svg?branch=master)](https://travis-ci.org/smrealms/smr)
[![Docker Build](https://img.shields.io/docker/build/smrealms/smr.svg)](https://cloud.docker.com/swarm/smrealms/repository/docker/smrealms/smr)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smrealms/smr/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smrealms/smr/?branch=master)

# Installation

## Dependencies
Make sure the following software is installed:
* docker (version 18.06.0+)
* docker-compose (version 1.22.0+)

To run unit tests on your machine:
* Composer (2.0.5+)

## Setup
First, you will need to clone this repository. Then inside the clone, you
will need to create installation-specific copies of the following files:

* `.env.sample` &rarr; `.env`
* `config/config.specific.sample.php` &rarr; `config/config.specific.php`

For "Caretaker" (IRC) or "Autopilot" (Discord) functionality:
* `config/irc/config.specific.sample.php` &rarr; `config/irc/config.specific.php`
* `config/discord/config.specific.sample.php` &rarr; `config/discord/config.specific.php`

For NPC's:
* `config/npc/config.specific.sample.php` &rarr; `config/npc/config.specific.php`

The sample versions have sensible defaults, but update the copies as necessary.
The options must be consistent between the various configuration files.

## Populate the mysql database
To initialize the database or update it with new patches, run:
```
docker-compose run --rm flyway
```

To modify the database, add a file called `V<VERSION_NUMBER>__<NAME>.sql` into
`db/patches` and rerun the command.

You can optionally start up [phpMyAdmin](https://www.phpmyadmin.net/) to
inspect your mysql database in a web browser at `http://localhost/pma/`:
```
docker-compose up --build -d pma
```

## Start up the services
Then you can start up the persistent game services
```
docker-compose up --build -d traefik smr
```

For development, it may be desirable to automatically pick up source code changes without rebuilding the docker image. Simply use the `smr-dev` service instead of `smr`, i.e.:
```
docker-compose up --build -d traefik smr-dev
```


# Runtime

## Permissions
In order to create an admin account you should first create a standard account
via the register form, then run the following command to give that account
admin permissions:

```bash
db/init_admin.sh
```

The account should now have an "Admin Tools" link on the left whilst logged in,
which will allow you to assign any extra permissions to yourself and others.

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
require_once(get_file_loc('SmrAlliance.class.php'));
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

### $db
This is a global instance of the SmrMySqlDatabase class. It can be used for
database queries anywhere, but be careful to avoid using it in two places
simultaneously (doing so will cause database errors).

### $var
$var contains all information passed using the $container from the previous page.
This *can* be assigned to, but only using SmrSession::updateVar($name, $value)

### $template
The global instance of the Template class should be the _only_ instance, and
it is used to assign variables for display processing.

### $account
This contains the current SmrAccount object and should not be assigned to.

### $player
_[Scope: in game]_ This contains the current SmrPlayer object and should
not be assigned to.

### $ship
_[Scope: in game]_ This contains the current SmrShip object and should not be
assigned to.

### $sector
_[Scope: in game]_ This contains the current SmrSector object and should not
be assigned to.


## Request variables
For any page which takes input through POST or GET (or other forms?) they should store these values in $var using SmrSession::updateVar() and only access via $var, this is required as when auto-refresh updates the page it will *not* resend these inputs but still requires them to render the page correctly.

## Abstract vs normal classes
This initially started out to be used in the "standard" way for NPCs but that idea has since been discarded.
Now all core/shared "Default" code should be in the abstract version, with the normal class child implementing game type specific functionality/overrides, for instance "lib/Semi Wars/SmrAccount" which is used to make every account appear to be a "vet" account when playing semi wars.

## Unit testing
SMR uses [PHPUnit](https://phpunit.de/) to run unit tests.
### Setup
1. Ensure the MySQL container is running, and ready for any integration tests that touch the database:
   * `composer start:integration-services`
1. Run `composer run test` to execute the full suite of tests.
1. Add new tests as needed in the `/test` directory.

### Setting up your IDE to run tests
This information applies to IDEA-based IDEs, e.g. `IntelliJ`, `PHPStorm`. For other vendors, please refer to your vendor's documentation for running PHPUnit tests against a remote container.
* In order to perform these next steps in `IntelliJ`, you must have the following Jetbrains-provided plugins installed:
 * [PHP](https://plugins.jetbrains.com/plugin/6610-php)
 * [PHP Docker](https://plugins.jetbrains.com/plugin/8595-php-docker)
   * This should also install [PHP Remote Intepreter](https://plugins.jetbrains.com/plugin/7511-php-remote-interpreter)

##### Configure a remote PHP interpreter for the project

1. `File > Settings > Languages & Frameworks > PHP`. In the `CLI Interpreter` area, click the `...` button
1. Press the "+" button in the top left, and select `From Docker, Vagrant, VM, WSL, Remote...`
1. In the new window, choose `Docker Compose`, and in the `Service` area, select `smr-integration-test`. Press "OK".
1. It will check the configuration by starting up the Docker container, and gathering PHP information. Once that's finished, you should be on a configuration screen for the new interpreter. It should have the PHP information from the Docker container, and also the `Xdebug` information.
1. In the `Environment variables` box on the new intepreter's screen, paste in the values from `/test.env` in the project directory.
1. The rest of the default settings should be fine, so you can press "Apply".
1. On the settings navigation tree, underneath PHP, click the `Composer` item: In the `CLI Interpreter` drop down, select the new interpreter you've created. Press "Apply".
1. On the settings navigation tree, underneath PHP, click the `Test Frameworks` item.
1. Click the `+` button to create an entry, select the newly created interpreter from the drop down, and press "OK".
1. In the `PHPUnit library` section, set the `Path to script` value to `/smr/vendor/autoload.php`
1. In the `Test Runner` section, set the `Default configuration file` to `/smr/phpunit.xml`
1. Press "OK", and you should be good to go for executing tests inside the IDE.

### Writing integration tests
1. To create an integration test that uses the database, your test should extend `SmrTest\BaseIntegrationSpec`. This will ensure that any test data that gets written to the database will be cleaned up after each test.
 * The SMR database uses MyISAM for a storage engine, so we cannot simply rollback transactions after each test. Instead, the `BaseIntegrationSpec` will check for any tables that are populated from the `flyway` migration during startup, and truncate all other tables after your test.
