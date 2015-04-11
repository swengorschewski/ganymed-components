<?php namespace Ganymed\Persistence;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\Exceptions\EntryNotFoundException;
use Ganymed\Exceptions\FileNotFoundException;
use Ganymed\Exceptions\MethodNotFoundException;
use Ganymed\Exceptions\ModelNotFoundException;

class FileStorage implements StorageInterface {

    /**
     * Name of the file to store a given models.
     *
     * @var string
     */
    protected $file;

    /**
     * Name of the model.
     *
     * @var string
     */
    protected $modelName;

    public function __construct($modelName, $config)
    {
        $this->file = $config['location'] . strtolower($modelName) . 's';

        $this->modelName = $modelName;
    }

    /**
     * Magic method to get a model by any attribute.
     * Method calls have to be of type 'getBy<attribute>'.
     *
     * @param $name
     * @param $value
     * @return mixed
     * @throws FileNotFoundException
     * @throws MethodNotFoundException
     * @throws ModelNotFoundException
     */
    public function __call($name, $value)
    {
        if (strpos($name,'getBy') === false) {
            throw new MethodNotFoundException('Model ' . $this->modelName . ' has no method ' . $name);
        }

        $fieldName = strtolower(str_replace('getBy', '', $name));

        $models = $this->getAll();

        if($fieldName == 'id')
            return $models[$value[0]];

        foreach($models as $model) {
            if($model->$fieldName == $value[0])
                return $model;
        }

        return [];
    }

    /**
     * Get an array of all entries of a given model.
     *
     * @return array
     * @throws FileNotFoundException
     */
    public function getAll()
    {

        if (is_file($this->file)) {
            return unserialize(file_get_contents($this->file));
        } else {
            throw new FileNotFoundException('File ' . $this->file . ' does not exists.');
        }
    }

    /**
     * Update a given model.
     *
     * @param $model
     * @throws EntryNotFoundException
     * @throws FileNotFoundException
     */
    public function update($model)
    {
        $models = $this->getAll();

        if(array_key_exists($model->id, $models)) {
            $models[$model->id] = $model;
            file_put_contents($this->file, serialize($models));
        } else  {
            throw new EntryNotFoundException('Entry with ' . $model->id . ' does not exist.');
        }
    }

    /**
     * Save a model to the corresponding file.
     *
     * @param $model
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function save($model)
    {
        if(file_exists($this->file)) {
            $models = $this->getAll();
        } else {
            $models = [];
        }

        if(isset($model->id)) {
            if(!array_key_exists($model->id, $models)) {
                $models[$model->id] = $model;
            } else {
                throw new \Exception('Id exists already');
            }

        } else {
            $key = $this->getHighestKey($models);
            $model->id = $key;
            $models[$key] = $model;
        }

        file_put_contents($this->file, serialize($models));
    }

    /**
     * Delete a given model from the corresponding file.
     *
     * @param $model
     * @throws FileNotFoundException
     */
    public function delete($model)
    {
        $models = $this->getAll();
        unset($models[$model->id]);
        file_put_contents($this->file, serialize($models));
    }

    /**
     * Get a new id for a new entry.
     * Does not work if the ids are not numeric.
     *
     * @param $models
     * @return int
     * @throws \Exception
     */
    private function getHighestKey($models)
    {
        $keys = array_keys($models);

        if(sizeof($keys) == 0)
            return 0;

        $max = 0;
        foreach($keys as $key) {
            if(intval($key)) {
               if($key > $max)
                   $max = $key;
            } else {
                throw new \Exception('Keys are not of type integer. Therefor need to supply an id.');
            }
        }

        return $max + 1;
    }
}