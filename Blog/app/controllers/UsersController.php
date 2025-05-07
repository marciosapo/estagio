<?php 

require_once __DIR__ . '/../core/Controller.php';

class UsersController extends Controller {

    private $usersModel;

    public function __construct() {
        try {
            $this->usersModel = $this->model('User');
        } catch (Exception $e) {
            die("Erro ao carregar o modelo User: " . $e->getMessage());
        }
    }
    public function registo(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!isset($input['username']) || !isset($input['nome']) || !isset($input['email']) || !isset($input['pass'])) {
                $this->sendJsonResponse(['erro' => 'Dados incompletos'], 400);
                return;
            }
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendJsonResponse(['erro' => 'Email inválido'], 400);
                return;
            }
            $username = $input['username'] ?? '';
            $nome = $input['nome'] ?? '';
            $email = $input['email'] ?? '';
            $pass = $input['pass'] ?? '';
            $novoUser = $this->usersModel->novoUser($username, $nome, $email, $pass);
            if (isset($novoUser['mensagem'])) {
                $this->sendJsonResponse(['mensagem' => $novoUser['mensagem']], 200);
            } elseif (isset($novoUser['erro'])) {
                $this->sendJsonResponse(['erro' => $novoUser['erro']], 400);
            } else {
                $erro = 'Erro desconhecido ao registar.';
            }
        }
    }
    public function index($username = null){
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if($username === null){
                $users = $this->usersModel->getUsers();
                if ($users) {
                    $this->sendJsonResponse($users);
                } else {
                    $this->sendJsonResponse(['erro' => 'Users não encontrados'], 404);
                }
            }
        }elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $username = $input['username'] ?? '';
            if (empty($username)) {
                return $this->sendJsonResponse(['erro' => 'Usuário ausentes.'], 400);
            }
            $user = $this->usersModel->getUser($username);
            if ($user) {
                $this->sendJsonResponse($user);
            } else {
                $this->sendJsonResponse(['erro' => 'User não encontrado - ' . $user], 404);
            }
        }elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!isset($input['username']) || !isset($input['nome']) || !isset($input['email']) || !isset($input['token'])) {
                $this->sendJsonResponse(['erro' => 'Dados incompletos'], 400);
                return;
            }
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendJsonResponse(['erro' => 'Email inválido'], 400);
                return;
            }
            if(!isset($input['token'])){
                $this->sendJsonResponse(['erro' => 'Falta o Token'], 400);
            } 
            $result = $this->usersModel->updateUser($input);
            if ($result) {
                $this->sendJsonResponse(['mensagem' => 'Utilizador atualizado com sucesso']);
            } else {
                $this->sendJsonResponse(['erro' => 'Erro ao atualizar utilizador'], 500);
            }
        } else {
            $this->sendJsonResponse(['erro' => 'Método não permitido'], 405);
        } 
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $user = $input['user'] ?? '';
            $pass = $input['pass'] ?? '';
    
            if (empty($user) || empty($pass)) {
                return $this->sendJsonResponse(['erro' => 'Usuário ou senha ausentes.'], 400);
            }
    
            $users = $this->usersModel->getToken($user, $pass);
            if ($users) {
                $this->sendJsonResponse($users);
            } else {
                $this->sendJsonResponse(['erro' => 'Credenciais inválidas'], 401);
            }
        }
    }
    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $input = json_decode(file_get_contents('php://input'), true);
            $user = $input['user'] ?? '';
    
            if (empty($user)) {
                return $this->sendJsonResponse(['erro' => 'Usuário ausente.'], 400);
            }
    
            $users = $this->usersModel->deleteToken($user);
            if ($users) {
                $this->sendJsonResponse($users);
            }
        }
    } 
}

?>