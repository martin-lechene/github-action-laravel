<?php

namespace MartinLechene\GitHubActions\Commands;

use Illuminate\Console\Command;
use MartinLechene\GitHubActions\Validators\WorkflowValidator;
use Symfony\Component\Yaml\Yaml;

class ValidateWorkflowCommand extends Command
{
    protected $signature = 'github-actions:validate {file?}';
    protected $description = 'Validate a GitHub Actions workflow file';

    protected WorkflowValidator $validator;

    public function __construct(WorkflowValidator $validator)
    {
        parent::__construct();
        $this->validator = $validator;
    }

    public function handle(): int
    {
        $file = $this->argument('file') ?? '.github/workflows/test.yml';

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Validating: {$file}");

        $yaml = file_get_contents($file);

        if (!$this->validator->validateYaml($yaml)) {
            $this->error('Validation failed:');
            foreach ($this->validator->getErrors() as $error) {
                $this->error("  - {$error}");
            }
            return 1;
        }

        $warnings = $this->validator->getWarnings();
        if (!empty($warnings)) {
            $this->warn('Warnings:');
            foreach ($warnings as $warning) {
                $this->warn("  - {$warning}");
            }
        }

        $this->info('✅ Workflow is valid!');
        return 0;
    }
}

