<?php 

namespace App\Routing;

use App\Controllers\Abstract\AbstractController;
use ReflectionMethod;

class Router
{
    protected $routes = [];

    public function __construct()
    {
        
    }

    protected function loadRoutesFromControllers(): void
    {
        $controllersPath = __DIR__ . '/app/Controllers';
        $controllers = glob($controllersPath. '/*Controllers.php');

        foreach($controllers as $controllerFile) {
            $controllerName = basename($controllerFile, '.php');
            $controllerClass = 'App\\Controllers\\'. $controllerName;

            (class_exists($controllerClass)) ? $this->registerRoutesForController(new $controllerClass) : NULL;
        }
    }

    protected function registerRoutesForController(AbstractController $controller): void
    {
        $reflection = new \ReflectionClass($controller);

        foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            ($method->class === $reflection->getName()) ? $this->routes[] = $this->createRouteFromMethod($method, $controller) : NULL;     
        }
    }

    protected function createRouteFromMethod(ReflectionMethod $method , AbstractController $controller): array
    {
        $methodName = $method->getName();
        $className = strtolower(str_replace('Controller','', $method->getDeclaringClass()->getShortName()));
        $route = ($className != $methodName) ? $className : '' . '/' . $methodName;
        $method = (strpos($methodName, 'post') === 0) ? 'POST' : 'GET';

        return [
            'method' => $method,
            'route' => '/' . $route,
            'action' => [$controller, $methodName]
        ];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}