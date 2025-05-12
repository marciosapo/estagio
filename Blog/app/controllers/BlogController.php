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
        $this->renderView('../app/views/index.php', ['posts' => $posts]);
    }
    public function verPost()
    {
        require_once '../app/helpers/tempo.php';
        require_once '../app/helpers/refresh_pagina.php';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'], $_POST['comment'], $_POST['id'])) {
            $post = $this->postModel->getPostById($_POST['id']);
            $this->renderView('../app/views/verPost.php', ['post' => $post]);
        }else { 
            if (isset($_POST['id'])) {
                $post = $this->postModel->getPostById($_POST['id']);
                $this->renderView('../app/views/verPost.php', ['post' => $post]);
            } else {
                header("Location: /Blog/");
                exit;
            }
        } 
    } 
    public function novoPost(){
        if (!isset($_SESSION['user'])) {
            header("Location: /Blog/");
            exit;
        }
        $sucesso = null;
        $erro = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['titulo']) || !isset($_POST['conteudo'])) {
                return ['erro' => 'Dados incompletos'];
            }
            $token = $_SESSION['token'];
            $id_user = $this->userModel->verificarToken($token);
            if (!$id_user) {
                return ['erro' => 'Token inválido ou expirado'];
            }
            $titulo = $_POST['titulo'];
            $conteudo = $_POST['conteudo'];
            if (empty($titulo) || empty($conteudo)) {
                return ['erro' => 'Título e conteúdo não podem estar vazios'];
            }
            $result = $this->postModel->criarPost($titulo, $conteudo, $token);
            if (isset($result['mensagem'])) {
                $_SESSION['flash_sucesso'] = 'Post criado com sucesso!';
            } elseif (isset($result['erro'])) {
                $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
            }
        } 
        $this->renderView('../app/views/novoPost.php', compact('sucesso', 'erro'));
    } 

    public function login() {
        if (isset($_SESSION['user']) && isset($_SESSION['token'])) {
            header("Location: /Blog/");
            exit;
        }
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['user'] ?? '';
            $password = $_POST['pass'] ?? '';
            $result = $this->userModel->getToken($username, $password);
            if (isset($result['token'])) {
                $_SESSION['user'] = $username;
                $_SESSION['token'] = $result['token'];
                $_SESSION['nivel'] = $result['nivel'];
                header("Location: /Blog/");
                exit;
            } elseif ($result['error'] === "Já existe um token ativo para este usuário") {
                $_SESSION['user'] = $username;
                $_SESSION['token'] = $result['token'];
                $_SESSION['nivel'] = $result['nivel'];
                header("Location: /Blog/");
                exit;
            } else {
                $error = $result['error'];
            }
        }
        $this->renderView('../app/views/login.php', compact('error'));
    }
    public function logout() {
        if (isset($_SESSION['user'])) {
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
                $this->renderView('../app/views/login.php', compact('error'));
            }
        } else {
            header("Location: /Blog/");
            exit;
        }
    }
    public function dados(){
        if (isset($_SESSION['user'])) {
            $username = $_SESSION['user'];
            $token = $_SESSION['token'];
            $result = $this->userModel->getUser($username); 
            $this->renderView('../app/views/dados.php', ['result' => $result]);
        } else {
            header("Location: /Blog/");
            exit;
        }
    }
    public function registar() {
        if (!isset($_SESSION['user'])) {
            $sucesso = false;
            $erro = null;
            if (isset($_POST['registar'])) {
                $user = $_POST['username'];
                $nome = $_POST['nome'];
                $email = $_POST['email'];
                $pass = $_POST['pass'];
                $result = $this->userModel->novoUser($user, $nome, $email, $pass);
                if (isset($result['mensagem'])) {
                    $_SESSION['flash_sucesso'] = 'Novo utilizador registado com sucesso...';
                } elseif (isset($result['erro'])) {
                    $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
                }
            }
            $this->renderView('../app/views/registar.php', compact('sucesso', 'erro'));
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
                    $_SESSION['flash_sucesso'] = 'Dados atualizados com sucesso.';
                } elseif (isset($result['erro'])) {
                    $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
                }
            }
            $formData = $data;
            $this->renderView('../app/views/dados.php', ['result' => $formData]);
        } else {
            header("Location: /Blog/");
            exit;
        }
    }
    
    private function renderView($viewPath, $data = []) {
        extract($data);
        $view = $viewPath;
        require '../app/templates/layout.php';
    }
} 
?>