<?php 

namespace App\Routing;

use App\Controllers\Abstract\AbstractController;
use ReflectionClass;
use ReflectionMethod;

class Router
{
    protected $routes = [];

    public function __construct()
    {
        $this->loadRoutesFromControllers();
    }

    protected function loadRoutesFromControllers(): void
    {
        $controllersPath = __DIR__ . '/../Controllers';
        $controllers = glob($controllersPath . '/*Controller.php');

        foreach($controllers as $controllerFile) {
            $controllerName = basename($controllerFile, '.php');
            $controllerClass = 'App\\Controllers\\'. $controllerName;

            (class_exists($controllerClass)) ? $this->registerRoutesForController(new $controllerClass) : NULL;
        }
    }

    protected function registerRoutesForController(AbstractController $controller): void
    {
        $reflection = new ReflectionClass($controller);

        foreach($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            ($method->class === $reflection->getName()) ? $this->routes[] = $this->createRouteFromMethod($method, $controller) : NULL;     
        }
    }

    protected function createRouteFromMethod(ReflectionMethod $method , AbstractController $controller): array
    {
        $methodName = $method->getName();
        $className = strtolower(str_replace('Controller','', $method->getDeclaringClass()->getShortName()));
        
        $method = (str_contains($methodName, 'post')) ? 'POST' : 'GET';
        
        $shortMethodName = strtolower(str_replace(['post','get'],['',''], $methodName));
        $className = ($className != $shortMethodName) ? '/' . $className : '';
        $route = $className . '/' . $shortMethodName;
        

        return [
            'method' => $method,
            'route' => $route,
            'action' => [$controller, $methodName]
        ];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}