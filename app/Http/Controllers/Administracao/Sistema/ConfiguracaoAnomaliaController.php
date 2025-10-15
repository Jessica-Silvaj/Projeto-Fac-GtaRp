<?php

namespace App\Http\Controllers\Administracao\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Baus;
use App\Models\Itens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ConfiguracaoAnomaliaController extends Controller
{
    public function edit()
    {
        $config = config('anomalias');

        $limitesEspecificos = collect($config['limites_especificos'] ?? [])->map(function ($item) {
            $bauId = $item['bau_id'] ?? null;
            $itemId = $item['item_id'] ?? null;
            return [
                'bau_id' => $bauId,
                'bau_nome' => $bauId ? optional(Baus::find($bauId))->nome : null,
                'item_id' => $itemId,
                'item_nome' => $itemId ? optional(Itens::find($itemId))->nome : null,
                'limite' => $item['limite'] ?? null,
            ];
        })->values()->all();

        return view('administracao.sistema.configuracao-anomalia', [
            'config' => $config,
            'limitesEspecificos' => $limitesEspecificos,
        ]);
    }

    public function update(Request $request)
    {
        $sanitized = $request->all();
        if (isset($sanitized['limite_percentual_bau'])) {
            $sanitized['limite_percentual_bau'] = str_replace(',', '.', (string) $sanitized['limite_percentual_bau']);
        }

        $validator = Validator::make($sanitized, [
            'limite_percentual_bau' => ['required', 'numeric', 'between:0,1'],
            'limite_padrao_bau' => ['required', 'integer', 'min:1'],
            'limite_quantidade_movimento' => ['required', 'integer', 'min:1'],
            'janela_movimento_dias' => ['required', 'integer', 'min:1'],
            'limite_estoque_baixo' => ['required', 'integer', 'min:0'],
            'limite_estoque_critico' => ['required', 'integer', 'min:0'],
            'limites_especificos.bau_id.*' => ['nullable', 'integer', 'min:1'],
            'limites_especificos.item_id.*' => ['nullable', 'integer', 'min:1'],
            'limites_especificos.limite.*' => ['nullable', 'numeric', 'min:0'],
        ], [
            'limite_percentual_bau.between' => 'Informe um percentual entre 0 e 1 (ex.: 0.8).',
        ]);

        if ($validator->fails()) {
            $request->merge($sanitized);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $limitesEspecificosInput = $request->get('limites_especificos', []);
        $limitesEspecificos = [];
        if (is_array($limitesEspecificosInput)) {
            $bauIds = $limitesEspecificosInput['bau_id'] ?? [];
            $itemIds = $limitesEspecificosInput['item_id'] ?? [];
            $limites = $limitesEspecificosInput['limite'] ?? [];
            $length = max(count($bauIds), count($itemIds), count($limites));
            for ($i = 0; $i < $length; $i++) {
                $bauId = (int) ($bauIds[$i] ?? 0);
                $itemId = (int) ($itemIds[$i] ?? 0);
                $valor = trim((string) ($limites[$i] ?? ''));
                if ($bauId > 0 && $itemId > 0 && $valor !== '' && is_numeric($valor)) {
                    $limitesEspecificos[] = [
                        'bau_id' => $bauId,
                        'item_id' => $itemId,
                        'limite' => (float) $valor,
                    ];
                }
            }
        }

        $data = [
            'limite_percentual_bau' => (float) $sanitized['limite_percentual_bau'],
            'limite_padrao_bau' => (int) $sanitized['limite_padrao_bau'],
            'limites_baus' => config('anomalias.limites_baus', []),
            'limite_quantidade_movimento' => (int) $sanitized['limite_quantidade_movimento'],
            'janela_movimento_dias' => (int) $sanitized['janela_movimento_dias'],
            'limite_estoque_baixo' => (int) $sanitized['limite_estoque_baixo'],
            'limite_estoque_critico' => (int) $sanitized['limite_estoque_critico'],
            'limites_especificos' => $limitesEspecificos,
        ];

        $this->persistirConfig($data);

        return redirect()->back()->with('success', 'Configurações de anomalias atualizadas com sucesso.');
    }

    private function persistirConfig(array $data): void
    {
        $path = config_path('anomalias.php');

        $export = var_export($data, true);
        $content = <<<PHP
<?php

return {$export};
PHP;
        File::put($path, $content);
    }
}
