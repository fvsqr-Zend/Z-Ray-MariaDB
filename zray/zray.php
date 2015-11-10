<?php
namespace MariaDB;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'MariaDB.php';

$zre = new \ZRayExtension('MariaDB', true);
$zrayMariaDB = new MariaDB();
$zre->setMetadata(array(
    'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'mariadb-logo.png'
));

/**
 * HACK: Drupal is not only overwriting PDO::__construct() but also overwriting the
 * signature. That's why we have to determine the dsn before calling MariaDB::init()
 */ 
function getDsnForDrupal($connection_options) {
    if (isset($connection_options['unix_socket'])) {
        $dsn = 'mysql:unix_socket=' . $connection_options['unix_socket'];
    }
    else {
        // Default to TCP connection on port 3306.
        $dsn = 'mysql:host=' . $connection_options['host'] . ';port=' . (empty($connection_options['port']) ? 3306 : $connection_options['port']);
    }
    $dsn .= ';dbname=' . $connection_options['database'];
    return $dsn;
}

$zre->traceFunction(
    'PDO::__construct', 
    function($context, &$storage) use ($zrayMariaDB) {
        if ($context['functionName'] == "DatabaseConnection_mysql::__construct") {
            $zrayMariaDB->init(
                getDsnForDrupal($context['functionArgs'][0]),
                $context['functionArgs'][0]['username'],
                $context['functionArgs'][0]['password']
            );
            return;
        }
        
        $zrayMariaDB->init(
            $context['functionArgs'][0],
            $context['functionArgs'][1],
            $context['functionArgs'][2]
        );
    },
    function () {}
);
$zre->traceFunction(
    'MariaDB\shutdown',
    function () {},
    array($zrayMariaDB, 'statistics')
);

function shutdown() {}

register_shutdown_function('MariaDB\shutdown');
