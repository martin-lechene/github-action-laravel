<?php

return [
    'enabled' => env('GITHUB_ACTIONS_ENABLED', true),
    
    'workflows_path' => '.github/workflows',
    'templates_path' => __DIR__ . '/../resources/templates',
    'backups_path' => '.github/workflows/.backups',
    
    'defaults' => [
        'runner' => 'ubuntu-latest',
        'php_versions' => ['8.2', '8.3'],
        'databases' => ['mysql:8.0'],
        'extensions' => ['pdo', 'json', 'bcmath'],
    ],
    
    'git' => [
        'auto_commit' => env('GA_AUTO_COMMIT', false),
        'commit_message' => 'chore: generate GitHub Actions workflow',
        'auto_push' => env('GA_AUTO_PUSH', false),
    ],
    
    'notifications' => [
        'slack' => env('SLACK_WEBHOOK_URL'),
        'discord' => env('DISCORD_WEBHOOK_URL'),
    ],
    
    'validation' => [
        'enabled' => true,
        'strict' => false,
        'check_actions' => true,
    ],
    
    'secrets' => [
        'auto_detect' => true,
        'generate_instructions' => true,
    ],
    
    'templates' => [
        'engine' => 'handlebars',
        'cache' => true,
    ],
];

