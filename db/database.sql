CREATE TABLE `poker_vote`(
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `project_id` INT(10) UNSIGNED NOT NULL,
  `vote` INT(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poker_vote_user_project` (`user_id`, `project_id`),
  CONSTRAINT `poker_vote_user_id` FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `poker_vote_project_id` FOREIGN KEY (`project_id`) REFERENCES `issue`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
);
