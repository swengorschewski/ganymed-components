<?php namespace Ganymed;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\Http\Request;
use Ganymed\Services\Session;

class Controller {

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Session
     */
    protected $session;

    public function __construct(Request $request, Session $session)
    {
        $this->request = $request;
        $this->session = $session;
    }

}