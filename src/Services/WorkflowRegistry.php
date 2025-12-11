<?php

namespace MartinLechene\GitHubActions\Services;

use MartinLechene\GitHubActions\Models\Workflow;
use Illuminate\Support\Facades\Storage;

class WorkflowRegistry
{
    public function save(Workflow $workflow, string $name): bool
    {
        $path = "workflow-configs/{$name}.json";
        return Storage::put($path, $workflow->toJson());
    }

    public function recall(string $name): ?Workflow
    {
        $path = "workflow-configs/{$name}.json";
        
        if (!Storage::exists($path)) {
            return null;
        }

        $data = Storage::get($path);
        return Workflow::fromJson($data);
    }

    public function list(): array
    {
        $files = Storage::files('workflow-configs');
        $names = [];

        foreach ($files as $file) {
            $name = basename($file, '.json');
            $names[] = $name;
        }

        return $names;
    }

    public function delete(string $name): bool
    {
        $path = "workflow-configs/{$name}.json";
        return Storage::delete($path);
    }
}

