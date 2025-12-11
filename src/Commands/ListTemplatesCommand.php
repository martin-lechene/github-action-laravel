<?php

namespace MartinLechene\GitHubActions\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ListTemplatesCommand extends Command
{
    protected $signature = 'github-actions:list-templates';
    protected $description = 'List available workflow templates';

    public function handle(): int
    {
        $templatesPath = config('github-actions.templates_path');
        
        if (!is_dir($templatesPath)) {
            $this->error("Templates directory not found: {$templatesPath}");
            return 1;
        }

        $this->info('Available Templates:');
        $this->line('');

        $categories = File::directories($templatesPath);

        foreach ($categories as $categoryPath) {
            $category = basename($categoryPath);
            $this->line("<comment>{$category}:</comment>");

            $templates = File::glob($categoryPath . '/*.template');
            foreach ($templates as $template) {
                $name = basename($template, '.template');
                $this->line("  - {$name}");
            }

            $this->line('');
        }

        return 0;
    }
}

