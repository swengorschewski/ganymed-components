<?php namespace Ganymed\Exceptions;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\Services\View;

class ErrorHandler {

    /**
     * Instance of the error handler class.
     *
     * @ErrorHandler
     */
    private static $instance;

    /**
     * Environment of the application.
     *
     * @var String
     */
    private $production;

    /**
     * Return the an instance of the error handler class.
     *
     * @return ErrorHandler
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Private constructor to prevent instantiating with the new attribute.
     */
    private function __construct() {}

    /**
     * Set the application environment.
     *
     * @param $env
     */
    public function setEnv($env)
    {
        if($env == 'production') {
            $this->production = true;
        } else {
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
            $this->production = false;
        }
    }

    /**
     * Check if the last error is a fatal error and throw appropriate exception.
     */
    public function checkForFatal()
    {
        $error = error_get_last();
        if ($error['type'] == E_ERROR)
            $this->logError($error["type"], $error["message"], $error["file"], $error["line"]);
                        
    }

    /**
     * Throw appropriate exception for caught error.
     *
     * @param $errorNumber
     * @param $message
     * @param $file
     * @param $line
     */
    public function logError($errorNumber, $message, $file, $line)
    {
        $this->displayException( new \ErrorException($message, 0, $errorNumber, $file, $line));
    }

    /**
     * Display a view with the corresponding exception.
     * @param \Exception $exception
     */
    public function displayException(\Exception $exception)
    {
        $this->setHeader($exception);

        $view = new View(__DIR__ . '/views/');

        if($this->production) {

            if($exception instanceof PageNotFoundException) {
                echo $view->withTemplate('404')->withData(compact('exception'))->render();
            } else {
                echo $view->withTemplate('simple_error')->render();
            }
        } else {
            echo $view->withTemplate('full_error')->withData(compact('exception'))->render();
        }

        die();
    }

    /**
     * Set the appropriate header for the given exception.
     *
     * @param \Exception $exception
     */
    private function setHeader(\Exception $exception)
    {
        switch($exception) {
            case ($exception instanceof PageNotFoundException):
                http_response_code(404);
                break;
            case ($exception instanceof NotImplementedException):
                http_response_code(501);
                break;
            default:
                http_response_code(500);
        }
    }

    /**
     * Set the error and exception handler.
     */
    public function run()
    {
        register_shutdown_function(array($this, 'checkForFatal'));
        set_error_handler(array($this, 'logError'));
        set_exception_handler(array($this, 'displayException'));
    }
    
}