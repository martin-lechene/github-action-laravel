# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2024-01-01

### Added
- Initial release
- Workflow generation from presets
- Interactive CLI commands
- Builder pattern for workflows
- Template engine with Handlebars
- YAML validation
- Project analysis
- Integration with audit suite
- 6 workflow presets (testing, code-quality, security, deployment, documentation, performance)
- Support for multiple PHP versions
- Database matrix support
- Secret detection and management
- Workflow registry for saved configurations

### Features
- `github-actions:generate` - Generate workflows interactively
- `github-actions:list-templates` - List available templates
- `github-actions:validate` - Validate workflow files
- `github-actions:generate-from-audit` - Generate from audit reports

