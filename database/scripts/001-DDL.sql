CREATE DATABASE projeto_fac;
USE projeto_fac;

-- CREATE SEQUENCE seq_perfil INCREMENT BY 1 START WITH 1;
create table if not exists PERFIL(
 id int auto_increment not null,
 nome VARCHAR(255) not null,
 ativo boolean not null default 1,
 primary key (id)
);

INSERT INTO PERFIL (id, nome, ativo) VALUES(0, 'Administrador', 1);

-- CREATE SEQUENCE seq_situacao INCREMENT BY 1 START WITH 1;
create table if not exists SITUACAO(
 id int auto_increment not null,
 nome VARCHAR(255) not null,
 primary key (id)
);

INSERT INTO SITUACAO (id, nome) VALUES(0, 'Ativo');


CREATE SEQUENCE seq_usuario INCREMENT BY 1 START WITH 1;
create table if not exists USUARIOS(
 id int auto_increment not null,
 nome VARCHAR(255) not null,
 senha VARCHAR(255) not null,
 matricula int not null unique,
 data_admissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 situacao_id int not null,
 perfil_id int not null,
 primary key (id),
 FOREIGN KEY (perfil_id) REFERENCES Perfil(id),
 FOREIGN KEY (situacao_id) REFERENCES Situacao(id)
);

INSERT INTO USUARIOS
(id, nome, senha, matricula, data_admissao, situacao_id, perfil_id)
VALUES(0, 'jessica jesus', 'a4P8TcgI1Jzyo', 1, current_timestamp(), 1, 1);

create table if not exists ITENS(
	 id int auto_increment not null,
	 nome VARCHAR(255) not null,
	 ativo boolean not null default 1,
	 primary key (id)
);

create table if not exists BAUS(
	 id int auto_increment not null,
	 nome VARCHAR(255) not null,
	 ativo boolean not null default 1,
	 primary key (id)
);