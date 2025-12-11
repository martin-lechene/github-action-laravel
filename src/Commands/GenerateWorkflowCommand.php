<?php

namespace MartinLechene\GitHubActions\Commands;

use Illuminate\Console\Command;
use MartinLechene\GitHubActions\Generators\WorkflowGenerator;
use MartinLechene\GitHubActions\Validators\WorkflowValidator;
use MartinLechene\GitHubActions\Services\ProjectAnalyzer;
use Illuminate\Support\Facades\File;

class GenerateWorkflowCommand extends Command
{
    protected $signature = 'github-actions:generate 
                            {--preset= : Preset to use (testing, code-quality, security, deployment, documentation, performance)}
                            {--config= : Use saved configuration}
                            {--branches= : Comma-separated list of branches}
                            {--php-versions= : Comma-separated PHP versions}
                            {--name= : Workflow name}
                            {--merge : Merge with existing workflow}
                            {--diff : Show diff before applying}
                            {--backup : Create backup before replacing}
                            {--debug : Show debug information}
                            {--no-interaction : Run in non-interactive mode}';

    protected $description = 'Generate a GitHub Actions workflow';

    protected WorkflowGenerator $generator;
    protected WorkflowValidator $validator;
    protected ProjectAnalyzer $projectAnalyzer;

    public function __construct(
        WorkflowGenerator $generator,
        WorkflowValidator $validator,
        ProjectAnalyzer $projectAnalyzer
    ) {
        parent::__construct();
        $this->generator = $generator;
        $this->validator = $validator;
        $this->projectAnalyzer = $projectAnalyzer;
    }

    public function handle(): int
    {
        $this->info('Welcome to Laravel GitHub Actions Generator!');

        $preset = $this->option('preset') ?? $this->askPreset();
        $config = $this->gatherConfig($preset);
        
        if ($this->option('debug')) {
            $this->line('Configuration:');
            $this->line(json_encode($config, JSON_PRETTY_PRINT));
        }

        $workflow = $this->generator->generate($config);

        if (!$this->validator->validate($workflow)) {
            $this->error('Workflow validation failed:');
            foreach ($this->validator->getErrors() as $error) {
                $this->error("  - {$error}");
            }
            return 1;
        }

        $warnings = $this->validator->getWarnings();
        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                $this->warn("  - {$warning}");
            }
        }

        $filename = $workflow->getFilename();
        $path = config('github-actions.workflows_path') . '/' . $filename;

        if ($this->option('backup') && file_exists($path)) {
            $this->createBackup($path);
        }

        if ($this->option('diff') && file_exists($path)) {
            $this->showDiff($path, $workflow->toYaml());
            if (!$this->confirm('Apply changes?', true)) {
                return 0;
            }
        }

        if ($this->option('merge') && file_exists($path)) {
            $this->mergeWorkflow($path, $workflow);
        } else {
            $workflow->save($path);
        }

        $this->info("Generated workflow: {$path} ✅");

        if ($this->confirm('Store configuration for later regeneration?', false)) {
            $this->storeConfig($config);
        }

        return 0;
    }

    protected function askPreset(): string
    {
        $presets = [
            'testing' => 'Testing Pipeline',
            'code-quality' => 'Code Quality',
            'security' => 'Security Scan',
            'deployment' => 'Deployment',
            'documentation' => 'Documentation',
            'performance' => 'Performance',
            'custom' => 'Custom',
        ];

        $choice = $this->choice(
            'Which preset do you want to use?',
            array_values($presets),
            0
        );

        return array_search($choice, $presets);
    }

    protected function gatherConfig(string $preset): array
    {
        $metadata = $this->projectAnalyzer->analyze();

        $config = [
            'name' => $this->option('name') ?? $this->ask('Workflow name', $preset),
            'on' => [],
        ];

        if (!$this->option('no-interaction')) {
            $branches = $this->option('branches') 
                ? explode(',', $this->option('branches'))
                : explode(',', $this->ask('Which branches to trigger on?', 'main,develop'));

            $config['on']['push'] = ['branches' => $branches];
            $config['on']['pull_request'] = ['branches' => $branches];
        } else {
            $branches = $this->option('branches') 
                ? explode(',', $this->option('branches'))
                : ['main'];
            $config['on']['push'] = ['branches' => $branches];
            $config['on']['pull_request'] = ['branches' => $branches];
        }

        $phpVersions = $this->option('php-versions')
            ? explode(',', $this->option('php-versions'))
            : ($metadata->getPhpVersions() ?: ['8.2', '8.3']);

        $config['jobs'] = $this->buildJobsForPreset($preset, $phpVersions, $metadata);

        return $config;
    }

    protected function buildJobsForPreset(string $preset, array $phpVersions, $metadata): array
    {
        $jobs = [];

        switch ($preset) {
            case 'testing':
                $jobs['test'] = $this->buildTestingJob($phpVersions, $metadata);
                break;
            case 'code-quality':
                $jobs['code-quality'] = $this->buildCodeQualityJob($phpVersions);
                break;
            case 'security':
                $jobs['security'] = $this->buildSecurityJob($phpVersions);
                break;
            case 'deployment':
                $jobs['deploy'] = $this->buildDeploymentJob($phpVersions);
                break;
            case 'documentation':
                $jobs['docs'] = $this->buildDocumentationJob();
                break;
            case 'performance':
                $jobs['performance'] = $this->buildPerformanceJob($phpVersions);
                break;
            default:
                $jobs['test'] = $this->buildTestingJob($phpVersions, $metadata);
        }

        return $jobs;
    }

    protected function buildTestingJob(array $phpVersions, $metadata): array
    {
        $job = [
            'runs-on' => 'ubuntu-latest',
            'strategy' => [
                'fail-fast' => false,
                'matrix' => [
                    'php-version' => $phpVersions,
                ],
            ],
            'steps' => [
                ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                [
                    'name' => 'Setup PHP',
                    'uses' => 'shivammathur/setup-php@v2',
                    'with' => [
                        'php-version' => '${{ matrix.php-version }}',
                        'extensions' => 'pdo,json,bcmath',
                        'tools' => 'composer:v2',
                    ],
                ],
                [
                    'name' => 'Cache dependencies',
                    'uses' => 'actions/cache@v3',
                    'with' => [
                        'path' => "vendor\n~/.composer/cache",
                        'key' => '${{ runner.os }}-php-${{ matrix.php-version }}-composer-${{ hashFiles(\'**/composer.lock\') }}',
                    ],
                ],
                ['name' => 'Install dependencies', 'run' => 'composer install --prefer-dist --no-interaction'],
                ['name' => 'Copy .env.example', 'run' => 'cp .env.example .env'],
                ['name' => 'Generate key', 'run' => 'php artisan key:generate'],
                ['name' => 'Run migrations', 'run' => 'php artisan migrate --force'],
                ['name' => 'Run tests', 'run' => 'php artisan test'],
            ],
        ];

        if (!empty($metadata->getDatabases())) {
            $job['strategy']['matrix']['database'] = $metadata->getDatabases();
        }

        return $job;
    }

    protected function buildCodeQualityJob(array $phpVersions): array
    {
        return [
            'runs-on' => 'ubuntu-latest',
            'strategy' => [
                'matrix' => ['php-version' => $phpVersions],
            ],
            'steps' => [
                ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                [
                    'name' => 'Setup PHP',
                    'uses' => 'shivammathur/setup-php@v2',
                    'with' => ['php-version' => '${{ matrix.php-version }}'],
                ],
                ['name' => 'Install dependencies', 'run' => 'composer install --prefer-dist'],
                ['name' => 'Run PHPStan', 'run' => 'vendor/bin/phpstan analyse'],
                ['name' => 'Run PHPCS', 'run' => 'vendor/bin/phpcs'],
            ],
        ];
    }

    protected function buildSecurityJob(array $phpVersions): array
    {
        return [
            'runs-on' => 'ubuntu-latest',
            'steps' => [
                ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                ['name' => 'Composer audit', 'run' => 'composer audit'],
                ['name' => 'Security check', 'run' => 'composer require --dev roave/security-advisories:dev-latest'],
            ],
        ];
    }

    protected function buildDeploymentJob(array $phpVersions): array
    {
        return [
            'runs-on' => 'ubuntu-latest',
            'environment' => 'production',
            'needs' => ['test'],
            'steps' => [
                ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                ['name' => 'Deploy', 'run' => 'echo "Deploy script here"'],
            ],
        ];
    }

    protected function buildDocumentationJob(): array
    {
        return [
            'runs-on' => 'ubuntu-latest',
            'steps' => [
                ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                ['name' => 'Generate docs', 'run' => 'echo "Generate documentation"'],
            ],
        ];
    }

    protected function buildPerformanceJob(array $phpVersions): array
    {
        return [
            'runs-on' => 'ubuntu-latest',
            'steps' => [
                ['name' => 'Checkout', 'uses' => 'actions/checkout@v4'],
                ['name' => 'Performance test', 'run' => 'echo "Performance tests"'],
            ],
        ];
    }

    protected function createBackup(string $path): void
    {
        $backupDir = config('github-actions.backups_path');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupPath = $backupDir . '/' . basename($path) . '.' . date('Y-m-d_H-i-s');
        copy($path, $backupPath);
        $this->info("Backup created: {$backupPath}");
    }

    protected function showDiff(string $path, string $newContent): void
    {
        $oldContent = file_get_contents($path);
        $this->line('Diff:');
        $this->line($oldContent !== $newContent ? 'Files differ' : 'Files are identical');
    }

    protected function mergeWorkflow(string $path, $workflow): void
    {
        $this->warn('Merge functionality not yet implemented. Overwriting file.');
        $workflow->save($path);
    }

    protected function storeConfig(array $config): void
    {
        $name = $this->ask('Configuration name?');
        $storagePath = storage_path('app/github-actions-configs');
        
        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        file_put_contents(
            $storagePath . '/' . $name . '.json',
            json_encode($config, JSON_PRETTY_PRINT)
        );

        $this->info("Configuration saved as: {$name}");
    }
}

