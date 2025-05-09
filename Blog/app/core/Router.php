 <?php

class Router {

    public function route() {
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        $url_parts = explode('/', $url);
        if ($url_parts[0] === 'avatar') {
            require_once '../app/controllers/BlogController.php';
            $controller = new BlogController();
            $controller->avatar();
    
            return;
        }
        
        $first_segment = array_shift($url_parts);
    
        $static_file = '/Blog/public/imgs/';
        if (strpos($url, $static_file) !== false) {
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $url;
            if (file_exists($file_path)) {
                header('Content-Type: ' . mime_content_type($file_path));
                readfile($file_path);
                exit;
            }
        }

        if ($first_segment === 'api') {
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
    
        } else {
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
    }
    
    public function sendJsonResponse($data, $status = 200) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}

?>