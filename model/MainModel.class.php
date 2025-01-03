<?php

require_once "config.php";
class MainModel{
    protected $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }
        
    public function read($table, $conditions = []){
        $sql = "SELECT * FROM {$table}";
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($table, $data)
    {
            if (isset($data[0]) && is_array($data[0])) {
                $columns = implode(',', array_keys($data[0]));
                $placeholders = '(' . implode(',', array_fill(0, count($data[0]), '?')) . ')';
                $sql = "INSERT INTO {$table} ({$columns}) VALUES " . implode(',', array_fill(0, count($data), $placeholders));
                
                $flatData = [];
                foreach ($data as $row) {
                    $flatData = array_merge($flatData, array_values($row));
                }
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($flatData);
            } else {
                $columns = implode(',', array_keys($data));
                $placeholders = ':' . implode(', :', array_keys($data));
                $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute($data);
    
                return $this->db->lastInsertId();
            }
        
    }
    
    public function delete($table, $conditions)
    {
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
        }

        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
    }

    public function update($table, $data, $conditions) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :where_{$key}";
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $where);
        
        $params = [];
        foreach ($data as $key => $value) {
            $params[$key] = $value;
        }
        foreach ($conditions as $key => $value) {
            $params["where_" . $key] = $value;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

}
