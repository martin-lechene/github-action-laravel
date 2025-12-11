<?php

namespace MartinLechene\GitHubActions\Builders;

class ServiceBuilder
{
    protected string $name;
    protected array $config = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function image(string $image): self
    {
        $this->config['image'] = $image;
        return $this;
    }

    public function env(string $key, string $value): self
    {
        if (!isset($this->config['env'])) {
            $this->config['env'] = [];
        }
        $this->config['env'][$key] = $value;
        return $this;
    }

    public function port(string $port): self
    {
        $this->config['ports'] = [$port];
        return $this;
    }

    public function options(string $options): self
    {
        $this->config['options'] = $options;
        return $this;
    }

    public function build(): array
    {
        return $this->config;
    }

    public function end(): self
    {
        return $this;
    }
}

