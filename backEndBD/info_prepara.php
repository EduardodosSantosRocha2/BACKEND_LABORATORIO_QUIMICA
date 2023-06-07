<?php
require_once 'conexao.php';

class Experimento {
    private $conn;
    private $table_name = 'info_prepara';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}

function handleRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $experimento = new Experimento($conn);
        $stmt = $experimento->readAll();
        $num = $stmt->rowCount();

        if ($num > 0) {
            // Experimentos encontrados
            $experimentos_arr = array();
            $experimentos_arr['experimentos'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($experimentos_arr['experimentos'], $row);
            }

            echo json_encode($experimentos_arr);
        } else {
            // Nenhum experimento encontrado
            echo json_encode(array('message' => 'Nenhum experimento encontrado.'));
        }
    }
}

// Lidar com a requisição
handleRequest($conn);
?>