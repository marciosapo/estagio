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
        if (isset($result['mensagem'])) {
            if (isset($result['token'])) {
                $_SESSION['token'] = $result['token'];
            }
        } else {
            session_unset();
            session_destroy();
            header("Location: /Blog");
            exit;
        }
    }

    public function index() {
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = 5;
        if(!isset($_SESSION['pesquisa'])){ 
            $_SESSION['pesquisa'] = 'ASC';
        }   

        if (isset($_POST['doRecente'])) {
            $_SESSION['pesquisa'] = 'DESC';
        } elseif (isset($_POST['doAntigo'])) {
            $_SESSION['pesquisa'] = 'ASC';
        }

        if (isset($_POST['recente'])) {
            $resultado = $this->postModel->getRecentPost();
        } elseif (isset($_POST['pesquisa'])) {
            $resultado = $this->postModel->getPost($_POST['pesquisa'], $pagina, $porPagina);
        } else {
            $resultado = $this->postModel->getAllPosts($pagina, $porPagina);
        }

        if (!isset($resultado['posts'])) {
            $resultado = [
                'posts' => [],
                'pagina_atual' => 1,
                'por_pagina' => $porPagina,
                'total_posts' => 0
            ];
        } else {
            $resultado['posts'] = (array) $resultado['posts'];
        }
        $this->renderView('../app/views/index.php', ['resultado' => $resultado]);
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
                header("Location: /Blog");
                exit;
            }
        } 
    } 
    public function novoPost(){
        if (!isset($_SESSION['user'])) {
            header("Location: /Blog");
            exit;
        }
        $sucesso = $_SESSION['flash_sucesso'] ?? null;
        $erro = $_SESSION['flash_erro'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['titulo']) || !isset($_POST['conteudo'])) {
                return ['erro' => 'Dados incompletos'];
            }
            $token = $_SESSION['token'];
            $id_user = $this->userModel->verificarTokenUser($token);
            if (!$id_user) {
                return ['erro' => 'Token inválido ou expirado'];
            }
            $titulo = $_POST['titulo'];
            $conteudo = $_POST['conteudo'];
            if (empty($titulo) || empty($conteudo)) {
                return ['erro' => 'Título e conteúdo não podem estar vazios'];
            }
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $imagem_binaria = file_get_contents($_FILES['imagem']['tmp_name']);
            } else {
                $imagem_binaria = null;
            }
            $result = $this->postModel->criarPost($titulo, $conteudo, $imagem_binaria, $token);
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
            header("Location: /Blog");
            exit;
        }
        $erro = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['user'] ?? '';
            $password = $_POST['pass'] ?? '';
            $result = $this->userModel->getToken($username, $password);
            if (isset($result['token']) && !isset($result['erro'])) {
                $_SESSION['user'] = $username;
                $_SESSION['token'] = $result['token'];
                $_SESSION['nivel'] = $result['nivel'];
                $_SESSION['flash_sucesso'] = 'Sessão iniciada com sucesso.';
                header("Location: /Blog");
                exit;
            }elseif (isset($result['token']) && isset($result['erro'])) {
                $_SESSION['user'] = $username;
                $_SESSION['token'] = $result['token'];
                $_SESSION['nivel'] = $result['nivel'];
                $_SESSION['flash_sucesso'] = 'Sessão retomada com sucesso.';
                header("Location: /Blog");
                exit;
            } elseif (isset($result['erro'])) {
                $_SESSION['flash_erro'] = $result['erro'];
                header("Location: /Blog");
                exit;
            } else {
                $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
            }
        }
        $this->renderView('../app/views/login.php', compact('erro'));
    }
    public function logout() {
        if (isset($_SESSION['user'])) {
            $username = $_SESSION['user'];
            $result = $this->userModel->deleteToken($username);
            if (isset($result['token'])) {
                unset($_SESSION['user']);
                unset($_SESSION['token']);
                unset($_SESSION['nivel']);
                header("Location: /Blog");
                exit;
            } else {
                $erro = "Erro ao fazer logout. Tente novamente.";
                $this->renderView('../app/views/login.php', compact('erro'));
            }
        } else {
            header("Location: /Blog");
            exit;
        }
    }
    public function addAdmin(){
        if(isset($_SESSION['nivel']) && $_SESSION['nivel'] != "Owner"){ 
            header("Location: /Blog");
            exit;
        } 
        if (isset($_SESSION['user'])) {
            $username = $_SESSION['user'];
            $token = $_SESSION['token'];
            $users = $this->userModel->getUsersByLevel('User'); 
            $this->renderView('../app/views/addAdmin.php', ['users' => $users]);
        } else {
            header("Location: /Blog");
            exit;
        }
    }
    public function remAdmin(){
        if(isset($_SESSION['nivel']) && $_SESSION['nivel'] != "Owner"){ 
            header("Location: /Blog");
            exit;
        } 
        if (isset($_SESSION['user'])) {
            $username = $_SESSION['user'];
            $token = $_SESSION['token'];
            $users = $this->userModel->getUsersByLevel('Admin'); 
            $this->renderView('../app/views/remAdmin.php', ['users' => $users]);
        } else {
            header("Location: /Blog");
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
            header("Location: /Blog");
            exit;
        }
    }
    public function verUser() {
        if(isset($_SESSION['user'])){
            if(!isset($_GET['username'])){
                header("Location: /Blog");
                exit();
            }
            if(!isset($_GET['userId'])){
                header("Location: /Blog");
                exit();
            }
            $user = $_GET['username'];
            $userId = $_GET['userId'];
            $result = $this->userModel->getUser($user);
            $resultData = $this->userModel->getUserData($userId); 
            $this->renderView('../app/views/verUser.php', ['result' => $result, 'resultData' => $resultData]);
        }else {
            header("Location: /Blog");
            exit;
        }
    }
    public function registar() {
        if (!isset($_SESSION['user'])) {
            $sucesso = false;
            $erro = null;
            if (isset($_POST['registar'])) {
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $_POST['username'])) {
                    $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
                }
                else{ 
                    $user = $_POST['username'];
                    $nome = $_POST['nome'];
                    $email = $_POST['email'];
                    $pass = $_POST['pass'];
                    $result = $this->userModel->novoUser($user, $nome, $email, $pass);
                    if (isset($result['mensagem'])) {
                        $_SESSION['flash_sucesso'] = 'Novo utilizador registado com sucesso...';
                        $subject = 'Registo no Blog!';
                        $message = "Obrigado por te registares no nosso blog!\nUrl: http://localhost/Blog";
                        $headers = 'From: root@root.com';
                        mail($email, $subject, $message, $headers);
                    } elseif (isset($result['erro'])) {
                        $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
                    }
                }
            } 
            $this->renderView('../app/views/registar.php', compact('sucesso', 'erro'));
        } else {
            header("Location: /Blog");
            exit;
        }
    }

    public function atualizarAdmin(){
        if(!isset($_POST['users'])){
            header("Location: /Blog/addAdmin");
            exit;
        } 
        $token = $_SESSION['token'];
        $user = $_POST['users'];  
        $id_user = $this->userModel->verificarTokenUser($token, $this->userModel->db);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT nivel FROM users WHERE id = :id_user";
        $checkStmt = $this->userModel->db->prepare($checkQuery);
        $checkStmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $checkStmt->execute();
        $nivel = $checkStmt->fetchColumn();
        if ($nivel !== 'Owner') {
            return ['erro' => 'Apenas utilizadores com nível Owner podem realizar esta operação.'];
        }
        $userCheckQuery = "SELECT id FROM users WHERE username = :username";
        $userCheckStmt = $this->userModel->db->prepare($userCheckQuery);
        $userCheckStmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        $userCheckStmt->execute();
        $userExists = $userCheckStmt->fetchColumn();
        if (!$userExists) {
            return ['erro' => 'Usuário não encontrado.'];
        }
        $stmt = $this->userModel->db->prepare("Update users set nivel = 'Admin' WHERE username = ?");
        $stmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        $success = $stmt->execute([$user]);
        if (isset($success)) {
            $_SESSION['flash_sucesso'] = $user . ' alterado para Administrador.';
        } elseif (isset($result['erro'])) {
            $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
        }
        header("Location: /Blog/addAdmin");
        exit;
    }

    public function removerAdmin(){
        if(!isset($_POST['users'])){
            header("Location: /Blog/remAdmin");
            exit;
        } 
        $token = $_SESSION['token'];
        $user = $_POST['users'];  
        $id_user = $this->userModel->verificarTokenUser($token, $this->userModel->db);
        if (!$id_user) {
            return ['erro' => 'Token inválido ou expirado'];
        }
        $checkQuery = "SELECT nivel FROM users WHERE id = :id_user";
        $checkStmt = $this->userModel->db->prepare($checkQuery);
        $checkStmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
        $checkStmt->execute();
        $nivel = $checkStmt->fetchColumn();
        if ($nivel !== 'Owner') {
            return ['erro' => 'Apenas utilizadores com nível Owner podem realizar esta operação.'];
        }
        $userCheckQuery = "SELECT id FROM users WHERE username = :username";
        $userCheckStmt = $this->userModel->db->prepare($userCheckQuery);
        $userCheckStmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        $userCheckStmt->execute();
        $userExists = $userCheckStmt->fetchColumn();
        if (!$userExists) {
            return ['erro' => 'Usuário não encontrado.'];
        }
        $stmt = $this->userModel->db->prepare("Update users set nivel = 'User' WHERE username = ?");
        $stmt->bindValue(':username', trim($user), PDO::PARAM_STR);
        $success = $stmt->execute([$user]);
        if (isset($success)) {
            $_SESSION['flash_sucesso'] = $user . ' alterado para User.';
        } elseif (isset($result['erro'])) {
            $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
        }
        header("Location: /Blog/remAdmin");
        exit;
    }

    public function atualizarDados() {
        if (isset($_SESSION['user'])) {
            $token = $_SESSION['token'];
            $id_user = $this->userModel->verificarTokenUser($token, $this->userModel->db);
            if (!$id_user) {
                return ['erro' => 'Token inválido ou expirado'];
            }
            if (isset($_POST['atualizarDados'])) {
                $userAtual = $this->userModel->getUser($_POST['username']);
                $nova_pass = $_POST['pass'] ?? '';
                if (!$userAtual) {
                    $_SESSION['flash_erro'] = 'Utilizador não encontrado.';
                    header("Location: /Blog/dados/");
                    exit;
                }
                if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                    $imagem_binaria = file_get_contents($_FILES['imagem']['tmp_name']);
                } else {
                    $imagem_binaria = null;
                }
                if (!empty($nova_pass) && !password_verify($nova_pass, $userAtual['pass'])) {
                    $nova_pass_hash = password_hash($nova_pass, PASSWORD_DEFAULT);
                } else {
                    $nova_pass_hash = $userAtual['pass']; 
                }
                $data = [
                    'username' => $_POST['username'],
                    'nome' => $_POST['nome'],
                    'email' => $_POST['email'],
                    'pass' => $nova_pass_hash,
                    'nivel' => $_POST['nivel'],
                    'imagem' => $imagem_binaria,
                    'token' => $_POST['token'],
                    'Ultimo_Login' => $_POST['Ultimo_Login'] 
                ];
                $result = $this->userModel->updateUser($data);
                if (isset($result['mensagem'])) {
                    $_SESSION['flash_sucesso'] = 'Dados atualizados com sucesso.';
                } elseif (isset($result['erro'])) {
                    $_SESSION['flash_erro'] = 'ERRO: ' . $result['erro'];
                }else {
                    $_SESSION['flash_erro'] = 'Erro inesperado ao atualizar os dados.';
                }
            }
            $formData = $data;
            $this->renderView('../app/views/dados.php', ['result' => $formData]);
        } else {
            header("Location: /Blog");
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