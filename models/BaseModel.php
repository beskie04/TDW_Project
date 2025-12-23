<?php
require_once __DIR__ . '/../config/database.php';

class BaseModel
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Récupérer tous les enregistrements
     */
    public function getAll($orderBy = null, $order = 'ASC')
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupérer un enregistrement par ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Insérer un nouvel enregistrement
     */
    public function insert($data)
    {
        $fields = array_keys($data);
        $placeholders = array_map(function ($field) {
            return ':' . $field; }, $fields);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    /**
     * Mettre à jour un enregistrement
     */
    public function update($id, $data)
    {
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = :{$field}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) .
            " WHERE {$this->primaryKey} = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        return $stmt->execute();
    }

    /**
     * Supprimer un enregistrement
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Rechercher avec des conditions WHERE
     */
    public function where($conditions, $orderBy = null, $order = 'ASC', $limit = null)
    {
        $whereClauses = [];
        $params = [];

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                // Pour les opérateurs comme LIKE, >, <, etc.
                $whereClauses[] = "{$field} {$value[0]} :{$field}";
                $params[$field] = $value[1];
            } else {
                $whereClauses[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClauses);

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $limit === 1 ? $stmt->fetch() : $stmt->fetchAll();
    }

    /**
     * Compter les enregistrements
     */
    public function count($conditions = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if (!empty($conditions)) {
            $whereClauses = [];
            $params = [];

            foreach ($conditions as $field => $value) {
                $whereClauses[] = "{$field} = :{$field}";
                $params[$field] = $value;
            }

            $sql .= " WHERE " . implode(' AND ', $whereClauses);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }

        return $stmt->fetch()['total'];
    }

    /**
     * Requête personnalisée
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Exécuter une requête (INSERT, UPDATE, DELETE)
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
?>