<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OptimizeConnections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otimiza conexões do banco de dados e limpa cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando otimização de conexões...');

        // Limpar cache para forçar novas consultas otimizadas
        Cache::flush();
        $this->info('Cache limpo.');

        // Fechar todas as conexões
        DB::disconnect();
        $this->info('Conexões fechadas.');

        // Limpar views compiladas
        $this->call('view:clear');

        // Limpar cache de configuração
        $this->call('config:clear');

        // Otimizar autoloader
        $this->call('optimize');

        $this->info('Otimização concluída com sucesso!');

        return 0;
    }
}
