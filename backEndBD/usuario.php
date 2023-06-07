<?php
require_once 'conexao.php';

// Classe Usuario
class Usuario {
    private $conn;
    private $table_name = 'Usuario';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByCPF($cpf) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE cpf = :cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->execute();
        return $stmt;
    }

    public function create($cpf, $nome, $email, $senha, $telefone) {
        $senha = md5($senha);
        $query = "INSERT INTO " . $this->table_name . " (cpf, nome, email, senha, telefone) VALUES (:cpf, :nome, :email, :senha, :telefone)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha);
        $stmt->bindParam(":telefone", $telefone);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update($cpf, $nome, $email, $telefone) {
        $query = "UPDATE " . $this->table_name . " SET ";
        $params = array();

        if (!empty($nome)) {
            $query .= "nome = :nome";
            $params[':nome'] = $nome;
        }

        if (!empty($email)) {
            if (!empty($nome)) {
                $query .= ", ";
            }
            $query .= "email = :email";
            $params[':email'] = $email;
        }

        if (!empty($telefone)) {
            if (!empty($nome) || !empty($email)) {
                $query .= ", ";
            }
            $query .= "telefone = :telefone";
            $params[':telefone'] = $telefone;
        }

        $query .= " WHERE cpf = :cpf";
        $params[':cpf'] = $cpf;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($cpf) {
        $query = "DELETE FROM " . $this->table_name . " WHERE cpf = :cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}

// Função para lidar com as requisições
// function handleRequest($conn) {
//     if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//         if (isset($_GET['cpf'])) {
//             // Requisição para obter um usuário por CPF
//             $cpf = $_GET['cpf'];

//             $usuario = new Usuario($conn);
//             $stmt = $usuario->readByCPF($cpf);
//             $num = $stmt->rowCount();

//             if ($num > 0) {
//                 // Usuário encontrado
//                 $usuarios_arr = array();
//                 $usuarios_arr['usuarios'] = array();

//                 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                     $usuario_item = array();
//                     array_push($usuarios_arr['usuarios'], $row);
//                 }

//                 echo json_encode($usuarios_arr);
//             } else {
//                 // Nenhum usuário encontrado
//                 echo json_encode(array('message' => 'Nenhum usuário encontrado.'));
//             }
//         } else {
//             // Requisição para obter todos os usuários
//             $usuario = new Usuario($conn);
//             $stmt = $usuario->readAll();
//             $num = $stmt->rowCount();

//             if ($num > 0) {
//                 // Usuários encontrados
//                 $usuarios_arr = array();
//                 $usuarios_arr['usuarios'] = array();
//                 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                     array_push($usuarios_arr['usuarios'], $row);
//                 }

//                 echo json_encode($usuarios_arr);
//             } else {
//                 // Nenhum usuário encontrado
//                 echo json_encode(array('message' => 'Nenhum usuário encontrado.'));
//             }
//         }
//     } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
//         // Requisição para criar um novo usuário
//         $data = json_decode(file_get_contents("php://input"));

//         $cpf = $data->cpf;
//         $nome = $data->nome;
//         $email = $data->email;
//         $telefone = $data->telefone;

//         $usuario = new Usuario($conn);
//         if ($usuario->create($cpf, $nome, $email, $telefone)) {
//             echo json_encode(array('message' => 'Usuário criado com sucesso.'));
//         } else {
//             echo json_encode(array('message' => 'Não foi possível criar o usuário.'));
//         }
//     } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
//         // Requisição para atualizar um usuário existente
//         $data = json_decode(file_get_contents("php://input"));

//         $cpf = $data->cpf;
//         $nome = isset($data->nome) ? $data->nome : '';
//         $email = isset($data->email) ? $data->email : '';
//         $telefone = isset($data->telefone) ? $data->telefone : '';

//         $usuario = new Usuario($conn);
//         if ($usuario->update($cpf, $nome, $email, $telefone)) {
//             echo json_encode(array('message' => 'Usuário atualizado com sucesso.'));
//         } else {
//             echo json_encode(array('message' => 'Não foi possível atualizar o usuário.'));
//         }
//     } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
//         // Requisição para excluir um usuário
//         $data = json_decode(file_get_contents("php://input"));

//         $cpf = $data->cpf;

//         $usuario = new Usuario($conn);
//         if ($usuario->delete($cpf)) {
//             echo json_encode(array('message' => 'Usuário excluído com sucesso.'));
//         } else {
//             echo json_encode(array('message' => 'Não foi possível excluir o usuário.'));
//         }
//     }
// }

// // Chamar a função handleRequest passando a conexão
// handleRequest($conn);
?>
