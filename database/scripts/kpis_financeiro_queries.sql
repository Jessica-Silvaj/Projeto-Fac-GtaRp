-- ===================================================
-- QUERIES PARA KPIs FINANCEIROS - DASHBOARD
-- ===================================================
-- Criado em: 26/10/2025
-- Uso: Consultas para exibir no dashboard financeiro

-- ===================================================
-- 1. MÉTRICAS DO MÊS ATUAL
-- ===================================================

-- Total repassado no mês atual
SELECT
    COUNT(*) as total_repasses,
    COALESCE(SUM(valor_limpo), 0) as total_limpo,
    COALESCE(SUM(valor_sujo), 0) as total_sujo,
    COALESCE(SUM(total), 0) as total_geral,
    COALESCE(AVG(total), 0) as media_repasse
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1;

-- ===================================================
-- 2. COMPARATIVO COM MÊS ANTERIOR
-- ===================================================

-- Crescimento percentual
SELECT
    mes_atual.total_geral as valor_mes_atual,
    mes_anterior.total_geral as valor_mes_anterior,
    CASE
        WHEN mes_anterior.total_geral > 0 THEN
            ROUND(((mes_atual.total_geral - mes_anterior.total_geral) / mes_anterior.total_geral) * 100, 2)
        ELSE 0
    END as crescimento_percentual,

    mes_atual.total_repasses as repasses_mes_atual,
    mes_anterior.total_repasses as repasses_mes_anterior,
    CASE
        WHEN mes_anterior.total_repasses > 0 THEN
            ROUND(((mes_atual.total_repasses - mes_anterior.total_repasses) / mes_anterior.total_repasses) * 100, 2)
        ELSE 0
    END as crescimento_repasses_percentual
FROM
    (SELECT
        COUNT(*) as total_repasses,
        COALESCE(SUM(total), 0) as total_geral
     FROM repasses
     WHERE MONTH(data_repasse) = MONTH(CURDATE())
     AND YEAR(data_repasse) = YEAR(CURDATE())
     AND status = 1) as mes_atual
CROSS JOIN
    (SELECT
        COUNT(*) as total_repasses,
        COALESCE(SUM(total), 0) as total_geral
     FROM repasses
     WHERE MONTH(data_repasse) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
     AND YEAR(data_repasse) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
     AND status = 1) as mes_anterior;

-- ===================================================
-- 3. RANKING DE VENDEDORES (TOP 10)
-- ===================================================

-- Ranking por valor total repassado no mês
SELECT
    vendedor_nome,
    COUNT(*) as total_repasses,
    COALESCE(SUM(valor_limpo), 0) as total_limpo,
    COALESCE(SUM(valor_sujo), 0) as total_sujo,
    COALESCE(SUM(total), 0) as total_geral,
    COALESCE(AVG(total), 0) as media_repasse,
    DATE_FORMAT(MAX(data_repasse), '%d/%m/%Y') as ultimo_repasse
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1
GROUP BY vendedor_id, vendedor_nome
ORDER BY total_geral DESC
LIMIT 10;

-- ===================================================
-- 4. EVOLUÇÃO DIÁRIA DO MÊS ATUAL
-- ===================================================

-- Repasses por dia no mês atual (para gráfico)
SELECT
    DATE_FORMAT(data_repasse, '%d/%m') as dia,
    DATE_FORMAT(data_repasse, '%Y-%m-%d') as data_completa,
    COUNT(*) as quantidade_repasses,
    COALESCE(SUM(valor_limpo), 0) as total_limpo,
    COALESCE(SUM(valor_sujo), 0) as total_sujo,
    COALESCE(SUM(total), 0) as total_dia
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1
GROUP BY DATE(data_repasse)
ORDER BY data_repasse ASC;

-- ===================================================
-- 5. DISTRIBUIÇÃO POR TIPO DE VALOR
-- ===================================================

-- Proporção limpo vs sujo
SELECT
    COALESCE(SUM(valor_limpo), 0) as total_limpo,
    COALESCE(SUM(valor_sujo), 0) as total_sujo,
    COALESCE(SUM(total), 0) as total_geral,
    CASE
        WHEN SUM(total) > 0 THEN ROUND((SUM(valor_limpo) / SUM(total)) * 100, 2)
        ELSE 0
    END as percentual_limpo,
    CASE
        WHEN SUM(total) > 0 THEN ROUND((SUM(valor_sujo) / SUM(total)) * 100, 2)
        ELSE 0
    END as percentual_sujo
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1;

-- ===================================================
-- 6. MÉTRICAS DE TEMPO E FREQUÊNCIA
-- ===================================================

-- Tempo médio entre repasses por vendedor
SELECT
    vendedor_nome,
    COUNT(*) as total_repasses,
    DATE_FORMAT(MIN(data_repasse), '%d/%m/%Y') as primeiro_repasse,
    DATE_FORMAT(MAX(data_repasse), '%d/%m/%Y') as ultimo_repasse,
    CASE
        WHEN COUNT(*) > 1 THEN
            ROUND(DATEDIFF(MAX(data_repasse), MIN(data_repasse)) / (COUNT(*) - 1), 1)
        ELSE 0
    END as dias_media_entre_repasses
FROM repasses
WHERE status = 1
AND data_repasse >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) -- Últimos 3 meses
GROUP BY vendedor_id, vendedor_nome
HAVING COUNT(*) > 1
ORDER BY total_repasses DESC;

-- ===================================================
-- 7. ALERTAS E INDICADORES
-- ===================================================

-- Vendedores sem repasse há mais de 30 dias
SELECT
    rv.vendedor_nome,
    DATE_FORMAT(MAX(rv.data_repasse), '%d/%m/%Y') as ultimo_repasse,
    DATEDIFF(CURDATE(), MAX(rv.data_repasse)) as dias_sem_repasse,
    COUNT(*) as total_repasses_historico
FROM repasses rv
WHERE rv.status = 1
GROUP BY rv.vendedor_id, rv.vendedor_nome
HAVING DATEDIFF(CURDATE(), MAX(rv.data_repasse)) > 30
ORDER BY dias_sem_repasse DESC;

-- Repasses de valores muito altos (acima da média + 2 desvios padrão)
SELECT
    vendedor_nome,
    total as valor_repasse,
    DATE_FORMAT(data_repasse, '%d/%m/%Y %H:%i') as data_repasse,
    observacoes
FROM repasses r
WHERE r.status = 1
AND r.total > (
    SELECT AVG(total) + (2 * STDDEV(total))
    FROM repasses
    WHERE status = 1
    AND data_repasse >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
)
AND r.data_repasse >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
ORDER BY r.total DESC;

-- ===================================================
-- 8. RESUMO EXECUTIVO (PARA CARDS DO DASHBOARD)
-- ===================================================

-- Cards principais do dashboard
SELECT
    'MES_ATUAL' as metric_type,
    COUNT(*) as value,
    'Repasses este mês' as label,
    'success' as color,
    'fas fa-chart-line' as icon
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1

UNION ALL

SELECT
    'VALOR_MES_ATUAL' as metric_type,
    COALESCE(SUM(total), 0) as value,
    'Total repassado (R$)' as label,
    'primary' as color,
    'fas fa-dollar-sign' as icon
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1

UNION ALL

SELECT
    'VENDEDORES_ATIVOS' as metric_type,
    COUNT(DISTINCT vendedor_id) as value,
    'Vendedores ativos' as label,
    'info' as color,
    'fas fa-users' as icon
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1

UNION ALL

SELECT
    'MEDIA_REPASSE' as metric_type,
    COALESCE(AVG(total), 0) as value,
    'Média por repasse (R$)' as label,
    'warning' as color,
    'fas fa-calculator' as icon
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1;

-- ===================================================
-- 9. DADOS PARA GRÁFICOS
-- ===================================================

-- Dados para gráfico de linha (evolução mensal - últimos 6 meses)
SELECT
    DATE_FORMAT(data_repasse, '%m/%Y') as mes_ano,
    DATE_FORMAT(data_repasse, '%Y-%m') as periodo_order,
    COUNT(*) as quantidade,
    COALESCE(SUM(total), 0) as valor_total
FROM repasses
WHERE data_repasse >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
AND status = 1
GROUP BY YEAR(data_repasse), MONTH(data_repasse)
ORDER BY YEAR(data_repasse), MONTH(data_repasse);

-- Dados para gráfico de pizza (distribuição limpo vs sujo)
SELECT
    'Valor Limpo' as categoria,
    COALESCE(SUM(valor_limpo), 0) as valor,
    '#28a745' as cor
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1

UNION ALL

SELECT
    'Valor Sujo' as categoria,
    COALESCE(SUM(valor_sujo), 0) as valor,
    '#dc3545' as cor
FROM repasses
WHERE MONTH(data_repasse) = MONTH(CURDATE())
AND YEAR(data_repasse) = YEAR(CURDATE())
AND status = 1;

-- ===================================================
-- 10. QUERIES DE AUDITORIA RESUMIDAS
-- ===================================================

-- Últimas atividades (para timeline)
SELECT
    rl.tipo_operacao,
    rl.usuario_nome,
    rl.vendedor_nome,
    JSON_UNQUOTE(JSON_EXTRACT(rl.valores_depois, '$.total')) as valor,
    rl.motivo,
    DATE_FORMAT(rl.created_at, '%d/%m/%Y %H:%i:%s') as data_hora,
    CASE
        WHEN rl.tipo_operacao = 'CREATE' THEN 'success'
        WHEN rl.tipo_operacao = 'UPDATE' THEN 'warning'
        WHEN rl.tipo_operacao = 'DELETE' THEN 'danger'
        WHEN rl.tipo_operacao = 'REVERSE' THEN 'info'
        ELSE 'secondary'
    END as badge_color
FROM repasses_logs rl
ORDER BY rl.created_at DESC
LIMIT 10;

-- Estatísticas de auditoria
SELECT
    COUNT(*) as total_operacoes,
    SUM(CASE WHEN tipo_operacao = 'CREATE' THEN 1 ELSE 0 END) as criados,
    SUM(CASE WHEN tipo_operacao = 'UPDATE' THEN 1 ELSE 0 END) as alterados,
    SUM(CASE WHEN tipo_operacao = 'DELETE' THEN 1 ELSE 0 END) as excluidos,
    SUM(CASE WHEN tipo_operacao = 'REVERSE' THEN 1 ELSE 0 END) as revertidos,
    COUNT(DISTINCT usuario_id) as usuarios_ativos,
    DATE_FORMAT(MIN(created_at), '%d/%m/%Y') as primeira_operacao,
    DATE_FORMAT(MAX(created_at), '%d/%m/%Y') as ultima_operacao
FROM repasses_logs
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH);

-- ===================================================
-- OBSERVAÇÕES DE USO:
-- ===================================================

/*
1. Execute essas queries conforme necessário no dashboard
2. Para performance, considere criar uma tabela de cache para métricas calculadas
3. Use LIMIT nas queries quando necessário para evitar sobrecarga
4. Considere criar índices adicionais se as consultas ficarem lentas
5. As queries estão otimizadas para MySQL/MariaDB

SUGESTÃO DE IMPLEMENTAÇÃO NO CONTROLLER:
- Crie métodos específicos para cada grupo de métricas
- Use cache Redis/Memcached para queries pesadas
- Implemente refresh automático a cada X minutos
- Adicione paginação em relatórios grandes
*/
