<?php

namespace MartinLechene\GitHubActions\Services;

use MartinLechene\GitHubActions\Models\Workflow;

class SecretDetector
{
    public function detect(Workflow $workflow): array
    {
        $secrets = [];
        
        foreach ($workflow->getAllSteps() as $step) {
            $yaml = $step->toYaml();
            
            if (preg_match_all('/\$\{\{\s*secrets\.(\w+)\s*\}\}/', $yaml, $matches)) {
                $secrets = array_merge($secrets, $matches[1]);
            }
        }
        
        return array_unique($secrets);
    }

    public function generateInstructions(array $secrets): string
    {
        $instructions = "# Configure these secrets in GitHub Settings\n\n";
        $instructions .= "## Instructions\n\n";
        $instructions .= "1. Go to https://github.com/[owner]/[repo]/settings/secrets/actions\n";
        $instructions .= "2. Add the following secrets:\n\n";

        foreach ($secrets as $secret) {
            $instructions .= "### {$secret}\n";
            $instructions .= "- Description: Add description for {$secret}\n";
            $instructions .= "- Value: [Your value here]\n\n";
        }

        return $instructions;
    }
}

