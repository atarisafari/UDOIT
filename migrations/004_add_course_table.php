<?php

global $db_type;

if ('sqlite' === $db_type || 'test' === $db_type) {
    // SQLITE (mostly for testing)
    $tables = [
        '
            CREATE TABLE IF NOT EXISTS courses (
                id integer PRIMARY KEY AUTOINCREMENT,
                canvas_id integer,
                name text,
                term text
            );
        '
    ];
}

if ('pgsql' === $db_type) {
    // POSTGRESQL
    echo("Setting up tables in PostgreSQL\r\n");
    $tables = [
        '
            CREATE TABLE IF NOT EXISTS courses (
                id SERIAL PRIMARY KEY,
                canvas_id integer,
                name text,
                term text
            );
        '
    ];
}

if ('mysql' === $db_type) {
    // MYSQL
    echo("Setting up tables in MySQL\r\n");
    $tables = [
        '
            CREATE TABLE IF NOT EXISTS `courses` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `canvas_id` int(10) unsigned NOT NULL,
                `name` varchar(255) NOT NULL,
                `term` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        '
    ];
}

//  run every query
foreach ($tables as $sql) {
    UdoitDB::query($sql);
}