CREATE DATABASE bijouterie_chimere;
USE bijouterie_chimere;

CREATE TABLE `fiche_suivi` (
    `id` int NOT NULL AUTO_INCREMENT,
    `type_service` int NOT NULL,
    `prix` int NOT NULL,
    `temps_travail` int NOT NULL,
    `photo` varchar(50),
    `id_client` bigint NOT NULL,
    `en_cours` bool NOT NULL,
    `client_accepte` bool NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `poste` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nom` varchar(20) NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `operation` (
    `id` int NOT NULL AUTO_INCREMENT,
    `prix_materiau` int,
    `poids_materiau` int,
    `type_materiau` varchar(20),
    `commentaire` TEXT NOT NULL,
    `temp_travail` int NOT NULL,
    `photo` varchar(50),
    `id_poste` int NOT NULL,
    `id_fiche_suivi` int NOT NULL,
    `id_prochain_poste` int NOT NULL,
    `date` DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY (`id`)
);

CREATE TABLE `client` (
    `id` int NOT NULL AUTO_INCREMENT,
    `email` varchar(80) NOT NULL UNIQUE,
    `nom` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `bijou` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nom` varchar(50) NOT NULL,
    `commentaire` TEXT NOT NULL,
    `prix` int NOT NULL,
    PRIMARY KEY (`id`)
);

ALTER TABLE `fiche_suivi` ADD CONSTRAINT `fk_client` FOREIGN KEY (`id_client`) REFERENCES `client`(`id`);
ALTER TABLE `operation` ADD CONSTRAINT `fk_post` FOREIGN KEY (`id_poste`) REFERENCES `poste`(`id`);
ALTER TABLE `operation` ADD CONSTRAINT `fk_fiche_suivi` FOREIGN KEY (`id_fiche_suivi`) REFERENCES `fiche_suivi`(`id`);
ALTER TABLE `operation` ADD CONSTRAINT `fk_prochain_poste` FOREIGN KEY (`id_prochain_poste`) REFERENCES `poste`(`id`);

CREATE USER 'bijouterie_chimere'@'%' IDENTIFIED BY 'bijouterie_chimere';
GRANT USAGE ON *.* TO 'bijouterie_chimere'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
GRANT ALL PRIVILEGES ON `bijouterie_chimere`.* TO 'bijouterie_chimere'@'%' WITH GRANT OPTION;

