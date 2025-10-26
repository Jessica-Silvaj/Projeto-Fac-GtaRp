CREATE SEQUENCE seq_REPASSES INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS REPASSES (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendedor_id INT NOT NULL,
    usuario_repasse_id INT NOT NULL,
    valor_limpo DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    valor_sujo DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    valor_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    observacoes TEXT NULL,
    data_repasse TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo','desfeito') NOT NULL DEFAULT 'ativo',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (vendedor_id) REFERENCES USUARIOS(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_repasse_id) REFERENCES USUARIOS(id) ON DELETE CASCADE
);

CREATE SEQUENCE seq_REPASSE_VENDAS INCREMENT BY 1 START WITH 1;
CREATE TABLE IF NOT EXISTS REPASSE_VENDAS (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repasse_id INT NOT NULL,
    fila_espera_id INT NOT NULL,
    valor_limpo DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    valor_sujo DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (repasse_id) REFERENCES REPASSES(id) ON DELETE CASCADE,
    FOREIGN KEY (fila_espera_id) REFERENCES FILA_ESPERA(id) ON DELETE CASCADE
);

CREATE INDEX idx_repasses_vendedor ON REPASSES (vendedor_id);
CREATE INDEX idx_repasses_usuario_repasse ON REPASSES (usuario_repasse_id);
CREATE INDEX idx_repasses_status ON REPASSES (status);
CREATE INDEX idx_repasses_data ON REPASSES (data_repasse);
CREATE INDEX idx_repasse_vendas_repasse ON REPASSE_VENDAS (repasse_id);
CREATE INDEX idx_repasse_vendas_fila_espera ON REPASSE_VENDAS (fila_espera_id);

CREATE OR REPLACE VIEW VIEW_SALDOS_USUARIOS AS
SELECT
    u.id,
    u.nome,
    COALESCE(SUM(fe.dinheiro_limpo), 0) as vendas_limpo,
    COALESCE(SUM(fe.dinheiro_sujo), 0) as vendas_sujo,
    COALESCE(SUM(rr.valor_limpo), 0) as repasses_recebidos_limpo,
    COALESCE(SUM(rr.valor_sujo), 0) as repasses_recebidos_sujo,
    COALESCE(SUM(rf.valor_limpo), 0) as repasses_feitos_limpo,
    COALESCE(SUM(rf.valor_sujo), 0) as repasses_feitos_sujo,
    (COALESCE(SUM(fe.dinheiro_limpo), 0) + COALESCE(SUM(rr.valor_limpo), 0) - COALESCE(SUM(rf.valor_limpo), 0)) as saldo_limpo,
    (COALESCE(SUM(fe.dinheiro_sujo), 0) + COALESCE(SUM(rr.valor_sujo), 0) - COALESCE(SUM(rf.valor_sujo), 0)) as saldo_sujo,
    (COALESCE(SUM(fe.dinheiro_limpo + fe.dinheiro_sujo), 0) +
     COALESCE(SUM(rr.valor_limpo + rr.valor_sujo), 0) -
     COALESCE(SUM(rf.valor_limpo + rf.valor_sujo), 0)) as saldo_total
FROM USUARIOS u
LEFT JOIN FILA_ESPERA fe ON u.id = fe.usuario_id AND fe.status = 'concluido'
LEFT JOIN REPASSES rr ON u.id = rr.usuario_repasse_id AND rr.status = 'ativo'
LEFT JOIN REPASSES rf ON u.id = rf.vendedor_id AND rf.status = 'ativo'
GROUP BY u.id, u.nome
HAVING saldo_total > 0
ORDER BY saldo_total DESC;

-- SISTEMA DE REPASSES FINANCEIROS
-- Controla repasses de dinheiro limpo e sujo entre usuários
-- Permite desfazer repasses (status = 'desfeito')
-- Histórico completo preservado para auditoria
