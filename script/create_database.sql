CREATE DATABASE bijouterie_chimere;
USE bijouterie_chimere;

CREATE TABLE `request` (
    `id` int NOT NULL AUTO_INCREMENT,
    `type` bool NOT NULL, /* false: creation, true: transformation */
    `jewel_estimation` float DEFAULT NULL,
    `estimated_price` float NOT NULL,
    `estimated_work_time` float NOT NULL,
    `image` varchar(50) NOT NULL,
    `id_client` int NOT NULL,
    `in_progress` bool NOT NULL DEFAULT true,
    `validated` bool NOT NULL DEFAULT false,
    `accepted` bool DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `job` (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(20) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `operation` (
    `id` int NOT NULL AUTO_INCREMENT,
    `description` TEXT,
    `work_time` int,
    `image` varchar(50) NOT NULL DEFAULT '/uploads/default.svg',
    `id_operator` int NOT NULL,
    `id_request` int NOT NULL,
    `date` DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `metal` (
    `id` int NOT NULL AUTO_INCREMENT,
    `type` varchar(20) NOT NULL,
    `price` FLOAT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `gem` (
    `id` int NOT NULL AUTO_INCREMENT,
    `type` varchar(20),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `metal_adding` (
    `id_metal` int NOT NULL,
    `id_operation` int NOT NULL,
    `mass` float NOT NULL,
    PRIMARY KEY (`id_metal`, `id_operation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `gem_adding` (
    `id_gem` int NOT NULL,
    `id_operation` int NOT NULL,
    `mass` float NOT NULL,
    `price` float NOT NULL,
    PRIMARY KEY (`id_gem`, `id_operation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `client` (
    `id` int NOT NULL AUTO_INCREMENT,
    `email` varchar(80) NOT NULL UNIQUE,
    `name` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `jewel` (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `description` TEXT NOT NULL,
    `price` float NOT NULL,
    `image` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
    `id` int NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `forename` varchar(50) NOT NULL,
    `email` varchar(120) NOT NULL,
    `password` varchar(60) NOT NULL,
    `auth_token` varchar(30),
    `id_job` int NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `metal_adding` ADD CONSTRAINT `metal_adding_fk_id_metal` FOREIGN KEY (`id_metal`) REFERENCES metal(`id`);
ALTER TABLE `metal_adding` ADD CONSTRAINT `metal_adding_fk_id_operation` FOREIGN KEY (`id_operation`) REFERENCES `operation`(`id`);
ALTER TABLE `gem_adding` ADD CONSTRAINT `gem_adding_fk_id_gem` FOREIGN KEY (`id_gem`) REFERENCES gem(`id`);
ALTER TABLE `gem_adding` ADD CONSTRAINT `gem_adding_fk_id_operation` FOREIGN KEY (`id_operation`) REFERENCES `operation`(`id`);
ALTER TABLE `user` ADD CONSTRAINT `user_fk_id_job` FOREIGN KEY (`id_job`) REFERENCES `job`(`id`);
ALTER TABLE `request` ADD CONSTRAINT `request_fk_id_client` FOREIGN KEY (`id_client`) REFERENCES `client`(`id`);
ALTER TABLE `operation` ADD CONSTRAINT `operation_fk_id_operator` FOREIGN KEY (`id_operator`) REFERENCES `user`(`id`);
ALTER TABLE `operation` ADD CONSTRAINT `operation_fk_id_request` FOREIGN KEY (`id_request`) REFERENCES `request`(`id`);

CREATE USER 'bijouterie_chimere'@'%' IDENTIFIED BY 'bijouterie_chimere';
GRANT USAGE ON *.* TO 'bijouterie_chimere'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON `bijouterie_chimere`.* TO 'bijouterie_chimere'@'%' WITH GRANT OPTION;
