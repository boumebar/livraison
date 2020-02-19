<?php


class MenusController{


    /****************************************************************************
     *                                  AFFICHER TOUT LES MENUS
     * 
     ***************************************************************************/

    public function show(){
        
        $user = new UserSession();
        $model = new MenusModel();
        $allMenus = $model->findAll();
        //var_dump($allMenus);

        return [
            "template"   => 
                [
                    "folder"  => "menus",
                    "file"    => "showAll",
                ],
            "allMenus"  => $allMenus,
            "user"      => $user,
        ];
    }


    /****************************************************************************
     *                                  AFFICHER UN MENU
     * 
     ***************************************************************************/

    public function showDetails(){

        $userSession = new UserSession();
        $flashbag = new Flashbag() ;
        $model = new MenusModel();
        $productModel = new ProductModel();
        $orderModel = new OrderModel();

        if(isset($_GET['id']) && ctype_digit($_GET['id'])){
            $allProducts = $productModel->findByMenu($_GET['id']);
            $menu = $model->findDetails($_GET['id']);
            if(!$menu){
                return [
                    "redirect" => "dej_menus_show",
                ];
            }
        }
        else
        {
            return [
                "redirect" => "dej_menus_show",
            ];
        }
        if(isset($_POST['quantity']) && ctype_digit($_POST['quantity'])){

            if(! $userSession->isAuthenticated())
            {
                $flashbag->addMessage("Merci de vous connecter pour passer une commande") ;
                return ["redirect" => "dej_user_login"] ;
            }
            $orderDetailsModel = new OrderDetailsModel();
            // trouve id order ou cree un nouvel order et donne son Id
            $idOrder =$orderModel->findBasketIdOrCreateByUser($userSession->getId());

             // verifie s'il y a un menu similaire dans le panier
             $isMenu = $orderDetailsModel->ismenusInBasket($menu['id'],$idOrder);
             
             // s'il y a deja ce menu dans le panier modifie la quantité de celui-ci
             if($isMenu){
                 // update la quantite de produit dans le panier
                 $orderDetailsModel->updateQuantitymenusByBasket($idOrder,$menu['id'],$_POST['quantity']);
             }
 
             // sinon l'ajoute
             else{
                 // ajoute le produit au panier
                 $orderDetailsModel->addmenusToBasket($idOrder,$menu['id'],$_POST['quantity'],$menu['price']);
             }
            
            // Renvoi le nombre de produits contenus dans le panier 
            $_SESSION['nbrProducts'] = $orderDetailsModel->findQuantityInBasket($userSession->getId());

            $flashbag->addMessage("Votre commande a bien été ajoutée au panier.") ;

            return [ "redirect" => "dej_menus_show"];
            
        }
        

        return [
            "template"   => 
                [
                    "folder"  => "menus",
                    "file"    => "showDetails",
                ],
            "menu"      => $menu,
            "allProducts"=> $allProducts,
        ];
    }

     /****************************************************************************
     *                                  CREE UN NOUVEAU MENU
     * 
     ***************************************************************************/

    public function create(){

        $flashbag = new Flashbag();
        $userSession = new UserSession();
        $productModel = new ProductModel();
        $model = new MenusModel();
        $router = new Router();


        $allJus = $productModel->findByCategoryAndIsMenu("jus");
        $allBoissonsChaudes = $productModel->findByCategoryAndIsMenu("boisson_chaude");
        $allViennoiseries = $productModel->findByCategoryAndIsMenu("viennoiserie");
        //die(var_dump($allViennoiseries));

        // verifie si Administrateur
        if(!$userSession->isAdmin()){
            return [ "redirect"  => "dej_home_main"];
        }
        // si pas de $_post redirection
        if(!$_POST){
            return [
                "template"   => 
                    [
                        "folder"  => "menus",
                        "file"    => "create",
                    ],
                "allJus"            => $allJus,
                "allViennoiseries"  => $allViennoiseries,
                "allBoissonsChaudes"=> $allBoissonsChaudes
            ];
        }

        // verifie les input 
        if((isset($_POST['name']) && !empty($_POST['name'])) && ctype_digit($_POST['nbrBoisson']) && ctype_digit($_POST['nbrViennoiseries']) && isset($_POST['boissonChaude']) && isset($_POST['viennoiseries']) && ctype_digit($_POST['nbrViennoiseries2']) && isset($_POST['viennoiseries2']) && ctype_digit($_POST['nbrJus']) && isset($_POST['jus']) && (isset($_POST['price']) && !empty($_POST['price'])))
        {
            $ids = $productModel->findAllIds();
            foreach($ids as $id){
                $allIds[] = $id['id'];
            }

            // verifie que les produits choisis existent dans la BDD
            if((!in_array($_POST['boissonChaude'],$allIds)) || (!in_array($_POST['viennoiseries'],$allIds)) || (!in_array($_POST['viennoiseries2'],$allIds)) || (!in_array($_POST['jus'],$allIds))){
                $flashbag->addMessage('Veuillez entrer un produit valide');
                return [
                    "template"   => 
                        [
                            "folder"  => "menus",
                            "file"    => "create",
                        ],
                    "allJus"            => $allJus,
                    "allViennoiseries"  => $allViennoiseries,
                    "allBoissonsChaudes"=> $allBoissonsChaudes

                ];  
            }

            // verifie qu 'au moins une boisson chaude 1 viennoiserie et 1 jus a été choisis
            if($_POST['nbrBoisson'] == 0 || $_POST['nbrViennoiseries'] == 0 || $_POST['nbrJus'] == 0){
                $flashbag->addMessage('Veuillez choisir au minimum 1 boisson chaude , 1 viennoiserie et un jus');
                return ['redirect'  => "dej_menus_create"];
            }

            // crée le menu et recupere son id
            $id = $model->create(htmlspecialchars(trim($_POST['name'])),floatval($_POST['price']));

            //ajoute le nbr de boisson chaude au menu
            $model->addProducts($id,$_POST['boissonChaude'],$_POST['nbrBoisson']);
            //ajoute le nbr et le choix de viennoiseries  au menu
            $model->addProducts($id,$_POST['viennoiseries'],$_POST['nbrViennoiseries']);

            //2ème viennoiseries ajoute le nbr et le choix de viennoiseries 2  au menu
            $model->addProducts($id,$_POST['viennoiseries2'],$_POST['nbrViennoiseries2']);
            

            //ajoute le nbr et le choix de jus  au menu
            $model->addProducts($id,$_POST['jus'],$_POST['nbrJus']);

            // verifie qu'une photo a été choisi
            if(isset($_FILES['img']) && $_FILES['img']['size'] > 0 && $_FILES['img']['error']>=0){
                
                // trouver l'extension renvoi .jpg ou .jpeg etc...
                $originalName = $_FILES['img']['name'];
                $lastDotPosition = strpos($originalName,".",strlen($originalName)-5);
                $ext = substr($originalName,$lastDotPosition);
                //die(var_dump($ext));
                
                //nom du fichier 
                $filename = "menu_".$id.$ext ;

                // chemin du fichier
                $filepath = $router->getWwwPath(true)."/uploads/menus/";
                
                
                
                // die($filepath.$filename) ;
                // prend le fichier (ex: $_FILES['tmp_name']) vers ou (ex: $filepath.$filename)
                move_uploaded_file($_FILES['img']['tmp_name'], $filepath.$filename);
                
                // 
                $model->updateImage($id,$filename);
            }


            $flashbag->addMessage('Votre menu a bien été crée');
            return [ "redirect"  => "dej_menus_show" ];   



        }else{
            $flashbag->addMessage('Veuillez remplir tout les champs');
            return [
                "template"   => 
                    [
                        "folder"  => "menus",
                        "file"    => "create",
                    ],
                "allJus"            => $allJus,
                "allViennoiseries"  => $allViennoiseries,
                "allBoissonsChaudes"=> $allBoissonsChaudes,
            ];
        }


         
    }


    
    /****************************************************************************
     *                                  MODIFIER UN MENU
     * 
     ***************************************************************************/

    public function update(){
         // Verifie si admin sinon redirige
        $flashbag = new Flashbag();
        $router = new Router();
        $userSession = new UserSession();
        $productModel = new ProductModel();
        
        $allBoissonChaudes = $productModel->findByCategoryAndMenu($_GET['id'],'boisson_chaude');
        //die(var_dump($allBoissonChaudes));
        $allJus = $productModel->findByCategoryAndMenu($_GET['id'],"jus");
        $allViennoiseries = $productModel->findByCategoryAndMenu($_GET['id'],"viennoiserie");
        //die(var_dump($boissonChaudes));
        if(!$userSession->isAdmin())
        {
            return ["redirect" => "dej_home_main"];
        }

        if((isset($_POST['name']) && !empty($_POST['name'])) && ctype_digit($_POST['nbrBoisson']) && ctype_digit($_POST['nbrViennoiseries']) && isset($_POST['viennoiseries']) && ctype_digit($_POST['nbrViennoiseries2']) && isset($_POST['viennoiseries2']) && ctype_digit($_POST['nbrJus']) && isset($_POST['jus']) && (isset($_POST['price']) && !empty($_POST['price']))){
            $model = new MenusModel();

            
             //modifie le nbr de boisson chaude au menu
             $model->updateProducts($_POST['id'],$_POST['boissonChaude'],$_POST['nbrBoisson']);

             //modifie le nbr et le choix de viennoiseries  au menu
             $model->updateProducts($_POST['id'],$_POST['viennoiseries'],$_POST['nbrViennoiseries']);
 
             //si 2ème viennoiseries modifie le nbr et le choix de viennoiseries 2  au menu
             if(($_POST['viennoiseries2'] != $_POST['viennoiseries']) || ($_POST['viennoiseries'] != 0)){
                    $model->updateProducts($_POST['id'],$_POST['viennoiseries2'],$_POST['nbrViennoiseries2']);
              }
 
            //modifie le nbr et le choix de jus  au menu
              $model->updateProducts($_POST['id'],$_POST['jus'],$_POST['nbrJus']);



            $model->update($_POST['id'],$_POST['name'],$_POST['price']);
            
             // nouvelle photo
             $newFile = isset($_FILES['img']) && $_FILES['img']['size'] > 0 && $_FILES['img']['error']>=0 ;
        
            
             if($newFile){
                 
                 $menu = $model->findDetails($_POST['id']);
                  
                 // chemin du dossier ou se trouve les images
                 $folderPath = $router->getWwwPath(true)."/uploads/menus/" ;
                 
                 // ancienne photo
                 $oldPicture = $menu['image'];
                 
                 
                 // chemin complet de l'ancienne image a supprimer
                 $fullPicturePath = $folderPath.$oldPicture;
                 
                 
                 if(file_exists($fullPicturePath)){
                      // supprime l'ancienne photo
                      unlink($fullPicturePath);
                     }
 
                 // trouver l'extension de la nouvelle photo renvoi .jpg ou .jpeg etc...
                 $originalName = $_FILES['img']['name'];
                 
                 $lastDotPosition = strpos($originalName,".",strlen($originalName)-5);
                 $ext = substr($originalName,$lastDotPosition);
                 
                 
                 //nouveau nom du fichier 
                 $filename = "menu_".$_POST['id'].$ext  ;
 
                 // prend le fichier (ex: $_FILES['tmp_name']) vers ou (ex: $filepath.$filename)
                 move_uploaded_file($_FILES['img']['tmp_name'], $folderPath.$filename);
 
                 
                 
                 $model->updateImage($_POST['id'],$filename);
             }     
              // affiche message avec le flashbag
              $flashbag = new Flashbag;
              $flashbag->addMessage("Le menu a bien été modifié");
 
             // retourne a la page de tout les plats
             return [
                 "redirect"  => "dej_menus_show",
             ];
        }
        elseif(isset($_GET['id']) && ctype_digit($_GET['id'])){

            $model = new MenusModel();
            $menu = $model->findDetails($_GET['id']);
            //$menu = $productModel->findByMenu($_GET['id']);
            //die(var_dump($boissonChaudes));
             //die(var_dump($menu));
            // $allBoissonsChaudes = $productModel->findByCategoryAndIsMenu('boisson_chaude');
            // $allJus = $productModel->findByCategoryAndIsMenu("jus");
            // $allViennoiseries = $productModel->findByCategoryAndIsMenu("viennoiserie");
            // $oldQuantityCoffee = $productModel->quantityProductsByMenu($_GET['id'],59);

            if(!$menu && !$allViennoiseries && !$allJus && !$allBoissonChaudes){

               return [
                    "redirect"  => "dej_menus_show"
                 ];
             }

            
            return [
                "template"     => 
                    [
                        "folder"    =>  "menus",
                        "file"      =>  "update",
                    ],
                "allJus"            => $allJus,
                "allViennoiseries"  => $allViennoiseries,
                "menu"              => $menu,
                "allBoissonChaudes" => $allBoissonChaudes

                ];
    
        }

        return [ "redirect"  =>  "dej_menus_show"];

    }


      /****************************************************************************
     *                                  SUPPRIME UN MENU
     * 
     ***************************************************************************/

     public function delete(){
         $router = new Router();
         // Verifie si admin sinon redirige
        $userSession = new UserSession();
        if(!$userSession->isAdmin())
        {
            return ["redirect" => "dej_home_main"];
        } 

        if(isset($_GET['id']) && ctype_digit($_GET['id'])){
            $model = new MenusModel();
            $menu =$model->findDetails($_GET['id']);
            
            if(!$menu){
                return ["redirect" => "dej_menus_show"];
            }

            $filename = $menu['image'] ;
            $fullFilePath = $router->getWwwPath(true)."/uploads/menus/".$filename ;
           
            if(file_exists($fullFilePath))
            {
                unlink($fullFilePath) ;
            }

            $model->delete($_GET['id']); 
        }


         // affiche message avec le flashbag
         $flashbag = new Flashbag;
         $flashbag->addMessage("Le menu a bien été effacé");
         // retourne a la page de tout les menus
         return [
             "redirect"  => "dej_menus_show"
         ];

     }

}