-- RoundCube Webmail update script for MySQL databases

-- Updates from version 0.1-stable

TRUNCATE TABLE `ROUNDCUBE_messages`;

ALTER TABLE `ROUNDCUBE_messages`
  DROP INDEX `idx`,
  DROP INDEX `uid`;

ALTER TABLE `ROUNDCUBE_cache`
  DROP INDEX `cache_key`,
  DROP INDEX `session_id`,
  ADD INDEX `user_cache_index` (`user_id`,`cache_key`);

ALTER TABLE `ROUNDCUBE_users`
    ADD INDEX `username_index` (`username`),
    ADD INDEX `alias_index` (`alias`);

-- Updates from version 0.1.1

ALTER TABLE `ROUNDCUBE_identities`
    MODIFY `signature` text, 
    MODIFY `bcc` varchar(128) NOT NULL DEFAULT '', 
    MODIFY `reply-to` varchar(128) NOT NULL DEFAULT '', 
    MODIFY `organization` varchar(128) NOT NULL DEFAULT '',
    MODIFY `name` varchar(128) NOT NULL, 
    MODIFY `email` varchar(128) NOT NULL; 

-- Updates from version 0.2-alpha

ALTER TABLE `ROUNDCUBE_messages`
    ADD INDEX `created_index` (`created`);

-- Updates from version 0.2-beta (InnoDB required)

ALTER TABLE `ROUNDCUBE_cache`
    DROP `session_id`;

ALTER TABLE `ROUNDCUBE_session`
    ADD INDEX `changed_index` (`changed`);

ALTER TABLE `ROUNDCUBE_cache`
    ADD INDEX `created_index` (`created`);

ALTER TABLE `ROUNDCUBE_users`
    CHANGE `language` `language` varchar(5);

ALTER TABLE `ROUNDCUBE_cache` ENGINE=InnoDB;
ALTER TABLE `ROUNDCUBE_session` ENGINE=InnoDB;
ALTER TABLE `ROUNDCUBE_messages` ENGINE=InnoDB;
ALTER TABLE `ROUNDCUBE_users` ENGINE=InnoDB;
ALTER TABLE `ROUNDCUBE_contacts` ENGINE=InnoDB;
ALTER TABLE `ROUNDCUBE_identities` ENGINE=InnoDB;


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
