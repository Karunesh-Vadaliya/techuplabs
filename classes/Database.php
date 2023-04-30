<?php

class Database
{
    private $host = DB_HOST;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    private $database = DB_NAME;
    private $pdo;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->database;
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }

    public function insert($table, $data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", :", array_keys($data));
        $query = "INSERT INTO " . $table . " (" . $columns . ") VALUES (:" . $placeholders . ")";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function select($table, $conditions = array(), $limit = null)
    {
        $query = "SELECT * FROM " . $table;
        if (!empty($conditions)) {
            $where = array();
            foreach ($conditions as $column => $value) {
                $where[] = $column . " = :" . $column;
            }
            $where = implode(" AND ", $where);
            $query .= " WHERE " . $where;
        }
        if ($limit) {
            $query .= " LIMIT " . $limit;
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($conditions);
        if ($limit == 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function getTask($tasksQuery)
    {
        $stmt = $this->pdo->prepare($tasksQuery);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function query($query, $params = array())
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete($table, $where)
    {
        $query = "DELETE FROM $table WHERE $where";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute();
    }
}
