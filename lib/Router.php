<?php


class Router{
    private $rootUrl;
    private $wwwPath;
    private $localhostPath;
    private $allUrls = [];
    private $allRoutes = 
    [   
        // Accueil -------------------------------
        "/"            => 
            [
                "controller" => "Home",
                "method"     => "main",
                "name"       => "dej_home_main"
            ],
        "/cgu"         => 
            [
                "controller" => "Cgu",
                "method"     => "show",
                "name"       => "dej_cgu_show"
            ],
        "/cgv"         => 
            [
                "controller" => "Cgv",
                "method"     => "show",
                "name"       => "dej_cgv_show"
            ],
        "/mentions"    => 
            [
                "controller" => "Mentions",
                "method"     => "show",
                "name"       => "dej_mentions_show"
            ],
        "/contact"      => 
            [
                "controller" => "Contact",
                "method"     => "show",
                "name"       => "dej_contact_show"
            ],
        "/faq"          =>
            [
                "controller" => "Faq",
                "method"     => "show",
                "name"       => "dej_faq_show",
            ],
        "/nous"         =>
            [
                "controller" => "About",
                "method"     => "show",
                "name"       => "dej_about_show",
            ],
        "/partenaires"  =>
            [
                "controller" => "Partner",
                "method"     => "show",
                "name"       => "dej_partner_show",
            ],

        // Menus--------------------------------------

        "/menus"     =>
            [
                "controller" => "Menus",
                "method"     => "show",
                "name"       => "dej_menus_show",
            ],
        "/menus/details"    =>
            [
                "controller" => "Menus",
                "method"     => "showDetails",
                "name"       => "dej_menus_showDetails",
            ],
        "/menus/create"     =>
            [
                "controller" => "Menus",
                "method"     => "create",
                "name"       => "dej_menus_create",
            ],
        "/menus/update"     =>
        [
            "controller" => "Menus",
            "method"     => "update",
            "name"       => "dej_menus_update",
        ],
        "/menus/delete"     =>
        [
            "controller" => "Menus",
            "method"     => "delete",
            "name"       => "dej_menus_delete",
        ],

        // Produits--------------------------------------
        
        "/products"             =>
            [
                "controller" => "Product",
                "method"     => "show",
                "name"       => "dej_products_show",
            ],
        "/products/details"     =>
            [
                "controller" => "Product",
                "method"     => "showDetails",
                "name"       => "dej_products_showDetails",
            ],
        "/products/create"      =>
            [
                "controller" => "Product",
                "method"     => "create",
                "name"       => "dej_products_create",
            ],
        "/products/update"      =>
        [
            "controller" => "Product",
            "method"     => "update",
            "name"       => "dej_products_update",
        ],
        "/products/delete"      =>
        [
            "controller" => "Product",
            "method"     => "delete",
            "name"       => "dej_products_delete",
        ],


        // Users--------------------------------------

        "/user/signup"     => 
            [
                "controller" => "User",
                "method"     => "create",
                "name"       => "dej_user_create"
            ],
        "/user/login"      =>
            [
                "controller" => "User",
                "method"     => "login",
                "name"       => "dej_user_login"
            ],
        "/user/logout"     =>
            [
                "controller" => "User",
                "method"     => "logout",
                "name"       => "dej_user_logout",   
            ],
        "/user/compte"     =>
            [
                "controller" => "User",
                "method"     => "compte",
                "name"       => "dej_user_compte",   
            ],
        "/user/update"     =>
            [
                "controller" => "User",
                "method"     => "update",
                "name"       => "dej_user_update",   
            ],
        
        // Order -----------------------------------------------

        "/basket"        =>
            [   
                "controller"    =>"Order",
                "method"        =>"showBasket",
                "name"          =>"dej_order_showbasket"
            ],
        "/order/details"        =>
        [   
            "controller"    =>"Order",
            "method"        =>"showOrderDetails",
            "name"          =>"dej_order_showorderdetails"
        ],

        "/order/addToBasket" =>
            [   
                "controller"    =>"Order",
                "method"        =>"addToBasket",
                "name"          =>"dej_order_addtobasket"
            ],
        
        "/order/deleteToBasket" =>
        [   
            "controller"    =>"Order",
            "method"        =>"deleteToBasket",
            "name"          =>"dej_order_deletetobasket"
        ],

        "/order/confirm" =>
            [   
                "controller"    =>"Order",
                "method"        =>"confirm",
                "name"          =>"dej_order_confirm"
            ],
        
        // Delivery -----------------------------------------------

        "/delivery"    =>
            [
                "controller" => "Delivery",
                "method"     => "show",
                "name"       => "dej_delivery_show",
            ],
        "/delivery/choice" =>
            [   
                "controller"    =>"Delivery",
                "method"        =>"choice",
                "name"          =>"dej_delivery_choice"
            ],


    ];

    public function __construct(){
        //die(var_dump($_SERVER));

        //die(var_dump($_SERVER));
        $this->rootUrl = $_SERVER['SCRIPT_NAME'];
        
        // reprend rootUrl et remonte un fichier avant le "/" 
        $this->wwwPath = dirname($this->rootUrl)."/www";
        //die(var_dump($this->wwwPath));

        $this->localhostPath = $_SERVER['DOCUMENT_ROOT'];

        // foreach pour associer chaque nom de route a son url et les mettre dans tableau allUrls
        foreach($this->allRoutes as $url => $route)
        {
            $this->allUrls[$route["name"]] = $url;
        }
        //die(var_dump($url));

    }
    public function getRoute($requestPath){

        if(isset($this->allRoutes[$requestPath])){
            return $this->allRoutes[$requestPath];
        }
        else{
            die("URL inconnue");
        }
    }

    public function getWwwPath($absolute = false){

        if($absolute){
            return realpath($this->localhostPath.$this->wwwPath);
        }
        else{
            return $this->wwwPath;
        }
    }

    public function generateUrl($routeName){

        if(isset($this->allUrls[$routeName])){
            return $this->rootUrl.$this->allUrls[$routeName];
        }
        else{
            throw new ErrorException("nom de route inconnu :".$routeName);
        }
    }
}