<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tabelas de preco por produto
    |--------------------------------------------------------------------------
    | Estrutura:
    | 'produto_id' => [
    |     'lote_minimo' => 1, // quantidade minima produzida por lote
    |     'padrao' => ['limpo' => 0, 'sujo' => 0],
    |     'desconto' => ['limpo' => 0, 'sujo' => 0],
    |     'alianca' => ['limpo' => 0, 'sujo' => 0],
    | ],
    */
    'precos' => [
        // Exemplo:
        3 => [
            'lote_minimo' => 1,
            'padrao' => ['limpo' => 400000, 'sujo' => 520000],
            'desconto' => ['limpo' => 380000, 'sujo' => 494000],
            'alianca' => ['limpo' => 360000, 'sujo' => 468000],
        ],
        1 => [
            'lote_minimo' => 4,
            'padrao' => ['limpo' => 900, 'sujo' => 1170],
            'desconto' => ['limpo' => 900, 'sujo' => 1170],
            'alianca' => ['limpo' => 600, 'sujo' => 780],
        ],
    ],
];
