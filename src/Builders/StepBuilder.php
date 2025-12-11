<?php

namespace MartinLechene\GitHubActions\Builders;

use MartinLechene\GitHubActions\Models\Step;

class StepBuilder
{
    protected Step $step;

    public function __construct(Step $step = null)
    {
        $this->step = $step ?? Step::make();
    }

    public static function make(): self
    {
        return new self();
    }

    public function name(string $name): self
    {
        $this->step->name($name);
        return $this;
    }

    public function uses(string $action): self
    {
        $this->step->uses($action);
        return $this;
    }

    public function with(array $with): self
    {
        $this->step->with($with);
        return $this;
    }

    public function id(string $id): self
    {
        $this->step->id($id);
        return $this;
    }

    public function if(string $condition): self
    {
        $this->step->if($condition);
        return $this;
    }

    public function continueOnError(bool $continue = true): self
    {
        $this->step->continueOnError($continue);
        return $this;
    }

    public function shell(string $shell): self
    {
        $this->step->shell($shell);
        return $this;
    }

    public function run(string $command): self
    {
        $this->step->run($command);
        return $this;
    }

    public function env(array $env): self
    {
        $this->step->env($env);
        return $this;
    }

    public function workingDirectory(string $directory): self
    {
        $this->step->workingDirectory($directory);
        return $this;
    }

    public function build(): Step
    {
        return $this->step;
    }
}

