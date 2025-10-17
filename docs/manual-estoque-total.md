# Manual — Parte: Estoque Total

Versão: 1.0  
Data: 17 de outubro de 2025

## Objetivo
Explicar de forma direta como usar a tela "Estoque Total": filtros, interpretações dos totais, ações rápidas e como exportar dados.

## Onde acessar
Menu: Controle de Baú → Estoque Total

## Filtros disponíveis
- Desde / Até (opcional): formato dd/mm/yyyy (o sistema converte para ISO ao enviar).
- Item (itens_id): pesquisa com autocomplete.
- Baú (bau_id): pesquisa com autocomplete.
Campos opcionais — deixar em branco significa "não filtrar".

## Painéis e indicadores
- Total em Estoque: soma dos saldos positivos de todos os itens (exibe número formatado).
- Itens Únicos: quantidade de itens com saldo ≠ 0.
- Baús Utilizados: número de baús que possuem saldo.

## Tabela de Saldos por Baú
Cada linha mostra:
- Nome do baú
- Quantidade total armazenada
- Quantidade de itens distintos
- Lista curta de itens com suas quantidades
- Ação "Ver histórico" (abre histórico filtrado para o baú)

## Ações rápidas
- Atualizar: aplica filtros e recarrega a página.
- Limpar: limpa filtros e recarrega.
- Exportar CSV:
  - Detalhes (dataset=detalhes) — lista completa com saldos por item/baú.
  - Resumo por baú (dataset=resumo_baus) — resumo consolidado.
  - As opções de exportação aparecem apenas para usuários com permissão.

## Interpretação dos dados
- Valores positivos representam quantidade disponível.
- Itens listados em cada baú indicam os principais saldos; use "Ver histórico" para ver movimentações.
- Use filtros por período quando quiser projeções com base em movimentações recentes.

## Erros e situações comuns
- Nenhum saldo encontrado: resultado dos filtros aplicados — tente remover filtros.
- Exportação bloqueada: verifique permissões do usuário.
- Problemas de autocomplete (item/baú): verifique conexões AJAX e logs do servidor.

## Boas práticas
- Use datas razoáveis para evitar relatórios muito grandes.
- Ao exportar, salve o arquivo e verifique a codificação (CSV já inclui BOM para Excel).
- Quando reportar problemas, inclua filtros usados, prints e horário da tentativa.

## Como reportar um problema
Forneça:
- Filtros usados (datas, item, baú).
- Mensagem de erro (se houver) e prints.
- Hora da tentativa e usuário que executou a ação.