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
define ('PLUGIN_SUCCESS', 0);
define ('PLUGIN_ERROR_DEFAULT', 1);
define ('PLUGIN_ERROR_CONNECT', 2);
define ('PLUGIN_ERROR_PROCESS', 3);

class forward extends rcube_plugin
{
	public $task = 'settings';
	private $rc;
	private $obj;

	/*
	 * Initializes the plugin.
	 */
	public function init()
	{
		$rcmail = rcmail::get_instance();
		$this->rc = &$rcmail;
		$this->add_texts('localization/', true);

		$this->rc->output->add_label('forward');
		$this->register_action('plugin.forward', array($this, 'forward_init'));
		$this->register_action('plugin.forward-save', array($this, 'forward_save'));
		$this->include_script('forward.js');

		$this->load_config();
		
		require_once ($this->home . '/lib/rcube_forward.php');
		$this->obj = new rcube_forward();
	}

	/*
	 * Plugin initialization function.
	 */
	public function forward_init()
	{
		$this->read_data();

		$this->register_handler('plugin.body', array($this, 'forward_form'));
		$this->rc->output->set_pagetitle($this->gettext('forward'));
		$this->rc->output->send('plugin');
	}

	/*
	 * Plugin save function.
	 */
	public function forward_save()
	{
		$this->write_data();

		$this->register_handler('plugin.body', array($this, 'forward_form'));
		$this->rc->output->set_pagetitle($this->gettext('forward'));
		rcmail_overwrite_action('plugin.forward');
		$this->rc->output->send('plugin');
	}

	/*
	 * Plugin UI form function.
	 */
	public function forward_form()
	{
		$table = new html_table(array('cols' => 2));
		if ($this->rc->config->get('forward_gui_forwardforwarder', FALSE))
		{
			$field_id = 'forwardforwarder';
			$input_forwardforwarder = new html_inputfield(array('name' => '_forwardforwarder', 'id' => $field_id, 'size' => 20));
			$table->add('title', html::label($field_id, Q($this->gettext('forwardforwarder'))));
			$table->add(null, $input_forwardforwarder->show($this->obj->get_forward_forwarder()));
		}
		$out = html::div(array('class' => "box"), html::div(array('id' => "prefs-title", 'class' => 'boxtitle'), $this->gettext('forward')) . html::div(array('class' => "boxcontent"), $table->show() .  "<br>Would you like to forward your mail to another address? <br> To forward your mail, put the destination address (one address only) in the box abovei. To stop forwarding your mail, simply delete everything in the box. <br> <br> Please note: if you choose to forward your mail, then <b>all</b> spam mail and virus notifications you receive <b>will</b> be forwarded too, and automatic replies <b>will not</b> be sent on your behalf. <br>" . html::p(null, $this->rc->output->button(array('command' => 'plugin.forward-save', 'type' => 'input', 'class' => 'button mainaction', 'label' => 'save')))));

		$this->rc->output->add_gui_object('forwardform', 'forward-form');

		return $this->rc->output->form_tag(array('id' => 'forward-form', 'name' => 'forward-form', 'method' => 'post', 'action' => './?_task=settings&_action=plugin.forward-save'), $out);
	}

	/*
	 * Reads plugin data.
	 */
	public function read_data()
	{
		$driver = $this->home . '/lib/drivers/' . $this->rc->config->get('forward_driver', 'sql').'.php';

		if (!is_readable($driver))
		{
			raise_error(array('code' => 600, 'type' => 'php', 'file' => __FILE__, 'message' => "Forward plugin: Unable to open driver file $driver"), true, false);

			return $this->gettext('internalerror');
		}

		require_once($driver);

		if (!function_exists('forward_read'))
		{
			raise_error(array('code' => 600, 'type' => 'php', 'file' => __FILE__, 'message' => "Forward plugin: Broken driver: $driver"), true, false);

			return $this->gettext('internalerror');
		}

		$data['email_local'] = $this->obj->get_email_local();
		$data['forward_forwarder'] = $this->obj->get_forward_forwarder();
	
		$ret = forward_read ($data);
		switch ($ret)
		{
			case PLUGIN_ERROR_DEFAULT:
				{
					$this->rc->output->command('display_message', $this->gettext('forwarddriverdefaulterror'), 'error');

					return FALSE;
				}

			case PLUGIN_ERROR_CONNECT:
				{
					$this->rc->output->command('display_message', $this->gettext('forwarddriverconnecterror'), 'error');

					return FALSE;
				}

			case PLUGIN_ERROR_PROCESS:
				{
					$this->rc->output->command('display_message', $this->gettext('forwarddriverprocesserror'), 'error');

					return FALSE;
				}

			case PLUGIN_SUCCESS:
			default:
				{
					break;
				}
		}
		if (isset($data['email_local']))
		{
			$this->obj->set_email_local($data['email_local']);
		}
		
		if (isset($data['forward_forwarder']))
		{
			$this->obj->set_forward_forwarder($data['forward_forwarder']);
		}

		return TRUE;
	}

	/*
	 * Writes plugin data.
	 */
	public function write_data()
	{
		if ($this->rc->config->get('forward_gui_forwardforwarder', FALSE))
		{
			$forwarder = get_input_value('_forwardforwarder', RCUBE_INPUT_POST);
			if (is_string($forwarder) && (strlen($forwarder) > 0))
			{
				if ($this->rc->config->get('forward_forwarder_multiple', FALSE))
				{
					$emails = preg_split('/' . $this->rc->config->get('forward_forwarder_separator', ',') .'/', $forwarder);
				}
				else
				{
					$emails[] = $forwarder;
				}
		
				foreach ($emails as $email)
				{
					if (!preg_match('#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#', $email))
					{
						if ($this->rc->config->get('forward_forwarder_multiple', FALSE))
							$this->rc->output->command('display_message', $this->gettext('forwardinvalidforwarders'), 'error');
						else
							$this->rc->output->command('display_message', $this->gettext('forwardinvalidforwarder'), 'error');
						
						return FALSE;
					}
				}
			}
			
			$this->obj->set_forward_forwarder($forwarder);
		}

		$driver = $this->home . '/lib/drivers/' . $this->rc->config->get('forward_driver', 'sql').'.php';

		if (!is_readable($driver))
		{
			raise_error(array('code' => 600, 'type' => 'php', 'file' => __FILE__, 'message' => "Forward plugin: Unable to open driver file $driver"), true, false);

			return $this->gettext('internalerror');
		}

		require_once($driver);

		if (!function_exists('forward_write'))
		{
			raise_error(array('code' => 600, 'type' => 'php', 'file' => __FILE__, 'message' => "Forward plugin: Broken driver: $driver"), true, false);

			return $this->gettext('internalerror');
		}

		$data['email_local'] = $this->obj->get_email_local();
		$data['forward_forwarder'] = $this->obj->get_forward_forwarder();
		
		$ret = forward_write ($data);
		switch ($ret)
		{
			case PLUGIN_ERROR_DEFAULT:
				{
					$this->rc->output->command('display_message', $this->gettext('forwarddriverdefaulterror'), 'error');

					return FALSE;
				}

			case PLUGIN_ERROR_CONNECT:
				{
					$this->rc->output->command('display_message', $this->gettext('forwarddriverconnecterror'), 'error');

					return FALSE;
				}

			case PLUGIN_ERROR_PROCESS:
				{
					$this->rc->output->command('display_message', $this->gettext('forwarddriverprocesserror'), 'error');

					return FALSE;
				}

			case PLUGIN_SUCCESS:
			default:
				{
					$this->rc->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');

					break;
				}
		}
		if (isset($data['email_local']))
		{
			$this->obj->set_email_local($data['email_local']);
		}
		if (isset($data['forward_forwarder']))
		{
			$this->obj->set_forward_forwarder($data['forward_forwarder']);
		}

		return TRUE;
	}

}
