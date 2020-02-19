<?php

class Kernel{

    private $viewData = [];


    // notre autoloader
    public function loadClass($class){

        $fileName = "";

        // dans $class si controller dans le nom 
        if(substr($class, -10) == "Controller")
        {
            // alors dans filename mets ce chemin
            $fileName = "app/controllers/" . $class . ".php";
        }

        // dans $class si Model dans le nom
        elseif(substr($class, -5) == "Model")
        {
            // alors dans filename mets ce chemin
            $fileName = "app/models/" . $class . ".php";
        }
        // sinon mets ce chemin
        else{
            $fileName = "app/class/" .$class . ".php";
        }

        

        if(file_exists($fileName)){
            include $fileName;
        }
        else{
            throw new ErrorException("impossbile de charger la classe : ".$fileName);
        }
    }


    // oublie ton autoloader prend le notre loadClass
    public function bootstrap(){
        spl_autoload_register([$this, "loadClass"]);
    }


    // verifie 

    public function run(){
      

        if(isset($_SERVER['PATH_INFO'])){
            // si (/menus)
            $requestPath = $_SERVER["PATH_INFO"];
            //die(var_dump($_SERVER));
        }
        else{
            // sinon (/)
            $requestPath = '/';
        }
        
        $router = new Router();
        $this->viewData['router'] = $router;
        $requestRoute = $router->getRoute($requestPath);
        $controllerName = $requestRoute["controller"]."Controller";
        $methodName = $requestRoute["method"];
        $controller = new $controllerName($router);
        

        if(method_exists($controller,$methodName)){
            
            $this->viewData = array_merge($this->viewData,(array)$controller->$methodName());
            $this->renderResponse();
        }
        else{
            throw new ErrorException("methode ".$methodName ." inconnue");
        }
    
    }

    public function renderResponse(){

        // extrait le contenu en variable de l espace globale
        extract($this->viewData,EXTR_OVERWRITE);
        if(isset($template)){
            $flashbag = new Flashbag();
            $userSession = new UserSession();
            
            $templatePath  = "www/views";
            $templatePath .= "/".$template["folder"];
            $templatePath .= "/".$template["file"];
            $templatePath .= "View.phtml";
            

            include "www/views/layout.phtml";
        }
        elseif(isset($redirect)){
            header("Location:".$router->generateUrl($redirect));
            exit();           
        }
        elseif($jsonresponse)
        {
            return json_encode($jsonresponse) ;
        }
     
    }

}