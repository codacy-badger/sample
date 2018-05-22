ALTER TABLE `answer` DROP INDEX `answer_name`;
ALTER TABLE `answer` CHANGE `answer_name`  `answer_name` text NOT NULL;
