<?php
require_once 'conexao.php';
require_once 'usuario.php';

// Classe Professor
class Professor {
    private $conn;
    private $table_name = 'Professor';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT p.*, u.Nome, u.Email, u.Telefone FROM " . $this->table_name . " p
                  INNER JOIN Usuario u ON p.fk_Usuario_CPF = u.CPF";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByCPF($cpf) {
        $query = "SELECT p.*, u.Nome, u.Email, u.Telefone FROM " . $this->table_name . " p
                  INNER JOIN Usuario u ON p.fk_Usuario_CPF = u.CPF
                  WHERE p.fk_Usuario_CPF = :cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->execute();
        return $stmt;
    }

    public function create($cpf, $nome, $email, $senha, $telefone, $cursoMinistrados, $experienciaEnsino, $areaEspecializacao, $numAulas) {
        $query = "INSERT INTO " . $this->table_name . " (fk_Usuario_CPF, Curso_ministrados, Experiencia_de_ensino, Area_de_especializacao, Num_aulas) 
                VALUES (:cpf, :cursoMinistrados, :experienciaEnsino, :areaEspecializacao, :numAulas)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->bindParam(":cursoMinistrados", $cursoMinistrados);
        $stmt->bindParam(":experienciaEnsino", $experienciaEnsino);
        $stmt->bindParam(":areaEspecializacao", $areaEspecializacao);
        $stmt->bindParam(":numAulas", $numAulas);

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

    public function update($cpf, $nome, $email, $telefone, $cursoMinistrados, $experienciaEnsino, $areaEspecializacao, $numAulas) {
        $query = "UPDATE " . $this->table_name . " SET Curso_ministrados = :cursoMinistrados, Experiencia_de_ensino = :experienciaEnsino, Area_de_especializacao = :areaEspecializacao, Num_aulas = :numAulas 
                WHERE fk_Usuario_CPF = :cpf";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->bindParam(":cursoMinistrados", $cursoMinistrados);
        $stmt->bindParam(":experienciaEnsino", $experienciaEnsino);
        $stmt->bindParam(":areaEspecializacao", $areaEspecializacao);
        $stmt->bindParam(":numAulas", $numAulas);

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

                $professor = new Professor($conn);
                $stmt_professor = $professor->readByCPF($cpf);
                $num_professor = $stmt_professor->rowCount();

                if ($num_professor > 0) {
                    // Professor encontrado
                    $professor_item = $stmt_professor->fetch(PDO::FETCH_ASSOC);

                    echo json_encode($professor_item);
                } else {
                    // Nenhum professor encontrado
                    echo json_encode(array('message' => 'Nenhum professor encontrado.','status' => true));
                }
            } else {
                // Nenhum usuário encontrado
                echo json_encode(array('message' => 'Nenhum usuário encontrado.','status' => true));
            }
        } else {
           // Requisição para obter todos os professores
           $professor = new Professor($conn);
           $stmt = $professor->readAll();
           $num = $stmt->rowCount();

           if ($num > 0) {
               // Professores encontrados
               $professores_arr = array();
               $professores_arr['professores'] = array();
               while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                   $professor_item = array($row);
                   array_push($professores_arr['professores'], $professor_item);
               }

               echo json_encode($professores_arr);
           } else {
               // Nenhum professor encontrado
               echo json_encode(array('message' => 'Nenhum professor encontrado.'));
           }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Requisição para criar um novo usuário
        $data = json_decode(file_get_contents("php://input"));

        $cpf = $data->cpf;
        $nome = $data->nome;
        $email = $data->email;
        $telefone = $data->telefone;
        $senha = $data->senha;
        

        $professor = new Professor($conn);
        if ($professor->create($cpf, $nome, $email, $senha, $telefone, $data->cursoMinistrados, $data->experienciaEnsino, $data->areaEspecializacao, $data-> numAulas)) {
            echo json_encode(array('message' => 'Usuário e professor criados com sucesso.','status' => true));
        } else {
            echo json_encode(array('message' => 'Não foi possível criar o usuário e professor.','status' => false));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Requisição para atualizar um usuário existente
        $data = json_decode(file_get_contents("php://input"));

        $cpf = $data->cpf;
        $nome = $data->nome;
        $email = $data->email;
        $telefone = $data->telefone;

        $professor = new Professor($conn);
        if ($professor->update($cpf, $nome, $email, $telefone, $data->cursoMinistrados, $data->experienciaEnsino, $data->areaEspecializacao, $data->numAulas, $data->experienciaEnsino)) {
            echo json_encode(array('message' => 'Usuário e professor atualizados com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível atualizar o usuário e professor.'));
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Requisição para deletar um usuário e professor
        $data = json_decode(file_get_contents("php://input"));

        $cpf = $data->cpf;

        $professor = new Professor($conn);
        if ($professor->delete($cpf)) {
            echo json_encode(array('message' => 'Usuário e professor deletados com sucesso.'));
        } else {
            echo json_encode(array('message' => 'Não foi possível deletar o usuário e professor.'));
        }
    } else {
        // Método de requisição inválido
        echo json_encode(array('message' => 'Método de requisição inválido.'));
    }
}

// Lidar com a requisição
handleRequest($conn);
?>
