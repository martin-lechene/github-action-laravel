<?php

namespace MartinLechene\GitHubActions\Models;

class WorkflowTemplate
{
    protected string $name;
    protected string $path;
    protected string $category;
    protected array $defaults = [];
    protected string $description;

    public function __construct(string $name, string $path, string $category = 'Laravel')
    {
        $this->name = $name;
        $this->path = $path;
        $this->category = $category;
    }

    public static function make(string $name, string $path, string $category = 'Laravel'): self
    {
        return new self($name, $path, $category);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setDefaults(array $defaults): self
    {
        $this->defaults = $defaults;
        return $this;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description ?? $this->name;
    }

    public function getContent(): string
    {
        if (!file_exists($this->path)) {
            throw new \RuntimeException("Template file not found: {$this->path}");
        }

        return file_get_contents($this->path);
    }
}

