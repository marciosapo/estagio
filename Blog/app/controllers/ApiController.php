<?php

require_once __DIR__ . '/../core/Controller.php';

class ApiController extends Controller {
    private $postModel;
    private $usersModel;
    public function __construct() {
        $this->postModel = $this->model('Post');
        $this->usersModel = $this->model('User');
    }

}

?>