<?php

namespace MartinLechene\GitHubActions\Contracts;

interface TemplateContract
{
    /**
     * Render template with data.
     */
    public function render(string $template, array $data): string;

    /**
     * Check if template exists.
     */
    public function exists(string $template): bool;

    /**
     * Get template path.
     */
    public function getPath(string $template): string;
}

