<?php

class FaqController{

    public function show(){
        return [
            "template" =>
                [
                    "folder"  =>  "infos",
                    "file"    =>  "faq",
                ],
            ];
    }
    
}