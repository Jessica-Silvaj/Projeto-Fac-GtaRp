# Manual — Parte: Solicitações (Integração Discord)

Versão: 1.0  
Data: 17 de outubro de 2025

## Objetivo
Explicar em linguagem simples como visualizar, ajustar, aprovar e rejeitar solicitações recebidas via Discord, quais validações o sistema faz e o que o usuário deve checar antes de aprovar.

## Onde acessar
Menu: Controle de Baú → Solicitações do Discord

## Status possíveis
- Pendente — aguardando revisão.
- Em ajuste — foi editada pela equipe; aguarda nova revisão.
- Aprovada — processada e convertida em lançamentos.
- Rejeitada — solicitante foi informado do motivo.

## O que vem na solicitação
- Tipo: ENTRADA | SAIDA | TRANSFERENCIA
- Itens: lista com objetos contendo itens_id, quantidade, bau_origem_id, bau_destino_id (pode vir incompleto; editar antes de aprovar)
- Observação: texto livre
- Anexos: imagens/arquivos enviados no Discord
- Metadados Discord: discord_user_id, discord_username, discord_message_id (usados para notificar)

## Regras de validação (sistema)
- Cada linha deve ter item válido (itens_id) e quantidade >= 1.
- Para ENTRADA: exige baú de destino.
- Para SAIDA: exige baú de origem.
- Para TRANSFERENCIA: exige origem e destino.
- Antes de aprovar, o sistema verifica:
  - Saldo no baú de origem para SAIDA/TRANSFERENCIA — bloqueia se insuficiente.
  - Capacidade do baú de destino (limites configuráveis) — aviso ou bloqueio se exceder.
- Ao aprovar, cada item é transformado em um lançamento (LancamentoService::salvar) dentro de transação; se algo falhar, tudo é revertido.
- Ao rejeitar, é obrigatório informar motivo.

## Avisos gerados automaticamente (exibidos na tela de edição)
- "Saldo disponível X no báu de origem Y, solicitado Z" — saldo insuficiente.
- "Informe o báu de origem/destino para o item X" — faltam campos obrigatórios.
- "Báu de destino Z excederá o limite (projeção)" — capacidade do baú será ultrapassada.
- Alertas aparecem antes de aprovar para orientar ajustes.

## Comportamento da interface
- Lista paginada de solicitações com filtros por status/tipo/busca.
- Tela de edição mostra resumo, anexos com visualização (modal para imagens) e tabela editável de itens.
- É possível adicionar/remover linhas e salvar ajustes sem aprovar imediatamente.
- Botões de ação:
  - Ajustar/Salvar — grava alterações e status vira "Em ajuste".
  - Aprovar e lançar — valida e gera lançamentos; solicitações aprovadas ficam como tal.
  - Rejeitar — abre modal para informar motivo; registra rejeição e notifica Discord.

## O que o Discord recebe ao aprovar/rejeitar
- Mensagem de confirmação com emoji (aprovada/rejeitada), lista resumida de itens, responsável, horário, observações e link para mensagem original (quando disponível).
- Se anexos foram enviados, a notificação menciona existência de anexos.
- Ao rejeitar, a mensagem inclui o motivo e, se possível, menciona o solicitante.

## Passo a passo recomendado antes de aprovar
1. Verifique que cada item possui itens_id e quantidade válida.
2. Confirme baú de origem (para SAIDA/TRANSFERENCIA) e baú de destino (para ENTRADA/TRANSFERENCIA).
3. Cheque alertas exibidos: saldo insuficiente ou limite do baú.
4. Realize ajustes (salvar) se necessário.
5. Ao aprovar, confirme pelo modal; o sistema processa tudo em transação.
6. Após aprovação, verifique IDs dos lançamentos gerados na mensagem de sucesso.

## O que fazer ao encontrar problemas
- Se falha na aprovação por validação: corrija os itens/baús/quantidades conforme a mensagem exibida.
- Se comportamento inesperado (ex.: lançamentos faltando, erros 500):
  - Reúna: ID da solicitação, horário, texto da mensagem de erro, prints e mensagem original do Discord.
  - Abra issue ou envie para suporte com os dados acima.
- Para anexos inválidos ou sem preview: abra o arquivo original pelo link fornecido na lista de anexos.

## Permissões e auditoria
- Apenas usuários com permissão veem e podem aprovar/rejeitar.
- Histórico: aprovações/rejeições e IDs de lançamentos são registrados e exibidos.
- Rejeições exigem motivo; aprovações registram quem processou e os lançamentos gerados.

## Boas práticas
- Sempre validar saldo e capacidade antes de aprovar.
- Use observação para registrar contexto administrativo.
- Ao rejeitar, explique claramente o motivo para facilitar reenvio correto pelo solicitante.

## Como reportar um bug relacionado a uma solicitação
Inclua:
- ID da solicitação.
- Ação tentada (aprovar/rejeitar/ajustar).
- Mensagem de erro completa.
- Prints / gravações.
- Hora e usuário que executou a ação.
- Links ou IDs do Discord (guild/channel/message) se aplicável.
