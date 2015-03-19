<?php namespace Ganymed\Persistence;


use Ganymed\Exceptions\FileNotFoundException;
use Ganymed\Exceptions\MethodNotFoundException;
use Ganymed\Exceptions\ModelNotFoundException;

class FileStorage implements StorageInterface {

    protected $file;

    protected $modelName;

    public function __construct($modelName, $config)
    {
        $this->file = $config['location'] . strtolower($modelName) . 's';

        $this->modelName = $modelName;
    }

    public function __call($name, $value)
    {
        if (strpos($name,'getBy') === false) {
            throw new MethodNotFoundException('Model ' . $this->modelName . ' has no method ' . $name);
        }

        $fieldName = strtolower(str_replace('getBy', '', $name));

        $models = $this->getAll();
        foreach($models as $model) {
            if($model->$fieldName == $value[0])
                return $model;
        }

        throw new ModelNotFoundException('Could not find Model.');
    }

    public function getAll()
    {
        if (is_file($this->file)) {
            return unserialize(file_get_contents($this->file));
        } else {
            throw new FileNotFoundException('File ' . $this->file . ' does not exists.');
        }
    }

    public function update($model)
    {
        $this->save($model);
    }

    public function save($model)
    {
        if(file_exists($this->file)) {
            $models = $this->getAll();
        } else {
            $models = [];
        }
        $models[$model->id] = $model;
        file_put_contents($this->file, serialize($models));
    }

    public function delete($model)
    {
        $models = $this->getAll();
        unset($models[$model->id]);
        file_put_contents($this->file, serialize($models));
    }
}