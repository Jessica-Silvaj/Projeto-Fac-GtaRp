# Manual — Parte: Histórico de Lançamentos

Versão: 1.0  
Data: 17 de outubro de 2025

## Objetivo
Explicar, em linguagem direta, como usar a tela de histórico de lançamentos: filtros, gráficos, exportações, carregamento de detalhes e ações comuns.

## Onde acessar
Menu: Controle de Baú → Histórico de Lançamentos (Últimos 30 dias)

## Filtros disponíveis
- Início / Fim — período (DD/MM/YYYY). No envio o sistema converte para formato ISO (YYYY-MM-DD).
- Item — pesquisar por nome (autocomplete).
- Tipo — ENTRADA / SAIDA / TRANSFERENCIA.
- Baú Origem / Baú Destino — pesquisar por nome (autocomplete).
- Usuário — filtrar por usuário responsável.
- Granularidade — Dia | Semana | Mês (define agrupamento dos gráficos).
- Métrica (modo) — Quantidade (soma de unidades) ou Movimentações (contagem de eventos).

Campos opcionais: deixe em branco para não filtrar.

## O que você verá
- Painel de totais: Entradas, Saídas e Saldo Líquido (entradas - saídas).
- Gráfico principal: Entradas x Saídas por período (barra).
- Gráfico de saldo acumulado (linha).
- Donuts laterais: Top itens por Entradas e por Saídas (clicáveis para filtrar por item).
- Tabela “Detalhes do período”: carregada via botão "Carregar detalhes".

## Ações e botões
- Atualizar (Pesquisar) — aplica filtros.
- Limpar — reseta filtros e reenvia formulário.
- Exportações:
  - Ver estoque total — abre visão de estoque para o item/baú filtrado.
  - Exportar CSV (séries / top entradas / top saídas) — gera CSV para download.
- Copiar link (quando disponível) — copia a URL com os filtros aplicados.
- Carregar detalhes — busca via AJAX a lista detalhada (rota: bau.lancamentos.historico.detalhes) e preenche a tabela.

## Interação com gráficos
- Donuts têm legenda clicável: clicar em um item da legenda preenche o filtro "Item" e reexecuta a pesquisa.
- Botões "ver mais / ver menos" alternam entre top limitado e lista completa.
- Os gráficos são responsivos e redimensionam automaticamente.

## Comportamento técnico (resumo para o usuário)
- Datas são convertidas antes do envio; use DD/MM/YYYY.
- Gráficos usam os dados agrupados conforme granularidade escolhida.
- Carregar detalhes faz uma requisição JSON e mostra linhas com: data, tipo, item, qtd, baú origem/destino, usuário, observação.
- Exportações geram arquivos CSV com BOM (compatível com Excel).

## Erros e como proceder
- Sem registros: mensagem "Sem registros" nos locais correspondentes.
- Erro ao carregar detalhes (AJAX): aparece mensagem de erro na tabela; tente recarregar e, se persistir, capture print e informe suporte com filtros usados.
- Problemas de exportação: verifique permissões; se continuar, registre o horário e contacte suporte.

## Permissões
- Alguns botões (Ver estoque total, exportar CSV) podem não aparecer se sua conta não tiver permissão.
- Acesso limitado: entre em contato com administrador se precisar de mais privilégios.

## Boas práticas
- Use filtros de data razoáveis (ex.: ≤ 3 meses) para evitar relatórios pesados.
- Ao compartilhar um erro, inclua os filtros usados, hora e prints dos gráficos ou da tabela de detalhes.
- Para análises persistentes, copie o link com filtros aplicados e salve referência.

## Como reportar um problema
Forneça:
- Filtros exatos (datas, item, baús, usuário, granularidade e modo).
- Ação tentada (ex.: carreguei detalhes / exportei CSV).
- Mensagem de erro ou comportamento observado.
- Prints e horário da tentativa.
