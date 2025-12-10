<?php


class AppController {

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }


    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }


    protected function url(string $path) {
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/{$path}");
    }


    protected function requireLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $this->url('login');
            exit();
        }
    }

    
    protected function render(string $template = null, array $variables = [])
    {
        $templatePathPhtml = 'public/views/' . $template . '.phtml';
        $templatePathHtml = 'public/views/' . $template . '.html';
        $templatePath404 = 'public/views/404.html';
        
        $templatePath = null;
        $output = "";

        
        if (file_exists($templatePathPhtml)) {
            $templatePath = $templatePathPhtml;
        }
        elseif (file_exists($templatePathHtml)) {
            $templatePath = $templatePathHtml;
        }
        
                 
        if(file_exists($templatePath)){
            
            extract($variables);
            
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        } else {
            ob_start();
            include $templatePath404;
            $output = ob_get_clean();
        }
        echo $output;
    }

}