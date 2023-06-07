<?php
require_once 'conexao.php';

// Classe RealizaExperimento
class RealizaExperimento {
    private $conn;
    private $table_name = 'Realiza_experimento';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($aula_id, $experimento_numero, $observacao) {
        // Verificar se a aula e o experimento existem
        if (!$this->isAulaExists($aula_id) || !$this->isExperimentoExists($experimento_numero)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " (fk_Aula_ID_aula, fk_Experimento_Numero_Experimento, Observacao) 
                VALUES (:aula_id, :experimento_numero, :observacao)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":aula_id", $aula_id);
        $stmt->bindParam(":experimento_numero", $experimento_numero);
        $stmt->bindParam(":observacao", $observacao);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($aula_id, $experimento_numero) {
        $query = "DELETE FROM " . $this->table_name . " WHERE fk_Aula_ID_aula = :aula_id AND fk_Experimento_Numero_Experimento = :experimento_numero";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":aula_id", $aula_id);
        $stmt->bindParam(":experimento_numero", $experimento_numero);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    private function isAulaExists($aula_id) {
        $query = "SELECT ID_aula FROM Aula WHERE ID_aula = :aula_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":aula_id", $aula_id);
        $stmt->execute();
        $num = $stmt->rowCount();

        return $num > 0;
    }

    private function isExperimentoExists($experimento_numero) {
        $query = "SELECT Numero_Experimento FROM Experimento WHERE Numero_Experimento = :experimento_numero";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":experimento_numero", $experimento_numero);
        $stmt->execute();
        $num = $stmt->rowCount();

        return $num > 0;
    }
}

// Função para lidar com as requisições
function handleRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar um novo registro de realização de experimento
        $data = json_decode(file_get_contents("php://input"));

        $aula_id = $data->aula_id;
        $experimento_numero = $data->experimento_numero;
        $observacao = $data->observacao;

        $realizaExperimento = new RealizaExperimento($conn);
        if ($realizaExperimento->create($aula_id, $experimento_numero, $observacao)) {
            echo json_encode(array('message' => 'Registro de realização de experimento criado com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar o registro de realização de experimento. Verifique se a aula e o experimento existem.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir um registro de realização de experimento existente
        $data = json_decode(file_get_contents("php://input"));

        $aula_id = $data->aula_id;
        $experimento_numero = $data->experimento_numero;

        $realizaExperimento = new RealizaExperimento($conn);
        if ($realizaExperimento->delete($aula_id, $experimento_numero)) {
            echo json_encode(array('message' => 'Registro de realização de experimento excluído com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir o registro de realização de experimento.'));
        }
    }
}

// Lidar com a requisição
handleRequest($conn);
?>
