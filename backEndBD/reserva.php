<?php
require_once 'conexao.php';
require_once 'usuario.php';

// Classe Reserva
class Reserva {
    private $conn;
    private $table_name = 'Reserva';

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE ID_RESERVA = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function create($horario, $data,$cpf) {
        // Verificar se o usuário com o CPF fornecido existe
        $usuario = new Usuario($this->conn);
        $stmt = $usuario->readByCPF($cpf);
        $num = $stmt->rowCount();

        if ($num > 0) {
            // O usuário existe, pode criar a reserva
            $query = "INSERT INTO " . $this->table_name . "(HORARIO_RESERVA, DATA_RESERVA, fk_Professor_fk_Usuario_CPF) 
                    VALUES (:horario, :data,:cpf)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":horario", $horario);
            $stmt->bindParam(":data", $data);
            $stmt->bindParam(":cpf", $cpf);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            // O usuário não existe, não pode criar a reserva
            return false;
        }
    }

    public function update($id, $horario,$data,$cpf) {
        // Verificar se o usuário com o CPF fornecido existe
        $usuario = new Usuario($this->conn);
        $stmt = $usuario->readByCPF($cpf);
        $num = $stmt->rowCount();

        if ($num > 0) {
            // O usuário existe, pode atualizar a reserva
            $query = "UPDATE " . $this->table_name . " SET HORARIO_RESERVA = :horario, DATA_RESERVA = :data, 
                    fk_Professor_fk_Usuario_CPF = :cpf WHERE ID_RESERVA = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":horario", $horario);
            $stmt->bindParam(":data", $data);
            $stmt->bindParam(":cpf", $cpf);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            // O usuário não existe, não pode atualizar a reserva
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID_RESERVA = :id";
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
            // Requisição para obter uma reserva por ID
            $id = $_GET['id'];

            $reserva = new Reserva($conn);
            $stmt = $reserva->readByID($id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Reserva encontrada
                $reservas_arr = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($reservas_arr);
            } else {
                // Nenhuma reserva encontrada
                echo json_encode(array('message' => 'Nenhuma reserva encontrada.','status'=>true));
            }
        } else {
            // Requisição para obter todas as reservas
            $reserva = new Reserva($conn);
            $stmt = $reserva->readAll();
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Reservas encontradas
                $reservas_arr = array();
                $reservas_arr['reservas'] = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($reservas_arr['reservas'], $row);
                }

                echo json_encode($reservas_arr);
            } else {
                // Nenhuma reserva encontrada
                echo json_encode(array('message' => 'Nenhuma reserva encontrada.','status'=> true));
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar uma nova reserva
        $data = json_decode(file_get_contents("php://input"));

        $horario = $data->horario;
        $dataR = $data->data;
        $cpf = $data->cpf;

        $reserva = new Reserva($conn);
        if ($reserva->create($horario, $dataR,$cpf)) {
            echo json_encode(array('message' => 'Reserva criada com sucesso.','status'=>true));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar a reserva ou o usuário com o CPF fornecido não existe.','status'=>false));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Requisição para atualizar uma reserva existente
        $data = json_decode(file_get_contents("php://input"));

        $id = $data->id;
        $horario = $data->horario;
        $dataR = $data->data;
        $cpf = $data->cpf;

        $reserva = new Reserva($conn);
        if ($reserva->update($id, $horario, $dataR,$cpf)) {
            echo json_encode(array('message' => 'Reserva atualizada com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível atualizar a reserva ou o usuário com o CPF fornecido não existe.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir uma reserva
        $data = json_decode(file_get_contents("php://input"));

        $id = $data->id;

        $reserva = new Reserva($conn);
        if ($reserva->delete($id)) {
            echo json_encode(array('message' => 'Reserva excluída com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir a reserva.'));
        }
    }
}

// Chamar a função handleRequest passando a conexão
handleRequest($conn);
?>
