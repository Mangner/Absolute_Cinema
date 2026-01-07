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
        
        $timeout = 600;
        
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            // Czas minął! Czyścimy sesję
            session_unset();
            session_destroy();
            
            // Przekierowujemy z komunikatem (opcjonalnie)
            $this->url('login');
            exit();
        }

        // Aktualizujemy czas ostatniej aktywności na TERAZ
        $_SESSION['last_activity'] = time();
    }

    
    protected function render(string $template = null, array $variables = [])
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
      
        $templatePath404 = 'public/views/404.html';
        $templatePath = 'public/views/' . $template . '.html';
        $output = "";
                 
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