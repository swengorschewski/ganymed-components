<?php namespace Ganymed\Http;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\View\View;

class Response {

    /**
     * Http headers.
     *
     * @var array
     */
    protected $headers = [];
    
    /**
     * Parsed Http body as string.
     *
     * @var string
     */
    protected $body;
    
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }

    public function setBody($body)
    {
        if(is_string($body)) {
            $this->body = $body;
        } else {
            throw new \InvalidArgumentException('The body content has to be of type string');
        }
    }

    public function getBody()
    {
        return $this->body;
    }

    public function fromView(View $view)
    {
        $this->setHeader('Content-Type', 'text/html');
        $this->setBody($view->render());
    }

    public function fromJson(Array $array)
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->setBody(json_encode($array));
    }

    public function fromPlain()
    {
        $this->setHeader('Content-Type', 'text/plain');
    }

    public function send()
    {
        foreach($this->headers as $key => $value) {
            header($key . ': ' . $value);
        }

        echo $this->body;
    }
}
