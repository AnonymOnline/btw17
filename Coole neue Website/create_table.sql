CREATE TABLE IF NOT EXISTS `user` (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(256) NOT NULL,
    `password` VARCHAR(256) NOT NULL
);