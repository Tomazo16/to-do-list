<?php 

use PHPUnit\Framework\TestCase;
use App\Routing\Router;
use App\Controllers\IndexController;
use App\Controllers\LoginController;
use App\Controllers\AbstractController;

class RoutingUnitTest extends TestCase
{
    public function testGetRoutes(): void
    {

        //checking if IndexController has method index
        $this->assertTrue(method_exists(IndexController::class, 'index'), "Method 'index' not exist in IndexController class");
        //checking if LognController has method login
        $this->assertTrue(method_exists(LoginController::class, 'login'), "Method 'login' not exist in LoginController class");

        $router = new Router();

        $expectedRoutes = [
            [
                'method' => 'GET',
                'route' => '/index',
                'action' => [new IndexController(), 'index']
            ],
            [
                'method' => 'GET',
                'route' => '/login',
                'action' => [new LoginController(), 'login']
            ]
        ];

        $routes = $router->getRoutes();
        $this->assertEquals($expectedRoutes, $routes);

 
    }

    public function testLoadRoutesFromMultipleControllers()
    {
        //counting all Controllers
        $directory = __DIR__ . '/../../app/Controllers';
        $controllers = glob($directory . '/*Controller.php');
        $controllerCount = count($controllers);

        $router = new Route();
        $routes = $router->getRoutes();

        //chcecking if count routes is equals number of controllers
        $this->assertEquals($controllerCount, $routes, "Number of routes isnt equals like number of Controllers!");

    }

    public function testIgnoreInheritedMethods()
    {
        //method from AbstractController
        $inheritedMethod = 'getUser';

        $router = new Router();
        $routes = $router->getRoutes();

        foreach($routes as $route) {
            $this->assertNotEquals($inheritedMethod, $route['action'][1], "'{$route['route']}' has method from AbstractController");
        }
    }

    public function testIgnorePrivateMethod()
    {
        $index = new IndexController();

        // Use reflection to set access from public to private `index` method in the Router class.
        $reflection = new \ReflectionClass($index);
        $method = $reflection->getMethod('index')->setAccessible(false);

        $router = new Router();
        $routes = $router->getRoutes();

        foreach($routes as $route) {
            // Assert that private method was not found.
            $this->assertNotEquals('/index', $route['route']);
        }
    }
    
    public function testPostHttpMethod()
    {
        // Create a mock controller which extends AbstractController simulating the `postIndex` method.
        //Controller must inherit AbstractController because 'registerRoutesForController' method requires it
        $testController = $this->getMockBuilder(AbstractController::class)
            ->setMockClassName('TestController')
            ->getMock();
        $testController->method('postIndex')->willReturn(null);

        $router = new Router();

        // Use reflection to access the protected `registerRoutesForController` method in the Router class.
        $reflection = new \ReflectionClass($router);
        $registerRoutesMethod = $reflection->getMethod('registerRoutesForController');
        $registerRoutesMethod->setAccessible(true);

        // Register routes for the mocked controller
        $registerRoutesMethod->invoke($router, $testController);

        $routes = $router->getRoutes();

        // Search for the route matching '/test/index' with the POST method.
        $routeFound = false;
        foreach($routes as $route) {
            if($route['route'] === '/test/index' && $route['method'] === 'POST') {
                $routeFound = true;
                break;
            }
        }

        // Assert that the expected route was found.
        $this->assertTrue($routeFound);
    }
}