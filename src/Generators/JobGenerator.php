<?php

namespace MartinLechene\GitHubActions\Generators;

use MartinLechene\GitHubActions\Models\Job;
use MartinLechene\GitHubActions\Builders\JobBuilder;

class JobGenerator
{
    public function generate(array $config): Job
    {
        $id = $config['id'] ?? 'job';
        $runsOn = $config['runs-on'] ?? 'ubuntu-latest';
        
        $builder = JobBuilder::make($id, $runsOn);

        if (isset($config['name'])) {
            $builder->name($config['name']);
        }

        if (isset($config['environment'])) {
            $builder->environment($config['environment']);
        }

        if (isset($config['timeout-minutes'])) {
            $builder->timeout($config['timeout-minutes']);
        }

        if (isset($config['needs'])) {
            $builder->needs($config['needs']);
        }

        if (isset($config['strategy'])) {
            $strategy = $builder->strategy();
            
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

            $strategy->end();
        }

        return $builder->build();
    }
}

