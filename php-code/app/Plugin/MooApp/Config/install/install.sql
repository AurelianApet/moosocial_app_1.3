INSERT INTO `{PREFIX}tasks` (`title`, `plugin`, `timeout`, `processes`, `semaphore`, `started_last`, `started_count`, `completed_last`, `completed_count`, `failure_last`, `failure_count`, `success_last`, `success_count`, `enable`, `class`) VALUES
('Background Notification', 'MooApp', 60, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 'MooApp_Task_Cron');

CREATE TABLE IF NOT EXISTS `{PREFIX}moo_app_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;