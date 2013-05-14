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

class rcube_forward
{
	public $username = '';
	public $email = '';
	public $email_local = '';
	public $email_domain =  '';
	public $forward_enable = FALSE;
	public $forward_start = 0;
	public $forward_end = 0;
	public $forward_subject = '';
	public $forward_message = '';
	public $forward_keepcopyininbox = TRUE;
	public $forward_forwarder = '';

	/**
	 * Constructor of the class.
	 */
	public function __construct()
	{
		$this->init();
	}
	
	/*
	 * Initialize the object.
	 */
	private function init()
	{
		$this->username = rcmail::get_instance()->user->get_username();
		
	    $parts = explode('@', $this->username);
	    if (count($parts) >= 2)
	    {
	       $this->email = $this->username;
	       $this->email_local = $parts[0];
	       $this->email_domain = $parts[1] ;
	    }
	}
	
	/*
	 * Gets the username.
	 *
	 * @return string the username.
	 */		
	public function get_username()
	{
		return $this->username;
	}
	
	/*
	 * Gets the full email of the user.
	 *
	 * @return string the email of the user.
	 */			
	public function get_email()
	{	    
	    return $this->email;
    }
	
	/*
	 * Gets the email local part of the user.
	 *
	 * @return string the email local part.
	 */		
	public function get_email_local()
	{    
	    return $this->email_local;
    }

	/*
	 * Gets the email domain of the user.
	 *
	 * @return string the email domain.
	 */			
	public function get_email_domain()
	{	    
	    return $this->email_domain;
    }

	/*
	 * Checks if the forward is enabled.
	 *
	 * @return boolean TRUE if forward is enabled; FALSE otherwise.
	 */
	public function is_forward_enable ()
	{
		return $this->forward_enable;
	}

	/*
	 * Gets the forward start date.
	 *
	 * @returng int the timestamp of the start date.
	 */
	public function get_forward_start()
	{
		return $this->forward_start;
	}

	/*
	 * Gets the forward end date.
	 *
	 * @returng int the timestamp of the end date.
	 */
	public function get_forward_end()
	{
		return $this->forward_end;
	}
	
	/*
	 * Gets the forward subject.
	 *
	 * @return string the forward subject.
	 */
	public function get_forward_subject()
	{
		return $this->forward_subject;
	}

	/*
	 * Gets the forward message.
	 *
	 * @return string the forward message.
	 */
	public function get_forward_message()
	{
		return $this->forward_message;
	}
	
	/*
	 * Checks if a copy in inbox must be keep when the forward is enabled.
	 *
	 * @return boolean TRUE if a copy must be keeped; FALSE otherwise.
	 */
	public function is_forward_keep_copy_in_inbox()
	{
		return $this->forward_keepcopyininbox;
	}
	
	/*
	 * Gets the forward forward address.
	 * 
	 * @return string the forward forward address.
	 */
	public function get_forward_forwarder()
	{
		return $this->forward_forwarder;
	}

	/*
	 * Sets the email of the user
	 *
	 * @param string $email the email.
	 */
	public function set_email($email)
	{
		$this->email = $email;
	}
	
	/*
	 * Sets the email local part of the user
	 *
	 * @param string $local the local part of the email.
	 */
	public function set_email_local($local)
	{
		$this->email_local = $local;
	}
	
	/*
	 * Sets the email domain part of the user
	 *
	 * @param string $local the domain part of the email.
	 */
	public function set_email_domain($domain)
	{
		$this->email_domain = $domain;
	}
	
	/*
	 * Enables or disables the forward.
	 *
	 * @param boolean the flag.
	 */
	public function set_forward_enable($flag)
	{
		$this->forward_enable = $flag;
	}

	/*
	 * Sets the forward start date.
	 *
	 * @param int the timestamp of the forward start date.
	 */
	public function set_forward_start ($date)
	{
		$this->forward_start = $date;
	}

	/*
	 * Sets the forward end date.
	 *
	 * @param int the timestamp of the forward end date.
	 */
	public function set_forward_end ($date)
	{
		$this->forward_end = $date;
	}
	
	/*
	 * Sets the forward subject.
	 *
	 * @param string $subject the forward subject.
	 */
	public function set_forward_subject($subject)
	{
		$this->forward_subject = $subject;
	}

	/*
	 * Sets the forward message.
	 *
	 * @param string $message the forward message.
	 */
	public function set_forward_message($message)
	{
		$this->forward_message = $message;
	}
	
	/*
	 * Sets the forward keep copy in inbox flag.
	 *
	 * @param boolean the flag.
	 */
	public function set_forward_keep_copy_in_inbox($flag)
	{
		$this->forward_keepcopyininbox = $flag;
	}
	
	/*
	 * Sets the forward forward address.
	 * 
	 * @param string $forwarder the forward forward address.
	 */
	public function set_forward_forwarder($forwarder)
	{
		$this->forward_forwarder = $forwarder;
	}
}
