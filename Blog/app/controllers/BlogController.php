<?php
require_once __DIR__ . '/../core/Controller.php';

class BlogController extends Controller {
    private $postModel;
    private $userModel;
    public function __construct() {
        $this->postModel = $this->model('Post');
        $this->userModel = $this->model('User');
        if (isset($_SESSION['user'])) {
            $this->renovarToken();
        }
    }

    private function renovarToken() {
        $result = $this->userModel->renovarToken($_SESSION['user']);
        if ($result) {
            $_SESSION['token'] = $result['token'];
        }
    }

    public function index(){
        
        if(!isset($_POST['pesquisa']) && !isset($_POST['recente'])){
            $posts = $this->postModel->getAllPosts("ASC");
        }else if(isset($_POST['recente'])){
            $posts = $this->postModel->getRecentPost();
        }else if(isset($_POST['doRecente'])){
            $posts = $this->postModel->getAllPosts("DESC");
        }else if(isset($_POST['doAntigo'])){
            $posts = $this->postModel->getAllPosts("ASC");
        }
        else {
            $posts = $this->postModel->getPost($_POST['pesquisa']);
        }
        $view = '../app/views/index.php';
        require '../app/views/layout.php';
    }
    public function verPost()
    {
        require_once '../app/helpers/tempo.php';
        require_once '../app/helpers/refresh_pagina.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['comment'], $_POST['id'])) {
            $post = $this->postModel->getPostById($_POST['id']);
            $view = '../app/views/verPost.php';
            require '../app/views/layout.php';
        }else { 
            if (isset($_POST['id'])) {
                $post = $this->postModel->getPostById($_POST['id']);
                $view = '../app/views/verPost.php';
                require '../app/views/layout.php';
            } else {
                header("Location: /Blog/");
                exit;
            }
        } 
    } 
    public function novoPost(){
        if (isset($_SESSION['user'])) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!isset($_POST['titulo']) || !isset($_POST['conteudo'])) {
                    $this->sendJsonResponse(['erro' => 'Dados incompletos'], 400);
                    return;
                }
                $token = $_SESSION['token'];
                $id_user = $this->userModel->verificarToken($token);
                if (!$id_user) {
                    $this->sendJsonResponse(['erro' => 'Token inválido ou expirado'], 400);
                }
                $titulo = $_POST['titulo'];
                $conteudo = $_POST['conteudo'];
                if (empty($titulo) || empty($conteudo)) {
                    $this->sendJsonResponse(['erro' => 'Título e conteúdo não podem estar vazios'], 400);
                    return;
                }
                $resultado = $this->postModel->criarPost($titulo, $conteudo, $token);
                if ($resultado) {
                    $sucesso = 'Post criado com sucesso!';
                } else {
                    $erro = 'Erro ao criar o post';
                }
            } 
            $view = '../app/views/novoPost.php';
            require '../app/views/layout.php';
        } else {
            header("Location: /Blog/");
            exit;
        }
    }

    public function login(){
        if (isset($_SESSION['user']) && isset($_SESSION['token'])) {
            header("Location: /Blog/");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['user'] ?? '';
            $password = $_POST['pass'] ?? '';
            $result = $this->userModel->getToken($username, $password);

            if (isset($result['token'])) {
                $_SESSION['user'] = $username;
                $_SESSION['token'] = $result['token'];
                $_SESSION['nivel'] = $result['nivel'];  
                var_dump($_SESSION['user']);
                header("Location: /Blog/");
                exit;
            } else {
                $error = $result['error'];
                if($result['error'] == "Já existe um token ativo para este usuário") {
                    $_SESSION['user'] = $username;
                    $_SESSION['token'] = $result['token'];
                    $_SESSION['nivel'] = $result['nivel'];
                    var_dump($_SESSION['user']);
                    header("Location: /Blog/");
                    exit;
                }else{
                    $error = $result['error'];
                }
                $view = '../app/views/login.php';
                require '../app/views/layout.php';
            }
        } else {
            $view = '../app/views/login.php';
            require '../app/views/layout.php';
        }
    }
    public function logout(){
        if(isset($_SESSION['user'])){
            $username = $_SESSION['user'];
            $result = $this->userModel->deleteToken($username);
            if (isset($result['token'])) {
                unset($_SESSION['user']);
                unset($_SESSION['token']);
                unset($_SESSION['nivel']);
                header("Location: /Blog/");
                exit;
            } else {
                $error = "Erro ao fazer logout. Tente novamente.";
                $view = '../app/views/login.php';
                require '../app/views/layout.php';
            }
        } 
    }
    public function dados(){
        if (isset($_SESSION['user'])) {
            $username = $_SESSION['user'];
            $token = $_SESSION['token'];
            $result = $this->userModel->getUser($username); 
            $view = '../app/views/dados.php';
            require '../app/views/layout.php';
        } else {
            header("Location: /Blog/");
            exit;
        }
    }
    public function registar() {
        if (!isset($_SESSION['user'])) {
            $sucesso = false;
            if (isset($_POST['registar'])) {
                $user = $_POST['username'];
                $nome = $_POST['nome'];
                $email = $_POST['email'];
                $pass = $_POST['pass'];
                $result = $this->userModel->novoUser($user, $nome, $email, $pass);
                if (isset($result['mensagem'])) {
                    $sucesso = true;
                } elseif (isset($result['erro'])) {
                    $erro = $result['erro'];
                } else {
                    $erro = 'Erro desconhecido ao atualizar.';
                }
            }
            $view = '../app/views/registar.php';
            require '../app/views/layout.php';
        } else {
            header("Location: /Blog/");
            exit;
        }
    }

    public function atualizarDados() {
        if (isset($_SESSION['user'])) {
            $sucesso = false;
            $token = $_SESSION['token'];
            $id_user = $this->userModel->verificarToken($token);
            if (!$id_user) {
                return ['erro' => 'Token inválido ou expirado'];
            }
            if (isset($_POST['atualizarDados'])) {
                if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                    $imagem_binaria = file_get_contents($_FILES['imagem']['tmp_name']);
                } else {
                    $imagem_binaria = null;
                }
                $data = [
                    'username' => $_POST['username'],
                    'nome' => $_POST['nome'],
                    'email' => $_POST['email'],
                    'pass' => $_POST['pass'],
                    'nivel' => $_POST['nivel'], 
                    'imagem' => $imagem_binaria, 
                    'token' => $_POST['token']
                ];
                $result = $this->userModel->updateUser($data);
                if (isset($result['mensagem'])) {
                    $sucesso = true;
                } elseif (isset($result['erro'])) {
                    $erro = $result['erro'];
                } else {
                    $erro = 'Erro desconhecido ao atualizar.';
                }
            }
            $result['username'] = $_POST['username'];
            $result['email'] = $_POST['email'];
            $result['nome'] = $_POST['nome'];
            $result['pass'] = $_POST['pass'];
            $result['nivel'] = $_POST['nivel'];
            $result['imagem'] = $imagem_binaria;    
            $result['token'] = $_POST['token'];
            $view = '../app/views/dados.php';
            require '../app/views/layout.php';
        } else {
            header("Location: /Blog/");
            exit;
        }
    }
} 
?>