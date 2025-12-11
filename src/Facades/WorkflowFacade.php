<?php

namespace MartinLechene\GitHubActions\Facades;

use Illuminate\Support\Facades\Facade;

class WorkflowFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'github-actions.workflow-generator';
    }
}

