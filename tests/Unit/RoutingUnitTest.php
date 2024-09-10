<?php 

use PHPUnit\Framework\TestCase;
use App\Routing\Router;
use App\Controllers\IndexController;
use App\Controllers\LoginController;

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
                'action' => [new IndexController(), 'login']
            ]
        ];

        $routes = $router->getRoutes();
        $this->assertEquals($expectedRoutes, $routes);

 
    }
}