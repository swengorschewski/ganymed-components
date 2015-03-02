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
    
    public function getUploadedFileInfo()
    {
        return $_FILES;
    }

}