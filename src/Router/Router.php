<?php namespace Ganymed\Router;

use Ganymed\Exceptions\MethodNotFoundException;
use Ganymed\Exceptions\NotFoundException;
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
     * Wrapper for GET routes
     *
     * @param $routePath
     * @param $callback
     */
    public function get($routePath, $callback)
    {
        $this->setRoute($routePath, $callback, Route::GET);
    }

    /**
     * Wrapper for POST routes
     *
     * @param $routePath
     * @param $callback
     */
    public function post($routePath, $callback)
    {
        $this->setRoute($routePath, $callback, Route::POST);
    }

    /**
     * Add a route to the routes array.
     *
     * @param $routePath
     * @param $callback
     * @param $method
     * @throws MethodNotFoundException
     */
    public function setRoute($routePath, $callback, $method)
    {
        // If the route has no leading slash it will be added.
        if (substr($routePath, 0, 1) != '/') {
            $routePath = '/' . $routePath;
        }

        $pattern = "~^" . preg_replace('~\\\:[a-zA-Z0-9\_\-]+~', '([a-zA-Z0-9\-\_]+)', preg_quote($routePath)) . "$~";

        array_push($this->routes, new Route(
            $pattern,
            $method,
            $this->resolveCallback($callback),
            $this->getParams($routePath)
        ));
    }

    /**
     * Resolve the current Route.
     *
     * @param Request $request
     * @return Route
     * @throws NotFoundException
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
                if($route->getMethod() == $_SERVER['REQUEST_METHOD']) {
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
            throw new NotImplementedException('501 - Method not implemented');
        }

        // Check if the missing callback is set.
        if (!is_null($this->missing)) {

            return $this->missing;

        }

        throw new NotFoundException('404 - Requested page not found');
    }

    /**
     * Determine if the callback is a closure or a string of type '<classname@method>.
     *
     * @param $callback
     * @return array
     * @throws MethodNotFoundException
     */
    private function resolveCallback($callback)
    {
        // Check if the callback was provided as a string of type <Class@method>.
        if (is_string($callback) && strpos($callback, '@')) {
            return explode('@', $callback);
        } elseif (is_callable($callback)) {
            return $callback;
        } else {
            throw new MethodNotFoundException('500 - Callback not found');
        }

    }

    /**
     * All routes that cant be resolved will respond with the callback provided here.
     *
     * @param $callback
     */
    public function missing($callback)
    {
        $this->missing = new Route(null, 'GET', $this->resolveCallback($callback), []);
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