CREATE TABLE IF NOT EXISTS `{PREFIX}api_gcms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `client_type` varchar(10) NOT NULL DEFAULT 'android',
  `sound` tinyint(1) NOT NULL DEFAULT '1',
  `language` varchar(16) NOT NULL DEFAULT 'eng',
  PRIMARY KEY (`id`)
);