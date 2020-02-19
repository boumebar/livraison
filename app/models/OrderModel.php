<?php

    class OrderModel
    {


        public function findBasketIdOrCreateByUser($idUser)
        {
            $pdo = (new Database())->getPdo() ;
            $req = $pdo->prepare("
                SELECT id
                FROM orders 
                WHERE user_id = :idUser
                AND status = 'basket' 
            ") ;

            $req->execute(
                [
                    ":idUser" => $idUser ,
                ]) ;

            $order = $req->fetch() ;

            if($order)
            {
                return $order['id'] ;
            }

            $req = $pdo->prepare("
                INSERT INTO orders
                (user_id , status, orderDate, deliveryDate,deliveryAdress,deliveryPostal,deliveryCity,total)
                 VALUES (:user_id, 'basket', NOW(), NOW(),'0','0','0','0') 
            ") ;

            $req->execute(
                [
                    ":user_id" => $idUser ,
                ]) ;

            return $pdo->lastInsertId() ;
        }

        public function findBasketId($idUser){
            $pdo = (new Database())->getPdo() ;
            $req = $pdo->prepare("SELECT id
                                  FROM orders 
                                  WHERE user_id = :idUser
                                  AND status = 'basket' 
            ") ;

            $req->execute(
                [
                    ":idUser" => $idUser ,
                ]) ;

            $result = $req->fetch() ;
            return $result['id'];
 
        }

        
        /***********************************************************************************************************
         *                                 TROUVE L HISTORIQUE DE TOUTES LES COMMANDES 
         * 
         ***********************************************************************************************************/
           
         public function findOrdersHistory(){
            $pdo = (new Database())->getPdo() ;

            $result = $pdo->query("SELECT * 
                                   FROM `orders` 
                                   ORDER BY `orders`.`status` DESC, `deliveryDate`");
            
            return $result->fetchAll() ;

        }

        /***********************************************************************************************************
         *                                 TROUVE L HISTORIQUE DES COMMANDES SELON USER_ID
         * 
         ***********************************************************************************************************/
            
         public function findOrdersHistoryByUser($idUser){
            $pdo = (new Database())->getPdo() ;

            $req = $pdo->prepare("SELECT `id`,`user_id`,`orderDate`,`status`,`total`
                                FROM `orders`
                                WHERE `user_id` = :idUser 
                                AND `status` = 'pending' OR `status` = 'confirmed' 
                                GROUP BY `id`");

            $req->execute(  [
                                ':idUser' => $idUser,
                            ]) ;

            return $req->fetchAll() ;

        }



        public function confirm($idUser, $idOrder)
        {
            $pdo = (new Database())->getPdo() ;
            $req = $pdo->prepare("
                                    UPDATE orders
                                    SET status = 'pending'
                                    WHERE user_Id = :idUser
                                    AND status = 'basket' 
                                    AND id = :idOrder
                                        ") ;

            $req->execute(
                [
                    "idUser" => $idUser ,
                    "idOrder" => $idOrder ,
                ]) ;
        }

        /****************************************************************************
         *                                 METTRE A JOUR LE TOTAL DE LA COMMANDE 
         * 
         ***************************************************************************/

        public function updateTotal($idOrder,$total){
            $pdo = (new Database())->getPdo() ;
            $req = $pdo->prepare("UPDATE `orders` 
                                SET `total` = :total
                                WHERE `id` = :idOrder
                                AND status = 'basket' 
                                        ") ;

            $req->execute(
                [
                    ":idOrder"         => $idOrder,
                    ":total"           => $total,
                ]) ;
        }

        /****************************************************************************
         *                                 VIDER LE PANIER 
         * 
         ***************************************************************************/

        public function emptyBasket($idUser){

            $pdo = (new Database())->getPdo() ;
            $req = $pdo->prepare("
                                    UPDATE orders
                                    SET status = 'cancelled'
                                    WHERE user_Id = :idUser
                                    AND status = 'basket' 
                                        ") ;

            $req->execute(
                [
                    "idUser" => $idUser ,
                ]) ;
        }



        // public function updatdeQuantity($idOrder,$idMenu,$quantity){
        //     $pdo = (new Database())->getPdo() ;
        //     $req = $pdo->prepare("
        //                             UPDATE orderdetails
        //                             SET quantity= :quantity
        //                             WHERE order_id = :idOrder
        //                             AND menu_id = :idMenu 
        //                                 ") ;

        //     $req->execute(
        //         [
        //             "idOrder" => $idOrder ,
        //             "idMenu" => $idMenu ,
        //             "quantity" => $quantity ,
        //         ]) ;
        // }
        

    }
