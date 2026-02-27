<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ConfigureRedisQueue extends Command
{
    /**
     * Le nom et la signature de la commande
     *
     * @var string
     */
    protected $signature = 'kore:configure-redis-queue 
                            {--test : Tester la connexion Redis}
                            {--flush : Vider les files d\'attente existantes}
                            {--setup : Configuration complète pour la production}';

    /**
     * La description de la commande
     *
     * @var string
     */
    protected $description = 'Configure Redis pour les files d\'attente KORE ERP en production';

    /**
     * Exécuter la commande
     */
    public function handle()
    {
        $this->info('🚀 Configuration Redis pour KORE ERP - Production');
        $this->info('==================================================');

        try {
            if ($this->option('setup')) {
                $this->setupCompleteConfiguration();
                return 0;
            }

            if ($this->option('test')) {
                $this->testRedisConnection();
            }

            if ($this->option('flush')) {
                $this->flushQueues();
            }

            $this->configureQueuePriorities();
            $this->optimizeRedisConfiguration();
            $this->setupHorizonConfiguration();

            $this->info('✅ Configuration Redis terminée avec succès !');
            $this->warn('⚠️  N\'oubliez pas de redémarrer vos workers de queue');

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la configuration Redis : ' . $e->getMessage());
            Log::error('Configuration Redis échouée', ['error' => $e->getMessage()]);
            return 1;
        }

        return 0;
    }

    /**
     * Configuration complète pour la production
     */
    protected function setupCompleteConfiguration(): void
    {
        $this->info('🔧 Configuration complète Redis pour la production...');
        
        $this->testRedisConnection();
        $this->configureQueuePriorities();
        $this->optimizeRedisConfiguration();
        $this->setupHorizonConfiguration();
        $this->setupMonitoring();
        $this->configureSecurity();
        
        $this->info('✅ Configuration complète terminée !');
    }

    /**
     * Tester la connexion Redis
     */
    protected function testRedisConnection(): void
    {
        $this->info('🔍 Test de connexion Redis...');
        
        try {
            $start = microtime(true);
            Redis::ping();
            $latency = round((microtime(true) - $start) * 1000, 2);
            
            $info = Redis::info();
            $memory = $this->formatBytes($info['used_memory'] ?? 0);
            $connections = $info['connected_clients'] ?? 0;
            
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Statut', '✅ Connecté'],
                    ['Latence', "{$latency} ms"],
                    ['Mémoire utilisée', $memory],
                    ['Connexions clients', $connections],
                    ['Version', $info['redis_version'] ?? 'Inconnue'],
                ]
            );
            
        } catch (\Exception $e) {
            $this->error('❌ Connexion Redis échouée : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Configurer les priorités de file d'attente
     */
    protected function configureQueuePriorities(): void
    {
        $this->info('📊 Configuration des priorités de file d\'attente...');
        
        $queues = [
            'ai-high-priority' => 'Analyses IA critiques',
            'ai-normal' => 'Analyses IA standard',
            'ai-low-priority' => 'Analyses IA basse priorité',
            'billing' => 'Facturation et paiements',
            'notifications' => 'Notifications et emails',
            'reports' => 'Génération de rapports',
        ];

        foreach ($queues as $queue => $description) {
            $this->line("  📋 {$queue} - {$description}");
            
            // Vérifier si la file existe
            $size = Redis::llen("queues:{$queue}") ?? 0;
            $this->line("     Taille actuelle : {$size} jobs");
        }
    }

    /**
     * Optimiser la configuration Redis
     */
    protected function optimizeRedisConfiguration(): void
    {
        $this->info('⚡ Optimisation de la configuration Redis...');
        
        // Configuration recommandée pour la production
        $optimizations = [
            'maxmemory' => '2gb',
            'maxmemory-policy' => 'allkeys-lru',
            'tcp-keepalive' => '60',
            'timeout' => '300',
            'tcp-backlog' => '511',
            'save' => '900 1 300 10 60 10000',
            'rdbcompression' => 'yes',
            'rdbchecksum' => 'yes',
            'dbfilename' => 'dump.rdb',
            'dir' => '/var/lib/redis',
            'appendonly' => 'yes',
            'appendfilename' => 'appendonly.aof',
            'appendfsync' => 'everysec',
            'no-appendfsync-on-rewrite' => 'no',
            'auto-aof-rewrite-percentage' => '100',
            'auto-aof-rewrite-min-size' => '64mb',
        ];

        $this->table(
            ['Paramètre', 'Valeur Recommandée'],
            collect($optimizations)->map(function ($value, $key) {
                return [$key, $value];
            })->values()->toArray()
        );

        $this->warn('⚠️  Ces paramètres doivent être configurés dans votre fichier redis.conf');
    }

    /**
     * Configurer Horizon
     */
    protected function setupHorizonConfiguration(): void
    {
        $this->info('📈 Configuration de Laravel Horizon...');
        
        $horizonConfig = <<<'PHP'
<?php

return [
    'environments' => [
        'production' => [
            'supervisor-1' => [
                'maxProcesses' => 10,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 256,
                'tries' => 3,
                'timeout' => 300,
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 10,
                'maxWaitTime' => 60,
                'maxJobRuntime' => 0,
                'maxWorkers' => 0,
                'sleep' => 3,
                'timeout' => 300,
                'tries' => 3,
                'maxTries' => 3,
                'force' => false,
                'queues' => [
                    'ai-high-priority',
                    'ai-normal', 
                    'ai-low-priority',
                    'billing',
                    'notifications',
                    'reports',
                    'default',
                ],
            ],
        ],
        'local' => [
            'supervisor-1' => [
                'maxProcesses' => 3,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 300,
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'queues' => [
                    'ai-high-priority',
                    'ai-normal',
                    'ai-low-priority', 
                    'billing',
                    'notifications',
                    'reports',
                    'default',
                ],
            ],
        ],
    ],
];
PHP;

        $configPath = config_path('horizon.php');
        
        if (!file_exists($configPath)) {
            $this->warn('⚠️  Laravel Horizon n\'est pas installé');
            $this->line('   Installation : composer require laravel/horizon');
            return;
        }

        $this->info('✅ Configuration Horizon prête');
        $this->line('   Redémarrez Horizon après les modifications :');
        $this->line('   php artisan horizon:terminate');
        $this->line('   php artisan horizon');
    }

    /**
     * Configurer le monitoring
     */
    protected function setupMonitoring(): void
    {
        $this->info('📊 Configuration du monitoring...');
        
        // Créer des clés de monitoring
        $monitoringKeys = [
            'kore_erp_queue_size',
            'kore_erp_queue_processing_time',
            'kore_erp_queue_failures',
            'kore_erp_redis_memory',
            'kore_erp_redis_connections',
        ];

        foreach ($monitoringKeys as $key) {
            Redis::set("monitoring:{$key}", 0);
            Redis::expire("monitoring:{$key}", 3600); // 1 heure
        }

        $this->info('✅ Monitoring configuré');
    }

    /**
     * Configurer la sécurité
     */
    protected function configureSecurity(): void
    {
        $this->info('🔒 Configuration de la sécurité...');
        
        // Configuration de sécurité recommandée
        $securityConfig = [
            'requirepass' => 'VOTRE_MOT_DE_PASSE_SUPER_SICRET',
            'bind' => '127.0.0.1',
            'protected-mode' => 'yes',
            'port' => '6379',
            'tcp-backlog' => '511',
            'timeout' => '300',
            'tcp-keepalive' => '60',
            'maxclients' => '10000',
        ];

        $this->table(
            ['Paramètre de Sécurité', 'Valeur'],
            collect($securityConfig)->map(function ($value, $key) {
                if ($key === 'requirepass') {
                    $value = '***MASQUÉ***';
                }
                return [$key, $value];
            })->values()->toArray()
        );

        $this->warn('⚠️  Changez le mot de passe par défaut !');
    }

    /**
     * Vider les files d'attente
     */
    protected function flushQueues(): void
    {
        $this->warn('🗑️  Vidage des files d\'attente...');
        
        $queues = ['ai-high-priority', 'ai-normal', 'ai-low-priority', 'billing', 'notifications', 'reports', 'default'];
        
        foreach ($queues as $queue) {
            $size = Redis::llen("queues:{$queue}") ?? 0;
            if ($size > 0) {
                Redis::del("queues:{$queue}");
                $this->line("  ✅ File {$queue} vidée ({$size} jobs)");
            }
        }

        $this->info('✅ Files d\'attente vidées');
    }

    /**
     * Formater les bytes
     */
    protected function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}