<?php

namespace MartinLechene\GitHubActions\Validators;

use MartinLechene\GitHubActions\Models\Workflow;
use Symfony\Component\Yaml\Yaml;

class WorkflowValidator
{
    protected YamlValidator $yamlValidator;
    protected array $warnings = [];
    protected array $errors = [];

    public function __construct(YamlValidator $yamlValidator)
    {
        $this->yamlValidator = $yamlValidator;
    }

    public function validate(Workflow $workflow): bool
    {
        $this->warnings = [];
        $this->errors = [];

        $yaml = $workflow->toYaml();
        
        try {
            $this->yamlValidator->validate($yaml);
        } catch (\InvalidArgumentException $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        $this->validateStructure($workflow);
        $this->validateJobs($workflow);

        return empty($this->errors);
    }

    public function validateYaml(string $yaml): bool
    {
        $this->warnings = [];
        $this->errors = [];

        try {
            $this->yamlValidator->validate($yaml);
        } catch (\InvalidArgumentException $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        $data = Yaml::parse($yaml);
        $this->validateWorkflowData($data);

        return empty($this->errors);
    }

    protected function validateStructure(Workflow $workflow): void
    {
        if (empty($workflow->getOn()) && !$workflow->getSchedule()) {
            $this->warnings[] = "Workflow has no triggers defined";
        }

        if ($workflow->getJobs()->isEmpty()) {
            $this->errors[] = "Workflow must have at least one job";
        }
    }

    protected function validateJobs(Workflow $workflow): void
    {
        foreach ($workflow->getJobs() as $job) {
            if (empty($job->getRunsOn())) {
                $this->errors[] = "Job '{$job->getId()}' must have a runs-on value";
            }

            if ($job->getSteps()->isEmpty()) {
                $this->warnings[] = "Job '{$job->getId()}' has no steps";
            }
        }
    }

    protected function validateWorkflowData(array $data): void
    {
        if (!isset($data['name'])) {
            $this->warnings[] = "Workflow should have a name";
        }

        if (!isset($data['on']) && !isset($data['schedule'])) {
            $this->warnings[] = "Workflow has no triggers defined";
        }

        if (!isset($data['jobs']) || empty($data['jobs'])) {
            $this->errors[] = "Workflow must have at least one job";
        }
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

