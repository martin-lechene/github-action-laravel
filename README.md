# Laravel GitHub Actions Generator

Package Laravel complet pour générer automatiquement des workflows GitHub Actions à partir d'une interface CLI interactive.

## Installation

```bash
composer require martin-lechene/github-actions-laravel
```

## Configuration

Publier la configuration :

```bash
php artisan vendor:publish --tag=github-actions-config
```

## Usage

### Génération Interactive

```bash
php artisan github-actions:generate
```

### Utiliser un Preset

```bash
php artisan github-actions:generate --preset=testing
```

### Options Disponibles

- `--preset` : Preset à utiliser (testing, code-quality, security, deployment, documentation, performance)
- `--config` : Utiliser une configuration sauvegardée
- `--branches` : Liste de branches séparées par des virgules
- `--php-versions` : Versions PHP séparées par des virgules
- `--name` : Nom du workflow
- `--merge` : Fusionner avec le workflow existant
- `--diff` : Afficher les différences avant application
- `--backup` : Créer une sauvegarde avant remplacement
- `--debug` : Afficher les informations de debug

### Valider un Workflow

```bash
php artisan github-actions:validate .github/workflows/testing.yml
```

### Lister les Templates

```bash
php artisan github-actions:list-templates
```

### Générer depuis un Audit

```bash
php artisan github-actions:generate-from-audit audit-report.json
```

## Presets Disponibles

1. **Testing Pipeline** - Tests avec PHPUnit, matrices PHP/DB, coverage
2. **Code Quality** - PHPStan, PHPCS, PHPInsights
3. **Security Scan** - Composer audit, security checker, SAST
4. **Deployment** - Déploiement SSH, migrations, cache warming
5. **Documentation** - Génération de docs, GitHub Pages
6. **Performance** - Benchmarks, profiling, load testing

## Builder Pattern

```php
use MartinLechene\GitHubActions\Builders\WorkflowBuilder;

$workflow = WorkflowBuilder::make('Testing')
    ->setDescription('Run tests')
    ->on('push', ['main', 'develop'])
    ->job('test', function ($job) {
        $job
            ->runsOn('ubuntu-latest')
            ->strategy()
                ->matrix('php', ['8.2', '8.3'])
                ->end()
            ->step('Checkout', 'actions/checkout@v4')
            ->step('Run tests', null, null, 'php artisan test');
    })
    ->generate();

$workflow->save('.github/workflows/testing.yml');
```

## License

MIT

