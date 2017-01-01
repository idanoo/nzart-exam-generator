-- Creates an empty DB for your own questions/etc.
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_time` INT(11) NOT NULL,
  `userdata_username` varchar(256) NOT NULL,
  `userdata_password` varchar(256) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id_UNIQUE` (`user_id`),
  UNIQUE KEY `userdata_username_UNIQUE` (`userdata_username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `question` (
  `question_id` INT(11) NOT NULL AUTO_INCREMENT,
  `question_time` INT(11) NOT NULL,
  `questiondata_number` VARCHAR(10) DEFAULT NULL,
  `questiondata_content` TEXT DEFAULT NULL,
  `questiondata_image` TEXT DEFAULT NULL,
  PRIMARY KEY (`question_id`),
  UNIQUE KEY `question_id_UNIQUE` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `answer` (
  `answer_id` INT(11) NOT NULL AUTO_INCREMENT,
  `answer_time` INT(11) NOT NULL,
  `answerdata_question` INT(11) NOT NULL,
  `answerdata_content` MEDIUMTEXT DEFAULT NULL,
  `answerdata_correct` INT(1) DEFAULT 0,
  PRIMARY KEY (`answer_id`),
  UNIQUE KEY `answer_id_UNIQUE` (`answer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `result` (
  `result_id` INT(11) NOT NULL AUTO_INCREMENT,
  `result_time` INT(11) NOT NULL,
  `resultdata_user` INT(11) NOT NULL,
  `resultdata_result` MEDIUMTEXT DEFAULT NULL,
  `resultdata_score` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`result_id`),
  UNIQUE KEY `result_id_UNIQUE` (`result_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;