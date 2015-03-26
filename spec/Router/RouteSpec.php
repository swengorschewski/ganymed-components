<?php

namespace spec\Ganymed\Router;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RouteSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('pattern', 'method', 'middleware', 'callback', 'params');
    }

    function it_is_initializable() {
        $this->shouldHaveType('Ganymed\Router\Route');
    }

    function it_should_return_pattern() {
        $this->getPattern()->shouldBe('pattern');
    }

    function it_should_return_method() {
        $this->getMethod()->shouldBe('method');
    }

    function it_should_return_middleware() {
        $this->getMiddleware()->shouldBe('middleware');
    }

    function it_should_return_callback() {
        $this->getCallback()->shouldBe('callback');
    }

    function it_should_return_params() {
        $this->getParams()->shouldBe('params');
    }

    function its_params_can_be_changed() {
        $this->setParams('otherParams');
        $this->getParams()->shouldBe('otherParams');
    }
}
