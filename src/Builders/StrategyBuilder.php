<?php

namespace MartinLechene\GitHubActions\Builders;

class StrategyBuilder
{
    protected JobBuilder $jobBuilder;
    protected array $matrix = [];
    protected bool $failFast = true;
    protected int $maxParallel = 0;
    protected array $exclude = [];
    protected array $include = [];

    public function __construct(JobBuilder $jobBuilder)
    {
        $this->jobBuilder = $jobBuilder;
    }

    public function matrix(string $key, array $values): self
    {
        $this->matrix[$key] = $values;
        return $this;
    }

    public function failFast(bool $failFast = true): self
    {
        $this->failFast = $failFast;
        return $this;
    }

    public function maxParallel(int $max): self
    {
        $this->maxParallel = $max;
        return $this;
    }

    public function exclude(array $exclude): self
    {
        $this->exclude[] = $exclude;
        return $this;
    }

    public function include(array $include): self
    {
        $this->include[] = $include;
        return $this;
    }

    public function end(): JobBuilder
    {
        $strategy = [
            'fail-fast' => $this->failFast,
        ];

        if (!empty($this->matrix)) {
            $strategy['matrix'] = $this->matrix;
        }

        if ($this->maxParallel > 0) {
            $strategy['max-parallel'] = $this->maxParallel;
        }

        if (!empty($this->exclude)) {
            $strategy['matrix']['exclude'] = $this->exclude;
        }

        if (!empty($this->include)) {
            $strategy['matrix']['include'] = $this->include;
        }

        $this->jobBuilder->setStrategy($strategy);
        return $this->jobBuilder;
    }
}

