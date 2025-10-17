# Manual — Parte: Anomalias (Detecção e Respostas)

Versão: 1.0  
Data: 17 de outubro de 2025

## Objetivo
Explicar, em linguagem simples, quais anomalias o sistema detecta, como os limites funcionam e o que fazer quando uma anomalia é reportada.

## Fonte de configuração
As regras e limites vêm de config/anomalias.php. Valores relevantes (exemplos do projeto):
- limite_percentual_bau: 0.8 (80% da capacidade do baú)
- limite_padrao_bau: 1000 (capacidade padrão por baú quando não configurado)
- limites_baus: lista (limites por baú, se preenchida)
- limite_quantidade_movimento: 500 (limite de quantidade em janela)
- janela_movimento_dias: 7 (janela para contar movimentações)
- limite_estoque_baixo: 26 (threshold para "estoque baixo")
- limite_estoque_critico: 30 (threshold para "estoque crítico")
- limites_especificos: regras finas por (bau_id,item_id,limite)

## Tipos de anomalias detectadas
1. Capacidade do baú excedida / quase cheia
   - Verifica se o saldo projetado do baú ultrapassa limite (limite específico do baú ou limite_padrao_bau * limite_percentual_bau).
   - Alerta antes de aprovar transferências/entradas que excedam esse limite.

2. Estoque baixo / crítico
   - Itens com saldo menor que limite_estoque_baixo ou limite_estoque_critico são marcados como atenção.
   - Limites específicos por item/baú (limites_especificos) têm prioridade.

3. Movimentação atípica (picos)
   - Se num intervalo de janela_movimento_dias a soma dos movimentos para um item excede limite_quantidade_movimento, gera anomalia.
   - Usado para detectar possíveis erros, fraudes ou operações em lote não esperadas.

4. Limites específicos por par (baú,item)
   - Quando definidos, substituem limites genéricos e disparam alertas se ultrapassados.

## Onde aparecem os avisos
- Tela de solicitações / edição de lançamentos: o sistema mostra alertas antes de aprovar (saldo insuficiente, baú excederá limite, etc).
- Painéis administrativos de anomalias (rota /anomalias) — lista e resumo das anomalias detectadas.
- Notificações (quando configuradas): podem enviar para staff canais definidos (ex.: Discord).

## O que fazer ao receber um alerta
- Conferir o saldo atual do item no baú indicado (Estoque Total → filtrar por baú/item).
- Se o alerta for "estoque baixo/critico": solicitar reposição (entrada) ou transferir de outro baú.
- Se o alerta for "movimentação atípica": validar com o responsável pela operação, checar logs e screenshots; se suspeita de erro, reverter/ajustar lançamentos.
- Se o baú ficará acima do limite: reduzir quantidade, dividir em entradas menores ou ajustar limite se autorizado.

## Procedimento para ajuste de limites
- Ajustes de limite por baú/item devem ser realizados por administrador (editar config ou UI de administração, se disponível).
- Documente justificativa ao alterar limites (auditoria).

## Boas práticas
- Monitorar itens críticos periodicamente com o relatório de Histórico/Estoque.
- Configurar limites específicos para itens sensíveis (medicamentos, consumíveis valiosos).
- Registrar sempre quem aprovou exceções e por quê.

## Como reportar e anexar evidências
Inclua:
- Tipo de anomalia, ID do item, ID do baú.
- Período afetado (datas).
- Mensagens exibidas pelo sistema.
- Prints, logs e nome do usuário que executou a operação.
