<?php

namespace MartinLechene\GitHubActions\Services;

use MartinLechene\GitHubActions\Models\Workflow;
use Symfony\Component\Process\Process;

class WorkflowPublisher
{
    public function publish(Workflow $workflow, bool $autoCommit = false): bool
    {
        $path = config('github-actions.workflows_path') . '/' . $workflow->getFilename();
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $saved = file_put_contents($path, $workflow->toYaml()) !== false;

        if ($saved && $autoCommit) {
            $this->commitAndPush($path, $workflow->getName());
        }

        return $saved;
    }

    protected function commitAndPush(string $path, string $workflowName): void
    {
        $commitMessage = config('github-actions.git.commit_message', 'chore: generate GitHub Actions workflow');
        $commitMessage = str_replace('{name}', $workflowName, $commitMessage);

        $commands = [
            ['git', 'add', $path],
            ['git', 'commit', '-m', $commitMessage],
        ];

        if (config('github-actions.git.auto_push', false)) {
            $commands[] = ['git', 'push'];
        }

        foreach ($commands as $command) {
            $process = new Process($command);
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new \RuntimeException("Git command failed: " . $process->getErrorOutput());
            }
        }
    }
}

