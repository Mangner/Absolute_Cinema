<?php


class AppController {

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