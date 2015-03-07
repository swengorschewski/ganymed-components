<?php namespace Ganymed\Http;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

class Request {

    /**
     * Return the HTTP method for the current request.
     *
     * @return mixed
     */
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Return the parsed uri of the current request.
     *
     * @return mixed
     */
    public function getUri()
    {
        return str_replace(ltrim($_SERVER['SCRIPT_NAME'], '/'), '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    /**
     * Return the get or post params as an array.
     * @return array
     */
    public function getInput()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            return $_GET;

        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            return $_POST;

        } else {
            return [];
        }
    }

    /**
     * Return the file info for uploaded files;
     *
     * @return mixed
     */
    public function getFileInfo()
    {
        return $_FILES;
    }
}
