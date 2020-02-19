<?php

class UserSession
{

    public function __construct()
    {
         
        // demande s'il y a une session s'il y en pas 
         if(session_status() == PHP_SESSION_NONE)
         {
             // il la crée
             session_start();
         }
    }

     /****************************************************************************
     *                                  NOUVELLE SESSION
     * 
     ***************************************************************************/

    public function create($id, $forname, $name, $email, $isAdmin)
    {
        $_SESSION["user"] = 
            [
                'id'           => $id,
                'forname'      => $forname,
                'name'         => $name,
                'email'        => $email,
                'isAdmin'      => $isAdmin,        
            ];
    }

    /****************************************************************************
     *                                  VIDE ET DETRUIT LA SESSION
     * 
     ***************************************************************************/

    public function destroy()
    {
        $_SESSION = [];
        session_destroy();
    }

    
    /****************************************************************************
     *            renvoi true s'il y a quelque chose dans la session 
     * 
     ***************************************************************************/

    public function isAuthenticated()
    {
        return isset($_SESSION["user"]);
    }


     /****************************************************************************
     *            verifie si authentifié si oui retourne isAdmin
     * 
     ***************************************************************************/
 
    public function isAdmin()
    {
        if(!$this->isAuthenticated())
        {
            return false;
        }
        return $_SESSION["user"]["isAdmin"];
    }


    /****************************************************************************
     *            verifie si authentifié si oui retourne ID
     * 
     ***************************************************************************/

    public function getId()
    {
        if(!$this->isAuthenticated())
        {
            return false;
        }
        return $_SESSION["user"]["id"];
    }


    /****************************************************************************
     *            verifie si authentifié si oui retourne FORNAME
     * 
     ***************************************************************************/
    
    public function getForname()
    {
        if(!$this->isAuthenticated())
        {
            return false;
        }
        return $_SESSION["user"]["forname"];
    }



    /****************************************************************************
     *            verifie si authentifié si oui retourne NAME
     * 
     ***************************************************************************/

    public function getName()
    {
        if(!$this->isAuthenticated())
        {
            return false;
        }
        return $_SESSION["user"]["name"];
    }


    /****************************************************************************
     *            verifie si authentifié si oui retourne EMAIL
     * 
     ***************************************************************************/

    public function getEmail()
    {
        if(!$this->isAuthenticated())
        {
            return false;
        }
        return $_SESSION["user"]["email"];
    }
}