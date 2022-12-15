CREATE DATABASE bijouterie_chimere;
USE bijouterie_chimere;

CREATE TABLE `request` (
    `id` int NOT NULL AUTO_INCREMENT,
    `type` int NOT NULL,
    `estimated_price` int NOT NULL,
    `estimated_work_time` int NOT NULL,
    `image` varchar(50),
    `id_client` int NOT NULL,
    `in_progess` bool NOT NULL,
    `accepted` bool NOT NULL,
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
    `image` varchar(50),
    `id_job` int NOT NULL,
    `id_request` int NOT NULL,
    `date` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `material` (
    `id` int NOT NULL AUTO_INCREMENT,
    `price` float NOT NULL,
    `type` varchar(20),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `material_adding` (
    `id_material` int NOT NULL,
    `id_operation` int NOT NULL,
    `mass` float NOT NULL,
    PRIMARY KEY (`id_material`, `id_operation`)
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
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `user` (
     `id` int NOT NULL AUTO_INCREMENT,
     `name` varchar(50) NOT NULL,
     `forename` varchar(50) NOT NULL,
     `email` varchar(120) NOT NULL,
     `password` varchar(60) NOT NULL,
     `auth_token` varchar(30),
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `occupation` (
    `id_user` int NOT NULL,
    `id_job` int NOT NULL,
    PRIMARY KEY (`id_user`,`id_job`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `material_adding` ADD CONSTRAINT `material_adding_fk_id_materiau` FOREIGN KEY (`id_material`) REFERENCES `material`(`id`);
ALTER TABLE `material_adding` ADD CONSTRAINT `material_adding_fk_id_operation` FOREIGN KEY (`id_operation`) REFERENCES `operation`(`id`);
ALTER TABLE `occupation` ADD CONSTRAINT `occupation_fk_id_user` FOREIGN KEY (`id_user`) REFERENCES `user`(`id`);
ALTER TABLE `occupation` ADD CONSTRAINT `occupation_fk_id_job` FOREIGN KEY (`id_job`) REFERENCES `job`(`id`);
ALTER TABLE `request` ADD CONSTRAINT `request_fk_id_client` FOREIGN KEY (`id_client`) REFERENCES `client`(`id`);
ALTER TABLE `operation` ADD CONSTRAINT `operation_fk_id_job` FOREIGN KEY (`id_job`) REFERENCES `job`(`id`);
ALTER TABLE `operation` ADD CONSTRAINT `operation_fk_id_request` FOREIGN KEY (`id_request`) REFERENCES `request`(`id`);

CREATE USER 'bijouterie_chimere'@'%' IDENTIFIED BY 'bijouterie_chimere';
GRANT USAGE ON *.* TO 'bijouterie_chimere'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON `bijouterie_chimere`.* TO 'bijouterie_chimere'@'%' WITH GRANT OPTION;
