<?php

namespace MartinLechene\GitHubActions\Generators;

use MartinLechene\GitHubActions\Services\ComposerAnalyzer;

class MatrixGenerator
{
    protected ComposerAnalyzer $composerAnalyzer;

    public function __construct(ComposerAnalyzer $composerAnalyzer = null)
    {
        $this->composerAnalyzer = $composerAnalyzer ?? new ComposerAnalyzer();
    }

    public function generateFromComposer(): array
    {
        $matrix = [];

        $phpVersions = $this->composerAnalyzer->getPhpVersions();
        if (!empty($phpVersions)) {
            $matrix['php-version'] = $phpVersions;
        }

        $databases = $this->composerAnalyzer->getDatabases();
        if (!empty($databases)) {
            $matrix['database'] = $databases;
        }

        return $matrix;
    }

    public function generate(array $config): array
    {
        $matrix = [];

        if (isset($config['php'])) {
            $matrix['php-version'] = is_array($config['php']) 
                ? $config['php'] 
                : [$config['php']];
        }

        if (isset($config['database'])) {
            $matrix['database'] = is_array($config['database']) 
                ? $config['database'] 
                : [$config['database']];
        }

        if (isset($config['laravel'])) {
            $matrix['laravel'] = is_array($config['laravel']) 
                ? $config['laravel'] 
                : [$config['laravel']];
        }

        return $matrix;
    }
}

