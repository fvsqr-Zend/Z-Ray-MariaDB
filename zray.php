<?php
namespace MariaDB;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'MariaDB.php';

$zre = new \ZRayExtension('MariaDB');
$zrayMariaDB = new MariaDB();
$zre->setMetadata(array(
    'logo' => __DIR__ . DIRECTORY_SEPARATOR . 'mariadb-logo.png'
));

$zre->setEnabledAfter('Mage_Core_Model_App::run');

$zre->traceFunction(
    'PDO::__construct', 
    function($context, &$storage) use ($zrayMariaDB) {
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
