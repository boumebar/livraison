<?php

class ContactController{

     /****************************************************************************
     *                                FORMULAIRE DE CONTACT
     * 
     ***************************************************************************/

    public function show(){
        
        if(!$_POST){
            return [
                "template" =>
                    [
                        "folder" => "nav",
                        "file"   => "contact",
                    ],
            ];
        }else{
           
            $flashbag = new Flashbag();
            $error = false;

            if(!isset($_POST['name']) || empty($_POST['name'])){
                $error = true;
                $flashbag->addMessage('Veuillez entrer votre nom ');
            }
            
            if(!isset($_POST['forname']) || empty($_POST['forname'])){
                $error = true;
                $flashbag->addMessage('Veuillez entrer votre prénom ');
            }

            if(!isset($_POST['email']) || !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL)){
                $error = true;
                $flashbag->addMessage('Veuillez entrer un email valide');
            }

            if(!isset($_POST['message']) || empty($_POST['message'])){
                $error = true;
                $flashbag->addMessage('Veuillez entrer votre message ');
            }

            if($error){
                return [ 
                    "template" =>
                    [
                        "folder" => "nav",
                        "file"   => "contact",
                    ],
                ];
            }
            else{
                $name = htmlspecialchars($_POST['name']);
                $forname = htmlspecialchars($_POST['forname']);
                $email = htmlspecialchars($_POST['email']);
                $subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : "Pas renseigné" ;
                $message = htmlspecialchars($_POST['message']);

                $header = "MIME-Version: 1.0\r\n";
                $header .= 'From :"Boume"<boume0@gmail.com>'."\n";
                $header .= "Content-Type:text/html; charset='utf-8'"."\n";
                $header .= 'Content-Transfer-Encoding: 8bit';

                $msg = '
                    <html>
                        <body>
                            <div align="center">
                                <p>Nom de l`\'expéditeur : '. $name." ". $forname. '</p>
                                <p>Mail de l\'expéditeur :' .$email.'</p><br>
                                '.nl2br($message).'
                            </div>
                        </body>
                    </html>';

                mail("boume0301@gmail.com",$subject,$msg,$header );
                $flashbag->addMessage('Votre mail a bien été envoyé');

                return [ "redirect"  => "dej_contact_show",
                         "flashbag"  => $flashbag];

                

            }
            
        }

       
    }

}