<?php 

use PHPUnit\Framework\TestCase;
use App\Routing\Router;
use App\Controllers\IndexController;
use App\Controllers\LoginController;
use App\Controllers\Abstract\AbstractController;

class RoutingUnitTest extends TestCase
{
    public function setUp(): void
    {
        if(!class_exists('TestController')){
            eval('
                use App\Controllers\Abstract\AbstractController;

                class TestController extends AbstractController
                {
                    public function postIndex() {}
                    public function postTest() {}
                    protected function protect() {}
                    private function priv() {}
                }
            ');
        }
    }

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
        
        $this->assertEquals(count($expectedRoutes), count($routes));
        $this->assertEquals($expectedRoutes, $routes);

 
    }

    public function testLoadRoutesFromMultipleControllers()
    {
        //counting all Controllers
        $controllersPath = __DIR__ . '/../../app/Controllers';
        $controllers = glob($controllersPath . '/*Controller.php');
        $controllersCount = count($controllers);

        $router = new Router();
        $routes = $router->getRoutes();

        //chcecking if count routes is equals number of controllers
        $this->assertEquals($controllersCount, count($routes), "Number of routes isnt equals like number of Controllers!");

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
  
            $this->assertTrue(class_exists('TestController'));

            $testController = new TestController();
            $router = new Router();

            $reflection = new \ReflectionClass($router);
            $registerRoutesForController = $reflection->getMethod('registerRoutesForController');
            $registerRoutesForController->setAccessible(true);
        
            $registerRoutesForController->invoke($router, $testController);

            $routes = $router->getRoutes();

            foreach($routes as $route) {
                // Assert that private method was not found.
                $this->assertNotEquals('/test/protect', $route['route']);
                $this->assertNotEquals('/test/priv', $route['route']);
            }
    
    }
    
    public function testPostHttpMethod()
    {
        $this->assertTrue(class_exists('TestController'));

        $testController = new \TestController();

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

         // Search for the route matching '/test' with the POST method from method postTest.
         $routeFound = false;
         foreach($routes as $route) {
             if($route['route'] === '/test' && $route['method'] === 'POST' && $route['action'][1] === 'postTest') {
                 $routeFound = true;
                 break;
             }
         }

         // Assert that the expected route was found.
        $this->assertTrue($routeFound);
    }
}