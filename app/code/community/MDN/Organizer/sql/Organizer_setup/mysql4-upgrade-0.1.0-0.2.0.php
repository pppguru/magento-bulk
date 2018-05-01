<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer=$this;
/* @var $installer Mage_Eav_Model_Entity_Setup */
 
$installer->startSetup();
 
/*
 
//Ajoute les emails template
$installer->run("
 
insert into {$this->getTable('core_email_template')} 
(template_code, template_text, template_type, template_subject)
values
(
	'Notification de tache',
	'Bonjour<p>Cet email est une notification de tache qui vous est affect&eacute;e.</p><p>Entit&eacute; : <a href=\"{{var task_link}}\">{{var entity_name}}</a></p><p>De : {{var author}}</p><p>Sujet : {{var sujet}}</p><p>Commentaires : {{var comments}}</p>',
	2,
	'Notification de tache'
);
 
insert into {$this->getTable('core_email_template')} 
(template_code, template_text, template_type, template_subject)
values
(
	'Task Notification',
	'Hello<p>You have been assigned to a task.</p><p>Entity : <a href=\"{{var task_link}}\">{{var entity_name}}</a></p><p>From : {{var author}}</p><p>Subject : {{var sujet}}</p><p>Comments : {{var comments}}</p>',
	2,
	'Task Notification'
);

    ");
	
*/
 
$installer->endSetup();