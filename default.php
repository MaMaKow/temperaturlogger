<?php

ini_set('log_errors', TRUE);
ini_set('error_log', 'error.log');


require_once './config.php';
$options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => FALSE,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );

$dsn = 'mysql:host=localhost;dbname=temperaturlogger;charset=utf8';
$pdo = new PDO($dsn, $config['user'], $config['password'], $options);

