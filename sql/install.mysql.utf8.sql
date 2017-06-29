CREATE TABLE IF NOT EXISTS `#__contact_email_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `sent` datetime NOT NULL,
  `contact_id` int(11) NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_subject` varchar(255) NOT NULL,
  `contact_message` varchar(5120) NOT NULL,
  `com_fields` varchar(5120),
  PRIMARY KEY (`log_id`)
);
