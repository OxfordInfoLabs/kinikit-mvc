<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\MVC\Objects\TestRESTObject;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\JSONResponse;
use Kinikit\MVC\Response\Redirect;
use Kinikit\MVC\Response\SimpleResponse;
use Kinikit\MVC\Response\View;

include_once "autoloader.php";

class RouterTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var Router
     */
    private $router;

    public function setUp(): void {
        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";
        $this->router = Container::instance()->get(Router::class);

        TestRouteInterceptor1::$afterRoutes = 0;
        TestRouteInterceptor1::$beforeRoutes = 0;

        TestRouteInterceptor2::$afterRoutes = 0;
        TestRouteInterceptor2::$beforeRoutes = 0;

        TestRouteInterceptor3::$afterRoutes = 0;
        TestRouteInterceptor3::$beforeRoutes = 0;

        if (file_exists("ratelimits/100.100.100.100")) {
            unlink("ratelimits/100.100.100.100");
        }

    }

    /**
     * @runInSeparateProcess
     */
    public function testProcessRequestResolvesSuccessfulRoutesForSimpleRestControllers() {


        // Check a rest list request
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest";

        $response = $this->router->processRequest(new Request(new Headers()));

        $list = array();
        for ($i = 0; $i < 10; $i++) {
            $list[] = new TestRESTObject("TEST " . $i, "test$i@test.com");
        }

        $this->assertEquals(new JSONResponse($list), $response);


        // Check a REST get request
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/22";

        $response = $this->router->processRequest(new Request(new Headers()));

        $this->assertEquals(new JSONResponse(new TestRESTObject("TEST 22", "test22@test.com", "GET SINGLE")), $response);

        // Check a REST put request
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/rest";

        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "Kinikit\MVC\Request\MockPHPInputStream");
        file_put_contents("php://input", '{"id": "23", "name": "Peter Pan", "email": "pan@neverland.com", "lastStatus": "SUCCESS"}');

        $response = $this->router->processRequest(new Request(new Headers()));

        $this->assertEquals(new JSONResponse(new TestRESTObject("Peter Pan", "pan@neverland.com", "POSTED", 23)), $response);


    }

    /**
     * @runInSeparateProcess
     *
     * @throws \Kinikit\MVC\Response\ViewNotFoundException
     */
    public function testProcessRequestResolvesSuccessfulRoutesForSimpleWebControllersAndViews() {

        // Check a Web controller
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/zone/simple";

        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);

        // Compare with the output from the router resolver
        $routerResolver = Container::instance()->get(RouteResolver::class);
        $output = $routerResolver->resolve($request);

        $this->assertEquals($output->handleRoute(), $response);


        // Check a direct view
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticview";


        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);

        // Compare with the output from the router resolver
        $routerResolver = Container::instance()->get(RouteResolver::class);
        $output = $routerResolver->resolve($request);

        $this->assertEquals($output->handleRoute(), $response);


    }

    /**
     * @runInSeparateProcess
     */
    public function testRouteInterceptorsAreCalledBeforeAndAfterRequests() {


        // Simple direct view first.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticview";

        $request = new Request(new Headers());
        $this->router->processRequest($request);

        $this->assertEquals(1, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(1, TestRouteInterceptor1::$afterRoutes);

        // Simple direct view first.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/zone/simple";

        $request = new Request(new Headers());
        $this->router->processRequest($request);

        $this->assertEquals(2, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(2, TestRouteInterceptor1::$afterRoutes);
        $this->assertEquals(1, TestRouteInterceptor2::$beforeRoutes);
        $this->assertEquals(1, TestRouteInterceptor2::$afterRoutes);

    }


    /**
     * @runInSeparateProcess
     */
    public function testBeforeInterceptorResponseUsedInsteadOfRouteResponseIfReturned() {

        // Simple direct view first.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticview";

        $request = new Request(new Headers());

        TestRouteInterceptor1::$returnResponseBefore = true;

        $response = $this->router->processRequest($request);

        $this->assertEquals(new SimpleResponse("RESPONSE"), $response);

        TestRouteInterceptor1::$returnResponseBefore = false;


    }


    /**
     * @runInSeparateProcess
     */
    public function testRateLimitingAppliedAccordingToFlowDownRules() {

        // Simple direct view first.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticview";

        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);

        $this->assertEquals(60, $response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_RATELIMIT_LIMIT));
        $this->assertTrue($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_RATELIMIT_REMAINING) > 50);
        $this->assertNotNull($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_RATELIMIT_RESET));


        // Simple direct view first.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/true/true";

        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);

        $this->assertEquals(120, $response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_RATELIMIT_LIMIT));
        $this->assertTrue($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_RATELIMIT_REMAINING) > 100);
        $this->assertNotNull($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_RATELIMIT_RESET));


    }

    /**
     * @runInSeparateProcess
     */
    public function testCachingAppliedAccordingToFlowDownRules() {

        // Simple direct view first.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticview";

        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);
        $this->assertNull($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_EXPIRES));
        $this->assertNull($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_CACHE_CONTROL));
        $this->assertNull($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_LAST_MODIFIED));


        // Simple direct view first.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/zone/simple";

        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);

        $this->assertNotNull($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_EXPIRES));
        $this->assertNotNull($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_CACHE_CONTROL));
        $this->assertNotNull($response->getHeaders()->get(\Kinikit\MVC\Response\Headers::HEADER_LAST_MODIFIED));

    }


    /**
     * @runInSeparateProcess
     */
    public function testRouteNotFoundExceptionsAreAlwaysMappedToGeneralJSONResponse() {

        Configuration::instance()->addParameter("default.decorator", null);

        // Simple direct view first.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/idontexist";

        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);

        $this->assertTrue($response instanceof JSONResponse);

        $this->assertEquals("The route idontexist cannot be found.", $response->getObject()["message"]);


        // Check interceptors still fire.
        $this->assertEquals(1, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(1, TestRouteInterceptor1::$afterRoutes);


    }

    /**
     * @runInSeparateProcess
     */
    public function testExceptionsForWebMethodsAreReturnedAsErrorResponses() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/zone/simple/throwsError";

        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);

        $this->assertTrue($response instanceof View);
        $this->assertEquals("error/error", $response->getViewName());

        $this->assertEquals("Bad Web Request", $response->getModel()["request"]->getParameter("errorMessage"));
        $this->assertEquals(22, $response->getModel()["request"]->getParameter("errorCode"));

        // Check interceptors still fire.
        $this->assertEquals(1, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(1, TestRouteInterceptor1::$afterRoutes);


    }


    /**
     * @runInSeparateProcess
     */
    public function testExceptionsForJSONMethodsAreEncodedAsJSONResponsesWithAppropriateStatusCode() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/throwsStatusException";

        $request = new Request(new Headers());
        $response = $this->router->processRequest($request);

        $this->assertEquals(new JSONResponse(["statusCode" => 406, "message" => "Should return a custom error response code", "code" => 50], 406), $response);


        // Check interceptors still fire.
        $this->assertEquals(1, TestRouteInterceptor1::$beforeRoutes);
        $this->assertEquals(1, TestRouteInterceptor1::$afterRoutes);

    }

    /**
     * @runInSeparateProcess
     */
    public function testAliasedPathsCanBeCalled() {


        // Check a simple internal alias.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/test";

        $response = $this->router->processRequest(new Request(new Headers()));

        $list = array();
        for ($i = 0; $i < 10; $i++) {
            $list[] = new TestRESTObject("TEST " . $i, "test$i@test.com");
        }

        $this->assertEquals(new JSONResponse($list), $response);


        // Check a simple internal alias.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/mark";

        $response = $this->router->processRequest(new Request(new Headers()));

        $this->assertEquals(new Redirect("/zone/simple", false), $response);


        // Check a simple external alias.
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/external";

        $response = $this->router->processRequest(new Request(new Headers()));

        $this->assertEquals(new Redirect("https://www.google.com", true), $response);


    }

}
