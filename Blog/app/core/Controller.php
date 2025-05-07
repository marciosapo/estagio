<?php

// app/core/Controller.php

class Controller {

    public function model($model) {
        if (empty($model)) {
            throw new Exception("Model não especificado.");
        }
    
        $modelPath = __DIR__ . "/../models/$model.php";
    
        if (!file_exists($modelPath)) {
            throw new Exception("Model não encontrado: $modelPath");
        }
    
        require_once $modelPath;
        return new $model();
    }

    public function sendJsonResponse($data, $status = 200) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }
}