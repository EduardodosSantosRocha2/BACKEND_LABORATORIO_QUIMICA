<?php
require_once 'conexao.php';
require_once 'usuario.php';

// Classe Reserva
class Reserva {
    private $conn;
    private $table_name = 'Reserva';
    private $table_name2 = 'ObterInformacoesUsuario';

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

function obterInformacoesUsuario($cpf) {      
        $query = "SELECT * FROM ObterInformacoesUsuario(:cpf)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $valor[0] = $result['funcao'];
        $valor[1] = $result['nome'];
        
        return $valor;
}
    
    
    
    
    
    
    
    
    
    

public function create($horario, $data) {
        // Verificar a disponibilidade do laboratório
        $message = $this->verificarDisponibilidadeLaboratorio($data, $horario);
    
        if (strpos($message, 'disponível') !== false) {
            // O laboratório está disponível, criar a reserva
            $query = "INSERT INTO " . $this->table_name . " (HORARIO_RESERVA, DATA_RESERVA) VALUES (:horario, :data)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":horario", $horario);
            $stmt->bindParam(":data", $data);
    
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            // O laboratório não está disponível
            return false;
        }
    }
    

    public function update($id, $horario, $data, $turno, $cpf) {
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
        if (isset($_GET['cpf'])) {
            // Requisição para obter uma reserva por ID
            $id = $_GET['cpf'];

            $reserva = new Reserva($conn);
            $stmt = $reserva->readByID($id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Reserva encontrada
                $reservas_arr = array();
                $reservas_arr['reservas'] = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $reserva_item = array();
                    array_push($reservas_arr['reservas'], $row);
                }

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
        
        $cpf = $data->cpf;
        $reserva = new Reserva($conn);
        
        $consulta = $reserva->obterInformacoesUsuario($cpf);
       
        echo json_encode(array('message' => 'Info Usuario!','valor'=>$consulta[0],'status'=>true, 'nome'=>$consulta[1]));
        
    } 
}

// Chamar a função handleRequest passando a conexão
handleRequest($conn);
?>
