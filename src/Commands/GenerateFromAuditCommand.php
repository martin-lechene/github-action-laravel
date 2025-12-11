<?php

namespace MartinLechene\GitHubActions\Commands;

use Illuminate\Console\Command;
use MartinLechene\GitHubActions\Generators\WorkflowGenerator;
use Illuminate\Support\Facades\File;

class GenerateFromAuditCommand extends Command
{
    protected $signature = 'github-actions:generate-from-audit 
                            {file? : Audit report file path}
                            {--output= : Output directory}';

    protected $description = 'Generate workflows from audit report';

    protected WorkflowGenerator $generator;

    public function __construct(WorkflowGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    public function handle(): int
    {
        $file = $this->argument('file') ?? storage_path('app/audit-report.json');

        if (!file_exists($file)) {
            $this->error("Audit file not found: {$file}");
            $this->info("Run: php artisan audit:run --output={$file}");
            return 1;
        }

        $this->info("Reading audit report: {$file}");
        $audit = json_decode(file_get_contents($file), true);

        if (!$audit) {
            $this->error("Invalid audit file format");
            return 1;
        }

        $workflows = $this->convertAuditToWorkflows($audit);

        $outputDir = $this->option('output') ?? config('github-actions.workflows_path');
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        foreach ($workflows as $name => $workflow) {
            $path = $outputDir . '/' . $workflow->getFilename();
            $workflow->save($path);
            $this->info("Generated: {$path}");
        }

        $this->info('✅ Workflows generated from audit!');
        return 0;
    }

    protected function convertAuditToWorkflows(array $audit): array
    {
        $workflows = [];

        if ($this->hasCriticalFindings($audit)) {
            $workflows['security'] = $this->generateSecurityWorkflow($audit);
        }

        if ($this->hasPerformanceFindings($audit)) {
            $workflows['performance'] = $this->generatePerformanceWorkflow($audit);
        }

        if ($this->hasQualityFindings($audit)) {
            $workflows['code-quality'] = $this->generateQualityWorkflow($audit);
        }

        return $workflows;
    }

    protected function hasCriticalFindings(array $audit): bool
    {
        $findings = $audit['findings'] ?? [];
        foreach ($findings as $finding) {
            if (($finding['severity'] ?? '') === 'critical') {
                return true;
            }
        }
        return false;
    }

    protected function hasPerformanceFindings(array $audit): bool
    {
        $findings = $audit['findings'] ?? [];
        foreach ($findings as $finding) {
            if (($finding['category'] ?? '') === 'performance') {
                return true;
            }
        }
        return false;
    }

    protected function hasQualityFindings(array $audit): bool
    {
        $findings = $audit['findings'] ?? [];
        foreach ($findings as $finding) {
            if (($finding['category'] ?? '') === 'quality') {
                return true;
            }
        }
        return false;
    }

    protected function generateSecurityWorkflow(array $audit)
    {
        $config = [
            'name' => 'Security Scan',
            'on' => [
                'push' => ['branches' => ['main']],
                'pull_request' => ['branches' => ['main']],
            ],
            'jobs' => [
                'security' => [
                    'runs-on' => 'ubuntu-latest',
                    'steps' => [
                        ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                        ['name' => 'Composer audit', 'run' => 'composer audit'],
                    ],
                ],
            ],
        ];

        return $this->generator->generate($config);
    }

    protected function generatePerformanceWorkflow(array $audit)
    {
        $config = [
            'name' => 'Performance Tests',
            'on' => [
                'push' => ['branches' => ['main']],
            ],
            'jobs' => [
                'performance' => [
                    'runs-on' => 'ubuntu-latest',
                    'steps' => [
                        ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                        ['name' => 'Performance test', 'run' => 'echo "Performance tests"'],
                    ],
                ],
            ],
        ];

        return $this->generator->generate($config);
    }

    protected function generateQualityWorkflow(array $audit)
    {
        $config = [
            'name' => 'Code Quality',
            'on' => [
                'push' => ['branches' => ['main']],
                'pull_request' => ['branches' => ['main']],
            ],
            'jobs' => [
                'quality' => [
                    'runs-on' => 'ubuntu-latest',
                    'steps' => [
                        ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                        ['name' => 'Install dependencies', 'run' => 'composer install'],
                        ['name' => 'Run PHPStan', 'run' => 'vendor/bin/phpstan analyse'],
                    ],
                ],
            ],
        ];

        return $this->generator->generate($config);
    }
}

