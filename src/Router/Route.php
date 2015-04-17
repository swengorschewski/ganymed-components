<?php namespace Ganymed\Router;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

class Route {

    /**
     *
     */
    const GET = 'GET';
    const POST = 'POST';

    /**
     * RegEx pattern for the route instance.
     *
     * @var String
     */
    protected $pattern;

    /**
     * HTTP method registered for this route.
     *
     * @var String
     */
    protected $method;

    /**
     * Middleware which will be executed before the supplied callback.
     *
     * @var String
     */
    protected $middleware;

    /**
     * Callback to execute and pattern match.
     *
     * @var String
     */
    protected $callback;

    /**
     * Parameters which should be supplied for this route.
     *
     * @var String
     */
    protected $params;

    function __construct($pattern, $method, $middleware, $callback, $params)
    {
        $this->pattern = $pattern;
        $this->method = $method;
        $this->middleware = $middleware;
        $this->callback = $callback;
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

}