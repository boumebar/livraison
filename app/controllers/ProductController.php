<?php


class ProductController{


    /****************************************************************************
     *                                  AFFICHER TOUT LES PRODUITS
     * 
     ***************************************************************************/

    public function show(){

        $user = new UserSession();
        $model = new ProductModel();
        $allProducts = $model->findAll();
        //var_dump($allProducts);

        return [
            "template"   => 
                [
                    "folder"  => "products",
                    "file"    => "showAll",
                ],
            "allProducts"  => $allProducts,
            "user"  => $user,
        ];
    }


    /****************************************************************************
     *                                  AFFICHER UN PRODUIT
     * 
     ***************************************************************************/

    public function showDetails(){

        $userSession = new UserSession();
        $flashbag = new Flashbag() ;
        $model = new ProductModel();

        if(isset($_GET['id']) && ctype_digit($_GET['id'])){
            $product = $model->findDetails($_GET['id']);
        //    var_dump($product);
           if(!$product){
                return [
                    "redirect" => "dej_products_show",
                ];
            }
        }
        else
        {
            return [
                "redirect" => "dej_products_show",
            ];
        }
        if(isset($_POST['quantity']) && ctype_digit($_POST['quantity'])){

            if(! $userSession->isAuthenticated())
            {
                $flashbag->addMessage("Merci de vous connecter pour passer une commande") ;
                return ["redirect" => "dej_user_login"] ;
            }

            $orderModel = new OrderModel();
            $orderDetailsModel = new OrderDetailsModel();
            // trouve id order ou cree un nouvel order et donne son Id
            $idOrder =$orderModel->findBasketIdOrCreateByUser($userSession->getId());

             // verifie s'il y a un produit similaire dans le panier
             $isProduct = $orderDetailsModel->isproductsInBasket($product['id'],$idOrder);
             // var_dump($product);
             
             // s'il y a deja ce produit dans le panier modifie la quantité de celui-ci
             if($isProduct){
                 // var_dump($idOrder);

                 // update la quantite de produit dans le panier
                 $orderDetailsModel->updateQuantityproductsByBasket($idOrder,$product['id'],$_POST['quantity']);
             }
 
             // sinon l'ajoute
             else{
                 // ajoute le produit au panier
                 $orderDetailsModel->addproductsToBasket($idOrder,$product['id'],$_POST['quantity'],$product['price']);
             }
            
            // Renvoi le nombre de produits contenus dans le panier 
            $_SESSION['nbrProducts'] = $orderDetailsModel->findQuantityInBasket($userSession->getId());

            
            $flashbag->addMessage("Votre commande a bien été ajoutée au panier.") ;

            return [ "redirect" => "dej_menus_show"];
            
        }
       
        

        return [
            "template"   => 
                [
                    "folder"  => "products",
                    "file"    => "showDetails",
                ],
            "product"      => $product,
        ];
    }

     /****************************************************************************
     *                                  CREE UN NOUVEAU PRODUIT
     * 
     ***************************************************************************/

    public function create(){

        $flashbag = new Flashbag();
        $userSession = new UserSession();

        if(!$userSession->isAdmin()){
            return [ "redirect"  => "dej_home_main"];
        }
        if(!$_POST){

            return [
                "template" =>
                    [
                        "folder" => "products",
                        "file"   => "create",
                    ],
                ];
        }

        else
        {
            $error = false;
            $model = new ProductModel();
            if(!isset($_POST['name']) || empty(trim($_POST['name']))){
                $error = true;
                $flashbag->addMessage('Veuillez entrer un nom de produit');
            }
            if(!isset($_POST['description']) || empty(trim($_POST['description']))){
                $error = true;
                $flashbag->addMessage('Veuillez entrer une description du produit');
            }
            if(!isset($_POST['price']) || empty(trim($_POST['price']))){
                $error = true;
                $flashbag->addMessage('Veuillez entrer le prix du produit');
            }

            $categories = $model->findCategories();
            foreach($categories as $category){
                $allCategories[] = $category['category']; 
            }
            //die(var_dump($allCategories));
            if(!isset($_POST['category']) || !in_array($_POST['category'],$allCategories)){
                $error = true;
                $flashbag->addMessage('Veuillez choisir une catégorie');
            }
            if($error){
                //die(var_dump($_POST));
                return [ "redirect"  => "dej_products_create"];

            }
            
            $id = $model->create(trim($_POST['name']),trim($_POST['description']),trim($_POST['price']),trim($_POST['category']));

            if(isset($_FILES['img']) && $_FILES['img']['size'] > 0 && $_FILES['img']['error']>=0)
            {
                $router = new Router();
               // trouver l'extension renvoi .jpg ou .jpeg etc...
               $originalName = $_FILES['img']['name'];
               $lastDotPosition = strpos($originalName,".",strlen($originalName)-5);
               $ext = substr($originalName,$lastDotPosition);
               //die(var_dump($ext));
               
               //nom du fichier 
               $filename = "product_".$id.$ext ;

               // chemin du fichier
               $filepath = $router->getWwwPath(true)."/uploads/products/";
               
               
               
               // die($filepath.$filename) ;
               // prend le fichier (ex: $_FILES['tmp_name']) vers ou (ex: $filepath.$filename)
               move_uploaded_file($_FILES['img']['tmp_name'], $filepath.$filename);
               
               // 
               $model->updateImg($id,$filename);
            }          
           
        }    


        $flashbag->addMessage('votre produit a bien été ajouté');
        return ["redirect"   =>  "dej_products_show"];
    }



    
    /****************************************************************************
     *                                 MODIFIE UN PRODUIT
     * 
     ***************************************************************************/

     public function update(){

        $userSession = new UserSession();
        $flashbag = new Flashbag();
        $router = new Router();

        if(!$userSession->isAdmin()){
            return [ "redirect"   => "dej_home_main"];
        }
        $model = new ProductModel();
        if((isset($_POST['name']) && !empty(trim($_POST['name']))) && (isset($_POST['description']) && !empty(trim($_POST['description']))) && (isset($_POST['price']) && !empty(trim          ($_POST['price']))) && (isset($_POST['category']) && !empty(trim($_POST['category'])))){

            $model->update($_POST['id'],$_POST['name'],$_POST['description'],$_POST['price'],$_POST['category']);

            // nouvelle photo
            $newFile = isset($_FILES['img']) && $_FILES['img']['size'] > 0 && $_FILES['img']['error']>=0 ;
        
            
            if($newFile){
                
                $product = $model->findDetails($_POST['id']);
                 
                // chemin du dossier ou se trouve les images
                $folderPath = $router->getWwwPath(true)."/uploads/products/" ;
                
                // ancienne photo
                $oldPicture = $product['image'];
                
                // chemin complet de l'ancienne image a supprimer
                $fullPicturePath = $folderPath.$oldPicture;
                
                
                if(file_exists($fullPicturePath)){
                     // supprime l'ancienne photo
                     unlink($fullPicturePath);
                    }

                // Nom du fichier
                $originalName = $_FILES['img']['name'];
                
                // trouve la position du point dans le nom du fichier
                $lastDotPosition = strpos($originalName,".",strlen($originalName)-5);
                // trouver l'extension de la nouvelle photo renvoi .jpg ou .jpeg etc...
                $ext = substr($originalName,$lastDotPosition);
                
                
                //nouveau nom du fichier 
                $filename = "product_".$_POST['id'].$ext  ;

                // prend le fichier (ex: $_FILES['tmp_name']) vers ou (ex: $filepath.$filename)
                move_uploaded_file($_FILES['img']['tmp_name'], $folderPath.$filename);
                
                
                
                $model->updateImg($_POST['id'],$filename);
            
                
            }


             // affiche message avec le flashbag
             $flashbag = new Flashbag;
             $flashbag->addMessage("Le plat a bien été modifié");

            // retourne a la page de tout les plats
            return [
                "redirect"  => "dej_products_show"
            ];

        }elseif(isset($_GET['id']) && ctype_digit($_GET['id'])){
            
            $product = $model->findDetails($_GET['id']);
            if(!$product){
                return ['redirect'  => "dej_products_show"];
            }

            return [
                "template"  =>
                    [
                        "folder"  => "products",
                        "file"    => "update",
                    ],
                "product"   => $product,
                ];
        }

         return [ "redirect"   => "dej_products_show"];
     }

     /****************************************************************************
     *                                  SUPPRIME UN PRODUIT
     * 
     ***************************************************************************/

    public function delete(){

         // Verifie si admin sinon redirige
        $userSession = new UserSession();
        if(!$userSession->isAdmin()){
            return ['redirect' => 'dej_home_main'];
        }
        
        if(isset($_GET['id']) && ctype_digit($_GET['id'])){
            $model = new ProductModel();
            $product = $model->findDetails($_GET['id']);
            //var_dump($product);
            if(!$product){
                return [
                    "redirect"  => "dej_products_show",
                ];
            }

            $router = new Router();            
            $filename = $product['image'];
            //var_dump($filename);
            $fullFilePath = $router->getWwwPath(true)."/uploads/products/".$filename;
            //var_dump($fullFilePath);

            if(file_exists($fullFilePath)){
                unlink($fullFilePath);
            }
            $model->delete($_GET['id']);

        }

        // affiche message avec le flashbag
        $flashbag = new Flashbag;
        $flashbag->addMessage("Le plat a bien été effacé");
        // retourne a la page de tout les plats
        return [
            "redirect"  => "dej_products_show"
        ];
    }
}