<?php 

require_once __DIR__ . '/../core/Controller.php';

class DbController extends Controller {

    private $usersModel;

    public function __construct() {
        try {
            $this->usersModel = $this->model('User');
        } catch (Exception $e) {
            die("Erro ao carregar o modelo User: " . $e->getMessage());
        }
    }
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $token = $input['token'] ?? '';
            if (empty($token)) {
                return $this->sendJsonResponse(['erro' => 'Token ausente.'], 400);
            }
            $resposta = $this->usersModel->recriarDB(['token' => $token]);
            $this->sendJsonResponse($resposta);

        }
    }  
} 
?>