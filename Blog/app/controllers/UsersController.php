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
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if (!isset($input['username']) || !isset($input['nome']) || !isset($input['email']) || !isset($input['pass'])) {
                $this->sendJsonResponse(['erro' => 'Dados incompletos'], 400);
                return;
            }
            if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                $this->sendJsonResponse(['erro' => 'Email inválido'], 400);
                return;
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
                $this->sendJsonResponse(['erro' => 'O nome de utilizador apenas pode conter letras, números e underscore (_)'], 400);
                return;
            }
            if (strlen($input['pass']) < 8) {
                $this->sendJsonResponse(['erro' => 'Password must be at least 5 characters long.'], 400);
                return;
            }
            if (!preg_match('/[A-Z]/', $pass)) {
                $this->sendJsonResponse(['erro' => 'A senha deve conter pelo menos uma letra maiúscula.'], 400);
                return;
            }

            if (!preg_match('/[0-9]/', $pass)) {
                $this->sendJsonResponse(['erro' => 'A senha deve conter pelo menos um número.'], 400);
                return;
            }

            if (!preg_match('/[\W_]/', $pass)) {
                $this->sendJsonResponse(['erro' => 'A senha deve conter pelo menos um símbolo.'], 400);
                return;
            }
            $username = preg_replace('/[^a-zA-Z0-9_]/', '', $input['username']);
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

    public function index(){
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $users = $this->usersModel->getUsers();
            if ($users) {
                $this->sendJsonResponse($users);
            } else {
                $this->sendJsonResponse(['erro' => 'Users não encontrados'], 404);
            }
        }elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            file_put_contents('/tmp/debug.txt', print_r($input, true), FILE_APPEND);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON inválido: " . json_last_error_msg());
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            $username = $input['username'] ?? '';
            if (empty($username)) {
                error_log("Usuário vazio");
                return $this->sendJsonResponse(['erro' => 'Usuário vazio.'], 400);
            }
            $user = $this->usersModel->getUserApi($username);
            if (is_array($user)) {
                error_log("User encontrado: " . json_encode($user));
                $this->sendJsonResponse($user);
            }else {
                error_log("User não encontrado");
                $this->sendJsonResponse(['erro' => 'User não encontrado'], 404);
            }
        }elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if (!isset($input['username'])) {
                $this->sendJsonResponse(['erro' => 'Falta o username'], 400);
                return;
            }
            if (!isset($input['token'])) {
                $this->sendJsonResponse(['erro' => 'Falta o token'], 400);
                return;
            }
            $username = $input['username'] ?? '';
            $token = $input['token'] ?? '';
            $apagarUser = $this->usersModel->apagarUser($username, $token);
            if (isset($apagarUser['mensagem'])) {
                $this->sendJsonResponse(['mensagem' => $apagarUser['mensagem']], 200);
            } elseif (isset($apagarUser['erro'])) {
                $this->sendJsonResponse(['erro' => $apagarUser['erro']], 400);
            } else {
                $erro = 'Erro desconhecido ao apagar registo.';
            }
        }elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
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

    public function addAdmin(){
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if (!isset($input['username']) || !isset($input['token'])) {
                $this->sendJsonResponse(['erro' => 'Falta o user ou token.'], 400);
                return;
            }
            $user = $input['username'];
            $token = $input['token'];
            $result = $this->usersModel->addAdmin($user, $token);
            $this->sendJsonResponse($result);
        } 
    } 
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            $user = $input['user'] ?? '';
            $pass = $input['pass'] ?? '';
    
            if (empty($user) || empty($pass)) {
                return $this->sendJsonResponse(['erro' => 'Usuário ou senha ausentes.'], 400);
            }
    
            $users = $this->usersModel->getToken($user, $pass);
            if (isset($users['mensagem'])) {
                $this->sendJsonResponse(['mensagem' => $users['mensagem']]);
            } else {
                $this->sendJsonResponse(['erro' => $users['erro']], 401);
            }
        }
    }
    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            $user = $input['user'] ?? '';
            if (empty($user)) {
                return $this->sendJsonResponse(['erro' => 'Usuário ausente.'], 400);
            }
            $users = $this->usersModel->deleteToken($user);
            if ($users) {
                $this->sendJsonResponse(['mensagem' => 'Logout efetuado com sucesso!']);
            }else {
                $this->sendJsonResponse(['erro' => 'Falha a efetuar o logout'], 401);
            }
        }
    }
}

?>