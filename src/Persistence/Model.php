<?php namespace Ganymed\Persistence;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

abstract class Model {

    /**
     * @var StorageInterface
     */
    private $storage;

    function __construct()
    {
        $this->storage = self::getStorage();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    private static function getStorage() {
        $config = require __DIR__ . '/../../../../app/config/models.php';
        $storageImplementation = '\Ganymed\Persistence\\' . ucfirst($config['driver']) . 'Storage';

        return new $storageImplementation((new \ReflectionClass(get_called_class()))->getShortName(), $config);
    }
    
    public static function __callStatic($name, $values)
    {
        $storage = self::getStorage();
        return $storage->$name(array_shift($values));
    }

    public static function getAll()
    {
        $storage = self::getStorage();
        return $storage->getAll();
    }

    public function update()
    {
        $this->storage->update($this);
    }

    public function save()
    {
        $this->storage->save($this);
    }

    public function delete()
    {
        $this->storage->delete($this);
    }
}