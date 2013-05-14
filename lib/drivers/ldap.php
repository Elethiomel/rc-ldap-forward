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

/*
 * Read driver function.
 *
 * @param array $data the array of data to get and set.
 *
 * @return boolean TRUE if load is successfull; FALSE otherwise.
 */
function forward_read(array &$data)
{
	require_once ('Net/LDAP2.php');
	$rcmail = rcmail::get_instance();
	
	$search = array('%username',
					'%email_local',
					'%email_domain',
					'%email');
	$replace = array($data['username'],
					 $data['email_local'],
					 $data['email_domain'],
					 $data['email']);	
	$ldap_basedn = str_replace($search, $replace, $rcmail->config->get('forward_ldap_basedn'));
	$ldap_binddn = str_replace($search, $replace, $rcmail->config->get('forward_ldap_binddn'));

	$search = array('%username',
					'%password',
					'%email_local',
					'%email_domain',
					'%email');
	$replace = array($data['username'],
					 $rcmail->decrypt($_SESSION['password']),
					 $data['email_local'],
					 $data['email_domain'],
					 $data['email']);
	$ldap_bindpw = str_replace($search, $replace, $rcmail->config->get('forward_ldap_bindpw'));
	
	$ldapConfig = array (
        'host'      => $rcmail->config->get('forward_ldap_host'),
        'port'      => $rcmail->config->get('forward_ldap_port'),
        'starttls'  => $rcmail->config->get('forward_ldap_starttls'),
        'version'   => $rcmail->config->get('forward_ldap_version'),
        'basedn'    => $ldap_basedn,
        'binddn'    => $ldap_binddn,
        'bindpw'    => $ldap_bindpw,
	);

	$ldap = Net_LDAP2::connect($ldapConfig);
	if (PEAR::isError($ldap))
	{
		return PLUGIN_ERROR_CONNECT;
	}

	$search = array('%username',
					'%email_local',
					'%email_domain',
					'%email',
					'%forward_enable',
					'%forward_start',
					'%forward_end',
					'%forward_subject',
					'%forward_message',
					'%forward_keepcopyininbox',
					'%forward_forwarder');
	$replace = array($data['username'],
					 $data['email_local'],
					 $data['email_domain'],
					 $data['email'],
					 $data['forward_enable'],
					 $data['forward_start'],
					 $data['forward_end'],
					 $data['forward_subject'],
					 $data['forward_message'],
					 $data['forward_keepcopyininbox'],
					 $data['forward_forwarder']);

	$search_base = str_replace($search, $replace, $rcmail->config->get('forward_ldap_search_base'));
	$search_filter = str_replace($search, $replace, $rcmail->config->get('forward_ldap_search_filter'));
	$search_params = array('attributes' => $rcmail->config->get('forward_ldap_search_attrs'));

	$search = $ldap->search($search_base, $search_filter, $search_params);
	if (Net_LDAP2::isError($userEntry))
	{
		$ldap->done();

		return PLUGIN_ERROR_PROCESS;
	}

	if ($search->count() < 1)
	{
		$ldap->done();

		return PLUGIN_ERROR_PROCESS;
	}

	$entry = $search->shiftEntry();
	
	if ($entry->exists($rcmail->config->get('forward_ldap_attr_email')))
	{
		$data['email'] = $entry->get_value($rcmail->config->get('forward_ldap_search_attr_email'));
	}

	if ($entry->exists($rcmail->config->get('forward_ldap_attr_emaillocal')))
	{
		$data['email_local'] = $entry->get_value($rcmail->config->get('forward_ldap_search_attr_emaillocal'));
	}

	if ($entry->exists($rcmail->config->get('forward_ldap_attr_emaildomain')))
	{
		$data['email_domain'] = $entry->get_value($rcmail->config->get('forward_ldap_search_attr_emaildomain'));
	}
	
	if ($entry->exists($rcmail->config->get('forward_ldap_attr_forwardenable')))
	{
		if ($entry->get_value($rcmail->config->get('forward_ldap_attr_forwardenable')) ==	$rcmail->config->get('forward_ldap_attr_forwardenable_value_enabled'))
			$data['forward_enable'] = 1;
		else
			$data['forward_enable'] = 0;
	}
	
	if ($entry->exists($rcmail->config->get('forward_ldap_attr_forwardstart')))
	{
		$data['forward_start'] = $entry->get_value($rcmail->config->get('forward_ldap_attr_forwardstart'));
	}
	
	if ($entry->exists($rcmail->config->get('forward_ldap_attr_forwardend')))
	{
		$data['forward_end'] = $entry->get_value($rcmail->config->get('forward_ldap_attr_forwardend'));
	}
	
	if ($entry->exists($rcmail->config->get('forward_ldap_attr_forwardsubject')))
	{
		$data['forward_subject'] = $entry->get_value($rcmail->config->get('forward_ldap_attr_forwardsubject'));
	}

	if ($entry->exists($rcmail->config->get('forward_ldap_attr_forwardmessage')))
	{
		$data['forward_message'] = $entry->get_value($rcmail->config->get('forward_ldap_attr_forwardmessage'));
	}
	
	if ($entry->exists($rcmail->config->get('forward_ldap_attr_forwardkeepcopyininbox')))
	{
		if ($entry->get_value($rcmail->config->get('forward_ldap_attr_forwardkeepcopyininbox')) ==	$rcmail->config->get('forward_ldap_attr_forwardkeepcopyininbox_value_enabled'))
			$data['forward_keepcopyininbox'] = 1;
		else
			$data['forward_keepcopyininbox'] = 0;
	}
	
	if ($entry->exists($rcmail->config->get('forward_ldap_attr_forwardforwarder')))
	{
		$data['forward_forwarder'] = $entry->get_value($rcmail->config->get('forward_ldap_attr_forwardforwarder'));
	}

	$ldap->done();

	return PLUGIN_SUCCESS;
}

/*
 * Write driver function.
 *
 * @param array $data the array of data to get and set.
 *
 * @return boolean TRUE if save is successfull; FALSE otherwise.
 */
function forward_write(array &$data)
{
	require_once ('Net/LDAP2.php');
	$rcmail = rcmail::get_instance();
	
	$search = array('%username',
					'%email_local',
					'%email_domain',
					'%email');
	$replace = array($data['username'],
					 $data['email_local'],
					 $data['email_domain'],
					 $data['email']);	
	$ldap_basedn = str_replace($search, $replace, $rcmail->config->get('forward_ldap_basedn'));
	$ldap_binddn = str_replace($search, $replace, $rcmail->config->get('forward_ldap_binddn'));

	$search = array('%username',
					'%password',
					'%email_local',
					'%email_domain',
					'%email');
	$replace = array($data['username'],
					 $rcmail->decrypt($_SESSION['password']),
					 $data['email_local'],
					 $data['email_domain'],
					 $data['email']);
	$ldap_bindpw = str_replace($search, $replace, $rcmail->config->get('forward_ldap_bindpw'));
	
	$ldapConfig = array (
        'host'      => $rcmail->config->get('forward_ldap_host'),
        'port'      => $rcmail->config->get('forward_ldap_port'),
        'starttls'  => $rcmail->config->get('forward_ldap_starttls'),
        'version'   => $rcmail->config->get('forward_ldap_version'),
        'basedn'    => $ldap_basedn,
        'binddn'    => $ldap_binddn,
        'bindpw'    => $ldap_bindpw,
	);

	$ldap = Net_LDAP2::connect($ldapConfig);
	if (PEAR::isError($ldap))
	{
		return PLUGIN_ERROR_CONNECT;
	}

	$dns = $rcmail->config->get('forward_ldap_modify_dns');
	$ops = $rcmail->config->get('forward_ldap_modify_ops');
	
	for ($i = 0; $i < count($dns) && $i < count($ops); $i++)
	{
		$search = array('%username',
						'%email_local',
						'%email_domain',
						'%email',
						'%forward_enable',
						'%forward_start',
						'%forward_end',
						'%forward_subject',
						'%forward_message',
						'%forward_keepcopyininbox',
						'%forward_forwarder',
		);
		$replace = array($data['username'],
						 $data['email_local'],
						 $data['email_domain'],
						 $data['email'],
						 ($data['forward_enable'] ? "TRUE" : "FALSE"),
						 $data['forward_start'],
						 $data['forward_end'],
						 $data['forward_subject'],
						 $data['forward_message'],
						 $data['forward_keepcopyininbox'],
						 $data['forward_forwarder']
		);
		$dns[$i] = str_replace($search, $replace, $dns[$i]);

		foreach ($ops[$i] as $op => $args)
		{
			foreach ($args as $key => $value)
			{
				$search = array('%username',
								'%email_local',
								'%email_domain',
								'%email',
								'%forward_enable',
								'%forward_start',
								'%forward_end',
								'%forward_subject',
								'%forward_message',
								'%forward_keepcopyininbox',
								'%forward_forwarder'
				);
				$replace = array($data['username'],
								 $data['email_local'],
								 $data['email_domain'],
								 $data['email'],
								($data['forward_enable'] ?
									$rcmail->config->get('forward_ldap_attr_forwardenable_value_enabled') :
									$rcmail->config->get('forward_ldap_attr_forwardenable_value_disabled')),
								$data['forward_start'],
								$data['forward_end'],
								$data['forward_subject'],
								$data['forward_message'],
								$data['forward_keepcopyininbox'],
								$data['forward_forwarder']
				);
				$ops[$i][$op][$key] = str_replace($search, $replace, $value);				
			}
		}

		$ret = $ldap->modify($dns[$i], $ops[$i]);
		if (PEAR::isError($ldap))
		{
			$ldap->done();
			
			return PLUGIN_ERROR_PROCESS;
		}
	}

	$ldap->done();

	return PLUGIN_SUCCESS;
}
