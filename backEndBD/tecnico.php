<?php
require_once 'conexao.php';
require_once 'usuario.php';

// Classe Técnico
class Tecnico {
    private $conn;
    private $table_name = 'Tecnico';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT t.*, u.Nome, u.Email FROM " . $this->table_name . " t
                  INNER JOIN Usuario u ON t.fk_Usuario_CPF = u.CPF";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByCPF($cpf) {
        $query = "SELECT t.*, u.Nome, u.Email,u.Telefone FROM " . $this->table_name . " t
                  INNER JOIN Usuario u ON t.fk_Usuario_CPF = u.CPF
                  WHERE t.fk_Usuario_CPF = :cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->execute();
        return $stmt;
    }

    public function create($cpf, $nome, $email, $senha, $telefone, $certificado, $numeroCRQ, $cargaHoraria) {
        $query = "INSERT INTO " . $this->table_name . " (fk_Usuario_CPF, Certificado, Numero_do_CRQ, Carga_horaria) 
                VALUES (:cpf, :certificado, :numeroCRQ, :cargaHoraria)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->bindParam(":certificado", $certificado);
        $stmt->bindParam(":numeroCRQ", $numeroCRQ);
        $stmt->bindParam(":cargaHoraria", $cargaHoraria);

        $usuario = new Usuario($this->conn);
        if (!$usuario->create($cpf, $nome, $email, $senha, $telefone)) {
            return false;
        }

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update($cpf, $nome, $email, $telefone, $certificado, $numeroCRQ, $cargaHoraria) {
        $query = "UPDATE " . $this->table_name . " SET Certificado = :certificado, Numero_do_CRQ = :numeroCRQ, Carga_horaria = :cargaHoraria 
                WHERE fk_Usuario_CPF = :cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->bindParam(":certificado", $certificado);
        $stmt->bindParam(":numeroCRQ", $numeroCRQ);
        $stmt->bindParam(":cargaHoraria", $cargaHoraria);

        $usuario = new Usuario($this->conn);
        if (!$usuario->update($cpf, $nome, $email, $telefone)) {
            return false;
        }

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($cpf) {
        $query = "DELETE FROM " . $this->table_name . " WHERE fk_Usuario_CPF = :cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
    
        if ($stmt->execute()) {
            $usuario = new Usuario($this->conn);
            if (!$usuario->delete($cpf)) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
}

// Função para lidar com as requisições
function handleRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['cpf'])) {
            // Requisição para obter um usuário por CPF
            $cpf = $_GET['cpf'];

            $usuario = new Usuario($conn);
            $stmt = $usuario->readByCPF($cpf);
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Usuário encontrado
                $usuario_item = $stmt->fetch(PDO::FETCH_ASSOC);

                $tecnico = new Tecnico($conn);
                $stmt_tecnico = $tecnico->readByCPF($cpf);
                $num_tecnico = $stmt_tecnico->rowCount();

                if ($num_tecnico > 0) {
                    // Técnico encontrado
                    $tecnico_item = $stmt_tecnico->fetch(PDO::FETCH_ASSOC);

                    echo json_encode($tecnico_item);
                } else {
                    // Nenhum técnico encontrado
                    echo json_encode(array('message' => 'Nenhum técnico encontrado.','status' => true));
                }
            } else {
                // Nenhum usuário encontrado
                echo json_encode(array('message' => 'Nenhum usuário encontrado.','status' => true));
            }
        } else {
           // Requisição para obter todos os técnicos
           $tecnico = new Tecnico($conn);
           $stmt = $tecnico->readAll();
           $num = $stmt->rowCount();

           if ($num > 0) {
               // Técnicos encontrados
               $tecnicos_arr = array();
               $tecnicos_arr['tecnicos'] = array();
               while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                   $tecnico_item = array($row);
                   array_push($tecnicos_arr['tecnicos'], $tecnico_item);
               }

               echo json_encode($tecnicos_arr);
           } else {
               // Nenhum técnico encontrado
               echo json_encode(array('message' => 'Nenhum técnico encontrado.'));
           }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar um novo usuário
        $data = json_decode(file_get_contents("php://input"));

        $cpf = $data->cpf;
        $nome = $data->nome;
        $email = $data->email;
        $senha = $data->senha;
        $telefone = $data->telefone;

        $tecnico = new Tecnico($conn);
        if ($tecnico->create($cpf, $nome, $email, $senha, $telefone, $data->certificado, $data->numeroCRQ, $data->cargaHoraria)) {
            echo json_encode(array('message' => 'Usuário e técnico criados com sucesso.','status' => true));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar o usuário e técnico.','status' => false));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Requisição para atualizar um usuário existente
        $data = json_decode(file_get_contents("php://input"));

        $cpf = $data->cpf;
        $nome = $data->nome;
        $email = $data->email;
        $telefone = $data->telefone;

        $tecnico = new Tecnico($conn);
        if ($tecnico->update($cpf, $nome, $email, $telefone, $data->certificado, $data->numeroCRQ, $data->cargaHoraria)) {
            echo json_encode(array('message' => 'Usuário e técnico atualizados com sucesso.','status' => true));
        } else {
            echo json_encode(array('message' => 'Não foi possível atualizar o usuário e técnico.','status' => false));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir um usuário e técnico
        $data = json_decode(file_get_contents("php://input"));
    
        $cpf = $data->cpf;
    
        $usuario = new Usuario($conn);
        $tecnico = new Tecnico($conn);
    
        if ($usuario->delete($cpf) && $tecnico->delete($cpf)) {
            echo json_encode(array('message' => 'Usuário e técnico excluídos com sucesso.','status' => true));
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir o usuário e técnico.','status' => false));
        }
    }
}
// Chamar a função handleRequest passando a conexão
handleRequest($conn);
?>
