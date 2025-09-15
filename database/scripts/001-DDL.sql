CREATE DATABASE projeto_fac;
USE projeto_fac;

CREATE SEQUENCE seq_PERFIL INCREMENT BY 1 START WITH 1;
create table if not exists PERFIL(
 id int auto_increment not null,
 nome VARCHAR(255) not null,
 ativo boolean not null default 1,
 primary key (id)
);

INSERT INTO PERFIL (id, nome, ativo) VALUES(1, 'El Jefe', 1);
INSERT INTO PERFIL (id, nome, ativo) VALUES(2, 'El Sub-Líder', 1);
INSERT INTO PERFIL (id, nome, ativo) VALUES(3, 'Cabeza', 1);
INSERT INTO PERFIL (id, nome, ativo) VALUES(4, 'El Gerente', 1);
INSERT INTO PERFIL (id, nome, ativo) VALUES(5, 'Sicários', 1);
INSERT INTO PERFIL (id, nome, ativo) VALUES(6, 'Hálcon', 1);
INSERT INTO PERFIL (id, nome, ativo) VALUES(7, 'Asociados', 1);
INSERT INTO PERFIL (id, nome, ativo) VALUES(8, 'Residente', 1);
INSERT INTO PERFIL (id, nome, ativo) VALUES(9, 'Morador', 1);

CREATE SEQUENCE seq_SITUACAO INCREMENT BY 1 START WITH 1;
create table if not exists SITUACAO(
 id int auto_increment not null,
 nome VARCHAR(255) not null,
 primary key (id)
);

INSERT INTO SITUACAO (id, nome) VALUES(1, 'Ativo');
INSERT INTO SITUACAO (id, nome) VALUES(2, 'Inativo');
INSERT INTO SITUACAO (id, nome) VALUES(3, 'Ausente');


CREATE SEQUENCE seq_USUARIOS INCREMENT BY 1 START WITH 1;
create table if not exists USUARIOS(
 id int auto_increment not null,
 nome VARCHAR(255) not null,
 senha VARCHAR(255) not null,
 matricula int not null unique,
 data_admissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 situacao_id int not null,
 perfil_id int not null,
 primary key (id),
 FOREIGN KEY (perfil_id) REFERENCES PERFIL(id),
 FOREIGN KEY (situacao_id) REFERENCES SITUACAO(id)
);

INSERT INTO USUARIOS
(id, nome, senha, matricula, data_admissao, situacao_id, perfil_id)
VALUES(0, 'jessica jesus', 'a4P8TcgI1Jzyo', 1, current_timestamp(), 1, 7);

CREATE SEQUENCE seq_ITENS INCREMENT BY 1 START WITH 1;
create table if not exists ITENS(
	 id int auto_increment not null,
	 nome VARCHAR(255) not null,
	 ativo boolean not null default 1,
	 primary key (id)
);

CREATE SEQUENCE seq_BAUS INCREMENT BY 1 START WITH 1;
create table if not exists BAUS(
	id int auto_increment not null,
	nome VARCHAR(255) not null,
	ativo boolean not null default 1,
	primary key (id)
);

CREATE SEQUENCE seq_LOGCADASTRO INCREMENT BY 1 START WITH 1;
CREATE TABLE LOGCADASTRO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    acao VARCHAR(50) NOT NULL,
    texto TEXT,
    referencia_id INT,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45),
    login VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE SEQUENCE seq_LOGEXCECAO INCREMENT BY 1 START WITH 1;
CREATE TABLE LOGEXCECAO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    excecao VARCHAR(255) NOT NULL,
    usuario_id INT NOT NULL,
    CONSTRAINT fk_logexcecao_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIOS(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE SEQUENCE seq_FUNCAO INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS FUNCAO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    ativo boolean not null default 1
);

INSERT INTO FUNCAO (id, nome) VALUES(1, 'TI/ADMINISTRADOR');
INSERT INTO FUNCAO (id, nome) VALUES(2, 'RH');
INSERT INTO FUNCAO (id, nome) VALUES(3, 'INVESTIGAÇÃO');
INSERT INTO FUNCAO (id, nome) VALUES(4, 'AÇÕES');
INSERT INTO FUNCAO (id, nome) VALUES(5, 'PISTA');
INSERT INTO FUNCAO (id, nome) VALUES(6, 'ORGANIZAÇÃO RESIDENTES');
INSERT INTO FUNCAO (id, nome) VALUES(7, 'VENDAS');
INSERT INTO FUNCAO (id, nome) VALUES(8, 'COMPRAS');
INSERT INTO FUNCAO (id, nome) VALUES(9, 'BAÚ');
INSERT INTO FUNCAO (id, nome) VALUES(10, 'RECRUTADORA');
INSERT INTO FUNCAO (id, nome) VALUES(11, 'RESPONSÁVEL RESIDENTES');
INSERT INTO FUNCAO (id, nome) VALUES(12, 'FARME/METAS');

CREATE TABLE IF NOT EXISTS USUARIO_FUNCAO (
    usuario_id INT NOT NULL,
    funcao_id INT NOT NULL,
    data_atribuicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, funcao_id),
    FOREIGN KEY (usuario_id) REFERENCES USUARIOS(id) ON DELETE CASCADE,
    FOREIGN KEY (funcao_id) REFERENCES FUNCAO(id) ON DELETE CASCADE
);
