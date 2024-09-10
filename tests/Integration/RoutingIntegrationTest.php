<?php 


use PHPUnit\Framework\TestCase;

class RoutingIntegrationTest extends TestCase
{
   

    public function testHomePage(): void
    {
        $_SERVER['REQUEST_URI'] = '/home';

        ob_start();
        require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
        $output = ob_get_clean();

        // Checking the HTTP response code
        $statusCode = http_response_code();
        $this->assertEquals(200, $statusCode, 'Expected status code 200');

        // Checking if the response contains a <title> element
        $this->assertStringContainsString('<title>to-do-list home</title>', $output, 'Response does not contain expected <title> tag');

    }

    public function testPageNotFound(): void
    {
        $_SERVER['REQUEST_URI'] = '/wrongRoute';

        ob_start();
        require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
        $output = ob_get_clean();

        //checking the HTTP response code
        $statusCode = http_response_code();
        $this->assertEquals(404,$statusCode, 'Expexted status code 404');

        //chcecking if the response contains <h1> tag
        $this->assertStringContainsString('<h1>Page Not Found</h1>', $output, 'Response does not contain expected <h1> tag');
        
    }

}