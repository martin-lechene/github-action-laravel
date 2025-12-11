<?php

namespace MartinLechene\GitHubActions\Validators;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlValidator
{
    public function validate(string $yaml): bool
    {
        try {
            Yaml::parse($yaml);
            return true;
        } catch (ParseException $e) {
            throw new \InvalidArgumentException("Invalid YAML: " . $e->getMessage());
        }
    }

    public function validateFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        return $this->validate($content);
    }

    public function getErrors(string $yaml): array
    {
        $errors = [];

        try {
            Yaml::parse($yaml);
        } catch (ParseException $e) {
            $errors[] = $e->getMessage();
        }

        return $errors;
    }
}

