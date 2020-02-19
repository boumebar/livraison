<?php

class CgvController{
    public function show(){
        return [
            "template" =>
                [
                    "folder" => "a_propos",
                    "file"   => "cgv",
                ],
        ];
    }

}