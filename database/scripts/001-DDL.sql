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
 ativo TINYINT(1) DEFAULT 1,
 primary key (id)
);

INSERT INTO SITUACAO (id, nome, ativo) VALUES(1, 'Ativo',1);
INSERT INTO SITUACAO (id, nome, ativo) VALUES(2, 'Inativo',1);
INSERT INTO SITUACAO (id, nome, ativo) VALUES(3, 'Ausente',1);


CREATE SEQUENCE seq_USUARIOS INCREMENT BY 1 START WITH 1;
create table if not exists USUARIOS(
 id int auto_increment not null,
 nome VARCHAR(255) not null,
 senha VARCHAR(255) null,
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
VALUES(0, 'jessica jesus', '$2y$12$DMoyuFQMaRTIScn.mBYipet42Jv5flAfME2XZBrZL3HxTu2jdAq2y', 1, current_timestamp(), 1, 7);

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
    usuario_id INT NULL,
    CONSTRAINT fk_logexcecao_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIOS(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE LOGEXCECAO MODIFY excecao MEDIUMTEXT NOT NULL;

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

CREATE SEQUENCE seq_PERMISSAO INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS PERMISSAO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,             -- Nome interno (ex.: estoque_editar)
    descricao VARCHAR(255) NOT NULL,        -- Descrição legível (ex.: Editar Estoque)
    ativo TINYINT(1) DEFAULT 1             -- Permissão ativa (1) ou desativada (0)
);

CREATE TABLE IF NOT EXISTS PERMISSAO_FUNCAO (
    permissao_id INT NOT NULL,
    funcao_id INT NOT NULL,
    data_atribuicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (permissao_id, funcao_id),
    FOREIGN KEY (permissao_id) REFERENCES PERMISSAO(id) ON DELETE CASCADE,
    FOREIGN KEY (funcao_id) REFERENCES FUNCAO(id) ON DELETE CASCADE
);

CREATE SEQUENCE IF NOT EXISTS seq_PRODUTO INCREMENT BY 1 START WITH 1;
-- Tabela de produto
CREATE TABLE IF NOT EXISTS PRODUTO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    quantidade INT NOT NULL ,
    ativo TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS PRODUTO_ITEM (
    produto_id INT NOT NULL,
    itens_id   INT NOT NULL,
    quantidade INT NOT NULL,
    PRIMARY KEY (produto_id, itens_id),
    CONSTRAINT fk_produto_item_produto FOREIGN KEY (produto_id) REFERENCES PRODUTO(id),
    CONSTRAINT fk_produto_item_itens   FOREIGN KEY (itens_id)   REFERENCES ITENS(id)
);


INSERT INTO projeto_fac.itens(nome, ativo)
values ('AK COMPACT',1),
	 ('ALUMINIO', 1),
	 ('ACETONA',1),
	 ('BLOQUEADOR',1),
	 ('BORRACHA',1),
	 ('C4',1),
	 ('CAPUZ',1),
	 ('CARTAO 01',1),
	 ('CARTAO DE CORRIDA',1),
	 ('CARTAO ILEGIVEL',1),
	 ('CHAPA DE METAL',1),
	 ('CIGARRO DE CANABIS',1),
	 ('CLONAGEM DE COCAINA',1),
	 ('COBRE',1),
	 ('COCAINA',1),
	 ('COCAINA EM PÓ',1),
	 ('COLETE',1),
	 ('CORPO DE PISTOLA',1),
	 ('CORPO DE RIFLE',1),
	 ('CORPO DE SUB',1),
	 ('DINHEIRO LIMPO',1),
	 ('DINHEIRO MOLHADO',1),
	 ('DINHEIRO SUJO',1),
	 ('ENGRENAGEM',1),
	 ('FOLHA DE COCAINA',1),
	 ('FRAGMENTOS DE APRENDIZADO',1),
	 ('GAZUA',1),
	 ('KIT COMUM',1),
	 ('KIT EPICO',1),
	 ('KIT LENDARIO',1),
	 ('KIT RARO',1),
	 ('LONA',1),
	 ('MACONHA',1),
	 ('METAFETAMINA',1),
	 ('MUNIÇÃO DE PISTOLA',1),
	 ('MUNIÇÃO DE RIFLE',1),
	 ('MUNIÇÃO DE SUB',1),
	 ('PAGER',1),
	 ('PEÇAS DE ARMAS',1),
	 ('PENDRIVE SEGURO',1),
	 ('PLACA DE CARRO',1),
	 ('PLACA DE TRANSITO',1),
	 ('PLASTICO',1),
	 ('PLATINA',1),
	 ('POLVORA',1),
	 ('SCORPION',1),
	 ('SUCATA',1),
	 ('TEC-9',1),
	 ('VIDRO',1),
	 ('SERINGA DE CRAK',1),
	 ('CELULAR',1),
	 ('RADIO',1),
	 ('FIO DE COBRE',1),
	 ('PARAFUSO PEQUENO',1),
	 ('TUBO DE PLASTICO',1),
	 ('PISTOLA (GLOCK)',1),
	 ('SCAR-H',1),
	 ('PACOTE DE MACONHA',1),
	 ('MESA DE PRODUÇÃO',1),
	 ('DISTINTIVO',1),
	 ('CARTAO 02',1),
	 ('CARTAO 03',1),
	 ('PASSAPORTE',1),
	 ('FICHA DE CASSINO',1),
	 ('M4',1),
	 ('PISTOLA (FIVE)',1),
	 ('M1911',1),
	 ('AUG',1),
	 ('SIG SAUER',1),
	 ('M16',1),
	 ('ALGEMA',1),
	 ('ROUPA DE MERGULHO',1),
	 ('PARAQUEDAS',1),
	 ('UPGRADE DE PISTOLA',1),
	 ('UPGRADE DE FUZIL',1),
	 ('UPGRADE DE SMG',1),
	 ('MP5',1),
	 ('M32',1),
	 ('PISTOLA (ATI)',1),
	 ('FITA ADESIVA',1),
	 ('QBZ',1),
	 ('G36',1),
	 ('CORDA',1),
	 ('EMPUNHADURA',1);

CREATE SEQUENCE IF NOT EXISTS seq_LANCAMENTO INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS LANCAMENTO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    itens_id   INT NOT NULL,
    tipo ENUM('ENTRADA','SAIDA','TRANSFERENCIA') NOT NULL,
    quantidade INT NOT NULL ,
    usuario_id INT NOT NULL,
    bau_origem_id INT NULL,
    bau_destino_id INT  NULL,
  	observacao  VARCHAR(255) NULL,
  	data_atribuicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_LANCAMENTO_itens FOREIGN KEY (itens_id) REFERENCES ITENS(id) ON DELETE CASCADE,
    CONSTRAINT fk_LANCAMENTO_USUARIO FOREIGN KEY (usuario_id) REFERENCES USUARIOS(id) ON DELETE cascade,
    CONSTRAINT fk_LANCAMENTO_BAU_ORIGEM FOREIGN KEY (bau_origem_id) REFERENCES BAUS(id) ON DELETE CASCADE,
    CONSTRAINT fk_LANCAMENTO_BAU_DESTINO FOREIGN KEY (bau_origem_id) REFERENCES BAUS(id) ON DELETE CASCADE
);

CREATE SEQUENCE IF NOT EXISTS seq_DISCORD_SOLICITACAO INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS DISCORD_SOLICITACAO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('ENTRADA','SAIDA','TRANSFERENCIA') NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pendente',
    discord_message_id VARCHAR(64) NULL,
    discord_channel_id VARCHAR(64) NULL,
    discord_user_id VARCHAR(64) NULL,
    discord_username VARCHAR(255) NULL,
    bau_origem_id INT NULL,
    bau_destino_id INT NULL,
    itens JSON NULL,
    payload JSON NULL,
    observacao TEXT NULL,
    processado_em DATETIME NULL,
    processado_por INT NULL,
    lancamentos_ids JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_discord_solicitacao_status (status),
    INDEX idx_discord_solicitacao_tipo (tipo),
    CONSTRAINT fk_discord_solicitacao_bau_origem FOREIGN KEY (bau_origem_id) REFERENCES BAUS(id) ON DELETE SET NULL,
    CONSTRAINT fk_discord_solicitacao_bau_destino FOREIGN KEY (bau_destino_id) REFERENCES BAUS(id) ON DELETE SET NULL,
    CONSTRAINT fk_discord_solicitacao_usuario FOREIGN KEY (processado_por) REFERENCES USUARIOS(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE SEQUENCE IF NOT EXISTS seq_ORGANIZACAO INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS ORGANIZACAO (
    id INT AUTO_INCREMENT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exemplos iniciais
INSERT INTO ORGANIZACAO (nome, ativo) VALUES
('CARTEL', 1),
('BALLAS', 1),
('HELLS ANGELS', 1),
('THE LOST', 1),
('AURA', 1),
('NEKUTAI', 1),
('VOID', 1),
('SOLO', 1),
('NOX', 1),
('MIDNIGTH', 1),
('HOOLINGANS', 1),
('VERTICE', 1),
('8 ANJO', 1),
('VAGOS', 1),
('NOVA ERA', 1),
('FAMILIES', 1);

CREATE SEQUENCE IF NOT EXISTS seq_FILA_ESPERA INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS FILA_ESPERA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organizacao_id INT NULL,
    nome VARCHAR(255) NOT NULL,
    data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_entrega_estimada DATETIME NULL,
    usuario_id INT NOT NULL,
    pedido TEXT NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pendente',
    dinheiro_limpo DECIMAL(12,2) DEFAULT 0,
    dinheiro_sujo DECIMAL(12,2) DEFAULT 0,
    desconto_aplicado TINYINT(1) DEFAULT 0,
    desconto_valor DECIMAL(12,2) DEFAULT 0,
    desconto_motivo VARCHAR(255) NULL,
    pagamento_tipo VARCHAR(10) DEFAULT 'limpo',
    CONSTRAINT fk_fila_espera_organizacao FOREIGN KEY (organizacao_id) REFERENCES ORGANIZACAO(id) ON DELETE SET NULL,
    CONSTRAINT fk_fila_espera_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIOS(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE SEQUENCE IF NOT EXISTS seq_FILA_ESPERA_ITEM INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS FILA_ESPERA_ITEM (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fila_espera_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    observacao VARCHAR(255) NULL,
    tabela_preco VARCHAR(50) DEFAULT 'padrao',
    preco_unitario_limpo DECIMAL(12,2) DEFAULT 0,
    preco_unitario_sujo DECIMAL(12,2) DEFAULT 0,
    CONSTRAINT fk_fila_item_fila FOREIGN KEY (fila_espera_id) REFERENCES FILA_ESPERA(id) ON DELETE CASCADE,
    CONSTRAINT fk_fila_item_produto FOREIGN KEY (produto_id) REFERENCES PRODUTO(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE SEQUENCE seq_FALTAS INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS FALTAS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_falta DATE NOT NULL,
    motivo TEXT NULL,
    ativo boolean not null default 1,
    registrado_por VARCHAR(255) NULL,
    FOREIGN KEY (usuario_id) REFERENCES USUARIOS(id)
);


ALTER TABLE FILA_ESPERA
    ADD COLUMN IF NOT EXISTS pagamento_tipo VARCHAR(10) DEFAULT 'limpo',
