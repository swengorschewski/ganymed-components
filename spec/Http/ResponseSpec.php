<?php namespace spec\Ganymed\Http;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResponseSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ganymed\Http\Response');
    }

    function it_should_return_the_currently_set_body() {
        $this->setBody('blub');
        $this->getBody()->shouldBe('blub');
    }
}
