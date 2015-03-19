<?php namespace Ganymed\Persistence;


use Ganymed\Exceptions\MethodNotFoundException;
use PDO;

class MySQLStorage implements StorageInterface {

    protected $dbh;

    protected $tableName;

    protected $modelName;

    public function __construct($modelName, $config)
    {
        $this->modelName = $modelName;
        $this->tableName = strtolower($modelName) . 's';

        $host = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $this->dbh = new PDO(
            "mysql:host=$host;dbname=$dbName",
            getenv('DB_USER'),
            getenv('DB_PASSWORD')
        );
        $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function __call($name, $values)
    {

        if (strpos($name,'getBy') === false) {
            throw new MethodNotFoundException('Model ' . $this->modelName . ' has no method ' . $name);
        }

        $fieldName = strtolower(str_replace('getBy', '', $name));

        $statement = $this->dbh->query(
            "SELECT * FROM " . $this->tableName . " WHERE " . $fieldName . "=" . $values[0]
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

        throw new ModelNotFoundException('Could not find Model.');

    }

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

    public function update($model)
    {
        $columnNames = $this->dbh->query("DESCRIBE " . $this->tableName)
            ->fetchAll(PDO::FETCH_COLUMN);

        if(($key = array_search('id', $columnNames)) !== false) {
            unset($columnNames[$key]);
        }

        $columnNamesAsString = '';
        foreach($columnNames as $name) {
            $columnNamesAsString .= $name . ', ';
        }

        $sql = 'INSERT INTO ' . $this->tableName . '(' . $columnNamesAsString . ') ' .
            'VALUES';

        dd($sql);
    }

    public function save($model)
    {

    }

    public function delete($id)
    {

    }
}