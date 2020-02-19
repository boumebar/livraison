<?php

class PartnerController{

    public function show(){

        return [
            "template"   =>
                [
                    "folder"  => "infos",
                    "file"    => "partner",
                ]
        ];
    }
}