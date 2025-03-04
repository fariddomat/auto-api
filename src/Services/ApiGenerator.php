<?php

namespace Fariddomat\AutoApi\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ApiGenerator
{
    protected $name;
    protected $fields;
    protected $command;

    public function __construct($name, $fields, $command)
    {
        $this->name = $name;
        $this->fields = $fields;
        $this->command = $command;
    }

    public function parseFields()
    {
        return array_map(function ($field) {
            $parts = explode(':', $field);
            return ['name' => $parts[0], 'type' => $parts[1] ?? 'string'];
        }, $this->fields);
    }

    public function generateController($version, $middleware)
    {
        $controllerName = "{$this->name}ApiController";
        $path = app_path("Http/Controllers/{$controllerName}.php");
        $middlewareString = !empty($middleware) ? "['" . implode("', '", $middleware) . "']" : '[]';

        $content = <<<EOT
        <?php

        namespace App\Http\Controllers;

        use App\Models\\{$this->name};
        use Illuminate\Http\Request;

        class {$controllerName} extends Controller
        {
            public function __construct()
            {
                \$this->middleware({$middlewareString});
            }

            public function index()
            {
                return response()->json(['data' => {$this->name}::all()]);
            }

            public function show(\$id)
            {
                \$record = {$this->name}::find(\$id);
                return \$record ? response()->json(['data' => \$record]) : response()->json(['message' => 'Not found'], 404);
            }

            public function store(Request \$request)
            {
                \$record = {$this->name}::create(\$request->all());
                return response()->json(['data' => \$record], 201);
            }
        }
        EOT;

        File::put($path, $content);
        $this->command->info("\033[32m Controller created: $path \033[0m");
    }

    public function generateRoutes($version, $middleware)
    {
        $routesPath = base_path("routes/api.php");
        $controller = "{$this->name}ApiController";
        $routePrefix = Str::plural(Str::snake($this->name));
        $middlewareString = !empty($middleware) ? "->middleware(['" . implode("', '", $middleware) . "'])" : '';

        $routeCode = <<<EOT
        Route::prefix('$version')
            ->group(function () {
                Route::apiResource('/$routePrefix', \\App\\Http\\Controllers\\{$controller}::class)$middlewareString;
            });
        EOT;

        File::append($routesPath, "\n" . $routeCode . "\n");
        $this->command->info("\033[32m Routes added to: $routesPath \033[0m");
    }

    public function generateOpenApiSpec()
    {
        // Placeholder for OpenAPI generation (simplified)
        $specPath = base_path("openapi/{$this->name}.json");
        File::ensureDirectoryExists(dirname($specPath));
        File::put($specPath, json_encode(['openapi' => '3.0.0', 'info' => ['title' => "{$this->name} API"]], JSON_PRETTY_PRINT));
        $this->command->info("\033[32m OpenAPI spec created: $specPath \033[0m");
    }
}