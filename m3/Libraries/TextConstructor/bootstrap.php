<?php //bootstrap.php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once __DIR__.'/vendor/autoload.php';

/**
 * Generar Gestor de entidades
 *
 * @return EntityManager
 * @throws \Doctrine\ORM\ORMException
 */
function getEntityManager(){
    //Carga configuracion de la conexion
    $bdParams = [
        'host' => $_ENV['DATABASE_HOST'],
        'port' => $_ENV['DATABASE_PORT'],
        'dbname' => $_ENV['DATABASE_NAME'],
        'user' => $_ENV['DATABASE_USER'],
        'password' => $_ENV['DATABASE_PASSWD'],
        'driver' => $_ENV['DATABASE_DRIVER'],
        'charset' => $_ENV['DATABASE_CHARSET']
    ];

    $config = Setup::createAnnotationMetadataConfiguration(
        [$_ENV['ENTITY_DIR']],
        $_ENV['DEBUG'],
        ini_get('sys_temp_dir'),
        null,
        false
    );

    $config->setAutoGenerateProxyClasses(true);

    if ($_ENV['DEBUG']){
        $config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
    }

    return EntityManager::create($bdParams, $config);
}

