<?php

namespace MartinLechene\GitHubActions\Models;

class Step
{
    protected ?string $name = null;
    protected ?string $id = null;
    protected ?string $uses = null;
    protected array $with = [];
    protected ?string $run = null;
    protected ?string $shell = null;
    protected ?string $if = null;
    protected bool $continueOnError = false;
    protected array $env = [];
    protected ?string $workingDirectory = null;

    public function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function uses(string $action): self
    {
        $this->uses = $action;
        return $this;
    }

    public function getUses(): ?string
    {
        return $this->uses;
    }

    public function with(array $with): self
    {
        $this->with = $with;
        return $this;
    }

    public function getWith(): array
    {
        return $this->with;
    }

    public function run(string $command): self
    {
        $this->run = $command;
        return $this;
    }

    public function getRun(): ?string
    {
        return $this->run;
    }

    public function shell(string $shell): self
    {
        $this->shell = $shell;
        return $this;
    }

    public function getShell(): ?string
    {
        return $this->shell;
    }

    public function if(string $condition): self
    {
        $this->if = $condition;
        return $this;
    }

    public function getIf(): ?string
    {
        return $this->if;
    }

    public function continueOnError(bool $continue = true): self
    {
        $this->continueOnError = $continue;
        return $this;
    }

    public function getContinueOnError(): bool
    {
        return $this->continueOnError;
    }

    public function env(array $env): self
    {
        $this->env = array_merge($this->env, $env);
        return $this;
    }

    public function getEnv(): array
    {
        return $this->env;
    }

    public function workingDirectory(string $directory): self
    {
        $this->workingDirectory = $directory;
        return $this;
    }

    public function getWorkingDirectory(): ?string
    {
        return $this->workingDirectory;
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name) {
            $data['name'] = $this->name;
        }

        if ($this->id) {
            $data['id'] = $this->id;
        }

        if ($this->uses) {
            $data['uses'] = $this->uses;
        }

        if (!empty($this->with)) {
            $data['with'] = $this->with;
        }

        if ($this->run) {
            $data['run'] = $this->run;
        }

        if ($this->shell) {
            $data['shell'] = $this->shell;
        }

        if ($this->if) {
            $data['if'] = $this->if;
        }

        if ($this->continueOnError) {
            $data['continue-on-error'] = $this->continueOnError;
        }

        if (!empty($this->env)) {
            $data['env'] = $this->env;
        }

        if ($this->workingDirectory) {
            $data['working-directory'] = $this->workingDirectory;
        }

        return $data;
    }

    public function toYaml(): string
    {
        $yaml = '';
        if ($this->name) {
            $yaml .= "name: {$this->name}\n";
        }
        if ($this->uses) {
            $yaml .= "uses: {$this->uses}\n";
        }
        if ($this->run) {
            $yaml .= "run: |\n";
            $lines = explode("\n", $this->run);
            foreach ($lines as $line) {
                $yaml .= "  {$line}\n";
            }
        }
        return $yaml;
    }

    public static function fromArray(array $data): self
    {
        $step = new self();

        if (isset($data['name'])) {
            $step->name($data['name']);
        }

        if (isset($data['id'])) {
            $step->id($data['id']);
        }

        if (isset($data['uses'])) {
            $step->uses($data['uses']);
        }

        if (isset($data['with'])) {
            $step->with($data['with']);
        }

        if (isset($data['run'])) {
            $step->run($data['run']);
        }

        if (isset($data['shell'])) {
            $step->shell($data['shell']);
        }

        if (isset($data['if'])) {
            $step->if($data['if']);
        }

        if (isset($data['continue-on-error'])) {
            $step->continueOnError($data['continue-on-error']);
        }

        if (isset($data['env'])) {
            $step->env($data['env']);
        }

        if (isset($data['working-directory'])) {
            $step->workingDirectory($data['working-directory']);
        }

        return $step;
    }
}

