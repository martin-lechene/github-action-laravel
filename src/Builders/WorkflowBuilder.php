<?php

namespace MartinLechene\GitHubActions\Builders;

use MartinLechene\GitHubActions\Models\Workflow;
use MartinLechene\GitHubActions\Models\Job;

class WorkflowBuilder
{
    protected Workflow $workflow;
    protected ?JobBuilder $currentJobBuilder = null;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public static function make(string $name): self
    {
        return new self(Workflow::make($name));
    }

    public function setName(string $name): self
    {
        $this->workflow = Workflow::make($name);
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->workflow->setDescription($description);
        return $this;
    }

    public function on(string $event, array $branches = []): self
    {
        $this->workflow->on($event, $branches);
        return $this;
    }

    public function schedule(string $cron): self
    {
        $this->workflow->schedule($cron);
        return $this;
    }

    public function concurrency(string $group, bool $cancelInProgress = true): self
    {
        $this->workflow->concurrency($group, $cancelInProgress);
        return $this;
    }

    public function env(string $key, string $value): self
    {
        $this->workflow->env($key, $value);
        return $this;
    }

    public function permissions(array $permissions): self
    {
        $this->workflow->permissions($permissions);
        return $this;
    }

    public function job(string $id, callable $callback): self
    {
        $job = Job::make($id);
        $jobBuilder = new JobBuilder($job);
        $callback($jobBuilder);
        $this->workflow->addJob($jobBuilder->build());
        return $this;
    }

    public function generate(): Workflow
    {
        return $this->workflow;
    }

    public function build(): Workflow
    {
        return $this->workflow;
    }
}

