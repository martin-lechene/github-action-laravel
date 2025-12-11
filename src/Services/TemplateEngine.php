<?php

namespace MartinLechene\GitHubActions\Services;

use LightnCandy\LightnCandy;

class TemplateEngine
{
    protected array $helpers = [];
    protected bool $cacheEnabled = true;
    protected array $cache = [];

    public function __construct()
    {
        $this->registerDefaultHelpers();
    }

    protected function registerDefaultHelpers(): void
    {
        $this->registerHelper('ifEquals', function ($left, $right) {
            return $left === $right;
        });

        $this->registerHelper('phpExtensions', function ($version) {
            return $this->getPhpExtensionsForVersion($version);
        });
    }

    public function registerHelper(string $name, callable $callback): void
    {
        $this->helpers[$name] = $callback;
    }

    public function render(string $template, array $data): string
    {
        $cacheKey = md5($template);
        
        if ($this->cacheEnabled && isset($this->cache[$cacheKey])) {
            $compiled = $this->cache[$cacheKey];
        } else {
            $compiled = LightnCandy::compile($template, [
                'flags' => LightnCandy::FLAG_HANDLEBARSJS | LightnCandy::FLAG_ERROR_EXCEPTION,
                'helpers' => $this->helpers,
            ]);
            
            if ($this->cacheEnabled) {
                $this->cache[$cacheKey] = $compiled;
            }
        }

        $renderer = LightnCandy::prepare($compiled);
        return $renderer($data);
    }

    public function renderFile(string $filePath, array $data): string
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Template file not found: {$filePath}");
        }

        $template = file_get_contents($filePath);
        return $this->render($template, $data);
    }

    protected function getPhpExtensionsForVersion(string $version): array
    {
        $defaults = ['pdo', 'json', 'bcmath', 'mbstring', 'xml', 'curl'];
        
        if (version_compare($version, '8.1', '>=')) {
            $defaults[] = 'enum';
        }

        return $defaults;
    }

    public function setCacheEnabled(bool $enabled): void
    {
        $this->cacheEnabled = $enabled;
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }
}

