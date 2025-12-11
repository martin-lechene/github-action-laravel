<?php

namespace MartinLechene\GitHubActions\Builders;

use MartinLechene\GitHubActions\Models\Job;
use MartinLechene\GitHubActions\Models\Step;

class JobBuilder
{
    protected Job $job;
    protected ?StrategyBuilder $strategyBuilder = null;
    protected ?ServiceBuilder $currentServiceBuilder = null;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public static function make(string $id, string $runsOn = 'ubuntu-latest'): self
    {
        return new self(Job::make($id, $runsOn));
    }

    public function name(string $name): self
    {
        $this->job->setName($name);
        return $this;
    }

    public function runsOn(string $runner): self
    {
        $this->job->runsOn($runner);
        return $this;
    }

    public function environment(string $environment): self
    {
        $this->job->environment($environment);
        return $this;
    }

    public function timeout(int $minutes): self
    {
        $this->job->timeout($minutes);
        return $this;
    }

    public function continueOnError(bool $continue = true): self
    {
        $this->job->continueOnError($continue);
        return $this;
    }

    public function concurrency(string $group, bool $cancelInProgress = true): self
    {
        $this->job->concurrency($group, $cancelInProgress);
        return $this;
    }

    public function permissions(array $permissions): self
    {
        $this->job->permissions($permissions);
        return $this;
    }

    public function strategy(): StrategyBuilder
    {
        if (!$this->strategyBuilder) {
            $this->strategyBuilder = new StrategyBuilder($this);
        }
        return $this->strategyBuilder;
    }

    public function needs(array $jobs): self
    {
        $this->job->needs($jobs);
        return $this;
    }

    public function if(string $condition): self
    {
        $this->job->if($condition);
        return $this;
    }

    public function defaults(array $defaults): self
    {
        $this->job->defaults($defaults);
        return $this;
    }

    public function service(string $name, callable $callback): self
    {
        $serviceBuilder = new ServiceBuilder($name);
        $callback($serviceBuilder);
        $this->job->addService($name, $serviceBuilder->build());
        return $this;
    }

    public function step(string $name, ?string $uses = null, ?array $with = null, ?string $run = null, ?array $env = null): self
    {
        $step = Step::make();
        
        if ($name) {
            $step->name($name);
        }
        
        if ($uses) {
            $step->uses($uses);
        }
        
        if ($with) {
            $step->with($with);
        }
        
        if ($run) {
            $step->run($run);
        }
        
        if ($env) {
            $step->env($env);
        }
        
        $this->job->addStep($step);
        return $this;
    }

    public function env(string $key, string $value): self
    {
        $this->job->env($key, $value);
        return $this;
    }

    public function setStrategy(array $strategy): self
    {
        $this->job->strategy($strategy);
        return $this;
    }

    public function build(): Job
    {
        return $this->job;
    }

    public function end(): self
    {
        return $this;
    }
}

