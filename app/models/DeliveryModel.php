<?php


    class DeliveryModel{

    
        /****************************************************************************
         *                                TROUVE LA DATE DE LIVRAISON 
         * 
         ***************************************************************************/
        public function findDeliveryDate($idOrder){
            $pdo = (new Database())->getPdo() ;
            $req = $pdo->prepare("
                SELECT `deliveryDate` 
                FROM `orders`
                WHERE `id` = :idOrder
            ") ;

            $req->execute(
                [
                    ":idOrder" => $idOrder ,
                ]) ;

            $result = $req->fetch() ;
            return $result['deliveryDate'];
        }


        /****************************************************************************
        *            TROUVER L'ADRESSE EN FONCTION DU NUMERO DE COMMANDE  
         * 
         ***************************************************************************/

        public function findAdressByOrder($idOrder){
            $pdo = (new Database())->getPdo() ;
            $req = $pdo->prepare("SELECT users.adress,users.codePostal,users.city
                                  FROM `orders` 
                                  JOIN users on users.id = orders.user_id 
                                  WHERE orders.id = :idOrder
            ") ;

            $req->execute(
                [
                    ":idOrder" => $idOrder ,
                ]) ;

            return $req->fetch() ;
        }

        /****************************************************************************
         *                                 METTRE A JOUR LA DATE DE LIVRAISON  
         * 
         ***************************************************************************/

            public function updateDeliveryDate($idOrder,$deliveryDate){
                $pdo = (new Database())->getPdo() ;
                $req = $pdo->prepare("
                                        UPDATE orders
                                        SET deliveryDate = :deliveryDate
                                        WHERE id = :idOrder
                                        AND status = 'basket' 
                                            ") ;

                $req->execute(
                    [
                        ":idOrder"         => $idOrder,
                        ":deliveryDate"    => $deliveryDate,
                    ]) ;
            }

        /****************************************************************************
         *                                 METTRE A JOUR LA DATE DE LIVRAISON  
         * 
         ***************************************************************************/

        public function updateDeliveryAdress($idOrder,$deliveryAdress,$deliveryPostal,$deliveryCity){
            $pdo = (new Database())->getPdo() ;
            $req = $pdo->prepare(" UPDATE orders
                                   SET deliveryAdress = :deliveryAdress , deliveryPostal = :deliveryPostal, deliveryCity = :deliveryCity
                                   WHERE id = :idOrder
                                   AND status = 'basket' 
                                        ") ;

            $req->execute(
                [
                    ":idOrder"         => $idOrder,
                    ":deliveryAdress"  => $deliveryAdress,
                    ":deliveryPostal"  => $deliveryPostal,
                    ":deliveryCity"    => $deliveryCity,
                ]) ;
        }

    }