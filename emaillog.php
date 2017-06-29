<?php
/**
 * @package    plg_system_emaillog
 *
 * @author     Sigrid Gramlinger
 * @copyright  Copyright (C) 2017 webgras
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.webgras.at
 */

defined('_JEXEC') or die;

/**
 * Plg_system_emaillog plugin.
 *
 * @package  plg_system_emaillog
 * @since    1.0
 *
 * Logs the emails sent through Joomla! com_contact forms.
 * Log entries are saved to database or file (administrator/logs/error.php) or both
 */
class plgSystemEmaillog extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  1.0
	 */
	protected $db;

    /**
     * Load plugin language files automatically
     *
     * @var    boolean
     * @since  3.6.3
     */
    protected $autoloadLanguage = true;

    /**
     * onSubmitContact
     *
     * @param   object   $contact   Comment
     * @param   array    &$data     Comment
     *
     * @return  void
     */
    public function onSubmitContact(&$contact, &$data)
    {
        $logtype = $this->params->get('logtype', 'db');

        switch ($logtype) {
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

    protected function writeLogDB($contact, $data)
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

    protected function writeLogFile($contact, $data)
    {
        $logEmail = array();
        $logEmail['status'] = 'emailcontact';

        // @TODO: use JText::sprintf(...)
        $logEmail['comment'] = JText::_('PLG_SYSTEM_EMAILLOG_FILELOG_CONTACTID') . $contact->id . JText::_('PLG_SYSTEM_EMAILLOG_FILELOG_FROM') . $data['contact_email'] . JText::_('PLG_SYSTEM_EMAILLOG_FILELOG_SUBJECT') . $data['contact_subject'];
        
        JLog::addLogger(array(), JLog::INFO);
        JLog::add($logEmail['comment'], JLog::INFO, $logEmail['status']);
    }
}
