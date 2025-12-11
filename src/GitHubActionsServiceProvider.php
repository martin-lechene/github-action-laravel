<?php

namespace MartinLechene\GitHubActions;

use Illuminate\Support\ServiceProvider;
use MartinLechene\GitHubActions\Commands\GenerateWorkflowCommand;
use MartinLechene\GitHubActions\Commands\ListTemplatesCommand;
use MartinLechene\GitHubActions\Commands\ValidateWorkflowCommand;
use MartinLechene\GitHubActions\Commands\GenerateFromAuditCommand;
use MartinLechene\GitHubActions\Services\TemplateEngine;
use MartinLechene\GitHubActions\Services\ComposerAnalyzer;
use MartinLechene\GitHubActions\Services\ProjectAnalyzer;
use MartinLechene\GitHubActions\Generators\WorkflowGenerator;
use MartinLechene\GitHubActions\Validators\WorkflowValidator;
use MartinLechene\GitHubActions\Validators\YamlValidator;

class GitHubActionsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/github-actions.php',
            'github-actions'
        );

        $this->app->singleton('github-actions.template-engine', function ($app) {
            return new TemplateEngine();
        });

        $this->app->singleton('github-actions.composer-analyzer', function ($app) {
            return new ComposerAnalyzer();
        });

        $this->app->singleton('github-actions.project-analyzer', function ($app) {
            return new ProjectAnalyzer(
                $app->make('github-actions.composer-analyzer')
            );
        });

        $this->app->singleton('github-actions.workflow-generator', function ($app) {
            return new WorkflowGenerator(
                $app->make('github-actions.template-engine')
            );
        });

        $this->app->singleton('github-actions.workflow-validator', function ($app) {
            return new WorkflowValidator(
                $app->make('github-actions.yaml-validator')
            );
        });

        $this->app->singleton('github-actions.yaml-validator', function ($app) {
            return new YamlValidator();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateWorkflowCommand::class,
                ListTemplatesCommand::class,
                ValidateWorkflowCommand::class,
                GenerateFromAuditCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/github-actions.php' => config_path('github-actions.php'),
        ], 'github-actions-config');
    }
}

