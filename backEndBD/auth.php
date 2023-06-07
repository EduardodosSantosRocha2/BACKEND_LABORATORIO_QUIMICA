<?php
session_start();

require_once 'conexao.php';

class Auth {
    private $conn;
    private $table_name = 'Usuario';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($login, $senha) {
        $senha = md5($senha);
        $query = "SELECT * FROM " . $this->table_name . " WHERE cpf = :login AND senha = :senha";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":login", $login);
        $stmt->bindParam(":senha", $senha);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['usuario'] = $row;
            return true;
        } else {
            return false;
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['usuario']);
    }

    public function getLoggedInUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['usuario'];
        } else {
            return null;
        }
    }
}
// Arquivo para login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->login)) {
        $login = $data->login;
        $senha = $data->senha;

        $auth = new Auth($conn);
        if ($auth->login($login, $senha)) {
            echo json_encode(array('message' => 'Login realizado com sucesso.', 'status' => true));
        } else {
            echo json_encode(array('message' => 'Credenciais inválidas.', 'status' => false));
        }
    } else if (isset($data->logout)) {
        $auth = new Auth($conn);
        $auth->logout();
        echo json_encode(array('message' => 'Logout realizado com sucesso.', 'status' => true));
    }
}

// Arquivo para verificação de login
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new Auth($conn);

    if ($auth->isLoggedIn()) {
        $usuario = $auth->getLoggedInUser();
        echo json_encode(array('message' => 'Usuário logado.', 'usuario' => $usuario, 'status' => true));
    } else {
        echo json_encode(array('message' => 'Usuário não está logado.', 'status' => false));
    }
}

?>
