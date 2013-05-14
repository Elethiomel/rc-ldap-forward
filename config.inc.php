<?php

/*
 +-----------------------------------------------------------------------+
 | LDAP forwarding plugin for Roundcube                                  |
 | Copyright (C) 2012 Colin Fowler <elethiomel@gmail.com>                |
 |                                                                       |
 |  Based Vacation Module for RoundCube                                  |
 |  Copyright (C) 2009 Boris HUISGEN <bhuisgen@hbis.fr>                  |
 |  Licensed under the GNU GPL                                           |
 +-----------------------------------------------------------------------+
 */

$rcmail_config = array();

// allow forward forwarder
$rcmail_config['forward_gui_forwardforwarder'] = TRUE;


// allow multiple forwarders
$rcmail_config['forward_forwarder_multiple'] = FALSE;
$rcmail_config['forward_forwarder_separator'] = ',';

// driver used for backend storage
$rcmail_config['forward_driver'] = 'ldap';


/*
 * LDAP driver
 */

// Server hostname
$rcmail_config['forward_ldap_host'] = 'ldap://ldap.foo.bar.com';

// Server port
$rcmail_config['forward_ldap_port'] = 389;

// Use TLS flag
$rcmail_config['forward_ldap_starttls'] = FALSE;

// Protocol version
$rcmail_config['forward_ldap_version'] = 3;

// Base DN
$rcmail_config['forward_ldap_basedn'] = 'dc=foo,dc=bar,dc=com';

// Bind DN
$rcmail_config['forward_ldap_binddn'] = 'uid=%email_local,ou=People,dc=foo,dc=bar,dc=com';

// Bind password
$rcmail_config['forward_ldap_bindpw'] = '%password';


// Attribute name to map forward forwarder
$rcmail_config['forward_ldap_attr_forwardforwarder'] = 'forwardingAddress'; 

// Search base to read data
$rcmail_config['forward_ldap_search_base'] = 'uid=%email_local,ou=People,dc=foo,dc=bar,dc=com';

// Search filter to read data
$rcmail_config['forward_ldap_search_filter'] = '(objectClass=posixAccount)';

// Search attributes to read data
$rcmail_config['forward_ldap_search_attrs'] = array ( 'forwardingAddress');

// array of DN to use for modify operations required to write data.
$rcmail_config['forward_ldap_modify_dns'] = array ( 'uid=%email_local,ou=People,dc=foo,dc=bar,dc=com');

// array of operations required to write data.
$rcmail_config['forward_ldap_modify_ops'] = array(
	array ('replace' => array(
 			$rcmail_config['forward_ldap_attr_forwardforwarder'] => '%forward_forwarder'
 		  )
	)
);


