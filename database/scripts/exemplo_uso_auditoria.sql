-- ===================================================
-- EXEMPLO DE USO DO SISTEMA DE AUDITORIA
-- ===================================================
-- Como configurar e usar as variáveis de sessão para auditoria

-- ===================================================
-- 1. CONFIGURAR USUÁRIO DA SESSÃO (SEMPRE ANTES DE OPERAÇÕES)
-- ===================================================

-- Definir usuário atual (substitua pelos dados reais)
SET @current_user_id = 1;
SET @current_user_name = 'Administrador';

-- ===================================================
-- 2. EXEMPLO DE REPASSE COM LOG AUTOMÁTICO
-- ===================================================

-- Definir motivo para update (opcional)
SET @update_reason = 'Correção de valor solicitada pelo vendedor';

-- Fazer um repasse (exemplo)
INSERT INTO repasses (
    vendedor_id,
    vendedor_nome,
    valor_limpo,
    valor_sujo,
    total,
    data_repasse,
    observacoes,
    status
) VALUES (
    123,
    'João Silva',
    1500.00,
    2500.00,
    4000.00,
    NOW(),
    'Repasse semanal - vendas da semana 42/2025',
    1
);

-- O trigger irá automaticamente criar um log na tabela repasses_logs

-- ===================================================
-- 3. EXEMPLO DE ATUALIZAÇÃO COM MOTIVO
-- ===================================================

-- Definir motivo da alteração
SET @update_reason = 'Correção de valor - erro de digitação';

-- Atualizar repasse
UPDATE repasses
SET valor_limpo = 1600.00,
    total = 4100.00,
    observacoes = CONCAT(observacoes, ' - Valor corrigido em ', NOW())
WHERE id = LAST_INSERT_ID();

-- ===================================================
-- 4. EXEMPLO DE REVERSÃO DE REPASSE
-- ===================================================

-- Definir motivo da reversão
SET @delete_reason = 'Repasse cancelado - vendedor solicitou reversão';

-- Para reversões, é melhor usar UPDATE para marcar como inativo
UPDATE repasses
SET status = 0,
    observacoes = CONCAT(observacoes, ' - REVERTIDO: ', @delete_reason)
WHERE id = LAST_INSERT_ID();

-- Ou criar um registro de reversão manual
SET @repasse_id = LAST_INSERT_ID();
INSERT INTO repasses_logs (
    repasse_id,
    tipo_operacao,
    usuario_id,
    usuario_nome,
    vendedor_id,
    vendedor_nome,
    valores_antes,
    valores_depois,
    motivo
) SELECT
    id,
    'REVERSE',
    @current_user_id,
    @current_user_name,
    vendedor_id,
    vendedor_nome,
    JSON_OBJECT(
        'valor_limpo', valor_limpo,
        'valor_sujo', valor_sujo,
        'total', total,
        'status', 1
    ),
    JSON_OBJECT(
        'valor_limpo', valor_limpo,
        'valor_sujo', valor_sujo,
        'total', total,
        'status', 0
    ),
    @delete_reason
FROM repasses
WHERE id = @repasse_id;

-- ===================================================
-- 5. CONSULTAR LOGS DE UM REPASSE ESPECÍFICO
-- ===================================================

-- Ver todos os logs de um repasse
SELECT
    id,
    tipo_operacao as 'Operação',
    usuario_nome as 'Usuário',
    JSON_UNQUOTE(JSON_EXTRACT(valores_antes, '$.total')) as 'Valor Anterior',
    JSON_UNQUOTE(JSON_EXTRACT(valores_depois, '$.total')) as 'Valor Atual',
    motivo as 'Motivo',
    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as 'Data/Hora'
FROM repasses_logs
WHERE repasse_id = @repasse_id
ORDER BY created_at ASC;

-- ===================================================
-- 6. RELATÓRIO DE AUDITORIA POR PERÍODO
-- ===================================================

-- Listar todas as operações do dia atual
SELECT
    rl.tipo_operacao as 'Ação',
    rl.usuario_nome as 'Executado Por',
    rl.vendedor_nome as 'Vendedor',
    JSON_UNQUOTE(JSON_EXTRACT(rl.valores_depois, '$.total')) as 'Valor',
    rl.motivo as 'Motivo',
    DATE_FORMAT(rl.created_at, '%H:%i:%s') as 'Hora'
FROM repasses_logs rl
WHERE DATE(rl.created_at) = CURDATE()
ORDER BY rl.created_at DESC;

-- ===================================================
-- 7. RELATÓRIO DE ATIVIDADES POR USUÁRIO
-- ===================================================

-- Ver atividades de um usuário específico
SELECT
    COUNT(*) as 'Total Operações',
    COUNT(CASE WHEN tipo_operacao = 'CREATE' THEN 1 END) as 'Criados',
    COUNT(CASE WHEN tipo_operacao = 'UPDATE' THEN 1 END) as 'Alterados',
    COUNT(CASE WHEN tipo_operacao = 'REVERSE' THEN 1 END) as 'Revertidos',
    SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(valores_depois, '$.total')) AS DECIMAL(15,2))) as 'Valor Total Movimentado',
    DATE_FORMAT(MIN(created_at), '%d/%m/%Y') as 'Primeira Atividade',
    DATE_FORMAT(MAX(created_at), '%d/%m/%Y') as 'Última Atividade'
FROM repasses_logs
WHERE usuario_id = @current_user_id
AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH);

-- ===================================================
-- 8. CALCULAR MÉTRICAS DO MÊS ATUAL
-- ===================================================

-- Executar cálculo de métricas
CALL sp_calcular_metricas_periodo(
    'MENSAL',
    DATE_FORMAT(CURDATE(), '%Y-%m-01'),
    LAST_DAY(CURDATE())
);

-- Ver resultado
SELECT * FROM financeiro_metricas
WHERE periodo_tipo = 'MENSAL'
AND periodo_inicio = DATE_FORMAT(CURDATE(), '%Y-%m-01')
AND periodo_fim = LAST_DAY(CURDATE());

-- ===================================================
-- 9. DASHBOARD RÁPIDO
-- ===================================================

-- KPIs principais
SELECT * FROM v_dashboard_financeiro;

-- Últimas atividades
SELECT
    CASE
        WHEN tipo_operacao = 'CREATE' THEN '✅ Repasse Criado'
        WHEN tipo_operacao = 'UPDATE' THEN '✏️ Repasse Alterado'
        WHEN tipo_operacao = 'DELETE' THEN '❌ Repasse Excluído'
        WHEN tipo_operacao = 'REVERSE' THEN '↩️ Repasse Revertido'
    END as acao,
    CONCAT(usuario_nome, ' → ', vendedor_nome) as movimento,
    CONCAT('R$ ', FORMAT(JSON_UNQUOTE(JSON_EXTRACT(valores_depois, '$.total')), 2)) as valor,
    DATE_FORMAT(created_at, '%d/%m %H:%i') as quando
FROM repasses_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY created_at DESC
LIMIT 10;

-- ===================================================
-- 10. LIMPEZA E MANUTENÇÃO (EXECUTAR PERIODICAMENTE)
-- ===================================================

-- Recalcular todas as métricas do ano
CALL sp_recalcular_metricas_ano();

-- Limpar logs muito antigos (opcional - manter apenas últimos 2 anos)
-- DELETE FROM repasses_logs WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 2 YEAR);

-- Verificar integridade dos dados
SELECT
    COUNT(*) as total_repasses,
    (SELECT COUNT(*) FROM repasses_logs WHERE tipo_operacao = 'CREATE') as logs_criacao,
    CASE
        WHEN COUNT(*) = (SELECT COUNT(*) FROM repasses_logs WHERE tipo_operacao = 'CREATE')
        THEN '✅ Integridade OK'
        ELSE '⚠️ Verificar logs'
    END as status_integridade
FROM repasses;

-- ===================================================
-- EXEMPLO DE IMPLEMENTAÇÃO NO PHP/LARAVEL
-- ===================================================

/*
// No Controller ou Service, antes de operações:

DB::statement("SET @current_user_id = ?", [auth()->id()]);
DB::statement("SET @current_user_name = ?", [auth()->user()->name]);

// Para updates com motivo:
DB::statement("SET @update_reason = ?", [$motivo]);

// Para deletes com motivo:
DB::statement("SET @delete_reason = ?", [$motivo]);

// Exemplo de uso em um método:
public function fazerRepasse($vendedorId, $dados, $motivo = null) {
    // Configurar sessão
    DB::statement("SET @current_user_id = ?", [auth()->id()]);
    DB::statement("SET @current_user_name = ?", [auth()->user()->name]);

    if ($motivo) {
        DB::statement("SET @update_reason = ?", [$motivo]);
    }

    // Fazer o repasse - trigger fará o log automaticamente
    $repasse = Repasse::create($dados);

    return $repasse;
}

// Para consultar logs:
public function consultarLogs($repasseId) {
    return DB::select("
        SELECT * FROM v_auditoria_repasses
        WHERE repasse_id = ?
        ORDER BY created_at ASC
    ", [$repasseId]);
}
*/

-- ===================================================
-- LEMBRETE IMPORTANTE:
-- ===================================================

/*
SEMPRE definir as variáveis de sessão antes das operações:
SET @current_user_id = [ID_DO_USUARIO];
SET @current_user_name = '[NOME_DO_USUARIO]';
SET @update_reason = '[MOTIVO_DA_ALTERACAO]'; (opcional)
SET @delete_reason = '[MOTIVO_DA_EXCLUSAO]'; (opcional)

Isso garante que os logs tenham as informações corretas!
*/
