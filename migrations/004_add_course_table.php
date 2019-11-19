<?php

global $db_type;

if ('sqlite' === $db_type || 'test' === $db_type) {
    // SQLITE (mostly for testing)
    $tables = [
        '
            CREATE TABLE IF NOT EXISTS courses (
                id integer PRIMARY KEY AUTOINCREMENT,
                user_id integer,
                course_id integer,
                report_json text,
                date_run timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
                errors integer,
                suggestions integer
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
                user_id integer,
                course_id integer,
                report_json text,
                date_run timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
                errors integer,
                suggestions integer
            );
        '
    ];
}

if ('mysql' === $db_type) {
    // MYSQL
    echo("Setting up tables in MySQL\r\n");
    $tables = [
        '
            CREATE TABLE IF NOT EXISTS `reports` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int(10) unsigned NOT NULL,
                `course_id` int(10) unsigned NOT NULL,
                `report_json` MEDIUMTEXT NOT NULL,
                `date_run` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `errors` int(10) unsigned NOT NULL,
                `suggestions` int(10) unsigned NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        '
    ];
}

//  run every query
foreach ($tables as $sql) {
    UdoitDB::query($sql);
}