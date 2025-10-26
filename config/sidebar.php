<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuração da Sidebar Dinâmica
    |--------------------------------------------------------------------------
    |
    | Este arquivo permite configurar seções da sidebar de forma dinâmica.
    | Para adicionar uma nova seção, basta incluí-la neste array.
    |
    */

    'sections' => [
        'administracao' => [
            'label' => 'Administração',
            'order' => 1,
            'permissions' => [
                'administracao.rh.usuario.index',
                'administracao.rh.perfil.index',
                'administracao.rh.situacao.index',
                'administracao.rh.funcao.index',
                'administracao.rh.frequencia.index',
                'administracao.sistema.permissoes.index',
                'administracao.sistema.configuracao.anomalia.edit',
                'administracao.estoque.itens.index',
                'administracao.estoque.baus.index',
                'administracao.fabricacao.produtos.index',
            ],
            'submenus' => [
                'rh' => [
                    'label' => 'Recursos Humanos',
                    'icon' => 'ti-user',
                    'order' => 1,
                    'permissions' => [
                        'administracao.rh.usuario.index',
                        'administracao.rh.perfil.index',
                        'administracao.rh.situacao.index',
                        'administracao.rh.funcao.index',
                        'administracao.rh.frequencia.index',
                    ],
                    'route_pattern' => 'administracao.rh.*',
                    'items' => [
                        [
                            'label' => 'Usuários',
                            'route' => 'administracao.rh.usuario.index',
                            'permission' => 'administracao.rh.usuario.index',
                            'icon' => 'ti-briefcase',
                            'route_active' => 'administracao.rh.usuario'
                        ],
                        [
                            'label' => 'Perfil',
                            'route' => 'administracao.rh.perfil.index',
                            'permission' => 'administracao.rh.perfil.index',
                            'icon' => 'ti-id-badge',
                            'route_active' => 'administracao.rh.perfil'
                        ],
                        [
                            'label' => 'Situação',
                            'route' => 'administracao.rh.situacao.index',
                            'permission' => 'administracao.rh.situacao.index',
                            'icon' => 'ti-flag',
                            'route_active' => 'administracao.rh.situacao'
                        ],
                        [
                            'label' => 'Funções',
                            'route' => 'administracao.rh.funcao.index',
                            'permission' => 'administracao.rh.funcao.index',
                            'icon' => 'ti-crown',
                            'route_active' => 'administracao.rh.funcao'
                        ],
                        [
                            'label' => 'Frequência',
                            'route' => 'administracao.rh.frequencia.index',
                            'permission' => 'administracao.rh.frequencia.index',
                            'icon' => 'ti-calendar',
                            'route_active' => 'administracao.rh.frequencia'
                        ]
                    ]
                ],
                'sistema' => [
                    'label' => 'Sistema',
                    'icon' => 'ti-settings',
                    'order' => 2,
                    'permissions' => [
                        'administracao.sistema.permissoes.index',
                        'administracao.sistema.configuracao.anomalia.edit',
                    ],
                    'route_pattern' => 'administracao.sistema.*',
                    'items' => [
                        [
                            'label' => 'Permissões',
                            'route' => 'administracao.sistema.permissoes.index',
                            'permission' => 'administracao.sistema.permissoes.index',
                            'icon' => 'ti-lock',
                            'route_active' => 'administracao.sistema.permissoes'
                        ],
                        [
                            'label' => 'Config. Anomalias',
                            'route' => 'administracao.sistema.configuracao.anomalia.edit',
                            'permission' => 'administracao.sistema.configuracao.anomalia.edit',
                            'icon' => 'ti-ruler-pencil',
                            'route_active' => 'administracao.sistema.configuracao.anomalia.edit'
                        ]
                    ]
                ],
                'estoque' => [
                    'label' => 'Estoque',
                    'icon' => 'ti-package',
                    'order' => 3,
                    'permissions' => [
                        'administracao.estoque.itens.index',
                        'administracao.estoque.baus.index',
                    ],
                    'route_pattern' => 'administracao.estoque.*',
                    'items' => [
                        [
                            'label' => 'Itens',
                            'route' => 'administracao.estoque.itens.index',
                            'permission' => 'administracao.estoque.itens.index',
                            'icon' => 'ti-package',
                            'route_active' => 'administracao.estoque.itens'
                        ],
                        [
                            'label' => 'Baús',
                            'route' => 'administracao.estoque.baus.index',
                            'permission' => 'administracao.estoque.baus.index',
                            'icon' => 'ti-archive',
                            'route_active' => 'administracao.estoque.baus'
                        ]
                    ]
                ],
                'fabricacao' => [
                    'label' => 'Fabricação',
                    'icon' => 'ti-truck',
                    'order' => 4,
                    'permissions' => [
                        'administracao.fabricacao.produtos.index',
                        'administracao.fabricacao.organizacao.index',
                    ],
                    'route_pattern' => 'administracao.fabricacao.*',
                    'items' => [
                        [
                            'label' => 'Produtos',
                            'route' => 'administracao.fabricacao.produtos.index',
                            'permission' => 'administracao.fabricacao.produtos.index',
                            'icon' => 'ti-package',
                            'route_active' => 'administracao.fabricacao.produtos'
                        ],
                        [
                            'label' => 'Organizações',
                            'route' => 'administracao.fabricacao.organizacao.index',
                            'permission' => 'administracao.fabricacao.organizacao.index',
                            'icon' => 'ti-briefcase',
                            'route_active' => 'administracao.fabricacao.organizacao'
                        ]
                    ]
                ]
            ]
        ],
        'vendas' => [
            'label' => 'Controle Vendas',
            'order' => 4,
            'permissions' => [
                'venda.fila.index',
                'venda.fila.create',
                'venda.fila.historico',
            ],
            'items' => [
                [
                    'label' => 'Fila de Vendas',
                    'route' => 'venda.fila.index',
                    'permission' => 'venda.fila.index',
                    'icon' => 'ti-shopping-cart',
                    'route_active' => ['venda.fila.index', 'venda.fila.create', 'venda.fila.edit']
                ],
                [
                    'label' => 'Histórico Vendas',
                    'route' => 'venda.fila.historico',
                    'permission' => 'venda.fila.historico',
                    'icon' => 'ti-bar-chart-alt',
                    'route_active' => 'venda.fila.historico'
                ]
            ]
        ],
        'navegacao' => [
            'label' => 'Navegação',
            'order' => 2,
            'always_visible' => true,
            'items' => [
                [
                    'label' => 'Painel',
                    'route' => 'dashboard',
                    'icon' => 'ti-home',
                    'route_active' => 'dashboard'
                ]
            ]
        ],
        'controle_bau' => [
            'label' => 'Controle Baú',
            'order' => 3,
            'permissions' => [
                'bau.lancamentos.index',
                'bau.lancamentos.historico',
                'bau.lancamentos.estoque.index',
                'bau.lancamentos.solicitacoes.index',
                'bau.lancamentos.anomalias',
            ],
            'items' => [
                [
                    'label' => 'Lançamentos',
                    'route' => 'bau.lancamentos.index',
                    'permission' => 'bau.lancamentos.index',
                    'icon' => 'ti-exchange-vertical',
                    'route_active' => ['bau.lancamentos.index', 'bau.lancamentos.edit']
                ],
                [
                    'label' => 'Solicitações',
                    'route' => 'bau.lancamentos.solicitacoes.index',
                    'permission' => 'bau.lancamentos.solicitacoes.index',
                    'icon' => 'ti-clipboard',
                    'route_active' => 'bau.lancamentos.solicitacoes.*'
                ],
                [
                    'label' => 'Histórico',
                    'route' => 'bau.lancamentos.historico',
                    'permission' => 'bau.lancamentos.historico',
                    'icon' => 'ti-bar-chart',
                    'route_active' => 'bau.lancamentos.historico'
                ],
                [
                    'label' => 'Estoque Total',
                    'route' => 'bau.lancamentos.estoque',
                    'permission' => 'bau.lancamentos.estoque.index',
                    'icon' => 'ti-archive',
                    'route_active' => 'bau.lancamentos.estoque'
                ],
                [
                    'label' => 'Anomalias',
                    'route' => 'bau.lancamentos.anomalias',
                    'permission' => 'bau.lancamentos.anomalias',
                    'icon' => 'ti-alert',
                    'route_active' => 'bau.lancamentos.anomalias'
                ]
            ]
        ],
        'financeiro' => [
            'label' => 'Sistema Financeiro',
            'order' => 5,
            'permissions' => [
                'financeiro.index',
                'financeiro.dashboard',
                'financeiro.relatorio',
                'financeiro.auditoria',
            ],
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'financeiro.dashboard',
                    'permission' => 'financeiro.dashboard',
                    'icon' => 'ti-chart-line',
                    'route_active' => 'financeiro.dashboard'
                ],
                [
                    'label' => 'Controle Financeiro',
                    'route' => 'financeiro.index',
                    'permission' => 'financeiro.index',
                    'icon' => 'ti-wallet',
                    'route_active' => 'financeiro.index'
                ],
                [
                    'label' => 'Relatórios',
                    'route' => 'financeiro.relatorio',
                    'permission' => 'financeiro.relatorio',
                    'icon' => 'ti-file-text',
                    'route_active' => 'financeiro.relatorio'
                ],
                [
                    'label' => 'Auditoria',
                    'route' => 'financeiro.auditoria',
                    'permission' => 'financeiro.auditoria',
                    'icon' => 'ti-search',
                    'route_active' => 'financeiro.auditoria'
                ]
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Configurações de Estilo
    |--------------------------------------------------------------------------
    */
    'styles' => [
        'compact_mode' => true,
        'show_icons' => true,
        'animation_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Como Adicionar Nova Seção
    |--------------------------------------------------------------------------
    |
    | Para adicionar uma nova seção, adicione um item ao array 'sections':
    |
    | 'nova_secao' => [
    |     'label' => 'Nome da Seção',
    |     'order' => 4, // Ordem de exibição
    |     'permissions' => ['permissao1', 'permissao2'], // Opcional
    |     'always_visible' => false, // true para sempre mostrar
    |     'items' => [
    |         [
    |             'label' => 'Item do Menu',
    |             'route' => 'route.name',
    |             'permission' => 'permissao.especifica', // Opcional
    |             'icon' => 'ti-icon-name',
    |             'route_active' => 'route.pattern'
    |         ]
    |     ]
    | ]
    |
    */
];
