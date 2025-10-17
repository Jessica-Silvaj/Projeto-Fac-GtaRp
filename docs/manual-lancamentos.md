# Manual — Parte: Lançamentos (Entradas / Saídas / Transferências)

Versão: 1.0  
Data: 17 de outubro de 2025

## Objetivo
Explicar, em linguagem direta, como registrar e gerir lançamentos de itens nos baús (entradas, saídas e transferências), as regras de funcionamento, mensagens de erro e boas práticas.

## Onde acessar
Menu: Controle de Baú → Lançamentos  
Botões principais: Novo (criar), Editar, Excluir, Pesquisar (filtros).

## Campos do formulário
- Item (itens_id): selecione o item. Busca por texto (autocomplete).
- Tipo (tipo): ENTRADA | SAIDA | TRANSFERENCIA.
- Quantidade (quantidade): número inteiro (mínimo 1).
- Baú Origem (bau_origem_id): baú de onde sai o item (usado em SAIDA/TRANSFERENCIA).
- Baú Destino (bau_destino_id): baú para onde entra o item (usado em ENTRADA/TRANSFERENCIA).
- Fabricação (fabricacao): SIM/NAO — gera consumos automáticos dos componentes, quando aplicável.
- Observação (observacao): texto livre.

## Regras de visibilidade do formulário (comportamento)
- Se tipo = ENTRADA → só é mostrado Baú Destino; Baú Origem é limpo.
- Se tipo = SAIDA → só é mostrado Baú Origem; Baú Destino é limpo.
- Se tipo = TRANSFERENCIA → ambos os campos são exibidos.
- Se nenhum tipo selecionado → origem e destino ficam ocultos.

## Validações principais (o que o sistema exige)
- Item é obrigatório.
- Tipo deve ser selecionado.
- Quantidade deve ser numérico inteiro e >= 1.
- Para ENTRADA: deve informar Baú Destino.
- Para SAIDA: deve informar Baú Origem.
- Para TRANSFERENCIA: deve informar ambos (origem e destino).
- O servidor valida novamente ao enviar e rejeita entradas inválidas.

## Fabricação automática (o que acontece se escolher "SIM")
- O sistema busca um Produto com nome igual ao nome do item (case-insensitive). Se houver correspondência e o produto tiver componentes (itens com pivot quantidade), o sistema:
  1. Calcula quantos lotes/componentes são necessários com base na quantidade do lançamento principal.
  2. Verifica o saldo disponível de cada componente no baú de consumo (normalmente o baú de origem para SAIDA/TRANSFERENCIA ou o baú destino para ENTRADA).
  3. Se algum componente estiver em falta, a operação é cancelada e o sistema informa qual componente falta, quanto é necessário e quanto há disponível.
  4. Se houver saldo suficiente, o sistema cria automaticamente lançamentos do tipo SAIDA para os componentes (observação com prefixo "FABRICACÃO AUTOMATICA {id} | …") e registra auditoria.
- Observação: o cálculo usa arredondamento para cima por componente; a saída por lote do produto pode ser 1 por padrão.

## Mensagens de erro e solução rápida
- "Registros não encontrados" — lista vazia para o filtro usado.
- Mensagem de validação (campo) — corrija o campo indicado.
- Erro de fabricação — mensagem como:
  "<Baú/Nome> não possui quantidade suficiente do item <Componente> para fabricar X <Produto>. Necessário Y, disponível Z."
  O que fazer: transferir/entrada dos componentes para o baú indicado ou reduzir a quantidade a fabricar.
- Erro ao salvar/excluir (genérico) — tente novamente; se persistir, contate suporte com prints e horário.
- Exclusão requer confirmação no modal.

## Permissões e auditoria
- A visualização e ações (Novo/Editar/Excluir) respeitam permissões. Se um botão não aparece, sua conta não tem permissão.
- Operações importantes são registradas em log (inserir, atualizar, excluir e ações automáticas de fabricação).

## Fluxo do usuário (passo a passo)
1. Abrir "Novo Lançamento".
2. Selecionar Item, Tipo e Quantidade.
3. Preencher Baú Origem / Destino conforme tipo.
4. Se for fabricação, marcar "SIM" e confirmar que o baú tem componentes suficientes.
5. Clicar em Salvar.
6. Em caso de sucesso: retorno para listagem com mensagem "O lançamento foi salvo com sucesso."
7. Para excluir: clicar em Excluir → confirmar no modal.

## Boas práticas
- Verifique o baú correto antes de gravar (origem/destino).
- Use observação para descrever motivo/lotação quando necessário.
- Ao usar fabricação automática, valide disponibilidade previamente no estoque para evitar rejeição.
- Ao reportar erro, inclua data/hora, mensagem mostrada e print da tela.

## Como reportar um problema relacionado a lançamentos
Enviar ao suporte:
- Passos para reproduzir.
- Data e hora.
- Mensagem completa de erro.
- Print ou gravação da tela.
- Item, baú e quantidade envolvidos.
