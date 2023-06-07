<?php
require_once 'conexao.php';
require_once 'produto.php';

// Classe Equipamento
class Equipamento {
    private $conn;
    private $table_name = 'Equipamento';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT e.*, p.* AS Nome_Produto FROM " . $this->table_name . " e
                  LEFT JOIN Produto p ON e.fk_Produto_ID_Prod = p.ID_Prod";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByID($id) {
        $query = "SELECT e.*, p.* AS Nome_Produto FROM " . $this->table_name . " e
                  LEFT JOIN Produto p ON e.fk_Produto_ID_Prod = p.ID_Prod
                  WHERE e.fk_Produto_ID_Prod = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt;
    }

    public function create($nomeProduto, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira, $tipo, $capacidade, $condicoesOperacao, $calibracao) {
        $produto = new Produto($this->conn);
        $idProduto = $produto->create($nomeProduto, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira);
        if ($idProduto) {
            $query = "INSERT INTO " . $this->table_name . " (Tipo, Capacidade, Condicoes_de_operacao, Calibracao, fk_Produto_ID_Prod) 
                    VALUES (:tipo, :capacidade, :condicoesOperacao, :calibracao, :idProduto)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":tipo", $tipo);
            $stmt->bindParam(":capacidade", $capacidade);
            $stmt->bindParam(":condicoesOperacao", $condicoesOperacao);
            $stmt->bindParam(":calibracao", $calibracao);
            $stmt->bindParam(":idProduto", $idProduto);

            if ($stmt->execute()) {
                return true;
            } else {
                // Em caso de falha na inserção do equipamento, excluir o produto correspondente
                echo 'Em caso de falha na inserção do equipamento, excluir o produto correspondente';
                $produto->delete($idProduto);
                return false;
            }
        } else {
            return false;
        }
    }

    public function update($idProduto, $nomeProduto, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira, $tipo, $capacidade, $condicoesOperacao, $calibracao) {
        $produto = new Produto($this->conn);
        $produtoExists = $produto->update($idProduto, $nomeProduto, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira);

        if ($produtoExists) {
            $query = "UPDATE " . $this->table_name . " 
                      SET Tipo = :tipo, Capacidade = :capacidade, Condicoes_de_operacao = :condicoesOperacao, Calibracao = :calibracao
                      WHERE fk_Produto_ID_Prod = :idProduto";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":tipo", $tipo);
            $stmt->bindParam(":capacidade", $capacidade);
            $stmt->bindParam(":condicoesOperacao", $condicoesOperacao);
            $stmt->bindParam(":calibracao", $calibracao);
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
            // Requisição para obter um equipamento por ID
            $id = $_GET['id'];

            $equipamento = new Equipamento($conn);
            $stmt = $equipamento->readByID($id);
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Equipamento encontrado
                $equipamento_item = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($equipamento_item);
            } else {
                // Nenhum equipamento encontrado
                echo json_encode(array('message' => 'Nenhum equipamento encontrado.','status'=> true));
            }
        } else {
            // Requisição para obter todos os equipamentos
            $equipamento = new Equipamento($conn);
            $stmt = $equipamento->readAll();
            $num = $stmt->rowCount();

            if ($num > 0) {
                // Equipamentos encontrados
                $equipamentos_arr = array();
                $equipamentos_arr['equipamentos'] = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($equipamentos_arr['equipamentos'], $row);
                }

                echo json_encode($equipamentos_arr);
            } else {
                // Nenhum equipamento encontrado
                echo json_encode(array('message' => 'Nenhum equipamento encontrado.','status'=> true));
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar um novo equipamento
        $data = json_decode(file_get_contents("php://input"));

        $nome = $data->nome;
        $descricao = $data->descricao;
        $fabricante = $data->fabricante;
        $preco = $data->preco;
        $dataFabricacao = $data->dataFabricacao;
        $fkPrateleirasNumeroPrateleira = $data->fkPrateleirasNumeroPrateleira;
        $tipoEquipamento = $data->tipoEquipamento;
        $capacidade = $data->capacidade;
        $condicoesOperacao = $data->condicoesOperacao;
        $calibracao = $data->calibracao;

        $equipamento = new Equipamento($conn);
        if ($equipamento->create($nome, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira, $tipoEquipamento, $capacidade, $condicoesOperacao, $calibracao)) {
            echo json_encode(array('message' => 'Equipamento criado com sucesso.','status'=> true));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar o equipamento.','status'=> false));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Requisição para atualizar um equipamento existente
        $data = json_decode(file_get_contents("php://input"));

        $id = $data->id;
        $nome = $data->nome;
        $descricao = $data->descricao;
        $fabricante = $data->fabricante;
        $preco = $data->preco;
        $dataFabricacao = $data->dataFabricacao;
        $fkPrateleirasNumeroPrateleira = $data->fkPrateleirasNumeroPrateleira;
        $tipo = $data->tipo;
        $capacidade = $data->capacidade;
        $condicoesOperacao = $data->condicoesOperacao;
        $calibracao = $data->calibracao;

        $equipamento = new Equipamento($conn);
        if ($equipamento->update($id, $nome, $descricao, $fabricante, $preco, $dataFabricacao, $fkPrateleirasNumeroPrateleira, $tipo, $capacidade, $condicoesOperacao, $calibracao)) {
            echo json_encode(array('message' => 'Equipamento atualizado com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível atualizar o equipamento.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir um equipamento
        $data = json_decode(file_get_contents("php://input"));

        $id = $data->id;

        $equipamento = new Equipamento($conn);
        if ($equipamento->delete($id)) {
            $produto = new Produto($conn);
            if ($produto->delete($id)) {
                echo json_encode(array('message' => 'Equipamento excluído com sucesso.'));
            } else {
                echo json_encode(array('message' => 'Não foi possível excluir o produto.'));
            }
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir o equipamento.'));
        }
    }
}
// Chamar a função handleRequest passando a conexão
handleRequest($conn);
?>
