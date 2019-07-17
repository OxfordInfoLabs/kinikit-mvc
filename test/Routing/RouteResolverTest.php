<?php

namespace Kinikit\MVC\Routing;


use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\MVC\Controllers\REST;
use Kinikit\MVC\Decorators\BespokeDecorator;
use Kinikit\MVC\Decorators\DefaultDecorator;
use Kinikit\MVC\Decorators\Zone;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\RouteHandler\ControllerRouteHandler;
use Kinikit\MVC\RouteHandler\ViewOnlyRouteHandler;
use Kinikit\MVC\RouteHandler\DecoratorRouteHandler;
use Kinikit\MVC\RouteHandler\MissingDecoratorHandlerException;

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
        $_SERVER["REQUEST_URI"] = "/rest";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("list");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, ""), $resolver->resolve($request));


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/256";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("get");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "256"), $resolver->resolve($request));


        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/rest";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("create");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, ""), $resolver->resolve($request));


        $_SERVER["REQUEST_METHOD"] = "PUT";
        $_SERVER["REQUEST_URI"] = "/rest/256";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("update");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "256"), $resolver->resolve($request));


        $_SERVER["REQUEST_METHOD"] = "PATCH";
        $_SERVER["REQUEST_URI"] = "/rest/12";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("patch");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "12"), $resolver->resolve($request));

        $_SERVER["REQUEST_METHOD"] = "DELETE";
        $_SERVER["REQUEST_URI"] = "/rest/123";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("delete");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "123"), $resolver->resolve($request));

    }

    public function testCanResolveValidControllerRequestsToControllerRouteHandlerForNestedRESTRequests() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/nested/123";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("nestedGet");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "nested/123"), $resolver->resolve($request));


        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "/rest/nested";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("nestedCreate");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "nested"), $resolver->resolve($request));


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/rest/nested/23/mark";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $targetMethod = $this->classInspectorProvider->getClassInspector(REST::class)->getPublicMethod("nestedVariableGet");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "nested/23/mark"), $resolver->resolve($request));

    }


    public function testExplicitPathsCheckedWhereNoRESTAvailable() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/sub/nestedsimple/update";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve($request);

        $targetMethod = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("update");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "update"), $resolved);


    }


    public function testImplicitHandleRequestMethodResolvedIfExistsAndNoOtherMatch() {


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/sub/nestedsimple";


        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve($request);

        $targetMethod = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, ""), $resolved);


        $_SERVER["REQUEST_URI"] = "/sub/nestedsimple/arbitrary";


        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve($request);

        $targetMethod = $this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest");

        $this->assertEquals(new ControllerRouteHandler($targetMethod, $request, "arbitrary"), $resolved);

    }


    public function testCanResolveViewOnlyRequestsToViewRouteHandler() {


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticview";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        $handler = $resolver->resolve($request);
        $this->assertEquals(new ViewOnlyRouteHandler("teststaticview",$request), $handler);


    }


    public function testUnresolvableRoutesThrowRouteNotFoundException() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/teststaticviewbad";

        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        try {
            $request = new Request(new Headers());
            $resolver->resolve($request);
            $this->fail("Should be throwing an exception here");
        } catch (RouteNotFoundException $e) {
            $this->assertTrue(true);
        }

    }


    public function testExplicitDecoratorRequestsAreResolvedToDecoratorRouteHandlerWithDecoratorAndControllerMethodInfo() {

        Configuration::instance()->addParameter("default.decorator", "DefaultDecorator");


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/bespoke/sub/nestedsimple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve($request);


        $targetDecoratorMethod = $this->classInspectorProvider->getClassInspector(BespokeDecorator::class)->getPublicMethod("handleRequest");
        $contentRouteHandler = new ControllerRouteHandler($this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest"), $request, "");

        $this->assertEquals(new DecoratorRouteHandler($targetDecoratorMethod, $contentRouteHandler, $request), $resolved);


    }


    public function testPathBasedDecoratorRequestsAreResolvedToDecoratorRouteHandlerWithDecoratorAndControllerMethodInfo() {

        Configuration::instance()->addParameter("default.decorator", "DefaultDecorator");

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/zone/simple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve($request);


        $targetDecoratorMethod = $this->classInspectorProvider->getClassInspector(Zone::class)->getPublicMethod("handleRequest");
        $contentRouteHandler = new ControllerRouteHandler($this->classInspectorProvider->getClassInspector(\Simple::class)->getPublicMethod("handleRequest"), $request, "");

        $this->assertEquals(new DecoratorRouteHandler($targetDecoratorMethod, $contentRouteHandler, $request), $resolved);


    }


    public function testDefaultDecoratorRequestsAreResolvedToDecoratorRouteHandlerWhenDefaultDecoratorConfigParameterSet() {

        Configuration::instance()->addParameter("default.decorator", "DefaultDecorator");

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/sub/nestedsimple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve($request);


        $targetDecoratorMethod = $this->classInspectorProvider->getClassInspector(DefaultDecorator::class)->getPublicMethod("handleRequest");
        $contentRouteHandler = new ControllerRouteHandler($this->classInspectorProvider->getClassInspector(\NestedSimple::class)->getPublicMethod("handleRequest"), $request, "");

        $this->assertEquals(new DecoratorRouteHandler($targetDecoratorMethod, $contentRouteHandler, $request), $resolved);

    }


    public function testViewOnlyRequestsCanBeDecorated() {

        Configuration::instance()->addParameter("default.decorator", "DefaultDecorator");

        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/simple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve($request);


        $targetDecoratorMethod = $this->classInspectorProvider->getClassInspector(DefaultDecorator::class)->getPublicMethod("handleRequest");
        $contentRouteHandler = new ViewOnlyRouteHandler("simple", $request);

        $this->assertEquals(new DecoratorRouteHandler($targetDecoratorMethod, $contentRouteHandler, $request), $resolved);


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/bespoke/simple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);
        $resolved = $resolver->resolve($request);


        $targetDecoratorMethod = $this->classInspectorProvider->getClassInspector(BespokeDecorator::class)->getPublicMethod("handleRequest");
        $contentRouteHandler = new ViewOnlyRouteHandler("simple", $request);

        $this->assertEquals(new DecoratorRouteHandler($targetDecoratorMethod, $contentRouteHandler, $request), $resolved);


    }


    public function testMissingDecoratorHandlerExceptionThrownIfNoHandleRequestMethodInDecorator() {


        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/baddecorator/sub/nestedsimple";

        $request = new Request(new Headers());
        $resolver = new RouteResolver($this->classInspectorProvider, $this->fileResolver);

        try {
            $resolved = $resolver->resolve($request);
            $this->fail("Should have thrown here");
        } catch (MissingDecoratorHandlerException $e) {
            $this->assertTrue(true);
        }


    }


}
