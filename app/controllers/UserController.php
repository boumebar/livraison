<?php


class UserController{

    /****************************************************************************
    *                           CREE UN NOUVEL UTILISATEUR DANS LA BDD
     * 
     ***************************************************************************/


    public function create()
    {
        $flashbag = new Flashbag();
        //    var_dump($_POST);
        //    exit();

        if(!$_POST){

            return [
                "template" =>
                    [
                        "folder" => "user",
                        "file"   => "create",
                    ],
                ];
        }
        else{

            $model = new UserModel();
            $error = false;

            if(!isset($_POST['forname']) || empty(trim($_POST['forname']))){
                $error = true;
                $flashbag->addMessage('Veuillez entrer votre prénom');
            }

            if(!isset($_POST['name']) || empty(trim($_POST['name']))){
                $error = true;
                $flashbag->addMessage('Veuillez entrer votre nom');
            }

            if(!isset($_POST['adress']) || empty(trim($_POST['adress']))){
                $error = true;
                $flashbag->addMessage("Veillez entrer votre adresse");
            }

            if(!isset($_POST['codePostal']) || empty(trim($_POST['codePostal']))){
                $error = true;
                $flashbag->addMessage('Veuillez entrer votre code postale');
            }

            if(!isset($_POST['city']) || empty(trim($_POST['city']))){
                $error = true;
                $flashbag->addMessage('Veuillez entrer votre ville');
            }
            
            if(!isset($_POST['phone']) || empty(trim($_POST['phone'])) || !is_numeric($_POST['phone'])){
                $error = true;
                $flashbag->addMessage('Veuillez entrer votre telephone au format 0612XXXX87');
            }

            if(!isset($_POST['email']) || !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
                $error = true;
                $flashbag->addMessage('Veuillez entrer un email valide');
            }elseif($model->isTaken($_POST['email'])){
                $error = true;
                $flashbag->addMessage('Cet Email est déja utilisé');
            }

            if(!isset($_POST['password']) || empty(trim($_POST['password']))){
                $error =true;
                $flashbag->addMessage('Veuillez entrer un mot de passe');
            }elseif(mb_strlen(trim($_POST['password'])) < 8){
                $error = true;
                $flashbag->addMessage('Le mot de passe doit comporter 8 caractères au minimum');
            }elseif($_POST['password'] !== $_POST['Password']){
                $error = true;
                $flashbag->addMessage('Les 2 mots de passe ne sont pas identiques !');
            }


            if($error){
                return [
                    "redirect"  => "dej_user_create",
                ];
            }

            try{
                $model->create(trim($_POST['forname']),trim($_POST['name']),trim($_POST['adress']),trim($_POST['codePostal']),trim($_POST['city']),trim($_POST['phone']),$_POST['email'],password_hash($_POST['password'],PASSWORD_BCRYPT));
            }
            catch(Exception $e)
            {
               // die(var_dump($e));
                if($e->getCode() == 23000)
                {
                    $flashbag->addMessage("Cet Email est déjà pris");
                }
                else{
                    $flashbag->addMessage("Impossible d'insérer en raison d'une erreur inconnue.");
                }
            }
            $flashbag->addMessage('Votre compte a bien été créé');
        }


        return [
            "redirect"  => "dej_user_login",
            "flashbag"  => $flashbag,
                ];

    }


     /****************************************************************************
     *                                  INFOS UTILISATEUR PAR SON ID 
     * 
     ***************************************************************************/

    public function compte(){

        $user = new UserSession();
        $model = new UserModel();
        $OrderModel = new OrderModel();
        

        if(isset($_GET['id']) && $user->isAuthenticated() && $_GET['id'] == $user->getId()){

            $userDetails = $model->findById($_GET['id']);

            if($user->isAdmin()){
                // toute les commandes
                $ordersHistory = $OrderModel->findOrdersHistory();
            }
            else{
                // uniquement commandes de l'utilisateur connecté
                $ordersHistory = $OrderModel->findOrdersHistoryByUser($_GET['id']);
            }
            
            return [
                "template"   => 
                        [
                            "folder"   =>  "user",
                            "file"     =>  "compte",
                        ],
                "user"           => $user,
                "userDetails"    => $userDetails,
                "ordersHistory"  => $ordersHistory,

                    ];
        }
        
        else{
            $user->destroy();

            $flashbag = new Flashbag();
            $flashbag->addMessage("Vous n'êtes pas connecté ");

            return [ "redirect"  => "dej_user_login"];
        }
    }


    
    /****************************************************************************
     *                                  CONNEXION
     * 
     ***************************************************************************/

    public function login(){
        
        if(isset($_POST['email']) && isset($_POST['password'])){

            $model = new UserModel();

            try{
                $user = $model->findEmailAndCheck($_POST['email'], $_POST['password']);
            }
            catch(Exception $e){
                // var_dump($e->getMessage());
                // exit();
                $flashbag = new Flashbag();
                // message d'erreur 
                $flashbag->addMessage($e->getMessage());
                // redirige vers login
                return ["redirect" => "dej_user_login"] ;
            }
            
            $model->lastLogin($user['id']);
            $userSession = new UserSession();
            $userSession->create($user['id'],$user['forname'],$user['name'],$user['email'],$user['isAdmin']);
            

            $flashbag = new Flashbag();
            $flashbag->addMessage('Bienvenue '.$user['forname']);
            return ["redirect"  => "dej_home_main"];
        
        }

        return [
                "template"  =>
                    [
                        "folder" => "user",
                        "file"   => "login",
                    ],
                ];
    }


     /****************************************************************************
     *                                  DECONNEXION
     * 
     ***************************************************************************/

     public function logout(){
        $session = new UserSession();
        $session->destroy();
        return ['redirect'  => 'dej_home_main'];
     }

     /****************************************************************************
     *                                  MODIFIER
     * 
     ***************************************************************************/

     public function update()
     {
         $user = new UserSession();
         $flashbag = new Flashbag();
        
        if(isset($_GET['id']) && $user->isAuthenticated() && $_GET['id'] == $user->getId()){
            $oldInfos = $this->compte($_GET['id']);
            $oldInfos = $oldInfos['userDetails'];
        
            $model = new UserModel();

            if($_POST)
            {
                $error = false;

                if(!isset($_POST['forname']) || empty(trim($_POST['forname']))){
                    $error = true;
                    $flashbag->addMessage('Veuillez entrer votre prénom');
                }

                if(!isset($_POST['name']) || empty(trim($_POST['name']))){
                    $error = true;
                    $flashbag->addMessage('Veuillez entrer votre nom');
                }

                if(!isset($_POST['adress']) || empty(trim($_POST['adress']))){
                    $error = true;
                    $flashbag->addMessage("Veillez entrer votre adresse");
                }

                if(!isset($_POST['codePostal']) || empty(trim($_POST['codePostal']))){
                    $error = true;
                    $flashbag->addMessage('Veuillez entrer votre code postale');
                }

                if(!isset($_POST['city']) || empty(trim($_POST['city']))){
                    $error = true;
                    $flashbag->addMessage('Veuillez entrer votre ville');
                }
                
                if(!isset($_POST['phone']) || empty(trim($_POST['phone'])) || !is_numeric($_POST['phone'])){
                    $error = true;
                    $flashbag->addMessage('Veuillez entrer votre telephone au format 0612XXXX87');
                }

                if(!isset($_POST['email']) || !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
                    $error = true;
                    $flashbag->addMessage('Veuillez entrer un email valide');
                }elseif($model->isTaken($_POST['email']) && $_POST['email'] !== $oldInfos['email']){
                    $error = true;
                    $flashbag->addMessage('Cet Email est déja utilisé');
                }

                if($error){
                    // echo 'je suis arriver la ';
                    // exit();

                    return [
                        "template"  => 
                            [
                                "folder"   => "user",
                                "file"     => "update",
                            ],
                        "oldInfos" => $oldInfos,
                    ];
                    
                }

                try{
                    $model->update(trim($_POST['name']),trim($_POST['forname']),trim($_POST['email']),trim($_POST['adress']),trim($_POST['codePostal']),trim($_POST['city']),$_POST['phone']);
                }
                catch(Exception $e)
                {
                    if($e->getCode() == 23000)
                    {
                        $flashbag->addMessage("Cet Email est déjà pris");
                    }
                    else{
                        $flashbag->addMessage("Impossible d'insérer en raison d'une erreur inconnue.");
                    }
                }
                $flashbag->addMessage("Votre compte a bien été modifié");
                $userDetails = $model->findById($_GET['id']);
                $_SESSION['user']['name'] = $userDetails['name'];
                $_SESSION['user']['forname'] = $userDetails['forname'];


            }
            elseif(isset($_GET['id'])){
                return [
                    "template"  => 
                    [
                        "folder"   =>  "user",
                        "file"     =>  "update",
                    ],
                    "oldInfos"  => $oldInfos,
                ];
            }

            
            $OrderModel = new OrderModel();

            // recupere l'historique des commandes pour pouvoir l'afficher
            $ordersHistory = $OrderModel->findOrdersHistory($_GET['id']);

            return [
                "template"  => 
                    [
                        "folder"   =>  "user",
                        "file"     =>  "compte",
                    ],
                "flashbag"      => $flashbag,
                "user"          => $user,
                "userDetails"   => $userDetails,
                "ordersHistory" => $ordersHistory,
                ];
        }
        else{
            $userSession->destroy();
            $flashbag->addMessage("Vous n'êtes pas connecté ");

            return [ "redirect"  => "dej_user_login"];
        }
     }
}

