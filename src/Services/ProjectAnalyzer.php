<?php

namespace MartinLechene\GitHubActions\Services;

use Illuminate\Support\Facades\File;

class ProjectAnalyzer
{
    protected ComposerAnalyzer $composerAnalyzer;

    public function __construct(ComposerAnalyzer $composerAnalyzer)
    {
        $this->composerAnalyzer = $composerAnalyzer;
    }

    public function analyze(): ProjectMetadata
    {
        return ProjectMetadata::make()
            ->phpVersions($this->getPhpVersions())
            ->databases($this->getDatabasesUsed())
            ->hasRedis($this->usesRedis())
            ->hasQueues($this->usesQueues())
            ->testFramework($this->detectTestFramework())
            ->codeQualityTools($this->detectQualityTools());
    }

    protected function getPhpVersions(): array
    {
        return $this->composerAnalyzer->getPhpVersions();
    }

    protected function getDatabasesUsed(): array
    {
        $databases = $this->composerAnalyzer->getDatabases();
        
        if (empty($databases)) {
            $configPath = config_path('database.php');
            if (file_exists($configPath)) {
                $config = include $configPath;
                $connections = $config['connections'] ?? [];
                
                foreach ($connections as $name => $connection) {
                    $driver = $connection['driver'] ?? null;
                    if ($driver === 'mysql') {
                        $databases[] = 'mysql';
                    } elseif ($driver === 'pgsql') {
                        $databases[] = 'postgres';
                    } elseif ($driver === 'sqlite') {
                        $databases[] = 'sqlite';
                    }
                }
            }
        }

        return array_unique($databases);
    }

    protected function usesRedis(): bool
    {
        return $this->composerAnalyzer->hasPackage('predis/predis') 
            || $this->composerAnalyzer->hasPackage('phpredis/phpredis');
    }

    protected function usesQueues(): bool
    {
        $queuePath = database_path('migrations');
        if (is_dir($queuePath)) {
            $files = File::glob($queuePath . '/*_create_jobs_table.php');
            return !empty($files);
        }
        return false;
    }

    protected function detectTestFramework(): ?string
    {
        return $this->composerAnalyzer->getTestFramework();
    }

    protected function detectQualityTools(): array
    {
        return $this->composerAnalyzer->getCodeQualityTools();
    }
}

