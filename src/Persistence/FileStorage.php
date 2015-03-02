<?php namespace Ganymed\Persistence;


class FileStorage implements StorageInterface {

    protected $config;
    protected $file;

    public function __construct($modelName)
    {
        $this->config = require __DIR__ . '/../../../../../app/config/models.php';
        $this->file = $this->config['location'] . strtolower($modelName) . 's';
    }

    public function get($id)
    {
        $usersArray = $this->getAll();
        if (array_key_exists($id, $usersArray)) {
            return $usersArray[$id];
        } else {
            return false;
        }
    }

    public function getAll()
    {
        if (is_file($this->file)) {
            return unserialize(file_get_contents($this->file));
        } else {
            return [];
        }
    }

    public function update($model)
    {
        $this->save($model);
    }

    public function save($model)
    {
        $usersArray = $this->getAll();
        $usersArray[$model->id] = $model;
        file_put_contents($this->file, serialize($usersArray));
    }

    public function delete($model)
    {
        $usersArray = $this->getAll();
        unset($usersArray[$model->id]);
        file_put_contents($this->file, serialize($usersArray));
    }
}