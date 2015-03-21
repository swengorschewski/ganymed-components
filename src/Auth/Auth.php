<?php namespace Ganymed\Auth;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\Services\Session;

class Auth {

    /**
     * Session implementation.
     *
     * @var Session
     */
    protected $session;

    /**
     * User model.
     *
     * @var User
     */
    protected $user;

    function __construct(Session $session, User $user)
    {
        $this->session = $session;
        $this->user = $user;
    }

    /**
     * Check if a user is authenticated.
     * @return bool
     */
    public function check()
    {
        return $this->session->get('auth') != null ? true : false;
    }

    /**
     * Validate the supplied login credentials.
     *
     * @param $email
     * @param $password
     * @return bool
     */
    public function validate($email, $password)
    {
        if ($this->user = \User::getByEmail($email)) {

            if (password_verify($password, $this->user->password)) {
                return true;
            }
        }

        // Handle errors on failed authentication.
        $errors = [
            'error' => 'Wrong Email or Password.'
        ];

        $this->session->put('errors', serialize($errors));

        return false;
    }

    /**
     * Register a user session if the supplied credentials are valid.
     *
     * @param $email
     * @param $password
     * @return bool
     */
    public function attempt($email, $password)
    {
        if ($this->validate($email, $password)) {
            $this->session->put('email', $email);
            $this->session->put('auth', true);
            return true;
        }

        return false;
    }

}