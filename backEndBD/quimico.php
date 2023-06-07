<?php
require_once 'conexao.php';
require_once 'produto.php';

// Classe Quimico
class Quimico {
    private $conn;
    private $table_name = 'Quimico';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT q.*, p.* AS Nome_Produto FROM " . $this->table_name . " q
                  LEFT JOIN Produto p ON q.fk_Produto_ID_Prod = p.ID_Prod";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByID($id) {
        $query = "SELECT q.*, p.* AS Nome_Produto FROM " . $this->table_name . " q
                  LEFT JOIN Produto p ON q.fk_Produto_ID_Prod = p.ID_Prod
                  WHERE q.fk_Produto_ID_Prod = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function create($nomeProduto, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira, $formulaQuimica, $concentracao, $classeRisco, $volume, $dataValidade, $quantidadeQuimico) {
        $produto = new Produto($this->conn);
        $idProduto = $produto->create($nomeProduto, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira);
        if ($idProduto) {
            $query = "INSERT INTO " . $this->table_name . " (Formula_quimica, Concentracao, Classe_de_risco, Volume, Data_de_validade, fk_Produto_ID_Prod, Quantidade_Quimico) 
                    VALUES (:formulaQuimica, :concentracao, :classeRisco, :volume, :dataValidade, :idProduto, :quantidadeQuimico)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":formulaQuimica", $formulaQuimica);
            $stmt->bindParam(":concentracao", $concentracao);
            $stmt->bindParam(":classeRisco", $classeRisco);
            $stmt->bindParam(":volume", $volume);
            $stmt->bindParam(":dataValidade", $dataValidade);
            $stmt->bindParam(":idProduto", $idProduto);
            $stmt->bindParam(":quantidadeQuimico", $quantidadeQuimico);

            if ($stmt->execute()) {
                return true;
            } else {
                // Em caso de falha na inserção do químico, excluir o produto correspondente
                $produto->delete($idProduto);
                return false;
            }
        } else {
            return false;
        }
    }

    public function update($idProduto, $nomeProduto, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira, $formulaQuimica, $concentracao, $classeRisco, $volume, $dataValidade, $quantidadeQuimico) {
        $produto = new Produto($this->conn);
        $produtoExists = $produto->update($idProduto, $nomeProduto, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira);

        if ($produtoExists) {
            $query = "UPDATE " . $this->table_name . " 
                      SET Formula_quimica = :formulaQuimica, Concentracao = :concentracao, Classe_de_risco = :classeRisco, Volume  = :volume, 
                      Data_de_validade = :dataValidade, Quantidade_Quimico = :quantidadeQuimico 
                      WHERE fk_Produto_ID_Prod = :idProduto";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":formulaQuimica", $formulaQuimica);
            $stmt->bindParam(":concentracao", $concentracao);
            $stmt->bindParam(":classeRisco", $classeRisco);
            $stmt->bindParam(":volume", $volume);
            $stmt->bindParam(":dataValidade", $dataValidade);
            $stmt->bindParam(":quantidadeQuimico", $quantidadeQuimico);
            $stmt->bindParam(":idProduto", $idProduto);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function delete($idProduto) {
        $produto = new Produto($this->conn);
        return $produto->delete($idProduto);
    }
}

// Função para lidar com as requisições
function handleRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['id'])) {
            // Requisição para obter um químico por ID
            $id = $_GET['id'];

            $quimico = new Quimico($conn);
            $stmt = $quimico->readByID($id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Químico encontrado
                $quimico_item = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($quimico_item);
            } else {
                // Nenhum químico encontrado
                echo json_encode(array('message' => 'Nenhum químico encontrado.','status'=> true));
            }
        } else {
            // Requisição para obter todos os químicos
            $quimico = new Quimico($conn);
            $stmt = $quimico->readAll();
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Químicos encontrados
                $quimicos_arr = array();
                $quimicos_arr['quimicos'] = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($quimicos_arr['quimicos'], $row);
                }

                echo json_encode($quimicos_arr);
            } else {
                // Nenhum químico encontrado
                echo json_encode(array('message' => 'Nenhum químico encontrado.','status'=> true));
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar um novo químico
        $data = json_decode(file_get_contents("php://input"));

        $nome = $data->nome;
        $descricao = $data->descricao;
        $fabricante = $data->fabricante;
        $preco = $data->preco;
        $dataFabricacao = $data->dataFabricacao;
        $fkPrateleirasNumeroPrateleira = $data->fkPrateleirasNumeroPrateleira;
        $formulaQuimica = $data->formulaQuimica;
        $concentracao = $data->concentracao;
        $classeRisco = $data->classeRisco;
        $volume = $data->volume;
        $dataValidade = $data->dataValidade;
        $quantidadeQuimico = $data->quantidadeQuimico;

        $quimico = new Quimico($conn);
        if ($quimico->create($nome, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira, $formulaQuimica, $concentracao, $classeRisco, $volume, $dataValidade, $quantidadeQuimico)) {
            echo json_encode(array('message' => 'Químico criado com sucesso.', 'status' => true));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar o produto.','status' => false));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Requisição para atualizar um químico existente
        $data = json_decode(file_get_contents("php://input"));

        $id = $data->id;
        $nome = $data->nome;
        $descricao = $data->descricao;
        $fabricante = $data->fabricante;
        $preco = $data->preco;
        $dataFabricacao = $data->dataFabricacao;
        $fkPrateleirasNumeroPrateleira = $data->fkPrateleirasNumeroPrateleira;
        $formulaQuimica = $data->formulaQuimica;
        $concentracao = $data->concentracao;
        $classeRisco = $data->classeRisco;
        $volume = $data->volume;
        $dataValidade = $data->dataValidade;
        $quantidadeQuimico = $data->quantidadeQuimico;

        $quimico = new Quimico($conn);
        if ($quimico->update($id, $nome, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira, $formulaQuimica, $concentracao, $classeRisco, $volume, $dataValidade, $quantidadeQuimico)) {
            echo json_encode(array('message' => 'Químico atualizado com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível atualizar o produto.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir um químico
        $data = json_decode(file_get_contents("php://input"));

        $id = $data->id;

        $quimico = new Quimico($conn);
        if ($quimico->delete($id)) {
            $produto = new Produto($conn);
            if ($produto->delete($id)) {
                echo json_encode(array('message' => 'Químico excluído com sucesso.'));
            } else {
                echo json_encode(array('message' => 'Não foi possível excluir o produto.'));
            }
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir o químico.'));
        }
    }
}
// Chamar a função handleRequest passando a conexão
handleRequest($conn);
?>