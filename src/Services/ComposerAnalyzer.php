<?php

namespace MartinLechene\GitHubActions\Services;

use Illuminate\Support\Facades\File;

class ComposerAnalyzer
{
    protected ?array $composerData = null;

    public function __construct()
    {
        $this->loadComposerJson();
    }

    protected function loadComposerJson(): void
    {
        $path = base_path('composer.json');
        
        if (file_exists($path)) {
            $content = File::get($path);
            $this->composerData = json_decode($content, true);
        }
    }

    public function getPhpVersions(): array
    {
        if (!$this->composerData || !isset($this->composerData['require']['php'])) {
            return ['8.2', '8.3'];
        }

        $phpConstraint = $this->composerData['require']['php'];
        return $this->extractPhpVersions($phpConstraint);
    }

    protected function extractPhpVersions(string $constraint): array
    {
        $versions = [];
        
        if (preg_match('/\^(\d+\.\d+)/', $constraint, $matches)) {
            $minVersion = $matches[1];
            $major = (int) explode('.', $minVersion)[0];
            $minor = (int) explode('.', $minVersion)[1];
            
            $versions[] = $minVersion;
            if ($minor < 3) {
                $versions[] = "{$major}." . ($minor + 1);
            }
        } elseif (preg_match('/~(\d+\.\d+)/', $constraint, $matches)) {
            $versions[] = $matches[1];
        } else {
            $versions = ['8.2', '8.3'];
        }

        return array_unique($versions);
    }

    public function getDatabases(): array
    {
        if (!$this->composerData) {
            return [];
        }

        $databases = [];
        $requires = $this->composerData['require'] ?? [];

        if (isset($requires['mysql/mysql-server']) || isset($requires['doctrine/dbal'])) {
            $databases[] = 'mysql';
        }

        if (isset($requires['postgresql/postgresql']) || strpos(json_encode($requires), 'pgsql') !== false) {
            $databases[] = 'postgres';
        }

        if (isset($requires['doctrine/dbal'])) {
            $databases[] = 'sqlite';
        }

        return array_unique($databases);
    }

    public function hasPackage(string $package): bool
    {
        if (!$this->composerData) {
            return false;
        }

        $requires = $this->composerData['require'] ?? [];
        $devRequires = $this->composerData['require-dev'] ?? [];

        return isset($requires[$package]) || isset($devRequires[$package]);
    }

    public function getLaravelVersion(): ?string
    {
        if (!$this->composerData) {
            return null;
        }

        $requires = $this->composerData['require'] ?? [];
        
        if (isset($requires['laravel/framework'])) {
            return $requires['laravel/framework'];
        }

        return null;
    }

    public function getTestFramework(): ?string
    {
        if ($this->hasPackage('phpunit/phpunit')) {
            return 'phpunit';
        }

        if ($this->hasPackage('pestphp/pest')) {
            return 'pest';
        }

        return null;
    }

    public function getCodeQualityTools(): array
    {
        $tools = [];

        if ($this->hasPackage('phpstan/phpstan')) {
            $tools[] = 'phpstan';
        }

        if ($this->hasPackage('squizlabs/php_codesniffer')) {
            $tools[] = 'phpcs';
        }

        if ($this->hasPackage('nunomaduro/larastan')) {
            $tools[] = 'larastan';
        }

        if ($this->hasPackage('nunomaduro/phpinsights')) {
            $tools[] = 'phpinsights';
        }

        return $tools;
    }
}

