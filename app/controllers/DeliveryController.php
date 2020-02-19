<?php


class DeliveryController{

      /****************************************************************************
     *                                 PAGE de ZONE DE LIVRAISON
     * 
     ***************************************************************************/

    public function show(){

        return [
            "template"  => 
                [
                    "folder"  => "delivery",
                    "file"    => "delivery",
                ]
        ];
    }


      /****************************************************************************
     *                                 CHOIX DES OPTIONS DE LIVRAISON 
     * 
     ***************************************************************************/
    public function choice(){

        $userSession = new UserSession();
        $flashbag = new Flashbag() ;
        $orderModel = new OrderModel();
        $model = new DeliveryModel();

        // Trouve l 'id de la commande dans le panier
        $idOrder = $orderModel->findBasketId($userSession->getId());
        
        // adresse de celui qui commande
        $adress = $model->findAdressByOrder($idOrder);

        // si pas connecté
        if(! $userSession->isAuthenticated())
        {
            $flashbag->addMessage("Merci de vous connecter pour passer une commande") ;
            return ["redirect" => "dej_user_login"] ;
        }

        if(!$_POST){
            return [
                "template"  => 
                    [
                        "folder"  => "delivery",
                        "file"    => "choicedelivery",
                    ],
                "adress"   => $adress,
            ];
        }
        else{
            $error = false;

            // si pas d'adresse
            if(!isset($_POST['adress']) || empty(trim($_POST['adress']))){
                $error = true;
                $flashbag->addMessage('Vous devez entrer une adresse');
            }
            // si code postale non valide et pas de paris 
            if(!isset($_POST['codePostal']) || empty(trim($_POST['codePostal'])) || !ctype_digit($_POST['codePostal']) || (strlen($_POST['codePostal'])!=5) || (substr($_POST['codePostal'],0,3) != '750') ){
                $error = true;
                $flashbag->addMessage('Vous devez entrer un code postal valide commençant par 75 (5 chiffres uniquement)');
            }
            // si ville non valide 
            if(!isset($_POST['city']) || empty(trim($_POST['city'])) || strtolower($_POST['city']) != 'paris'){
                $error = true;
                $flashbag->addMessage('Nous livrons uniquement paris (75)');
            }
           
            // si date non valide 
            if(!isset($_POST['year']) || !ctype_digit($_POST['year']) || !isset($_POST['month']) || ($_POST['month'] > 12) || !ctype_digit($_POST['month']) || !isset($_POST['day']) || !ctype_digit($_POST['day']) || ($_POST['day'] > 31)|| !isset($_POST['hour']) || !ctype_digit($_POST['hour']) || ($_POST['hour'] > 24) || !isset($_POST['minutes']) || !ctype_digit($_POST['minutes']) || ($_POST['minutes'] > 60)){
                $error = true;
                $flashbag->addMessage('Une erreur s\'est produite ');
            }
            else{
                // si heure non valide 
                if($_POST['hour'] < 5 || $_POST['hour'] > 14){
                    $error = true;
                    $flashbag->addMessage('Désolé nous ne livrons que de 5h à 14h');
                }

                // fuseau horaire paris
                date_default_timezone_set('Europe/Paris');
                
                // Heure de livraison
                $deliveryHour = $_POST['hour'];
                // minutes de livraison
                $deliveryMinutes = $_POST['minutes'];
                
                // Heure de livraison entiere
                $deliveryHourAndMinutes = $deliveryHour.':'.$deliveryMinutes;
                // Date de livraison 
                $deliveryDate = $_POST['year'].'-'.$_POST['month'].'-'.$_POST['day'];
                // change le format de la date 
                $deliveryDate = (new DateTime($deliveryDate))->format('Y-m-d');
                // date et heure de livraison 
                $deliveryDateAndHour = $deliveryDate. ' ' . $deliveryHourAndMinutes;
                
                // si date de livraison avant date du jour alors renvoyer la page
                if($deliveryDate <= (new DateTime())->format('Y-m-d')){
                    $error = true;
                    $flashbag->addMessage("Seules les réservations à partir du lendemain sont possibles ") ;
                }
            }

            // si erreur 
            if($error){
                return [
                    "redirect"  => "dej_delivery_choice",
                ];
            }

            try{
                 // mets a jour la date de livraison dans la BDD
                $model->updateDeliveryDate($idOrder,$deliveryDateAndHour);

                // mets a jour l'adresse de livraison dans la BDD
                $model->updateDeliveryAdress($idOrder,strtolower($_POST['adress']),$_POST['codePostal'],ucfirst($_POST['city']));
            }
            catch(Exception $e){
                echo 'Une erreur est survenue';
            }
           
        }
        return [
            "redirect"       => "dej_order_confirm",
        ];
    }
}