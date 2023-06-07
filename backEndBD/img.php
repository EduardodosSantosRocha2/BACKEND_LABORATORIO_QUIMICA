<?php
require_once 'conexao.php';
class Experimento {
    private $conn;
    private $table_name = 'Experimento';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByID($id) {
        //objetivo,nome,numero_experimento,discussao,vezes_realizadas,arquivo_img,arquivo_pdf
        $query = "SELECT encode(arquivo_pdf::bytea, 'base64') AS arquivo_pdf, 
        encode(arquivo_img::bytea, 'base64') AS arquivo_img  FROM " . $this->table_name . " WHERE Numero_Experimento = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function create($objetivo, $nome, $numeroExperimento, $arquivoPDF, $discussao, $arquivoIMG, $vezesRealizadas) {
        $query = "INSERT INTO " . $this->table_name . " (Objetivo, Nome, Numero_Experimento, Arquivo_PDF, Discussao, Arquivo_IMG, Vezes_Realizadas) 
                VALUES (:objetivo, :nome, :numeroExperimento, :arquivoPDF, :discussao, :arquivoIMG, :vezesRealizadas)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":objetivo", $objetivo);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":numeroExperimento", $numeroExperimento);
        $stmt->bindParam(":arquivoPDF", $arquivoPDF);
        $stmt->bindParam(":discussao", $discussao);
        $stmt->bindParam(":arquivoIMG", $arquivoIMG);
        $stmt->bindParam(":vezesRealizadas", $vezesRealizadas);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update($numeroExperimento, $objetivo, $nome, $arquivoPDF, $discussao, $arquivoIMG, $vezesRealizadas) {
        $query = "UPDATE " . $this->table_name . " SET Objetivo = :objetivo, Nome = :nome, Arquivo_PDF = :arquivoPDF, Discussao = :discussao, 
                Arquivo_IMG = :arquivoIMG, Vezes_Realizadas = :vezesRealizadas WHERE Numero_Experimento = :numeroExperimento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numeroExperimento", $numeroExperimento);
        $stmt->bindParam(":objetivo", $objetivo);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":arquivoPDF", $arquivoPDF);
        $stmt->bindParam(":discussao", $discussao);
        $stmt->bindParam(":arquivoIMG", $arquivoIMG);
        $stmt->bindParam(":vezesRealizadas", $vezesRealizadas);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($numeroExperimento) {
        $query = "DELETE FROM " . $this->table_name . " WHERE Numero_Experimento = :numeroExperimento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":numeroExperimento", $numeroExperimento);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

function handleRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            // Requisição para obter um experimento por ID
            $id = $_GET['id'];

            $experimento = new Experimento($conn);
            $stmt = $experimento->readByID($id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Experimento encontrado

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                // Nenhum experimento encontrado
                echo json_encode(array('message' => 'Nenhum experimento encontrado.','status'=>true));
            }
        } else {
            // Requisição para obter todos os experimentos
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
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar um novo experimento
        $data = json_decode(file_get_contents("php://input"));

        $objetivo = $data->objetivo;
        $nome = $data->nome;
        $numeroExperimento = $data->numeroExperimento;
        $arquivoPDF = $data->arquivoPDF;
        $discussao = $data->discussao;
        $arquivoIMG = $data->arquivoIMG;
        $vezesRealizadas = $data->vezesRealizadas;

        $experimento = new Experimento($conn);
        if ($experimento->create($objetivo, $nome, $numeroExperimento, $arquivoPDF, $discussao, $arquivoIMG, $vezesRealizadas)) {
            echo json_encode(array('message' => 'Experimento criado com sucesso.', 'status'=> true));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar o experimento.','status'=> false));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Requisição para atualizar um experimento existente
        $data = json_decode(file_get_contents("php://input"));

        $numeroExperimento = $data->numeroExperimento;
        $objetivo = $data->objetivo;
        $nome = $data->nome;
        $arquivoPDF = $data->arquivoPDF;
        $discussao = $data->discussao;
        $arquivoIMG = $data->arquivoIMG;
        $vezesRealizadas = $data->vezesRealizadas;

        $experimento = new Experimento($conn);
        if ($experimento->update($numeroExperimento, $objetivo, $nome, $arquivoPDF, $discussao, $arquivoIMG, $vezesRealizadas)) {
            echo json_encode(array('message' => 'Experimento atualizado com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível atualizar o experimento.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir um experimento existente
        $data = json_decode(file_get_contents("php://input"));

        $numeroExperimento = $data->numeroExperimento;

        $experimento = new Experimento($conn);
        if ($experimento->delete($numeroExperimento)) {
            echo json_encode(array('message' => 'Experimento excluído com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir o experimento.'));
        }
    }
}

// Lidar com a requisição
handleRequest($conn);
?>