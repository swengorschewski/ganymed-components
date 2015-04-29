<?php namespace Ganymed\Persistence;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\Exceptions\MethodNotFoundException;
use Ganymed\Exceptions\ModelNotFoundException;
use PDO;

class MySQLStorage implements StorageInterface {

    /**
     * PDO object.
     *
     * @var PDO
     */
    protected $dbh;

    /**
     * Name of the database table.
     *
     * @var string
     */
    protected $tableName;

    /**
     * Name of the model.
     *
     * @var string
     */
    protected $modelName;

    public function __construct($modelName, $config)
    {
        $this->modelName = $modelName;
        $this->tableName = strtolower($modelName) . 's';

        $host = getenv('DB_HOST');
        $dbPort = getenv('DB_PORT');
        $dbName = getenv('DB_NAME');
        $this->dbh = new PDO(
            "mysql:host=$host;port=$dbPort;dbname=$dbName",
            getenv('DB_USER'),
            getenv('DB_PASSWORD')
        );
        $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Magic method, which excepts only method names prefixed with 'getBy'
     * to determine by which column name to select entries.
     *
     * @param $name
     * @param $values
     * @return mixed
     * @throws MethodNotFoundException
     * @throws ModelNotFoundException
     */
    public function __call($name, $values)
    {
        if (strpos($name,'getBy') === false) {
            throw new MethodNotFoundException('Model ' . $this->modelName . ' has no method ' . $name);
        }

        $fieldName = strtolower(str_replace('getBy', '', $name));

        $statement = $this->dbh->query(
            "SELECT * FROM " . $this->tableName . " WHERE " . $fieldName . "='" . $values[0] . "';'"
        );
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        $row = $statement->fetch();

        $modelInstance = new $this->modelName;

        if(is_array($row)) {
            foreach($row as $key => $value) {
                $modelInstance->$key = $value;
            }

            return $modelInstance;
        }

        throw new ModelNotFoundException('Could not find ' . $this->modelName . ' by ' . $fieldName);
    }

    /**
     * Method to get all entries from a database table.
     *
     * @return array
     */
    public function getAll()
    {
        $statement = $this->dbh->query("SELECT * FROM " . $this->tableName);
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        $result = [];

        while($row = $statement->fetch()) {
            $modelInstance = new $this->modelName;

            foreach($row as $key => $value) {
                $modelInstance->$key = $value;
            }

            array_push($result, $modelInstance);
        }

        return $result;
    }

    /**
     * Update all entries in a table corresponding to a given model.
     *
     * @param $model
     */
    public function update($model)
    {
        $columnNames = $this->getColumnNames($model);

        $columnNamesAsString = '';
        $bindings = [];
        foreach($columnNames as $name) {
            $columnNamesAsString .= $name . '=?, ';
            $bindings[] = $model->$name;
        }
        $bindings[] = $model->id;

        $columnNamesAsString = rtrim(trim($columnNamesAsString), ',');

        $sql = 'UPDATE ' . $this->tableName . ' SET ' . $columnNamesAsString . ' WHERE id=?;';

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($bindings);
    }

    /**
     * Save all attributes from a model to the corresponding table.
     *
     * @param $model
     */
    public function save($model)
    {
        $columnNames = $this->getColumnNames($model);

        $columnNamesAsString = '';
        $bindingNames = '';
        $bindings = [];
        foreach($columnNames as $name) {
            $columnNamesAsString .= $name . ', ';
            $bindingNames .= ':' . $name . ', ';
            $bindings[':' . $name] = $model->$name;
        }

        $columnNamesAsString = rtrim(trim($columnNamesAsString), ',');
        $bindingNames = rtrim(trim($bindingNames), ',');

        $sql = 'INSERT INTO ' . $this->tableName . ' (' . $columnNamesAsString . ') ' .
            'VALUES (' . $bindingNames . ');';

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($bindings);
    }

    /**
     * Delete all entries corresponding to a model.
     *
     * @param $model
     */
    public function delete($model)
    {
        $sql = 'DELETE FROM ' . $this->tableName . ' WHERE id=?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute(array($model->id));
    }

    /**
     * @param $model
     * @return array
     */
    private function getColumnNames($model)
    {
        $columnNames = $this->dbh->query("DESCRIBE " . $this->tableName)
            ->fetchAll(PDO::FETCH_COLUMN);

        if (!isset($model->id) && ($key = array_search('id', $columnNames)) !== false) {
            unset($columnNames[$key]);
        }
        return $columnNames;
    }
}