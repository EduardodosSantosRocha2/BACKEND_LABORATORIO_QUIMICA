<?php
require_once 'conexao.php';
require_once 'usuario.php';

// Classe Aula
class Aula {
    private $conn;
    private $table_name = 'Aula';

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE ID_aula = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function create($topico, $topicos_abordado, $nome, $cpf) {
        // Verificar se o usuário com o CPF fornecido existe
        $usuario = new Usuario($this->conn);
        $stmt = $usuario->readByCPF($cpf);
        $num = $stmt->rowCount();

        if ($num > 0) {
            // O usuário existe, pode criar a aula
            $query = "INSERT INTO " . $this->table_name . " (Topico, Topicos_abordado, Nome, fk_Professor_fk_Usuario_CPF) 
                    VALUES (:topico, :topicos_abordado, :nome, :cpf)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":topico", $topico);
            $stmt->bindParam(":topicos_abordado", $topicos_abordado);
            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":cpf", $cpf);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            // O usuário não existe, não pode criar a aula
            return false;
        }
    }

    public function update($id, $topico, $topicos_abordado, $nome, $cpf) {
        // Verificar se o usuário com o CPF fornecido existe
        $usuario = new Usuario($this->conn);
        $stmt = $usuario->readByCPF($cpf);
        $num = $stmt->rowCount();

        if ($num > 0) {
            // O usuário existe, pode atualizar a aula
            $query = "UPDATE " . $this->table_name . " SET Topico = :topico, Topicos_abordado = :topicos_abordado, Nome = :nome, 
                    fk_Professor_fk_Usuario_CPF = :cpf WHERE ID_aula = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":topico", $topico);
            $stmt->bindParam(":topicos_abordado", $topicos_abordado);
            $stmt->bindParam(":nome", $nome);
            $stmt->bindParam(":cpf", $cpf);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            // O usuário não existe, não pode atualizar a aula
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID_aula = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

// Função para lidar com as requisições
function handleRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            // Requisição para obter uma aula por ID
            $id = $_GET['id'];

            $aula = new Aula($conn);
            $stmt = $aula->readByID($id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Aula encontrada            
                $row = $stmt->fetch(PDO::FETCH_ASSOC);            
                echo json_encode($row);
            } else {
                // Nenhuma aula encontrada
                echo json_encode(array('message' => 'Nenhuma aula encontrada.','status'=>true));
            }
        } else {
            // Requisição para obter todas as aulas
            $aula = new Aula($conn);
            $stmt = $aula->readAll();
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Aulas encontradas
                $aulas_arr = array();
                $aulas_arr['aulas'] = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($aulas_arr['aulas'], $row);
                }

                echo json_encode($aulas_arr);
            } else {
                // Nenhuma aula encontrada
                echo json_encode(array('message' => 'Nenhuma aula encontrada.','status'=>true));
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar uma nova aula
        $data = json_decode(file_get_contents("php://input"));

        $topico = $data->topico;
        $topicos_abordado = $data->topicos_abordado;
        $nome = $data->nome;
        $cpf = $data->cpf;
        

        $aula = new Aula($conn);
        if ($aula->create($topico, $topicos_abordado, $nome, $cpf)) {
            echo json_encode(array('message' => 'Aula criada com sucesso.','status'=>true));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar a aula ou o usuário com o CPF fornecido não existe.', 'status'=>false));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Requisição para atualizar uma aula existente
        $data = json_decode(file_get_contents("php://input"));

        $id = $data->id;
        $topico = $data->topico;
        $topicos_abordado = $data->topicos_abordado;
        $nome = $data->nome;
        $cpf = $data->cpf;

        $aula = new Aula($conn);
        if ($aula->update($id, $topico, $topicos_abordado, $nome, $cpf)) {
            echo json_encode(array('message' => 'Aula atualizada com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível atualizar a aula ou o usuário com o CPF fornecido não existe.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir uma aula existente
        $data = json_decode(file_get_contents("php://input"));

        $id = $data->id;

        $aula = new Aula($conn);
        if ($aula->delete($id)) {
            echo json_encode(array('message' => 'Aula excluída com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir a aula.'));
        }
    }
}

// Lidar com a requisição
handleRequest($conn);
?>
