drop database if exists blog;
create database if not exists blog character set utf8mb4 collate utf8mb4_unicode_ci;

use blog;

drop table if exists users;
create table if not exists users(
	id INT AUTO_INCREMENT PRIMARY KEY,
	username varchar(50) NOT NULL UNIQUE,
	email varchar(100) NOT NULL UNIQUE,
    nome varchar(100) NOT NULL,
	pass varchar(255) NOT NULL,
	criado timestamp default current_timestamp,
    nivel enum('Owner', 'Admin', 'User'),
    imagem LONGBLOB
);

INSERT INTO users (username, email, nome, pass) VALUES('root', 'root@root.com', 'root', '1234'),
('marcio', 'marcio@root.com', 'marcio', '1234'),
('rui', 'rui@root.com', 'rui', '1234');

drop table if exists posts;
create table if not exists posts(
	id INT AUTO_INCREMENT PRIMARY KEY,
    id_user int,
    title varchar(255),
    post TEXT NOT NULL,
    post_data timestamp default current_timestamp,
    FOREIGN KEY (id_user) REFERENCES users(id)
);


drop table if exists comentarios;
create table if not exists comentarios(
	id INT AUTO_INCREMENT PRIMARY KEY,
    id_user int,
    id_post int,
    id_parent int,
    comentario TEXT NOT NULL,
    post_data timestamp default current_timestamp,
    FOREIGN KEY (id_user) REFERENCES users(id),
    FOREIGN KEY (id_post) REFERENCES posts(id)
);

drop table if exists tokens;
CREATE TABLE if not exists tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    username varchar(50) NOT NULL UNIQUE,
    expira DATETIME NOT NULL
);