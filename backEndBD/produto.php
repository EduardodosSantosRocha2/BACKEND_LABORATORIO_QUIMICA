<?php
require_once 'conexao.php';

// Classe Produto
class Produto {
    private $conn;
    private $table_name = 'Produto';

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE ID_Prod = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function create($nome, $descricao, $fabricante, $preco, $data_fabricacao, $fk_prateleiras) {
        $query = "INSERT INTO " . $this->table_name . " (Nome, Descricao, Fabricante, Preco, Data_de_fabricacao, fk_Prateleiras_Numero_Prateleira) VALUES (:nome, :descricao, :fabricante, :preco, :data_fabricacao, :fk_prateleiras)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nome", $nome);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->bindParam(":fabricante", $fabricante);
        $stmt->bindParam(":preco", $preco);
        $stmt->bindParam(":data_fabricacao", $data_fabricacao);
        $stmt->bindParam(":fk_prateleiras", $fk_prateleiras);
    
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        } else {
            return null;
        }
    }

    public function update($id, $nome, $descricao, $fabricante, $preco, $data_fabricacao, $fk_prateleiras) {
        $query = "UPDATE " . $this->table_name . " SET ";
        $params = array();

        if (!empty($nome)) {
            $query .= "Nome = :nome";
            $params[':nome'] = $nome;
        }

        if (!empty($descricao)) {
            if (!empty($nome)) {
                $query .= ", ";
            }
            $query .= "Descricao = :descricao";
            $params[':descricao'] = $descricao;
        }

        if (!empty($fabricante)) {
            if (!empty($nome) || !empty($descricao)) {
                $query .= ", ";
            }
            $query .= "Fabricante = :fabricante";
            $params[':fabricante'] = $fabricante;
        }

        if (!empty($preco)) {
            if (!empty($nome) || !empty($descricao) || !empty($fabricante)) {
                $query .= ", ";
            }
            $query .= "Preco = :preco";
            $params[':preco'] = $preco;
        }

        if (!empty($data_fabricacao)) {
            if (!empty($nome) || !empty($descricao) || !empty($fabricante) || !empty($preco)) {
                $query .= ", ";
            }
            $query .= "Data_de_fabricacao = :data_fabricacao";
            $params[':data_fabricacao'] = $data_fabricacao;
        }

        if (!empty($fk_prateleiras)) {
            if (!empty($nome) || !empty($descricao) || !empty($fabricante) || !empty($preco) || !empty($data_fabricacao)) {
                $query .= ", ";
            }
            $query .= "fk_Prateleiras_Numero_Prateleira = :fk_prateleiras";
            $params[':fk_prateleiras'] = $fk_prateleiras;
        }

        $query .= " WHERE ID_Prod = :id";
        $params[':id'] = $id;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE ID_Prod = :id";
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
// function handleRequest($conn) {
//     if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//         if (isset($_GET['id'])) {
//             // Requisição para obter um produto por ID
//             $id = $_GET['id'];

//             $produto = new Produto($conn);
//             $stmt = $produto->readByID($id);
//             $num = $stmt->rowCount();

//             if ($num > 0) {
//                 // Produto encontrado
//                 $produtos_arr = array();
//                 $produtos_arr['produtos'] = array();

//                 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                     $produto_item = array();
//                     array_push($produtos_arr['produtos'], $row);
//                 }

//                 echo json_encode($produtos_arr);
//             } else {
//                 // Nenhum produto encontrado
//                 echo json_encode(array('message' => 'Nenhum produto encontrado.'));
//             }
//         } else {
//             // Requisição para obter todos os produtos
//             $produto = new Produto($conn);
//             $stmt = $produto->readAll();
//             $num = $stmt->rowCount();

//             if ($num > 0) {
//                 // Produtos encontrados
//                 $produtos_arr = array();
//                 $produtos_arr['produtos'] = array();
//                 while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                     array_push($produtos_arr['produtos'], $row);
//                 }

//                 echo json_encode($produtos_arr);
//             } else {
//                 // Nenhum produto encontrado
//                 echo json_encode(array('message' => 'Nenhum produto encontrado.'));
//             }
//         }
//     } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
//         // Requisição para criar um novo produto
//         $data = json_decode(file_get_contents("php://input"));

//         $nome = $data->nome;
//         $descricao = $data->descricao;
//         $fabricante = $data->fabricante;
//         $preco = $data->preco;
//         $data_fabricacao = $data->data_fabricacao;
//         $fk_prateleiras = $data->fk_prateleiras;

//         $produto = new Produto($conn);
//         if ($produto->create($nome, $descricao, $fabricante, $preco, $data_fabricacao, $fk_prateleiras)) {
//             echo json_encode(array('message' => 'Produto criado com sucesso.'));
//         } else {
//             echo json_encode(array('message' => 'Não foi possível criar o produto.'));
//         }
//     } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
//         // Requisição para atualizar um produto existente
//         $data = json_decode(file_get_contents("php://input"));

//         $id = $data->id;
//         $nome = isset($data->nome) ? $data->nome : '';
//         $descricao = isset($data->descricao) ? $data->descricao : '';
//         $fabricante = isset($data->fabricante) ? $data->fabricante : '';
//         $preco = isset($data->preco) ? $data->preco : '';
//         $data_fabricacao = isset($data->data_fabricacao) ? $data->data_fabricacao : '';
//         $fk_prateleiras = isset($data->fk_prateleiras) ? $data->fk_prateleiras : '';

//         $produto = new Produto($conn);
//         if ($produto->update($id, $nome, $descricao, $fabricante, $preco, $data_fabricacao, $fk_prateleiras)) {
//             echo json_encode(array('message' => 'Produto atualizado com sucesso.'));
//         } else {
//             echo json_encode(array('message' => 'Não foi possível atualizar o produto.'));
//         }
//     } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
//         // Requisição para excluir um produto
//         $data = json_decode(file_get_contents("php://input"));

//         $id = $data->id;

//         $produto = new Produto($conn);
//         if ($produto->delete($id)) {
//             echo json_encode(array('message' => 'Produto excluído com sucesso.'));
//         } else {
//             echo json_encode(array('message' => 'Não foi possível excluir o produto.'));
//         }
//     }
// }

// // Chamar a função handleRequest passando a conexão
// handleRequest($conn);
?>
