<?php

namespace MartinLechene\GitHubActions\Models;

use Illuminate\Support\Collection;

class Workflow
{
    protected string $name;
    protected ?string $description = null;
    protected array $on = [];
    protected ?string $schedule = null;
    protected array $concurrency = [];
    protected array $env = [];
    protected Collection $jobs;
    protected array $permissions = [];

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->jobs = new Collection();
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    public static function builder(string $name = 'workflow'): \MartinLechene\GitHubActions\Builders\WorkflowBuilder
    {
        return \MartinLechene\GitHubActions\Builders\WorkflowBuilder::make($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function on(string $event, array $branches = []): self
    {
        if (!isset($this->on[$event])) {
            $this->on[$event] = [];
        }

        if (!empty($branches)) {
            $this->on[$event]['branches'] = $branches;
        }

        return $this;
    }

    public function getOn(): array
    {
        return $this->on;
    }

    public function schedule(string $cron): self
    {
        $this->schedule = $cron;
        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function concurrency(string $group, bool $cancelInProgress = true): self
    {
        $this->concurrency = [
            'group' => $group,
            'cancel-in-progress' => $cancelInProgress,
        ];
        return $this;
    }

    public function getConcurrency(): array
    {
        return $this->concurrency;
    }

    public function env(string $key, string $value): self
    {
        $this->env[$key] = $value;
        return $this;
    }

    public function getEnv(): array
    {
        return $this->env;
    }

    public function addJob(Job $job): self
    {
        $this->jobs->put($job->getId(), $job);
        return $this;
    }

    public function getJob(string $id): ?Job
    {
        return $this->jobs->get($id);
    }

    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function permissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
        ];

        if ($this->description) {
            $data['description'] = $this->description;
        }

        if (!empty($this->on)) {
            $data['on'] = $this->on;
        }

        if ($this->schedule) {
            if (!isset($data['on'])) {
                $data['on'] = [];
            }
            $data['on']['schedule'] = [['cron' => $this->schedule]];
        }

        if (!empty($this->concurrency)) {
            $data['concurrency'] = $this->concurrency;
        }

        if (!empty($this->env)) {
            $data['env'] = $this->env;
        }

        if (!empty($this->permissions)) {
            $data['permissions'] = $this->permissions;
        }

        $jobs = [];
        foreach ($this->jobs as $job) {
            $jobs[$job->getId()] = $job->toArray();
        }
        $data['jobs'] = $jobs;

        return $data;
    }

    public function toYaml(): string
    {
        return \Symfony\Component\Yaml\Yaml::dump($this->toArray(), 10, 2, \Symfony\Component\Yaml\Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        $workflow = new self($data['name']);

        if (isset($data['description'])) {
            $workflow->setDescription($data['description']);
        }

        if (isset($data['on'])) {
            foreach ($data['on'] as $event => $config) {
                if ($event === 'schedule') {
                    $workflow->schedule($config[0]['cron']);
                } else {
                    $branches = $config['branches'] ?? [];
                    $workflow->on($event, $branches);
                }
            }
        }

        if (isset($data['concurrency'])) {
            $workflow->concurrency(
                $data['concurrency']['group'],
                $data['concurrency']['cancel-in-progress'] ?? true
            );
        }

        if (isset($data['env'])) {
            foreach ($data['env'] as $key => $value) {
                $workflow->env($key, $value);
            }
        }

        if (isset($data['permissions'])) {
            $workflow->permissions($data['permissions']);
        }

        if (isset($data['jobs'])) {
            foreach ($data['jobs'] as $id => $jobData) {
                $job = Job::fromArray($id, $jobData);
                $workflow->addJob($job);
            }
        }

        return $workflow;
    }

    public function save(string $path): bool
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($path, $this->toYaml()) !== false;
    }

    public function getFilename(): string
    {
        return strtolower(str_replace(' ', '-', $this->name)) . '.yml';
    }

    public function getAllSteps(): Collection
    {
        $steps = new Collection();
        foreach ($this->jobs as $job) {
            $steps = $steps->merge($job->getSteps());
        }
        return $steps;
    }
}

