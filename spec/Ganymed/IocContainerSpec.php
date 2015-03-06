<?php

namespace spec\Ganymed;

use Ganymed\App;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IocContainerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('getInstance', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ganymed\IocContainer');
    }

    function it_stores_bindings()
    {
        $this->bind(['className' => function() {}]);

        $this->shouldHaveBinding('className');
    }

    function its_bind_should_only_accept_an_array() {
        $this->shouldThrow('InvalidArgumentException')->duringBind('notAnArray');
    }

    function its_bind_should_only_accept_a_closure_as_array_value() {
        $this->shouldThrow('Exception')->duringBind(['className' => 'noClosure']);
    }

    function it_should_resolve_class_from_class_name() {
        $this->getClass('Ganymed\App')->shouldHaveType('Ganymed\App');
    }

    function its_resolve_method_should_only_except_existing_class_name() {
        $this->shouldThrow('Ganymed\Exceptions\ClassNotFoundException')->duringGetClass('App');
    }

    function it_should_resolve_class_from_alias() {
        $this->bind(['App' => function() {
            return new App();
        }]);

        $this->getClass('App')->shouldHaveType('Ganymed\App');
    }

}
