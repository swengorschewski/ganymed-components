<?php namespace Ganymed;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\Exceptions\MethodNotFoundException;
use Ganymed\Exceptions\NotFoundException;
use Ganymed\Exceptions\NotImplementedException;
use Ganymed\Exceptions\TypeHintException;
use Ganymed\exceptions\ViewNotFoundException;
use Ganymed\Router\Router;

class App {

    /**
     * Set environment variables supplied by the .env.php.
     *
     * @param $dotEnv
     */
    public function setEnv($dotEnv)
    {
        $env = parse_ini_file($dotEnv);

        foreach ($env as $key => $variable) {
            putenv($key . '=' . $variable);
        }

        // If no variables named environment is supplied set the environment to production.
        if (getenv('environment') == '') {
            putenv('environment=production');
        }
    }

    /**
     * Execute the app to resolve routes, controllers and dispatch views.
     */
    public function execute()
    {

        // Set error handling depending on the supplied environment.
        if (getenv('environment') != 'production') {
            register_shutdown_function("\\Ganymed\\Exceptions\\ErrorHandler::checkForFatal");
            set_error_handler("\\Ganymed\\Exceptions\\ErrorHandler::logError");
            set_exception_handler("\\Ganymed\\Exceptions\\ErrorHandler::logException");
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        } else {

        }

        // Get the current route.
        $route = Router::getInstance()->getRoute();
        $callback = $route->getCallback();

        // Resolve callbacks and matching parameters.
        if (is_array($callback)) {
            $className = $callback[0];
            $methodName = $callback[1];

            $class = IocContainer::getInstance()->getClass($className);
            $params = IocContainer::getInstance()->resolveMethodParams($className, $methodName, $route->getParams());

            echo call_user_func_array([$class, $methodName], $params);

        } else {

            echo call_user_func($callback, $route->getParams());

        }

/*        // Try to resolve route, controller, view and handle occurring errors.
        try {



        } catch (\Exception $e) {

            $error = $e->getMessage();

            switch($e) {
                case ($e instanceof NotImplementedException):
                    header($_SERVER["SERVER_PROTOCOL"] . " 501 Not Implemented");
                    $error = $e->getMessage();
                    break;
                case ($e instanceof MethodNotFoundException):
                    header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
                    $error = $e->getMessage();
                    break;
                case ($e instanceof ViewNotFoundException):
                    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                    $error = $e->getMessage();
                    break;
                case ($e instanceof NotFoundException):
                    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
                    $error = $e->getMessage();
                case ($e instanceof TypeHintException):
                    header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
                    $error = $e->getMessage();
                    break;
            }

        }*/

    }
}