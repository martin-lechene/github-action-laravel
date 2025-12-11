<?php

namespace MartinLechene\GitHubActions\Models;

use Illuminate\Support\Collection;

class Job
{
    protected string $id;
    protected ?string $name = null;
    protected string $runsOn;
    protected ?string $environment = null;
    protected ?int $timeoutMinutes = null;
    protected bool $continueOnError = false;
    protected array $concurrency = [];
    protected array $permissions = [];
    protected array $strategy = [];
    protected array $needs = [];
    protected ?string $if = null;
    protected array $defaults = [];
    protected Collection $services;
    protected Collection $steps;
    protected array $env = [];

    public function __construct(string $id, string $runsOn = 'ubuntu-latest')
    {
        $this->id = $id;
        $this->runsOn = $runsOn;
        $this->services = new Collection();
        $this->steps = new Collection();
    }

    public static function make(string $id, string $runsOn = 'ubuntu-latest'): self
    {
        return new self($id, $runsOn);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function runsOn(string $runner): self
    {
        $this->runsOn = $runner;
        return $this;
    }

    public function getRunsOn(): string
    {
        return $this->runsOn;
    }

    public function environment(string $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    public function getEnvironment(): ?string
    {
        return $this->environment;
    }

    public function timeout(int $minutes): self
    {
        $this->timeoutMinutes = $minutes;
        return $this;
    }

    public function getTimeout(): ?int
    {
        return $this->timeoutMinutes;
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

    public function permissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function strategy(array $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function getStrategy(): array
    {
        return $this->strategy;
    }

    public function needs(array $jobs): self
    {
        $this->needs = $jobs;
        return $this;
    }

    public function getNeeds(): array
    {
        return $this->needs;
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

    public function defaults(array $defaults): self
    {
        $this->defaults = $defaults;
        return $this;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function addService(string $name, array $config): self
    {
        $this->services->put($name, $config);
        return $this;
    }

    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addStep(Step $step): self
    {
        $this->steps->push($step);
        return $this;
    }

    public function getSteps(): Collection
    {
        return $this->steps;
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

    public function toArray(): array
    {
        $data = [
            'runs-on' => $this->runsOn,
        ];

        if ($this->name) {
            $data['name'] = $this->name;
        }

        if ($this->environment) {
            $data['environment'] = $this->environment;
        }

        if ($this->timeoutMinutes) {
            $data['timeout-minutes'] = $this->timeoutMinutes;
        }

        if ($this->continueOnError) {
            $data['continue-on-error'] = $this->continueOnError;
        }

        if (!empty($this->concurrency)) {
            $data['concurrency'] = $this->concurrency;
        }

        if (!empty($this->permissions)) {
            $data['permissions'] = $this->permissions;
        }

        if (!empty($this->strategy)) {
            $data['strategy'] = $this->strategy;
        }

        if (!empty($this->needs)) {
            $data['needs'] = $this->needs;
        }

        if ($this->if) {
            $data['if'] = $this->if;
        }

        if (!empty($this->defaults)) {
            $data['defaults'] = $this->defaults;
        }

        if ($this->services->isNotEmpty()) {
            $data['services'] = $this->services->toArray();
        }

        $steps = [];
        foreach ($this->steps as $step) {
            $steps[] = $step->toArray();
        }
        $data['steps'] = $steps;

        if (!empty($this->env)) {
            $data['env'] = $this->env;
        }

        return $data;
    }

    public static function fromArray(string $id, array $data): self
    {
        $job = new self($id, $data['runs-on'] ?? 'ubuntu-latest');

        if (isset($data['name'])) {
            $job->setName($data['name']);
        }

        if (isset($data['environment'])) {
            $job->environment($data['environment']);
        }

        if (isset($data['timeout-minutes'])) {
            $job->timeout($data['timeout-minutes']);
        }

        if (isset($data['continue-on-error'])) {
            $job->continueOnError($data['continue-on-error']);
        }

        if (isset($data['concurrency'])) {
            $job->concurrency(
                $data['concurrency']['group'],
                $data['concurrency']['cancel-in-progress'] ?? true
            );
        }

        if (isset($data['permissions'])) {
            $job->permissions($data['permissions']);
        }

        if (isset($data['strategy'])) {
            $job->strategy($data['strategy']);
        }

        if (isset($data['needs'])) {
            $job->needs($data['needs']);
        }

        if (isset($data['if'])) {
            $job->if($data['if']);
        }

        if (isset($data['defaults'])) {
            $job->defaults($data['defaults']);
        }

        if (isset($data['services'])) {
            foreach ($data['services'] as $name => $config) {
                $job->addService($name, $config);
            }
        }

        if (isset($data['steps'])) {
            foreach ($data['steps'] as $stepData) {
                $step = Step::fromArray($stepData);
                $job->addStep($step);
            }
        }

        if (isset($data['env'])) {
            foreach ($data['env'] as $key => $value) {
                $job->env($key, $value);
            }
        }

        return $job;
    }
}

