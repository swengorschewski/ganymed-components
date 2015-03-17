<?php

namespace spec\Ganymed\Auth;

use Ganymed\Persistence\Model;
use Ganymed\Services\Session;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class User extends Model {
    public $password;
}

class AuthSpec extends ObjectBehavior
{

    function let(Session $session, User $user) {
        $this->beConstructedWith($session, $user);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ganymed\Auth\Auth');
    }

    function it_should_validate_user(User $user) {

        $user->get("blub@bla.de")->willReturn($user)->shouldBeCalled();
        $user->password->willReturn("")->shouldBeCalled();

        $this->validate("blub@bla.de", "password")->shouldBe(true);
    }
}
