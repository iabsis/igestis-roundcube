
-- Updates from version 0.3-stable

TRUNCATE `ROUNDCUBE_messages`;

ALTER TABLE `ROUNDCUBE_messages`
    ADD INDEX `index_index` (`user_id`, `cache_key`, `idx`);

ALTER TABLE `ROUNDCUBE_session` 
    CHANGE `vars` `vars` MEDIUMTEXT NOT NULL;

ALTER TABLE `ROUNDCUBE_contacts`
    ADD INDEX `user_contacts_index` (`user_id`,`email`);

-- Updates from version 0.3.1

/* MySQL bug workaround: http://bugs.mysql.com/bug.php?id=46293 */
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

ALTER TABLE `ROUNDCUBE_messages` DROP FOREIGN KEY `user_id_fk_messages`;
ALTER TABLE `ROUNDCUBE_cache` DROP FOREIGN KEY `user_id_fk_cache`;
ALTER TABLE `ROUNDCUBE_contacts` DROP FOREIGN KEY `user_id_fk_contacts`;
ALTER TABLE `ROUNDCUBE_identities` DROP FOREIGN KEY `user_id_fk_identities`;

ALTER TABLE `ROUNDCUBE_messages` ADD CONSTRAINT `user_id_fk_messages` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ROUNDCUBE_cache` ADD CONSTRAINT `user_id_fk_cache` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ROUNDCUBE_contacts` ADD CONSTRAINT `user_id_fk_contacts` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ROUNDCUBE_identities` ADD CONSTRAINT `user_id_fk_identities` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `ROUNDCUBE_contacts` ALTER `name` SET DEFAULT '';
ALTER TABLE `ROUNDCUBE_contacts` ALTER `firstname` SET DEFAULT '';
ALTER TABLE `ROUNDCUBE_contacts` ALTER `surname` SET DEFAULT '';

ALTER TABLE `ROUNDCUBE_identities` ADD INDEX `user_identities_index` (`user_id`, `del`);
ALTER TABLE `ROUNDCUBE_identities` ADD `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' AFTER `user_id`;

CREATE TABLE `ROUNDCUBE_contactgroups` (
  `contactgroup_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY(`contactgroup_id`),
  CONSTRAINT `user_id_fk_contactgroups` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `contactgroups_user_index` (`user_id`,`del`)
) /*!40000 ENGINE=INNODB */ /*!40101 CHARACTER SET utf8 COLLATE utf8_general_ci */;

CREATE TABLE `ROUNDCUBE_contactgroupmembers` (
  `contactgroup_id` int(10) UNSIGNED NOT NULL,
  `contact_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`contactgroup_id`, `contact_id`),
  CONSTRAINT `contactgroup_id_fk_contactgroups` FOREIGN KEY (`contactgroup_id`)
    REFERENCES `contactgroups`(`contactgroup_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contact_id_fk_contacts` FOREIGN KEY (`contact_id`)
    REFERENCES `contacts`(`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE
) /*!40000 ENGINE=INNODB */;

/*!40014 SET FOREIGN_KEY_CHECKS=1 */;

-- Updates from version 0.4-beta

ALTER TABLE `ROUNDCUBE_users` CHANGE `last_login` `last_login` datetime DEFAULT NULL;
UPDATE `ROUNDCUBE_users` SET `last_login` = NULL WHERE `last_login` = '1000-01-01 00:00:00';