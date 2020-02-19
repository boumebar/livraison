<?php

class Flashbag
{

    
    /****************************************************************************
     *            CONSTRUIT SESSION
     * 
     ***************************************************************************/

    public function __construct()
    {
        // demande s'il y a une session s'il y en pas 
        if(session_status() == PHP_SESSION_NONE)
        {
            // il la crée
            session_start();
        }

        // verifie dans $_SESSION si case flashbag sinon la cree
        if(!array_key_exists("flashbag",$_SESSION))
        {
            $_SESSION["flashbag"] = [];
        }
         // verifie dans $_SESSION si case nbrProducts sinon la cree
         if(!array_key_exists("nbrProducts",$_SESSION))
         {
             $_SESSION["nbrProducts"] = [];
         }
    }

    /****************************************************************************
     *            AJOUTE UN MESSAGE
     * 
     ***************************************************************************/ 

    public function addMessage($message)
    {
        $_SESSION["flashbag"][] = $message;
    }


    /****************************************************************************
     *            RECUPERE TOUS LES MESSAGES ET LES EFFACE
     * 
     ***************************************************************************/

    public function getAllMessages()
    {
        $allMessages = $_SESSION["flashbag"];
        $_SESSION["flashbag"] = [];

        return $allMessages;
    }
}