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
    public function index($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            
            if($id === null){ 
                $posts = $this->postModel->getAllPosts();
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
            $input = json_decode(file_get_contents('php://input'), true);
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
    }
    public function comentario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            
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
    }
} 

?>