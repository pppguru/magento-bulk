<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/department_agent')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
INSERT IGNORE INTO {$this->getTable('aw_hdu3/department_agent')} (user_id, name, email)
  SELECT user_id, CONCAT(firstname,' ', lastname), email FROM {$this->getTable('admin/user')}
;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/customer_note')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `initiator_department_agent_id` INT(10) UNSIGNED NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `note` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `customer_email_UNIQUE` (`customer_email` ASC),
  INDEX `fk_aw_hdu3_customer_note_aw_hdu3_department_agent1_idx` (`initiator_department_agent_id` ASC),
  CONSTRAINT `fk_aw_hdu3_customer_note_aw_hdu3_department_agent1`
    FOREIGN KEY (`initiator_department_agent_id`)
    REFERENCES {$this->getTable('aw_hdu3/department_agent')} (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/department')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `primary_agent_id` INT(10) UNSIGNED NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `store_ids` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `is_active` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sort_order` INT(10) UNSIGNED NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu3_department_aw_hdu3_department_agent1_idx` (`primary_agent_id` ASC),
  CONSTRAINT `fk_aw_hdu3_department_aw_hdu3_department_agent1`
    FOREIGN KEY (`primary_agent_id`)
    REFERENCES {$this->getTable('aw_hdu3/department_agent')} (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket_status')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `is_active` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `font_color` VARCHAR(255) NOT NULL,
  `background_color` VARCHAR(255) NOT NULL,
  `is_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket_status_label')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_id` INT(10) UNSIGNED NOT NULL,
  `value` VARCHAR(100) NOT NULL,
  `store_id` SMALLINT(5) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu3_status_label_aw_hdu3_status1_idx` (`status_id` ASC),
  CONSTRAINT `fk_aw_hdu3_status_label_aw_hdu3_status1`
    FOREIGN KEY (`status_id`)
    REFERENCES {$this->getTable('aw_hdu3/ticket_status')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_status')} VALUES (1,1,'FF0000','',1);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_status_label')} VALUES (1,1,'New',0);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_status')} VALUES (2,1,'FF0000','',1);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_status_label')} VALUES (2,2,'Open',0);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_status')} VALUES (3,1,'000000','',1);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_status_label')} VALUES (3,3,'Closed',0);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_status')} VALUES (4,1,'059E05','',1);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_status_label')} VALUES (4,4,'Waiting for customer',0);
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket_priority')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `is_active` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `font_color` VARCHAR(255) NOT NULL,
  `background_color` VARCHAR(255) NOT NULL,
  `is_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket_priority_label')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `priority_id` INT(10) UNSIGNED NOT NULL,
  `value` VARCHAR(100) NOT NULL,
  `store_id` SMALLINT(5) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu3_priority_label_aw_hdu3_priority1_idx` (`priority_id` ASC),
  CONSTRAINT `fk_aw_hdu3_priority_label_aw_hdu3_priority1`
    FOREIGN KEY (`priority_id`)
    REFERENCES {$this->getTable('aw_hdu3/ticket_priority')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_priority')} VALUES (1,1,'FFFFFF','FF0000',1);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_priority_label')} VALUES (1,1,'URGENT',0);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_priority')} VALUES (2,1,'000000','E9D812',1);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_priority_label')} VALUES (2,2,'ASAP',0);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_priority')} VALUES (3,1,'000000','9AF0FC',1);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_priority_label')} VALUES (3,3,'To Do',0);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_priority')} VALUES (4,1,'000000','',1);
INSERT IGNORE INTO {$this->getTable('aw_hdu3/ticket_priority_label')} VALUES (4,4,'If Time',0);
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_agent_id` INT(10) UNSIGNED NOT NULL,
  `department_id` INT(10) UNSIGNED NOT NULL,
  `uid` VARCHAR(255) NOT NULL,
  `status` INT(10) UNSIGNED NOT NULL,
  `priority` INT(10) UNSIGNED NOT NULL,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `subject` TEXT NOT NULL,
  `order_increment_id` VARCHAR(50) NULL,
  `is_locked` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `locked_by_department_agent_id` INT(10) UNSIGNED NULL,
  `locket_at` DATETIME NULL,
  `store_id` SMALLINT(5) UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu_ticket_aw_hdu_department_agent1_idx` (`department_agent_id` ASC),
  UNIQUE INDEX `uid_UNIQUE` (`uid` ASC),
  INDEX `fk_aw_hdu3_ticket_aw_hdu3_department1_idx` (`department_id` ASC),
  INDEX `fk_aw_hdu3_ticket_aw_hdu3_ticket_status1_idx` (`status` ASC),
  INDEX `fk_aw_hdu3_ticket_aw_hdu3_ticket_priority1_idx` (`priority` ASC),
  CONSTRAINT `fk_aw_hdu_ticket_aw_hdu_department_agent1`
    FOREIGN KEY (`department_agent_id`)
    REFERENCES {$this->getTable('aw_hdu3/department_agent')} (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_aw_hdu3_ticket_aw_hdu3_department1`
    FOREIGN KEY (`department_id`)
    REFERENCES {$this->getTable('aw_hdu3/department')} (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_aw_hdu3_ticket_aw_hdu3_ticket_status1`
    FOREIGN KEY (`status`)
    REFERENCES {$this->getTable('aw_hdu3/ticket_status')} (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_aw_hdu3_ticket_aw_hdu3_ticket_priority1`
    FOREIGN KEY (`priority`)
    REFERENCES {$this->getTable('aw_hdu3/ticket_priority')} (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket_history')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT(10) UNSIGNED NOT NULL,
  `initiator_department_agent_id` INT(10) UNSIGNED NULL,
  `event_type` SMALLINT(5) UNSIGNED NOT NULL,
  `event_data` TEXT NOT NULL,
  `is_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu_ticket_history_aw_hdu_ticket1_idx` (`ticket_id` ASC),
  CONSTRAINT `fk_aw_hdu_ticket_history_aw_hdu_ticket1`
    FOREIGN KEY (`ticket_id`)
    REFERENCES {$this->getTable('aw_hdu3/ticket')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket_history_attachment')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_history_id` INT(10) UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_real_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu3_ticket_history_attachment_aw_hdu3_ticket_history_idx` (`ticket_history_id` ASC),
  CONSTRAINT `fk_aw_hdu3_ticket_history_attachment_aw_hdu3_ticket_history1`
    FOREIGN KEY (`ticket_history_id`)
    REFERENCES {$this->getTable('aw_hdu3/ticket_history')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/gateway_mail')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` VARCHAR(255) CHARACTER SET 'latin1' COLLATE 'latin1_general_cs' NOT NULL,
  `gateway_id` SMALLINT(5) UNSIGNED NOT NULL,
  `from` VARCHAR(255) NOT NULL,
  `to` VARCHAR(255) NOT NULL,
  `status` SMALLINT(5) UNSIGNED NOT NULL,
  `subject` TEXT NOT NULL,
  `body` MEDIUMTEXT NOT NULL,
  `headers` TEXT NOT NULL,
  `content_type` VARCHAR(32) NOT NULL,
  `reject_pattern_id` INT(10) UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8
ROW_FORMAT = DEFAULT;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/gateway_mail_reject_pattern')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `types` VARCHAR(255) NOT NULL,
  `pattern` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
INSERT IGNORE INTO `{$this->getTable('aw_hdu3/gateway_mail_reject_pattern')}` (`id`, `title`, `is_active`, `types`, `pattern`) VALUES
(1, 'Auto-Submitted header', 1, '" . AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern::HEADER_VALUE . "', '/(?i:auto-submitted: )(?i:(?!no)).*/m'),
(2, 'Having X-Spam-Flag header set to YES', 1, '" . AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern::HEADER_VALUE . "', '/x-spam-flag: yes/mi'),
(3, 'X-Spam header', 1, '" . AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern::HEADER_VALUE . "', '/^x-spam: (?!not detected).*$/mi');
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/department_notification')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT(10) UNSIGNED NOT NULL,
  `sender` VARCHAR(255) NOT NULL,
  `to_admin_new_ticket_email` VARCHAR(255) NOT NULL,
  `to_customer_new_ticket_email` VARCHAR(255) NOT NULL,
  `to_customer_new_ticket_by_admin_email` VARCHAR(255) NOT NULL,
  `to_admin_new_reply_email` VARCHAR(255) NOT NULL,
  `to_customer_new_reply_email` VARCHAR(255) NOT NULL,
  `to_primary_agent_reassign_email` VARCHAR(255) NOT NULL,
  `to_new_assignee_reassign_email` VARCHAR(255) NOT NULL,
  `to_customer_ticket_changed` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu3_department_notification_aw_hdu3_department1_idx` (`department_id` ASC),
  CONSTRAINT `fk_aw_hdu3_department_notification_aw_hdu3_department1`
    FOREIGN KEY (`department_id`)
    REFERENCES {$this->getTable('aw_hdu3/department')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/gateway')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT(10) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `protocol` SMALLINT(5) UNSIGNED NOT NULL,
  `email` VARCHAR(255) NULL,
  `host` VARCHAR(255) NOT NULL,
  `login` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `port` INT(4) UNSIGNED NOT NULL,
  `secure_type` SMALLINT(5) UNSIGNED NOT NULL,
  `delete_emails` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_allow_attachment` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  INDEX `fk_aw_hdu3_gateway_aw_hdu3_department1_idx` (`department_id` ASC),
  CONSTRAINT `fk_aw_hdu3_gateway_aw_hdu3_department1`
    FOREIGN KEY (`department_id`)
    REFERENCES {$this->getTable('aw_hdu3/department')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/template')} (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `store_ids` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/gateway_mail_attachment')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mailbox_id` INT(10) UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_real_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu3_mailbox_attachment_aw_hdu3_mailbox1_idx` (`mailbox_id` ASC),
  CONSTRAINT `fk_aw_hdu3_mailbox_attachment_aw_hdu3_mailbox1`
    FOREIGN KEY (`mailbox_id`)
    REFERENCES {$this->getTable('aw_hdu3/gateway_mail')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/department_permission')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT(10) UNSIGNED NOT NULL,
  `department_ids` VARCHAR(255) NOT NULL,
  `admin_role_ids` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu3_department_permissions_aw_hdu3_department1_idx` (`department_id` ASC),
  CONSTRAINT `fk_aw_hdu3_department_permissions_aw_hdu3_department1`
    FOREIGN KEY (`department_id`)
    REFERENCES {$this->getTable('aw_hdu3/department')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/department_agent_link')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` INT(10) UNSIGNED NOT NULL,
  `agent_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_id_agent_id` (`department_id`,`agent_id`),
  INDEX `fk_aw_hdu3_department_agent_link_aw_hdu3_department1_idx` (`department_id` ASC),
  INDEX `fk_aw_hdu3_department_agent_link_aw_hdu3_department_agent1_idx` (`agent_id` ASC),
  CONSTRAINT `fk_aw_hdu3_department_agent_link_aw_hdu3_department1`
    FOREIGN KEY (`department_id`)
    REFERENCES {$this->getTable('aw_hdu3/department')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_aw_hdu3_department_agent_link_aw_hdu3_department_agent1`
    FOREIGN KEY (`agent_id`)
    REFERENCES {$this->getTable('aw_hdu3/department_agent')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket_history_additional')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_history_id` INT(10) UNSIGNED NOT NULL,
  `department_agent_id` INT(10) UNSIGNED NULL,
  `department_id` INT(10) UNSIGNED NOT NULL,
  `status` SMALLINT(5) UNSIGNED NOT NULL,
  `priority` SMALLINT(5) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_aw_hdu3_ticket_history_additional_aw_hdu3_ticket_history_idx` (`ticket_history_id` ASC),
  CONSTRAINT `fk_aw_hdu3_ticket_history_additional_aw_hdu3_ticket_history1`
    FOREIGN KEY (`ticket_history_id`)
    REFERENCES {$this->getTable('aw_hdu3/ticket_history')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
CREATE TABLE IF NOT EXISTS {$this->getTable('aw_hdu3/ticket_message')} (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT(10) UNSIGNED NOT NULL,
  `history_id` INT(10) UNSIGNED NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_id_history_id` (`ticket_id`,`history_id`),
  INDEX `fk_aw_hdu3_ticket_message_aw_hdu3_ticket1_idx` (`ticket_id` ASC),
  INDEX `fk_aw_hdu3_ticket_message_aw_hdu3_ticket_history1_idx` (`history_id` ASC),
  CONSTRAINT `fk_aw_hdu3_ticket_message_aw_hdu3_ticket1`
    FOREIGN KEY (`ticket_id`)
    REFERENCES {$this->getTable('aw_hdu3/ticket')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_aw_hdu3_ticket_message_aw_hdu3_ticket_history1`
    FOREIGN KEY (`history_id`)
    REFERENCES {$this->getTable('aw_hdu3/ticket_history')} (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
");
$installer->endSetup();