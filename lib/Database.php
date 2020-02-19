<?php

class Database{

    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
                    "mysql:host=localhost;dbname=petitdej;charset=UTF8",
                    "root",
                    "",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                            );
    }

    public function getPdo(){
        return $this->pdo;
    }
}

