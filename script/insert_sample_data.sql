USE `bijouterie_chimere`;
INSERT INTO `user` (name, forename, email, password, auth_token) VALUES ('admin', 'admin', 'admin@chimere.net', '$2y$10$pVA9d4Eqtku0cjD6Nc.o8eUU2hxdOmwH1k6UD645eIt8/3rr9j1pK', NULL);
INSERT INTO `job` (name) VALUES ('Chef d\'équipe');
INSERT INTO `occupation` (id_user, id_job) VALUES (1, 1);

INSERT INTO `jewel` (name, description, price, image) VALUES ('Bague', 'Bague en or' , 100, 'uploads/default.svg');