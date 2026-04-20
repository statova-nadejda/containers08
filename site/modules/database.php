<?php

class Database
{
    private PDO $pdo;

    public function __construct($path)
    {
        try {
            $this->pdo = new PDO('sqlite:' . $path);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }

    public function Execute($sql)
    {
        try {
            return $this->pdo->exec($sql);
        } catch (PDOException $e) {
            die("SQL execution error: " . $e->getMessage());
        }
    }

    public function Fetch($sql)
    {
        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            die("Fetch error: " . $e->getMessage());
        }
    }

    public function Create($table, $data)
    {
        try {
            $fields = array_keys($data);
            $placeholders = array_map(function ($field) {
                return ':' . $field;
            }, $fields);

            $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";

            $stmt = $this->pdo->prepare($sql);

            foreach ($data as $field => $value) {
                $stmt->bindValue(':' . $field, $value);
            }

            $stmt->execute();

            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            die("Create record error: " . $e->getMessage());
        }
    }

    public function Read($table, $id)
    {
        try {
            $sql = "SELECT * FROM {$table} WHERE id = :id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            die("Read error: " . $e->getMessage());
        }
    }

    public function Update($table, $id, $data)
    {
        try {
            $fields = [];

            foreach ($data as $field => $value) {
                $fields[] = "{$field} = :{$field}";
            }

            $sql = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);

            foreach ($data as $field => $value) {
                $stmt->bindValue(':' . $field, $value);
            }

            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Update error: " . $e->getMessage());
        }
    }

    public function Delete($table, $id)
    {
        try {
            $sql = "DELETE FROM {$table} WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            die("Delete error: " . $e->getMessage());
        }
    }

    public function Count($table)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$table}";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();

            return (int)$result['count'];
        } catch (PDOException $e) {
            die("Count error: " . $e->getMessage());
        }
    }
}