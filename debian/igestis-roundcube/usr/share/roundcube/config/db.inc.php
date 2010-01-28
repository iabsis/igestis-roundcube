<?php

/*
 +-----------------------------------------------------------------------+
 | Configuration file for database access                                |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+

*/

include "/etc/igestis/debian-db.php";

$rcmail_config = array();

// PEAR database DSN for read/write operations
// format is db_provider://user:password@host/database 
// currently supported db_providers: mysql, mysqli, pgsql, sqlite, mssql

$rcmail_config['db_dsnw'] = "mysql://$dbuser:$dbpass@localhost/$dbname";

// postgres example: 'pgsql://roundcube:pass@localhost/roundcubemail';
// sqlite example: 'sqlite://./sqlite.db?mode=0646';

// PEAR database DSN for read only operations (if empty write database will be used)
// useful for database replication
$rcmail_config['db_dsnr'] = '';

// maximum length of a query in bytes
$rcmail_config['db_max_length'] = 512000;  // 500K

// use persistent db-connections
// beware this will not "always" work as expected
// see: http://www.php.net/manual/en/features.persistent-connections.php
$rcmail_config['db_persistent'] = FALSE;


// you can define specific table names used to store webmail data
$rcmail_config['db_table_users'] = 'ROUNDCUBE_users';

$rcmail_config['db_table_identities'] = 'ROUNDCUBE_identities';

$rcmail_config['db_table_contacts'] = 'ROUNDCUBE_contacts';

$rcmail_config['db_table_session'] = 'ROUNDCUBE_session';

$rcmail_config['db_table_cache'] = 'ROUNDCUBE_cache';

$rcmail_config['db_table_messages'] = 'ROUNDCUBE_messages';


// you can define specific sequence names used in PostgreSQL
$rcmail_config['db_sequence_users'] = 'user_ids';

$rcmail_config['db_sequence_identities'] = 'identity_ids';

$rcmail_config['db_sequence_contacts'] = 'contact_ids';

$rcmail_config['db_sequence_cache'] = 'cache_ids';

$rcmail_config['db_sequence_messages'] = 'message_ids';


// end db config file
?>
