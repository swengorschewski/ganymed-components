<?php namespace Ganymed\Router;

use Ganymed\Exceptions\MethodNotFoundException;
use Ganymed\Exceptions\PageNotFoundException;
use Ganymed\Exceptions\NotImplementedException;
use Ganymed\Http\Request;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

class Router {

    /**
     * Storage for all defined routes.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Missing is called if no route path matches.
     *
     * @var Route
     */
    protected $missing;

    /**
     * Instance of the router.
     *
     * @Router
     */
    private static $instance;

    /**
     * Return an instance of the router.
     *
     * @return Router
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
     * Wrapper for GET routes
     *
     * @param $routePath
     * @param $controller
     */
    public function get($routePath, $controller)
    {
        $this->setRoute($routePath, $controller, Route::GET);
    }

    /**
     * Wrapper for POST routes
     *
     * @param $routePath
     * @param $controller
     * @throws MethodNotFoundException
     */
    public function post($routePath, $controller)
    {
        $this->setRoute($routePath, $controller, Route::POST);
    }

    /**
     * Add a route to the routes array.
     *
     * @param $routePath
     * @param $controller
     * @param $method
     * @throws MethodNotFoundException
     */
    public function setRoute($routePath, $controller, $method)
    {
        // If the route has no leading slash it will be added.
        if (substr($routePath, 0, 1) != '/') {
            $routePath = '/' . $routePath;
        }

        $middleware = null;

        if(is_array($controller)) {
            if(!array_key_exists('controller', $controller)) {
                throw new MethodNotFoundException('No controller assigned in route definition.');
            }

            extract($controller);
        }

        $pattern = "~^" . preg_replace('~\\\:[a-zA-Z0-9\_\-]+~', '([a-zA-Z0-9\-\_]+)', preg_quote($routePath)) . "$~";

        array_push($this->routes, new Route(
            $pattern,
            $method,
            $middleware,
            $this->resolveCallable($controller),
            $this->getParams($routePath)
        ));
    }

    /**
     * Resolve the current Route.
     *
     * @param Request $request
     * @return Route
     * @throws PageNotFoundException
     * @throws NotImplementedException
     */
    public function getRoute(Request $request)
    {
        $notImplemented = false;

        /**
         * Resolve the current uri.
         *
         * @var $route Route
         */
        foreach ($this->routes as $route) {
            // Check if the current route matches the current uri and determine the parameter values.
            if (preg_match($route->getPattern(), $request->getUri(), $paramValues)) {

                // Check if the current HTTP method matches the method stored in the route.
                if($route->getMethod() == $request->getMethod()) {
                    $notImplemented = false;
                    // Get rid of the full match.
                    array_shift($paramValues);
                    $params = $route->getParams();

                    // Create an associative array from the parameter names and values.
                    if(count($paramValues) == count($params)) {
                        $route->setParams(array_combine($params, $paramValues));
                    }

                    return $route;
                } else {

                    $notImplemented = true;

                }

            }
        }

        if ($notImplemented) {
            throw new NotImplementedException('Method not implemented');
        }

        // Check if the missing callback is set.
        if (!is_null($this->missing)) {

            return $this->missing;

        }

        throw new PageNotFoundException('Requested page not found');
    }

    /**
     * Determine if the callback is a closure or a string of type '<classname@method>.
     *
     * @param $callback
     * @return array
     * @throws MethodNotFoundException
     */
    private function resolveCallable($callback)
    {
        // Check if the callback was provided as a string of type <Class@method>.
        if (is_string($callback) && strpos($callback, '@')) {
            return explode('@', $callback);
        } elseif (is_callable($callback)) {
            return $callback;
        } else {
            throw new MethodNotFoundException('Callback not found');
        }

    }

    /**
     * All routes that cant be resolved will respond with the callback provided here.
     *
     * @param $callback
     */
    public function missing($callback)
    {
        $this->missing = new Route(null, 'GET', null, $this->resolveCallable($callback), []);
    }

    /**
     * Determine the route parameter names.
     *
     * @param $routePath
     * @return array
     */
    private function getParams($routePath)
    {
        $paramKeyPattern = "~^" . preg_replace('~\\\:[a-zA-Z0-9\_\-]+~', '(\:[a-zA-Z0-9\-\_]+)', preg_quote($routePath)) . "$~";

        preg_match($paramKeyPattern, $routePath, $matches);

        // Shift the array to get rid of the full match.
        array_shift($matches);

        // Store the parameters without the leading ':'.
        $params = [];
        foreach($matches as $match) {
            array_push($params, str_replace(':', '', $match));
        }

        return $params;
    }

}