<?php declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Smr\Container\DiContainer;
use staabm\PHPStanDba\DbSchema\SchemaHasherMysql;
use staabm\PHPStanDba\QueryReflection\PdoMysqlQueryReflector;
use staabm\PHPStanDba\QueryReflection\QueryReflection;
use staabm\PHPStanDba\QueryReflection\ReflectionCache;
use staabm\PHPStanDba\QueryReflection\ReplayAndRecordingQueryReflector;
use staabm\PHPStanDba\QueryReflection\RuntimeConfiguration;

require_once __DIR__ . '/../vendor/autoload.php';

$config = new RuntimeConfiguration();
$config->debugMode(true);
$config->utilizeSqlAst(true);

DiContainer::initialize(false);
$conn = DiContainer::getClass(Connection::class)->getNativeConnection();
if (!($conn instanceof PDO)) {
	throw new Exception('Connection is type ' . gettype($conn) . ', expected PDO');
}

$cacheFile = __DIR__ . '/../.phpstan-dba.cache';
$reflector = new ReplayAndRecordingQueryReflector(
	ReflectionCache::create($cacheFile),
	new PdoMysqlQueryReflector($conn),
	new SchemaHasherMysql($conn),
);

$reflector = new PdoMysqlQueryReflector($conn);

QueryReflection::setupReflector($reflector, $config);
