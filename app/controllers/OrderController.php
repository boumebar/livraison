<?php

class OrderController {


      /****************************************************************************
     *                                 DETAILS DE LA COMMANDE DANS LE PANIER
     * 
     ***************************************************************************/
    public function showBasket()
    {
        
        $userSession = new UserSession();
        $orderDetailsmodel = new OrderDetailsModel();
        $model = new OrderModel();
        $flashbag = new Flashbag() ;
        $totalPrice = 0;

        // Trouve l 'id de la commande dans le panier
        $idOrder = $model->findBasketId($userSession->getId());
        // die(var_dump($idOrder));

        if(! $userSession->isAuthenticated())
        {
            $flashbag->addMessage("Merci de vous connecter pour passer une commande") ;
            return ["redirect" => "dej_user_login"] ;

        }
        
        if($idOrder){
            // Trouves les menus qui sont dans le panier 
            $orderMenus = $orderDetailsmodel->findMenuByOrder($idOrder) ;

            // Trouves les produits qui sont dans le panier 
            $orderProducts = $orderDetailsmodel->findProductsByOrder($idOrder) ;
        
            // Regroupes les produits et les menus dans un seul tableau
            $orderDetails = array_merge($orderMenus,$orderProducts);

            // prix total de la commande 
            $totalOrder = $orderDetailsmodel->findTotal($idOrder);  
            if($totalOrder < 25){
                $totalOrder += 5;
            }
            
            return [
                "template" =>
                [
                    "folder" => "order",
                    "file"   => "showBasket",
                ],
                "orderDetails"   => $orderDetails ,
                "totalOrder"     => $totalOrder,
                "totalPrice"     => $totalPrice,
            ] ;
        }
        else{
            $flashbag->addMessage('Votre panier est vide ');
            return ["redirect"  => 'dej_home_main'];
        }
    }


    /****************************************************************************
     *                                 DETAILS DE LA COMMANDE VALIDEE
     * 
     ***************************************************************************/

    public function showOrderDetails(){
        $userSession = new UserSession();
        $orderDetailsmodel = new OrderDetailsModel();
        $flashbag = new Flashbag() ;
        $totalPrice = 0;

        if(!$userSession->isAuthenticated())
        {
            $flashbag->addMessage("Merci de vous connecter pour passer une commande") ;
            return ["redirect" => "dej_user_login"] ;

        }

        if(isset($_GET['id']) && ctype_digit($_GET['id'])){
            $orders = $orderDetailsmodel->findOrderDetails($_GET['id']);
            if($orders){
                $userModel = new UserModel();

                // details de l'utilisateur
                $user = $userModel->findById($orders[0]['user_id']);
                
                // prix total de la commande 
                $totalOrder = $orderDetailsmodel->findTotal($_GET['id']);
                // si total inferieur a 25 alors total +5
                if($totalOrder < 25){
                    $totalOrder += 5;
                }
                return [
                    "template" =>
                    [
                        "folder" => "order",
                        "file"   => "showOrderDetails",
                    ],
                    "orders"     => $orders,
                    "totalOrder" => $totalOrder,
                    "totalPrice" => $totalPrice,
                    "user"       => $user,
                ];
            }
            else{
                $flashbag->addMessage('Ce numéro de commande n\'existe pas');
                return ["redirect" => "dej_home_main"];
            }
        }
       


        return [
            "template" =>
            [
                "folder" => "order",
                "file"   => "showOrderDetails",
            ],
        ] ;


    }
     /****************************************************************************
     *                                 AJOUTER AU PANIER
     * 
     ***************************************************************************/

    public function addToBasket()
    {
        $userSession = new UserSession();
        $flashbag = new Flashbag() ;
        $orderDetailsModel = new OrderDetailsModel() ;

        if(! $userSession->isAuthenticated())
        {
            $flashbag->addMessage("Merci de vous connecter pour passer une commande") ;
            return ["redirect" => "dej_user_login"] ;
        }

        if((isset($_GET['menus']) || isset($_GET['products'])) && (ctype_digit($_GET['menus']) || ctype_digit($_GET['products']) ) && isset($_GET['q']) && ctype_digit($_GET['q']) )
        {
            $model = new OrderModel() ;
            $menuModel = new MenusModel();

            // trouve id order ou cree un nouvel order et donne son Id
            $idOrder = $model->findBasketIdOrCreateByUser($userSession->getId()) ;
            // die(var_dump($idOrder));

            // donne la categorie (menus ou produits) 
            $category = array_keys($_GET)[0];

            // trouve la bonne methode pour rechercher le prix dans la table  menus ou produits 
            $findCategory = "find".$category."Price";

            // prix du produit ou du menu 
            $price = $menuModel->$findCategory($_GET[$category]) ;
            // var_dump($_GET);
            // var_dump($_GET[$category]);
            // echo 'la categorie est ' .$category;
            // echo 'le prix est '. $price;
            // die(var_dump(array_keys($_GET)));
            
            // Est ce un produit ou un menu?
            $isInBasket = "is".$category."InBasket";

            // verifie s'il y a un produit ou menu similaire dans le panier
            $product = $orderDetailsModel->$isInBasket($_GET[$category],$idOrder);
            // var_dump($product);
            
            // s'il y a deja ce produit ou menu dans le panier modifie la quantité de celui-ci
            if($product){
                
                // trouve la bonne methode pour update selon que s'est un produit ou un menu
                $updateQuantityByBasket = "updateQuantity".$category."ByBasket";
                // var_dump($updateQuantityByBasket);
                // var_dump($idOrder);
                // var_dump($_GET[$category]);
                // var_dump($_GET['q']);
                // die('je suis deja');
                // update la quantite de produit ou de menus dans le panier
                $orderDetailsModel->$updateQuantityByBasket($idOrder,$_GET[$category],$_GET['q']);
            }

            // sinon l'ajoute
            else{
                
                 // trouve le bon nom de fonction pour savoir s'il faut ajouter un produit ou un menu
                 $addToBasket = "add".$category."ToBasket";
                //  var_dump($addToBasket);
                //  die('je suis deja');
                // ajoute le produit ou le menu au panier
                $orderDetailsModel->$addToBasket($idOrder, $_GET[$category], $_GET['q'], $price) ;
            }
            
            // Renvoi le nombre de produits contenus dans le panier 
            $_SESSION['nbrProducts'] = $orderDetailsModel->findQuantityInBasket($userSession->getId());

            $flashbag->addMessage("Votre commande a bien été ajoutée au panier.") ;

            return [ "redirect" => "dej_".$category."_show"];
        }


        return ["redirect" => "dej_home_main"] ;

    }

    /****************************************************************************
     *                                 SUPPRIMER DU PANIER
     * 
     ***************************************************************************/

    public function deleteToBasket(){
        $userSession = new UserSession();
        $flashbag = new Flashbag() ;

        if(! $userSession->isAuthenticated())
        {
            $flashbag->addMessage("Merci de vous connecter") ;
            return ["redirect" => "dej_user_login"] ;
        }

        if((isset($_GET['menus']) || isset($_GET['products'])) && (ctype_digit($_GET['menus']) || ctype_digit($_GET['products']) ) && isset($_GET['order']) && ctype_digit($_GET['order'])){
            $orderDetailsModel = new OrderDetailsModel() ;

            // trouve si le produit a supprimer est un menus ou un produits details 
            $category = array_keys($_GET)[0];
            
            // trouve le bon nom de fonction pour savoir s'il faut supprimer un produit ou un menu
            $deleteToBasket = "delete".$category."ToBasket";
           
            // supprime le produit ou le menu au panier
            $orderDetailsModel->$deleteToBasket($_GET['order'],$_GET[$category]);

            $flashbag->addMessage("Votre article a bien été supprimée du panier.") ;

            // Renvoi le nombre de produits contenus dans le panier
            $_SESSION['nbrProducts'] = $orderDetailsModel->findQuantityInBasket($userSession->getId());
            return [ "redirect" => "dej_order_showbasket"];
        }
        
        
        return ["redirect" => "dej_home_main"] ;
    }


    /****************************************************************************
     *                                 VIDER LE PANIER 
     * 
     ***************************************************************************/

    public function emptyBasket()
    {
        // pour vider le panier, on passer le status de l'Order correspondant de 'basket' à "cancelledd"
        // (on n'efface pas les données) ;
        $userSession = new UserSession();
        $flashbag = new Flashbag() ;

        if(! $userSession->isAuthenticated())
        {
            $flashbag->addMessage("Merci de vous connecter") ;
            return ["redirect" => "dej_user_login"] ;
        }
        if($userSession){
            $model = new OrderModel() ;
            $model->emptyBasket($userSession->getId());
        }

        return ["redirect" => "dej_home_main"] ;
        
    }


    /****************************************************************************
     *                                 CONFIRMER LE PANIER 
     * 
     ***************************************************************************/

    public function confirm()
    {
        $userSession = new UserSession();
        $flashbag = new Flashbag() ;
        $model = new OrderModel();

         // Trouve l 'id de la commande dans le panier
         $idOrder = $model->findBasketId($userSession->getId());

        if(! $userSession->isAuthenticated())
        {
            $flashbag->addMessage("Merci de vous connecter pour confirmer votre commande") ;
            return ["redirect" => "dej_user_login"] ;
        }

        if($idOrder)
        {
            $orderDetailsmodel = new OrderDetailsModel();
            $deliveryModel = new DeliveryModel();
            
            // prix total de la commande 
            $totalOrder = $orderDetailsmodel->findTotal($idOrder);
            if($totalOrder < 25){
                $totalOrder += 5;
            }

            // trouve la Date de livraison
            $deliveryDate = $deliveryModel->findDeliveryDate($idOrder);
            //met a jour le prix de la commande dans la BDD
            $model->updateTotal($idOrder,number_format($totalOrder, 1));

            $model->confirm($userSession->getId(),$idOrder) ;

            
            $_SESSION['nbrProducts'] = null;

            return [
                "template"  => 
                    [
                        "folder"        => "order",
                        "file"          => "orderConfirmation",
                    ],
                    "deliveryDate"  => $deliveryDate,
            ] ;
        }
        else{
            $flashbag->addMessage("Ce numéro de commande n'existe pas ") ;

            return ["redirect" => "dej_home_main"] ;
        }
        
        


    }


}