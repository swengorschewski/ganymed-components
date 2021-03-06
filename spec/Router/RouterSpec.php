<?php

namespace spec\Ganymed\Router;

use Ganymed\Http\Request;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RouterSpec extends ObjectBehavior
{

    function let() {
        $this->beConstructedThrough('getInstance', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ganymed\Router\Router');
    }

    function it_should_register_a_get_route(Request $request) {
        $request->getUri()->willReturn('/login')->shouldBeCalled();
        $request->getMethod()->willReturn('GET')->shouldBeCalled();

        $this->get('/login', function() {});
        $this->getRoute($request)->shouldHaveType('Ganymed\Router\Route');
    }

    function it_should_register_a_route_with_middleware(Request $request) {
        $request->getUri()->willReturn('/login')->shouldBeCalled();
        $request->getMethod()->willReturn('GET')->shouldBeCalled();

        $this->get('/middle', [
            'middleware' => 'MiddlewareClass',
            'controller' => function() {}
        ]);

        $this->getRoute($request)->shouldHaveType('Ganymed\Router\Route');
    }

    function it_should_throw_exception(Request $request) {
        $request->getUri()->willReturn('/auth')->shouldBeCalled();

        $this->shouldThrow('Ganymed\Exceptions\PageNotFoundException')->duringGetRoute($request);
    }

    function it_should_return_route_on_404_if_missing_is_set(Request $request) {
        $this->missing(function() {});
        $this->getRoute($request)->shouldHaveType('Ganymed\Router\Route');
    }
}
