<?php

defined('_JEXEC') or die;

/**
 * Plg_system_emaillog plugin.
 **/
class PlgSystemEmaillog extends JPlugin
{
	protected $db;
	protected $app;
	protected $autoloadLanguage = true;

	public function onSubmitContact(&$contact, &$data)
	{
		$logtype = $this->params->get('logtype', 'db');

		switch ($logtype)
		{
			case 'file':
				$this->writeLogFile($contact, $data);
				break;
			case 'both':
				$this->writeLogFile($contact, $data);
				$this->writeLogDB($contact, $data);
				break;
			default:
				$this->writeLogDB($contact, $data);
				break;
		}
	}

	private function writeLogDB($contact, $data)
	{
		$comFields = json_encode($data['com_fields']);

		$email = new stdClass;

		$email->sent = JFactory::getDate()->toSql();

		//check if fields should be logged
		if ($this->params->get('log_contactid', 1))
		{
			$email->contact_id = (int) $contact->id;
		}

		if ($this->params->get('log_sender_name', 1))
		{
			$email->contact_name = $data['contact_name'];
		}

		if ($this->params->get('log_sender_email', 1))
		{
			$email->contact_email = $data['contact_email'];
		}

		if ($this->params->get('log_mail_subject', 0))
		{
			$email->contact_subject = $data['contact_subject'];
		}

		if ($this->params->get('log_message', 0))
		{
			$email->contact_message = $data['contact_message'];
		}

		if ($this->params->get('log_mail_fields', 0))
		{
			$email->com_fields = $comFields;
		}

		$this->db->insertObject('#__contact_email_log', $email, 'log_id');
	}

	private function writeLogFile($contact, $data)
	{
		//set message entry
		$logEmail            = array();
		$logEmail['status']  = 'emailcontact';
		$logEmail['comment'] = JText::sprintf('PLG_SYSTEM_EMAILLOG_FILELOG_TEXTENTRY', $contact->id, $data['contact_email'], $data['contact_subject']);

		//set filename in joomla logs-directory
		$options              = array();
		$options['text_file'] = 'email_log.php';

		//save to log
		JLog::addLogger($options, JLog::INFO);
		JLog::add($logEmail['comment'], JLog::INFO, $logEmail['status']);
	}
}
