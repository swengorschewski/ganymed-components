<?php

namespace spec\Ganymed\Router;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RouterSpec extends ObjectBehavior
{
    function let()
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/login';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->beConstructedThrough('getInstance', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ganymed\Router\Router');
    }

    function it_should_register_a_get_route() {
        $this->get('/login', function() {});
        $this->getRoute()->shouldHaveType('Ganymed\Router\Route');
    }
}
