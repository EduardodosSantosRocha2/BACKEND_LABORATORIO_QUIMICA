<?php
require_once 'conexao.php';

// Classe PreparaPrateleirasExperimentoTecnico
class PreparaPrateleirasExperimentoTecnico {
    private $conn;
    private $table_name = 'Prepara_Prateleiras_Experimento_Tecnico';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($fk_Prateleiras_Numero_Prateleira, $fk_Experimento_Numero_Experimento, $fk_Tecnico_fk_Usuario_CPF, $Quantidade) {
        // Verificar se a prateleira, experimento e técnico existem
        //if (!$this->isPrateleiraExists($prateleira_numero) || !$this->isExperimentoExists($experimento_numero) || !$this->isTecnicoExists($tecnico_cpf)) {
           // return false;
        //}

        $query = "INSERT INTO " . $this->table_name . " (fk_Prateleiras_Numero_Prateleira, fk_Experimento_Numero_Experimento, fk_Tecnico_fk_Usuario_CPF, Quantidade) 
                VALUES (:fk_Prateleiras_Numero_Prateleira, :fk_Experimento_Numero_Experimento, :fk_Tecnico_fk_Usuario_CPF, :Quantidade)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fk_Prateleiras_Numero_Prateleira", $fk_Prateleiras_Numero_Prateleira);
        $stmt->bindParam(":fk_Experimento_Numero_Experimento", $fk_Experimento_Numero_Experimento);
        $stmt->bindParam(":fk_Tecnico_fk_Usuario_CPF", $fk_Tecnico_fk_Usuario_CPF);
        $stmt->bindParam(":Quantidade", $Quantidade);


        try {
            // Seu código existente ...
        
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $errorMessage = $e->getMessage();
            //echo "Erro do PostgreSQL: " . $errorMessage;
            return $errorMessage;
        }
    
}

    public function delete($prateleira_numero, $experimento_numero, $tecnico_cpf) {
        $query = "DELETE FROM " . $this->table_name . " WHERE fk_Prateleiras_Numero_Prateleira = :prateleira_numero 
                AND fk_Experimento_Numero_Experimento = :experimento_numero 
                AND fk_Tecnico_fk_Usuario_CPF = :tecnico_cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":prateleira_numero", $prateleira_numero);
        $stmt->bindParam(":experimento_numero", $experimento_numero);
        $stmt->bindParam(":tecnico_cpf", $tecnico_cpf);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    private function isPrateleiraExists($prateleira_numero) {
        $query = "SELECT Numero_Prateleira FROM Prateleiras WHERE Numero_Prateleira = :prateleira_numero";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":prateleira_numero", $prateleira_numero);
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

    private function isTecnicoExists($tecnico_cpf) {
        $query = "SELECT fk_Usuario_CPF FROM Tecnico WHERE fk_Usuario_CPF = :tecnico_cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tecnico_cpf", $tecnico_cpf);
        $stmt->execute();
        $num = $stmt->rowCount();

        return $num > 0;
    }
}

// Função para lidar com as requisições
function handleRequest($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar um novo registro de preparação de prateleiras de experimento para técnico
        $data = json_decode(file_get_contents("php://input"));

        $prateleira_numero = $data->prateleira_numero;
        $experimento_numero = $data->experimento_numero;
        $tecnico_cpf = $data->tecnico_cpf;
        $quantidade = $data->quantidade;

        $preparaPrateleiras = new PreparaPrateleirasExperimentoTecnico($conn);
        $resu = $preparaPrateleiras->create($prateleira_numero, $experimento_numero, $tecnico_cpf, $quantidade);
        if ($resu) {
            echo json_encode(array('message' => 'Registro de preparação de prateleiras de experimento para técnico criado com sucesso.','status'=>$resu));
        } else{
            echo json_encode(array('message' => 'Não foi possível criar o registro de preparação de prateleiras de experimento para técnico. Verifique se a prateleira, experimento e técnico existem.','status'=>$resu));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para excluir um registro de preparação de prateleiras de experimento para técnico existente
        $data = json_decode(file_get_contents("php://input"));

        $prateleira_numero = $data->prateleira_numero;
        $experimento_numero = $data->experimento_numero;
        $tecnico_cpf = $data->tecnico_cpf;

        $preparaPrateleiras = new PreparaPrateleirasExperimentoTecnico($conn);
        if ($preparaPrateleiras->delete($prateleira_numero, $experimento_numero, $tecnico_cpf)) {
            echo json_encode(array('message' => 'Registro de preparação de prateleiras de experimento para técnico excluído com sucesso.','status'=>true));
        } else {
            echo json_encode(array('message' => 'Não foi possível excluir o registro de preparação de prateleiras de experimento para técnico.','status'=>false));
        }
    }
}

// Lidar com a requisição
handleRequest($conn);
?>
