<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MonitorConnections extends Command
{
    protected $signature = 'monitor:connections';
    protected $description = 'Monitora conexões ativas do MySQL';

    public function handle()
    {
        try {
            // Força fechamento de todas as conexões
            DB::disconnect('mysql');

            // Tenta nova conexão para teste
            $conexoes = DB::select('SHOW STATUS LIKE "Threads_connected"');
            $this->info('Conexões ativas: ' . $conexoes[0]->Value);

            // Mostra conexões por hora
            $processlist = DB::select('SHOW PROCESSLIST');
            $this->info('Total de processos: ' . count($processlist));

            // Força desconexão novamente
            DB::disconnect('mysql');

            $this->info('✅ Monitoramento concluído - Conexão fechada');
        } catch (\Exception $e) {
            $this->error('❌ Erro: ' . $e->getMessage());
        }

        return 0;
    }
}
