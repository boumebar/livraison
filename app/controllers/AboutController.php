<?php


class AboutController{

    public function show(){

        return [
            "template"  =>
                [
                    "folder"  =>  "infos",
                    "file"    =>  "about_us"
                ]
        ];
    }
}