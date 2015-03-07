<?php

namespace spec\Ganymed\Http;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RequestSpec extends ObjectBehavior
{
    function let() {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/login';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET = ['input1' => 'input1'];
        $_FILES = [];
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ganymed\Http\Request');
    }

    function it_should_have_a_http_method() {
        $this->getMethod()->shouldReturn('GET');
    }

    function it_should_return_the_request_uri() {
        $this->getUri()->shouldBe('/login');
    }

    function it_should_return_the_input() {
        $this->getInput()->shouldBe(['input1' => 'input1']);
    }

    function it_should_return_file_info() {
        $this->getFileInfo()->shouldBe([]);
    }
}
