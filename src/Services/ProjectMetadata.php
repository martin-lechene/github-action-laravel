<?php

namespace MartinLechene\GitHubActions\Services;

class ProjectMetadata
{
    protected array $phpVersions = [];
    protected array $databases = [];
    protected bool $hasRedis = false;
    protected bool $hasQueues = false;
    protected ?string $testFramework = null;
    protected array $codeQualityTools = [];

    public static function make(): self
    {
        return new self();
    }

    public function phpVersions(array $versions): self
    {
        $this->phpVersions = $versions;
        return $this;
    }

    public function getPhpVersions(): array
    {
        return $this->phpVersions;
    }

    public function databases(array $databases): self
    {
        $this->databases = $databases;
        return $this;
    }

    public function getDatabases(): array
    {
        return $this->databases;
    }

    public function hasRedis(bool $hasRedis = true): self
    {
        $this->hasRedis = $hasRedis;
        return $this;
    }

    public function getHasRedis(): bool
    {
        return $this->hasRedis;
    }

    public function hasQueues(bool $hasQueues = true): self
    {
        $this->hasQueues = $hasQueues;
        return $this;
    }

    public function getHasQueues(): bool
    {
        return $this->hasQueues;
    }

    public function testFramework(?string $framework): self
    {
        $this->testFramework = $framework;
        return $this;
    }

    public function getTestFramework(): ?string
    {
        return $this->testFramework;
    }

    public function codeQualityTools(array $tools): self
    {
        $this->codeQualityTools = $tools;
        return $this;
    }

    public function getCodeQualityTools(): array
    {
        return $this->codeQualityTools;
    }

    public function toArray(): array
    {
        return [
            'php_versions' => $this->phpVersions,
            'databases' => $this->databases,
            'has_redis' => $this->hasRedis,
            'has_queues' => $this->hasQueues,
            'test_framework' => $this->testFramework,
            'code_quality_tools' => $this->codeQualityTools,
        ];
    }
}

