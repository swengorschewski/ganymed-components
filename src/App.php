<?php namespace Ganymed;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\Exceptions\ErrorHandler;
use Ganymed\Exceptions\FileNotFoundException;
use Ganymed\Http\Request;
use Ganymed\Router\Router;

class App {

    /**
     * Set environment variables supplied by the .env.php.
     *
     * @param $dotEnv
     * @throws FileNotFoundException
     */
    public function setEnv($dotEnv)
    {
        $errorHandler = new ErrorHandler(new Request());

        // Manually catch error because the error handling is not initialized yet.
        if (!is_file($dotEnv)) {
            $errorHandler->displayException(new FileNotFoundException('File .env.php not found.'));
        }

        $env = parse_ini_file($dotEnv);

        foreach ($env as $key => $variable) {
            putenv($key . '=' . $variable);
        }

        // If no variables named environment is supplied set the environment to production.
        if (getenv('ENVIRONMENT') == '') {
            putenv('ENVIRONMENT=production');
        }

        // Set error handling depending on the supplied environment.
        $errorHandler->setEnv(getenv('ENVIRONMENT'));
        $errorHandler->run();
    }

    /**
     * Execute the app to resolve routes, controllers and dispatch views.
     *
     * @param Router $router
     * @throws Exceptions\ClassNotFoundException
     * @throws Exceptions\MethodNotFoundException
     * @throws Exceptions\TypeHintException
     */
    public function execute(Router $router)
    {

        // Get the current route.
        $route = $router->getRoute(new Request());

        $middleware = $route->getMiddleware();
        if($middleware != null) {

            if(is_callable($middleware)) {
                call_user_func($middleware);
            } else {
                $middleware = IocContainer::getInstance()->resolveMiddleware($middleware);
                $middleware->execute();
            }

        }

        $callback = $route->getCallback();

        // Resolve callbacks and matching parameters.
        if (is_array($callback)) {

            $controllerName = $callback[0];
            $methodName = $callback[1];

            $controller = IocContainer::getInstance()->getClass($controllerName);
            $methodParams = IocContainer::getInstance()->resolveMethodParams($controllerName, $methodName, $route->getParams());

            call_user_func_array([$controller, $methodName], $methodParams);

            $controller->getResponse()->send();

        } else {

            echo call_user_func($callback, $route->getParams());

        }

    }
}