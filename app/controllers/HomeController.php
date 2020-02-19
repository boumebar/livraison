<?php
class HomeController{
    public function main(){

        $orderDetailsModel = new OrderDetailsModel();
        $userSession = new UserSession();
        $productModel = new ProductModel();
        $menusModel = new MenusModel();

        // trouve 3 produits de facon aleatoire
        $products = $productModel->findRandomProducts();
        
        // trouve 3 menus de facon aleatoire
        $menus = $menusModel->findRandomMenus();

         // Renvoi le nombre de produits contenus dans le panier 
         $_SESSION['nbrProducts'] = $orderDetailsModel->findQuantityInBasket($userSession->getId());

        return [
            "template" =>
                [
                    "folder" => "home",
                    "file"   => "main",
                ],
            "products" => $products,
            "menus" => $menus,
            
        ];
    }
}