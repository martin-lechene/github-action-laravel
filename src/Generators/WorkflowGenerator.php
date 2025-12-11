<?php

namespace MartinLechene\GitHubActions\Generators;

use MartinLechene\GitHubActions\Contracts\GeneratorContract;
use MartinLechene\GitHubActions\Models\Workflow;
use MartinLechene\GitHubActions\Services\TemplateEngine;
use MartinLechene\GitHubActions\Builders\WorkflowBuilder;

class WorkflowGenerator implements GeneratorContract
{
    protected TemplateEngine $templateEngine;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    public function generate(array $config): Workflow
    {
        $builder = WorkflowBuilder::make($config['name'] ?? 'workflow');

        if (isset($config['description'])) {
            $builder->setDescription($config['description']);
        }

        if (isset($config['on'])) {
            foreach ($config['on'] as $event => $eventConfig) {
                if ($event === 'schedule') {
                    $builder->schedule($eventConfig[0]['cron'] ?? '0 0 * * *');
                } else {
                    $branches = $eventConfig['branches'] ?? [];
                    $builder->on($event, $branches);
                }
            }
        }

        if (isset($config['concurrency'])) {
            $builder->concurrency(
                $config['concurrency']['group'],
                $config['concurrency']['cancel-in-progress'] ?? true
            );
        }

        if (isset($config['env'])) {
            foreach ($config['env'] as $key => $value) {
                $builder->env($key, $value);
            }
        }

        if (isset($config['permissions'])) {
            $builder->permissions($config['permissions']);
        }

        if (isset($config['jobs'])) {
            foreach ($config['jobs'] as $jobId => $jobConfig) {
                $builder->job($jobId, function ($jobBuilder) use ($jobConfig) {
                    $this->buildJob($jobBuilder, $jobConfig);
                });
            }
        }

        return $builder->generate();
    }

    public function generateFromTemplate(string $template, array $data): Workflow
    {
        $yaml = $this->templateEngine->renderFile($template, $data);
        $array = \Symfony\Component\Yaml\Yaml::parse($yaml);
        
        return $this->generate($array);
    }

    protected function buildJob($jobBuilder, array $config): void
    {
        if (isset($config['name'])) {
            $jobBuilder->name($config['name']);
        }

        if (isset($config['runs-on'])) {
            $jobBuilder->runsOn($config['runs-on']);
        }

        if (isset($config['environment'])) {
            $jobBuilder->environment($config['environment']);
        }

        if (isset($config['timeout-minutes'])) {
            $jobBuilder->timeout($config['timeout-minutes']);
        }

        if (isset($config['continue-on-error'])) {
            $jobBuilder->continueOnError($config['continue-on-error']);
        }

        if (isset($config['needs'])) {
            $jobBuilder->needs($config['needs']);
        }

        if (isset($config['if'])) {
            $jobBuilder->if($config['if']);
        }

        if (isset($config['strategy'])) {
            $strategy = $jobBuilder->strategy();
            
            if (isset($config['strategy']['fail-fast'])) {
                $strategy->failFast($config['strategy']['fail-fast']);
            }

            if (isset($config['strategy']['matrix'])) {
                foreach ($config['strategy']['matrix'] as $key => $values) {
                    if ($key !== 'exclude' && $key !== 'include') {
                        $strategy->matrix($key, $values);
                    }
                }
            }

            if (isset($config['strategy']['max-parallel'])) {
                $strategy->maxParallel($config['strategy']['max-parallel']);
            }

            $strategy->end();
        }

        if (isset($config['steps'])) {
            foreach ($config['steps'] as $stepConfig) {
                $jobBuilder->step(
                    $stepConfig['name'] ?? null,
                    $stepConfig['uses'] ?? null,
                    $stepConfig['with'] ?? null,
                    $stepConfig['run'] ?? null,
                    $stepConfig['env'] ?? null
                );
            }
        }

        if (isset($config['env'])) {
            foreach ($config['env'] as $key => $value) {
                $jobBuilder->env($key, $value);
            }
        }
    }
}

