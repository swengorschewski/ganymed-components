<?php namespace Ganymed\Router;


class Route {

    const GET = 'GET';
    const POST = 'POST';

    protected $pattern;
    protected $method;
    protected $callback;
    protected $params;

    function __construct($pattern, $method, $callback, $params)
    {
        $this->pattern = $pattern;
        $this->method = $method;
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
    public function getCallback()
    {
        return $this->callback;
    }

}