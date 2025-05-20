<?php 

require_once __DIR__ . '/../core/Controller.php';

class PostsController extends Controller {

    private $postModel;

    public function __construct() {
        try {
            $this->postModel = $this->model('Post');
        } catch (Exception $e) {
            die("Erro ao carregar o modelo Post: " . $e->getMessage());
        }
    }
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
             $id = $_GET['id'] ?? null;
            if($id === null){ 
                $posts = $this->postModel->getAllPosts("ASC", 0, 0);
                $this->sendJsonResponse($posts);
            }else{
                $post = $this->postModel->getPostById($id);
                if ($post) {
                    $this->sendJsonResponse($post);
                } else {
                    $this->sendJsonResponse(['erro' => 'Post não encontrado'], 404);
                }
            } 
        }  
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) { 
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if(!isset($input['token'])){
                $this->sendJsonResponse(['erro' => 'Falta o Token'], 400);
            } 
            if (!isset($input['title'], $input['post'])) {
                $this->sendJsonResponse(['erro' => 'Falta campos'], 400);
            }
            $result = $this->postModel->criarPost($input['title'], $input['post'], $input['token']);
            if (isset($result['erro'])) {
                $this->sendJsonResponse(['erro' => $result['erro']], 400);
            } else {
                $this->sendJsonResponse(['mensagem' => 'Post criado com sucesso', 'id' => $result], 201);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if(!isset($input['token'])){
                $this->sendJsonResponse(['erro' => 'Falta o Token'], 400);
            } 
            if (!isset($input['title'], $input['post'])) {
                $this->sendJsonResponse(['erro' => 'Falta campos'], 400);
            }
            if (!isset($input['id'])){
                $this->sendJsonResponse(['erro' => 'Falta id do Post'], 400);
            }
            $result = $this->postModel->editarPost($input['id'], $input['title'], $input['post'], $input['token']);
            if (isset($result['erro'])) {
                $this->sendJsonResponse(['erro' => $result['erro']], 400);
            } else {
                $this->sendJsonResponse(['mensagem' => 'Post editado com sucesso'] , 201);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if (!isset($input['token'])) {
                $this->sendJsonResponse(['erro' => 'Falta o token'], 400);
            } 
            if (!isset($input['id_post'])) {
                $this->sendJsonResponse(['erro' => 'Falta o ID do post'], 400);
            }
            $token = $input['token'];
            $id_post = (int) $input['id_post'];
            $this->postModel->apagarPost($id_post, $token);
        }
    }
    public function comentario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if (!isset($input['title'], $input['post'], $input['id_post'], $input['token'])) {
                $this->sendJsonResponse(['erro' => 'Falta campos obrigatórios'], 400);
                return;
            }
            $id_parent = isset($input['id_parent']) ? $input['id_parent'] : NULL; 
            $result = $this->postModel->criarComentario($input['title'], $input['post'], $input['id_post'], $input['token'], $id_parent);
            if ($result === 'POST_NOT_FOUND') {
                $this->sendJsonResponse(['erro' => 'O post com o id ' . $input['id_post'] . ' não existe.'], 404);
            } elseif (isset($result['erro'])) {
                $this->sendJsonResponse(['erro' => $result['erro']], 400);
            } else {
                $this->sendJsonResponse(['mensagem' => 'Comentário criado com sucesso', 'id' => $result], 201);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if (!isset($input['token'])) {
                $this->sendJsonResponse(['erro' => 'Falta o token'], 400);
            }
            if (!isset($input['idcomentario'])) {
                $this->sendJsonResponse(['erro' => 'Falta o id do comentário'], 400);
            } 
            $id_parent = isset($input['id_parent']) ? $input['id_parent'] : NULL; 
            $result = $this->postModel->apagarComentario($input['idcomentario'], $input['token'], $id_parent);

            if ($result === 'POST_NOT_FOUND') {
                $this->sendJsonResponse(['erro' => 'O comentário com o id ' . $input['idcomentario'] . ' não existe.'], 404);
            } elseif (isset($result['erro'])) {
                $this->sendJsonResponse(['erro' => $result['erro']], 400);
            } else {
                $this->sendJsonResponse(['mensagem' => 'Comentário/Resposta apagado/a com sucesso', 'id' => $result], 201);
            }
        }
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['erro' => 'JSON inválido'], 400);
                return;
            }
            if (!isset($input['token'])) {
                $this->sendJsonResponse(['erro' => 'Falta o token'], 400);
            }
            if (!isset($input['idcomentario'])) {
                $this->sendJsonResponse(['erro' => 'Falta o id do comentário'], 400);
            }
            if (!isset($input['comentario'])) {
                $this->sendJsonResponse(['erro' => 'Falta o conteudo do comentário'], 400);
            } 
            $result = $this->postModel->atualizarComentario($input['idcomentario'], $input['comentario'], $input['token']);

            if ($result === 'POST_NOT_FOUND') {
                $this->sendJsonResponse(['erro' => 'O comentário com o id ' . $input['idcomentario'] . ' não existe.'], 404);
            } elseif (isset($result['erro'])) {
                $this->sendJsonResponse(['erro' => $result['erro']], 400);
            } else {
                $this->sendJsonResponse(['mensagem' => 'Comentário/Resposta atualizado/a com sucesso', 'id' => $result], 201);
            }
        }
    }
} 

?>