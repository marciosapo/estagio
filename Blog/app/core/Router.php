 <?php

class Router {

    public function route() {
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        $url_parts = explode('/', $url);
        $first_segment = array_shift($url_parts);
        if ($first_segment != 'api' && $first_segment != 'Blog') {
            header('Location: /Blog');
            exit();
        }
        $static_file = '/Blog/public/imgs/';
        if (strpos($url, $static_file) !== false) {
            $file_path = realpath($_SERVER['DOCUMENT_ROOT'] . '/' . $url);
            if ($file_path && strpos($file_path, $_SERVER['DOCUMENT_ROOT'] . '/Blog/public/imgs/') === 0 && file_exists($file_path)) {
                header('Content-Type: ' . mime_content_type($file_path));
                readfile($file_path);
                exit;
            }
        }
        if ($first_segment === 'api') {
            if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
                $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
                if (empty($user_agent) || 
                    (strpos($user_agent, 'postman') === false && 
                    strpos($user_agent, 'curl') === false && 
                    strpos($user_agent, 'insomnia') === false)) {
                    header('HTTP/1.1 403 Forbidden');
                    echo "Acesso proibido. A API não pode ser acessada diretamente pelo navegador.";
                    exit();
                } 
            }
            $this->handleApiRequest($url_parts);
        } else {
            $this->handleFrontendRequest($first_segment, $url_parts);
        }
    }
    
    private function handleApiRequest($url_parts) {
        $controller_name = ucfirst(array_shift($url_parts)) . 'Controller';
        $method_name = isset($url_parts[0]) ? array_shift($url_parts) : 'index';
        $params = $url_parts;
    
        $controller_path = '../app/controllers/' . $controller_name . '.php';
        if (file_exists($controller_path)) {
            require_once $controller_path;
            $controller = new $controller_name();
            if (method_exists($controller, $method_name)) {
                call_user_func_array([$controller, $method_name], $params);
            } else {
                $this->sendJsonResponse(['erro' => 'Método não encontrado'], 404);
            }
        } else {
            $this->sendJsonResponse(['erro' => 'Controller não encontrado'], 404);
        }
    }
    
    private function handleFrontendRequest($first_segment, $url_parts) {
        $controller_name = ucfirst($first_segment ?: 'Blog') . 'Controller';
        $method_name = isset($url_parts[0]) ? array_shift($url_parts) : 'index';
        $params = $url_parts;
    
        $controller_path = '../app/controllers/' . $controller_name . '.php';
        if (file_exists($controller_path)) {
            require_once $controller_path;
            $controller = new $controller_name();
            if (method_exists($controller, $method_name)) {
                call_user_func_array([$controller, $method_name], $params);
            } else {
                echo "Método não encontrado no frontend. " . $method_name;
            }
        } else {
            echo "Página não encontrada.";
        }
    }
    
    public function sendJsonResponse($data, $status = 200) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}

?>