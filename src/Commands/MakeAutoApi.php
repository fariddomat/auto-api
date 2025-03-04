<?php

namespace Fariddomat\AutoApi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Fariddomat\AutoApi\Services\ApiGenerator;

class MakeAutoApi extends Command
{
    protected $signature = 'make:auto-api';
    protected $description = 'Generate a RESTful API module interactively.';

    public function handle()
    {
        $this->info("\033[34m Welcome to AutoAPI Generator! Let's create your API step-by-step. \033[0m");

        $name = $this->askModelName();
        $fields = $this->askFields($name);
        $version = $this->askVersion();
        $middleware = $this->askMiddleware();

        $this->displaySummary($name, $fields, $version, $middleware);
        if (!$this->confirm("\033[33m Proceed with these settings? \033[0m", true)) {
            $this->info("\033[31m Generation cancelled. \033[0m");
            return 0;
        }

        $this->info("\033[34m Generating Auto API for $name... \033[0m");

        $apiGenerator = new ApiGenerator($name, $fields, $this);
        $parsedFields = $apiGenerator->parseFields();
        $apiGenerator->generateController($version, $middleware);
        $apiGenerator->generateRoutes($version, $middleware);
        $apiGenerator->generateOpenApiSpec();

        $this->info("\033[34m API for $name created successfully! \033[0m");
    }

    protected function askModelName()
    {
        $name = $this->ask("\033[33m Model name? (e.g., Post; must start with a capital letter) \033[0m");
        if (empty($name) || !preg_match('/^[A-Z][a-zA-Z0-9_]*$/', $name)) {
            $this->error("\033[31m Invalid or empty model name. Aborting. \033[0m");
            exit(1);
        }
        return Str::studly($name);
    }

    protected function askFields($name)
    {
        $fields = [];
        $this->info("\033[36m Define fields for $name (e.g., title:string, user_id:select). Leave blank to finish. \033[0m");
        while (true) {
            $field = $this->ask("\033[33m Enter a field: \033[0m");
            if (empty($field)) break;
            $fields[] = $field;
        }
        return $fields;
    }

    protected function askVersion()
    {
        return $this->ask("\033[33m API version? (e.g., v1, v2; default: v1) \033[0m", 'v1');
    }

    protected function askMiddleware()
    {
        $input = $this->ask("\033[33m Middleware (comma-separated, e.g., auth:api,throttle)? \033[0m");
        return !empty($input) ? array_filter(array_map('trim', explode(',', $input))) : ['auth:api'];
    }

    protected function displaySummary($name, $fields, $version, $middleware)
    {
        $this->info("\033[36m API Settings: \033[0m");
        $this->line("  \033[32m Model: \033[0m $name");
        $this->line("  \033[32m Fields: \033[0m " . (empty($fields) ? 'None' : implode(', ', $fields)));
        $this->line("  \033[32m Version: \033[0m $version");
        $this->line("  \033[32m Middleware: \033[0m " . implode(', ', $middleware));
    }
}