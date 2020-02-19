<?php

class CguController{
    public function show(){
        return [
            "template" =>
                [
                    "folder" => "a_propos",
                    "file"   => "cgu",
                ],
        ];
    }

}