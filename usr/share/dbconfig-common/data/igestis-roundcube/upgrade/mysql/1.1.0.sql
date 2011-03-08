
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;

-- Updates from version 0.4-beta

ALTER TABLE `ROUNDCUBE_users` CHANGE `last_login` `last_login` datetime DEFAULT NULL;
UPDATE `ROUNDCUBE_users` SET `last_login` = NULL WHERE `last_login` = '1000-01-01 00:00:00';
