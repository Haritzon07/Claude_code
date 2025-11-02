<?php
// =====================================================
// CLASE PARA MANEJAR LA CONEXIÓN A LA BASE DE DATOS
// =====================================================

if (!class_exists('Database')) {

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $conn = null;

    /**
     * Obtener la conexión a la base de datos
     */
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            } catch(PDOException $e) {
                error_log("Error de conexión: " . $e->getMessage());
                die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
            }
        }

        return $this->conn;
    }

    /**
     * Cerrar la conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }

    /**
     * Ejecutar una consulta preparada
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error en query: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener un solo registro
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    /**
     * Obtener múltiples registros
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->getConnection()->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->getConnection()->rollback();
    }
}

} // End if (!class_exists('Database'))
