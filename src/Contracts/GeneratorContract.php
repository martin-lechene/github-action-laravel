<?php

namespace MartinLechene\GitHubActions\Contracts;

use MartinLechene\GitHubActions\Models\Workflow;

interface GeneratorContract
{
    /**
     * Generate a workflow from configuration.
     */
    public function generate(array $config): Workflow;

    /**
     * Generate from a template.
     */
    public function generateFromTemplate(string $template, array $data): Workflow;
}

