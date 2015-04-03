<?php

namespace Ganymed\Http;

class Response {

    protected $body;

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
}
