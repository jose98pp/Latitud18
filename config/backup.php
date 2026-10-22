<?php

return [
    'password' => env('SITE_BACKUP_PASSWORD', ''),
    'directory' => env('SITE_BACKUP_DIRECTORY', ''),
    'mysqldump' => env('MYSQLDUMP_BINARY', 'mysqldump'),
    'ssl_ca' => env('MYSQL_BACKUP_SSL_CA', ''),
    'mysql' => env('MYSQL_BINARY', 'mysql'),
];
