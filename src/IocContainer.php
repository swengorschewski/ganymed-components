<?php namespace Ganymed;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\Exceptions\ClassNotFoundException;
use Ganymed\Exceptions\MethodNotFoundException;
use Ganymed\Exceptions\TypeHintException;

class IocContainer {

    /**
     * Instance of the ioc container.
     *
     * @IocContainer
     */
    private static $instance;

    /**
     * Array to store classes specified via the bind method.
     *
     * @var array
     */
    private $bindings = [];

    /**
     * Return an instance of the IocContainer.
     *
     * @return IocContainer
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
     * Store multiple bindings.
     *
     * @param $bindings
     * @throws \Exception
     */
    public function bind($bindings)
    {
        if(!is_array($bindings)) {
            throw new \InvalidArgumentException('Expected an array.');
        }

        foreach($bindings as $binding) {
            if (!is_callable($binding)) {
                throw new \Exception('Binding has no closure.');
            }
        }

        $this->bindings = array_merge($this->bindings, $bindings);
    }

    /**
     * Check if class is bound.
     *
     * @param $className
     * @return bool
     */
    public function hasBinding($className)
    {
        return array_key_exists($className, $this->bindings);
    }

    /**
     * Returns a new class resolved by a closure stored
     * within the classBindings array.
     *
     * @param $className
     * @return mixed
     */
    private function resolveBinding($className)
    {
        return call_user_func($this->bindings[$className]);
    }

    /**
     * Gets the short class name from any given class name.
     *
     * @param $className
     * @return string
     */
    private function getAlias($className)
    {
        if(class_exists($className)) {
            return (new \ReflectionClass($className))->getShortName();
        } else {
            return $className;
        }
    }

    /**
     * Resolve a given class from the container.
     *
     * @param $className
     * @return object
     * @throws ClassNotFoundException
     * @throws TypeHintException
     */
    public function getClass($className)
    {

        $alias = $this->getAlias($className);

        // Try to resolve class from a registered binding.
        if ($this->hasBinding($alias)) {
            return $this->resolveBinding($alias);
        }

        // Checks if the supplied class exists.
        if (!class_exists($className)) {
            throw new ClassNotFoundException('Class ' . $className . ' does not exist');
        }

        // Checks if the given class has a constructor and if so resolves the dependencies of the constructor.
        try {
            $resolved = $this->resolveMethodParams($className, '__construct', []);
            return (new \ReflectionClass($className))->newInstanceArgs($resolved);
        } catch (MethodNotFoundException $e) {
            return new $className;
        }

    }

    /**
     * Resolve the params and type hinted classes of a supplied method.
     *
     * @param $className
     * @param $methodName
     * @param array $givenParams
     * @return array
     * @throws MethodNotFoundException
     * @throws TypeHintException
     */
    public function resolveMethodParams($className, $methodName, $givenParams )
    {

        // Check if the class has the supplied method.
        if (!method_exists($className, $methodName)) {
            throw new MethodNotFoundException('Method ' . $methodName . ' does not exist');
        }

        // Get params of the given method via PHP's reflection api.
        $classMethod = new \ReflectionMethod($className, $methodName);
        $unresolvedParams = $classMethod->getParameters();

        $resolved = [];

        // Check for each param if it is a type hint or a normal param and resolve the type hint.
        foreach ($unresolvedParams as $param) {
            if (array_key_exists($param->name, $givenParams)) {
                $resolved[$param->name] = $givenParams[$param->name];
            } else {
                if ($param->getClass()) {
                    $name = $param->getClass()->name;
                    $resolved[$param->name] = $this->getClass($name);
                } else {
                    throw new TypeHintException('Could not resolve Class of param <b>$' . $param->name . '</b> in '
                        . $className . '@' . $methodName . '. Did you forget to type hint?');
                }
            }
        }

        return $resolved;
    }

}