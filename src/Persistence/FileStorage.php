<?php namespace Ganymed\Persistence;


use Ganymed\Exceptions\PageNotFoundException;

class FileStorage implements StorageInterface {

    protected $file;

    public function __construct($fileName, $config)
    {
        $this->file = $config['location'] . $fileName;
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
            throw new PageNotFoundException('File ' . $this->file . ' does not exists.');
        }
    }

    public function update($model)
    {
        $this->save($model);
    }

    public function save($model)
    {
        if(file_exists($this->file)) {
            $usersArray = $this->getAll();
        } else {
            $usersArray = [];
        }
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