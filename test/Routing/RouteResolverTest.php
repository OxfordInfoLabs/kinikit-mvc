<?php

namespace Kinikit\MVC\Routing;


use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\Controllers\REST;
use Kinikit\MVC\Decorators\BespokeDecorator;
use Kinikit\MVC\Decorators\DefaultDecorator;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;

include_once "autoloader.php";

class RouteResolverTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var ClassInspectorProvider
     */
    private $classInspectorProvider;
    private $fileResolver;

    public function setUp(): void {
        $this->classInspectorProvider = Container::instance()->get(ClassInspectorProvider::class);
        $this->fileResolver = Container::instance()->get(FileResolver::class);

        $_SERVER["HTTP_HOST"] = "localhost";
        $_SERVER["SERVER_PORT"] = 80;

    }


    public function testCanResolveValidControllerRequestsToControllerRouteHandlerForSimpleRESTRequests() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("list");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/256";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("get");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());


        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/rest";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("create");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());


        $_SERVER["REQUEST_METHOD"] = "PUT";
        $_SERVER["REQUEST_URI"] = "/rest/256";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("update");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());


        $_SERVER["REQUEST_METHOD"] = "PATCH";
        $_SERVER["REQUEST_URI"] = "/rest/12";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("patch");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());

        $_SERVER["REQUEST_METHOD"] = "DELETE";
        $_SERVER["REQUEST_URI"] = "/rest/123";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("delete");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());

    }

    public function testCanResolveValidControllerRequestsToControllerRouteHandlerForNestedRESTRequests() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/nested/123";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("nestedGet");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());


        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/rest/nested";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("nestedCreate");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/nested/23/mark";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("nestedVariableGet");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolver->resolve());

    }


    public function testExplicitPathsCheckedWhereNoRESTAvailable() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/sub/nestedsimple/update";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve();

        $targetMethod = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("update");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolved);


    }


    public function testImplicitHandleRequestMethodResolvedIfExistsAndNoOtherMatch() {


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/sub/nestedsimple";


        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve();

        $targetMethod = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolved);


        $_SERVER["REQUEST_URI"] = "/sub/nestedsimple/arbitrary";


        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve();

        $targetMethod = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request), $resolved);

    }


    public function testExplicitDecoratorRequestsAreResolvedToDecoratorRouteHandlerWithDecoratorAndControllerMethodInfo() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/bespoke/sub/nestedsimple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve();


        $targetDecoratorMethod = $this->classInspectorProvider->getClassInspector(BespokeDecorator::class)->getPublicMethod("handleRequest");
        $targetControllerMethod = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest");

        $this->assertEquals(new DecoratorRouteHandler($targetDecoratorMethod, $targetControllerMethod, $request), $resolved);


    }


    public function testImplicitDecoratoRequestsAreResolvedToDecoratorHandleIfDefaultDecoratorConfigParameterSet() {

        Configuration::instance()->addParameter("default.decorator", "DefaultDecorator");

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/sub/nestedsimple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve();


        $targetDecoratorMethod = $this->classInspectorProvider->getClassInspector(DefaultDecorator::class)->getPublicMethod("handleRequest");
        $targetControllerMethod = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest");

        $this->assertEquals(new DecoratorRouteHandler($targetDecoratorMethod, $targetControllerMethod, $request), $resolved);

    }


    public function testMissingDecoratorHandlerExceptionThrownIfNoHandleRequestMethodInDecorator() {


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/baddecorator/sub/nestedsimple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($request, $this->classInspectorProvider, $this->fileResolver);

        try {
            $resolved = $resolver->resolve();
            $this->fail("Should have thrown here");
        } catch (MissingDecoratorHandlerException $e) {
            $this->assertTrue(true);
        }


    }


    public function testCanResolveViewOnlyRequestsToViewRouteHandler() {


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticview";

        $resolver = new RouteResolver(new Request(new Headers()), $this->classInspectorProvider, $this->fileResolver);

        $handler = $resolver->resolve();
        $this->assertEquals(new ViewOnlyRouteHandler("teststaticview"), $handler);


    }


    public function testUnresolvableRoutesThrowRouteNotFoundException() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticviewbad";

        $resolver = new RouteResolver(new Request(new Headers()), $this->classInspectorProvider, $this->fileResolver);

        try {
            $resolver->resolve();
            $this->fail("Should be throwing an exception here");
        } catch (RouteNotFoundException $e) {
            $this->assertTrue(true);
        }

    }


}
