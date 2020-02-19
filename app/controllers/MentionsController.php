<?php

class MentionsController{
    public function show(){
        return [
            "template" =>
                [
                    "folder" => "a_propos",
                    "file"   => "mentions",
                ],
        ];
    }

}