<?php

namespace MartinLechene\GitHubActions\Generators;

use MartinLechene\GitHubActions\Models\Step;
use MartinLechene\GitHubActions\Builders\StepBuilder;

class StepGenerator
{
    public function generate(array $config): Step
    {
        $builder = StepBuilder::make();

        if (isset($config['name'])) {
            $builder->name($config['name']);
        }

        if (isset($config['id'])) {
            $builder->id($config['id']);
        }

        if (isset($config['uses'])) {
            $builder->uses($config['uses']);
        }

        if (isset($config['with'])) {
            $builder->with($config['with']);
        }

        if (isset($config['run'])) {
            $builder->run($config['run']);
        }

        if (isset($config['shell'])) {
            $builder->shell($config['shell']);
        }

        if (isset($config['if'])) {
            $builder->if($config['if']);
        }

        if (isset($config['continue-on-error'])) {
            $builder->continueOnError($config['continue-on-error']);
        }

        if (isset($config['env'])) {
            $builder->env($config['env']);
        }

        if (isset($config['working-directory'])) {
            $builder->workingDirectory($config['working-directory']);
        }

        return $builder->build();
    }
}

