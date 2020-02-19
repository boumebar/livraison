<?php

    class OrderDetailsModel
    {

    /***********************************************************************************************************
     *                                 TROUVE LES MENUS DANS LE PANIER SELON USER_ID
     * 
     *********************************************************************************************************/

        public function findMenuByOrder($idOrder)
        {
            $pdo = (new Database())->getPdo() ;

            $req = $pdo->prepare("SELECT orderdetails.id,orderdetails.menu_Id,orderdetails.quantity, orderdetails.priceEach, menus.name, menus.image, orders.id as Id
                                  FROM orders
                                  INNER JOIN orderdetails
                                  INNER JOIN menus
                                  ON orders.id = :idOrder
                                  AND status = 'basket'
                                  AND orders.id = orderdetails.order_id
                                  AND menus.id = menu_id
                                    ");

            $req->execute(  [
                                'idOrder' => $idOrder,
                            ]) ;

            return $req->fetchAll() ;
        }


     /***********************************************************************************************************
     *                                 TROUVE LE TOTAL D UNE COMMANDE
     * 
     *********************************************************************************************************/
        public function findTotal($idOrder){
            $pdo = (new Database())->getPdo() ;

            $req = $pdo->prepare("SELECT ROUND(SUM(`quantity`*`priceEach`),2) as total 
                                  FROM `orderdetails` 
                                  WHERE order_id = :idOrder
                                    ");

            $req->execute(  [
                                'idOrder' => $idOrder,
                            ]) ;

            $result = $req->fetch() ;
            return $result['total'];
        }

    /***************************************************************************************************
     *                                 TROUVE LES PRODUITS DANS LE PANIER SELON USER_ID 
     * 
     ***************************************************************************************************/

        public function findProductsByOrder($idOrder)
        {
            $pdo = (new Database())->getPdo() ;

            $req = $pdo->prepare("SELECT orderdetails.id,orderdetails.menu_Id,orderdetails.product_Id,orderdetails.quantity, orderdetails.priceEach, products.name, products.image, orders.id as Id
                                  FROM orders
                                  INNER JOIN orderdetails
                                  INNER JOIN products
                                  ON orders.id= :idOrder
                                  AND status = 'basket'
                                  AND orders.id = orderdetails.order_id
                                  AND products.id = product_id
                                    ");

            $req->execute(  [
                                ':idOrder' => $idOrder,
                            ]) ;

            return $req->fetchAll() ;
        }


     /***************************************************************************************************
     *                                 TROUVE LE NOMBRE DE PRODUITS DANS LE PANIER 
     * 
     ***************************************************************************************************/

        public function findQuantityInBasket($idUser)
        {
            $pdo = (new Database())->getPdo() ;

            $req = $pdo->prepare("SELECT  SUM(`quantity`) as 'total'
                                  FROM `orderdetails` 
                                  JOIN orders on orders.id = orderdetails.order_id
                                  WHERE orders.user_id = :idUser
                                  AND orders.status = 'basket'");

            $req->execute(  [
                                'idUser' => $idUser,
                            ]) ;

            $result = $req->fetch() ;

            return $result['total'];
        }

     /***********************************************************************************************************
     *                                 TROUVE LE DETAIL D UNE COMMANDE
     * 
     ***********************************************************************************************************/
        public function findOrderDetails($idOrder)
        {
            $pdo = (new Database())->getPdo() ;

            $req = $pdo->prepare("SELECT orders.user_id,orders.orderDate,orders.deliveryDate,orders.deliveryAdress,orders.deliveryPostal,orders.deliveryCity,orders.total,orders.id,orderdetails.menu_id,menus.name,orderdetails.product_id,products.name as product_name,orderdetails.quantity,orderdetails.priceEach 
                                  FROM `orders`
                                  JOIN orderdetails ON orders.id = orderdetails.order_id 
                                  LEFT JOIN menus ON menus.id = orderdetails.menu_id 
                                  LEFT JOIN products ON products.id = orderdetails.product_id 
                                  WHERE orders.id = :idOrder
                                ");

            $req->execute(  [
                                ':idOrder' => $idOrder,
                            ]) ;

            return $req->fetchAll() ;

        }

    /****************************************************************************
     *                                 AJOUTER MENU AU PANIER
     * 
     ***************************************************************************/

        public function addmenusToBasket($idOrder, $idMenu, $quantity, $priceEach )
        {
            $pdo = (new Database())->getPdo() ;

            $query = $pdo->prepare("INSERT INTO orderdetails
                                    (`order_Id`, `menu_id`,`product_id`, `quantity`, `priceEach`)
                                    VALUES (:idOrder, :idMenu,NULL, :quantity, :priceEach)
                                    ON DUPLICATE KEY UPDATE 
                                    quantity = quantity + :quantity");

            $query->execute([
                                "idOrder" => $idOrder,
                                "idMenu" => $idMenu,
                                "quantity" => $quantity,
                                "priceEach" => $priceEach,
                            ]) ;
        }

        public function ismenusInBasket($idMenu,$idOrder){

            $pdo = (new Database())->getPdo() ;

            $req = $pdo->prepare("SELECT * 
                                  FROM `orderdetails` 
                                  JOIN orders on orders.id = orderdetails.order_id 
                                  WHERE `order_id` = :idOrder
                                  AND menu_id = :idMenu
                                  AND orders.status = 'basket'");

            $req->execute(  [
                                ':idMenu' => $idMenu,
                                ':idOrder' => $idOrder,
                            ]) ;

            return $req->fetchAll() ;
            
        }
    /*******************************************************************************************
     *                                 MODIFIE LA QUANTITE DE MENUS DANS LE PANIER
     * 
     *********************************************************************************************/

        public function updateQuantitymenusByBasket($idOrder,$idMenu,$quantity){
        
        $pdo = (new Database())->getPdo() ;

        $query = $pdo->prepare("UPDATE `orderdetails`
                                JOIN orders on orders.id = orderdetails.order_id
                                SET `quantity` = orderdetails.quantity + :quantity
                                WHERE `order_id` = :idOrder 
                                AND orderdetails.menu_id = :idMenu
                                AND orders.status = 'basket'");

        $query->execute([
                            "idOrder" => $idOrder,
                            "idMenu" => $idMenu,
                            "quantity" => $quantity,
                        ]) ;
        }
    
    /****************************************************************************
     *                                 AJOUTER PRODUIT AU PANIER
     * 
     ***************************************************************************/

    public function addproductsToBasket($idOrder, $idProduct, $quantity, $priceEach )
    {
        $pdo = (new Database())->getPdo() ;

        $query = $pdo->prepare("INSERT INTO orderdetails
                                (`order_Id`,`menu_id`, `product_id`, `quantity`, `priceEach`)
                                VALUES (:idOrder,NULL, :idProduct, :quantity, :priceEach)
                                ON DUPLICATE KEY UPDATE 
                                quantity = quantity + :quantity");

        $query->execute([
                            "idOrder" => $idOrder,
                            "idProduct" => $idProduct,
                            "quantity" => $quantity,
                            "priceEach" => $priceEach,
                        ]) ;
    }

    public function isproductsInBasket($idProduct,$idOrder){

        $pdo = (new Database())->getPdo() ;

        $req = $pdo->prepare("SELECT * 
                              FROM `orderdetails` 
                              JOIN orders on orders.id = orderdetails.order_id 
                              WHERE `order_id` = :idOrder
                              AND product_id = :idProduct
                              AND orders.status = 'basket'");

        $req->execute(  [
                            ':idProduct' => $idProduct,
                            ':idOrder' => $idOrder,
                        ]) ;

        return $req->fetchAll() ;
        
    }

     /*******************************************************************************************
     *                                 MODIFIE LA QUANTITE DE PRODUIT DANS LE PANIER
     * 
     *********************************************************************************************/

    public function updateQuantityproductsByBasket($idOrder,$idProduct,$quantity){
        
        $pdo = (new Database())->getPdo() ;

        $query = $pdo->prepare("UPDATE `orderdetails`
                                JOIN orders on orders.id = orderdetails.order_id
                                SET `quantity` = orderdetails.quantity + :quantity
                                WHERE `order_id` = :idOrder 
                                AND orderdetails.product_id = :idProduct
                                AND orders.status = 'basket'");

        $query->execute([
                            "idOrder" => $idOrder,
                            "idProduct" => $idProduct,
                            "quantity" => $quantity,
                        ]) ;
    }

    /****************************************************************************
     *                                 SUPPRIMER MENUS DU PANIER
     * 
     ***************************************************************************/

    public function deletemenusToBasket($idOrderdetails,$idMenu){
            $pdo = (new Database())->getPdo() ;
    
            $query = $pdo->prepare("DELETE FROM `orderdetails` 
                                    WHERE id = :idOrderdetails
                                    AND   menu_id  = :idMenu");
    
            $query->execute([
                "idOrderdetails" => $idOrderdetails,
                "idMenu" => $idMenu,
            ]) ;
    }

    /****************************************************************************
     *                                 SUPPRIMER PRODUIT DU PANIER
     * 
     ***************************************************************************/

    public function deleteproductsToBasket($idOrderdetails,$idProduct){
        $pdo = (new Database())->getPdo() ;

        $query = $pdo->prepare("DELETE FROM `orderdetails` 
                                WHERE id = :idOrderdetails
                                AND   product_id  = :idProduct");

        $query->execute([
            "idOrderdetails" => $idOrderdetails,
            "idProduct" => $idProduct,
        ]) ;
    }
        

    }