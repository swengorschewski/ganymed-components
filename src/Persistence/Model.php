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
        $config = require __DIR__ . '/../../../../../app/config/models.php';
        $storageImplementation = '\Ganymed\Persistence\\' . ucfirst($config['driver']) . 'Storage';
        $this->storage = new $storageImplementation(get_class($this));
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function get($id)
    {
        return $this->storage->get($id);
    }

    public function getAll()
    {
        return $this->storage->getAll();
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