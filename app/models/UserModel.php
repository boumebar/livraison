<?php

class UserModel{

    /****************************************************************************
     *            CREE UN NOUVEL UTILISATEUR DANS LA BDD
     * 
     ***************************************************************************/

     public function create($forname,$name,$adress,$codePostal,$city,$numTel =null,$email,$password){

        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("INSERT INTO `users`
                              (`forname`, `name`, `adress`, `codePostal`, `city`, `numTel`, `email`, `password`, `isAdmin`, `dateCreation`,`lastLogin`)
                              VALUES (:forname, :name, :adress, :codePostal, :city, :numTel, :email, :password, 0,now(),'1970-01-01 00:00:00')" );

        $req->execute([
            ':forname'     => $forname,
            ':name'        => $name,
            ':adress'      => $adress,
            ':codePostal'  => $codePostal,
            ':city'        => $city,
            ':numTel'      => $numTel,
            ':email'       => $email,
            ':password'    => $password,    
        ]);

        // return le dernier Id cree
        return $pdo->lastInsertId();
    }


    /****************************************************************************
     *            VERIFIE SI UN EMAIL EST DEJA UTILISE
     * 
     ***************************************************************************/


     public function isTaken($email){

        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("SELECT Count(*) as nbr
                              FROM users
                              WHERE email = :email ");

        $req->execute([
                ":email"    => $email,
        ]);

        $resultats = $req->fetch();
        
        return $resultats['nbr'] > 0;

     }


     /****************************************************************************
     *            RETROUVE UN EMAIL DANS LA BDD ET VERIFIE SI BON MOT DE PASSE
     * 
     ***************************************************************************/

     public function findEmailAndCheck($email,$password){
         $pdo = (new Database())->getPdo();

         $req = $pdo->prepare("SELECT *
                               FROM users
                               WHERE email = :email");

        $req->execute([
            ":email"  => $email,
        ]);

        $user = $req->fetch();
         
        if(!$user){
            throw new DomainException('Cet email n\'existe pas') ;
        }

        if(!password_verify($password,$user['password'])){
            throw new DomainException('Mot de passe invalide');
        }

        return $user;
     }


     /****************************************************************************
     *           CHERCHE UTILISATEUR PAR ID
     * 
     ***************************************************************************/


     public function findById($id){

        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("SELECT id,forname,name,adress,codePostal,city,numTel,email
                              FROM users
                              WHERE id = :id");
        
        $req->execute([
            ":id"  => $id
        ]);

        $user = $req->fetch();

        if(!$user){
            throw new DomainException("id inconnu");
        }

        return $user;
     }


     /****************************************************************************
     *           MET A JOUR LA DERNIERE CONNEXION
     * 
     ***************************************************************************/


     public function lastLogin($id){

        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("UPDATE users
                              SET lastLogin = now()
                              WHERE id = :id ");

        $req->execute([
            ":id"  => $id
        ]);
     }

      /****************************************************************************
     *           MODIFIER DONNEES UTILISATEUR
     * 
     ***************************************************************************/

    public function update($name,$forname,$email,$adress,$codePostal,$city,$phone){

        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("UPDATE users
                              SET name = :name , forname = :forname, email = :email,adress = :adress, codePostal = :codePostal, city = :city, numTel = :phone 
                              WHERE email = :email ");

        $req->execute([
            ":name"         =>  $name,
            ":forname"      =>  $forname,
            ":email"        =>  $email,
            ":adress"       =>  $adress,
            ":codePostal"   =>  $codePostal,
            ":city"         =>  $city,
            ":phone"        =>  $phone,
        ]);
    }

     /****************************************************************************
     *           VERIFIER ID EXISTE
     * 
     ***************************************************************************/

     public function idExist($id){
        $pdo = (new Database())->getPdo();

        $array = $pdo->query("SELECT id
                            FROM `users`");

        $array = $array->fetchAll();
        var_dump($array);

        return in_array($id,$array);

     }
}