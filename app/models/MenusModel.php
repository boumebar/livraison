<?php


class MenusModel{

    /****************************************************************************
     *                                 TROUVER TOUT LES MENUS
     * 
     ***************************************************************************/

    public function findAll(){

        $pdo = (new Database())->getPdo();

        $result = $pdo->query("SELECT *
                                FROM menus");
        
        return $result->fetchAll();
    }


    /****************************************************************************
     *                                  TROUVER UN MENU SELON SON ID
     * 
     ***************************************************************************/

    public function findDetails($id){

        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("SELECT *
                              FROM menus
                              WHERE id = :id ");
        
        $req->execute([
            ":id"  => $id,
        ]);
        return $req->fetch();
    }


     /****************************************************************************
     *                                 TROUVER 3  MENUS AU HASARD
     * 
     ***************************************************************************/

    public function findRandomMenus(){

        $pdo = (new Database())->getPdo();

        $result = $pdo->query("SELECT `id`,`name`,`image`
                               FROM menus
                               ORDER BY RAND()
                               LIMIT 3"); 
        return $result->fetchAll();
    }

     /****************************************************************************
     *                                  TROUVER UN PRIX DE MENU SELON SON ID
     * 
     ***************************************************************************/

    public function findmenusPrice($id){
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("SELECT price
                              FROM menus
                              WHERE id = :id ");
        
        $req->execute([
            ":id"  => $id,
        ]);
        return $req->fetch(PDO::FETCH_COLUMN);

    }

    /****************************************************************************
     *                                  TROUVER UN PRIX DE PRODUIT SELON SON ID
     * 
     ***************************************************************************/

    public function findproductsPrice($id){
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("SELECT price
                              FROM products
                              WHERE id = :id ");
        
        $req->execute([
            ":id"  => $id,
        ]);
        return $req->fetch(PDO::FETCH_COLUMN);

    }

    /****************************************************************************
     *                                  CREE UN NOUVEAU MENU
     * 
     ***************************************************************************/

    public function create($name,$price){
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("INSERT INTO menus
                                (name,image,price)
                                VALUES (:name,'0',:price) ");
        
        $req->execute([
            ":name"  => $name,
            ":price" => $price,
        ]);
        
        // return le dernier Id cree
        return $pdo->lastInsertId();
    }

    // Changer l'image d'un menu ///////////////////////////////////////////

    public function updateImage($id,$filename){
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("UPDATE menus
                            SET image = :filename
                            WHERE id = :id ");
        
        $req->execute([
            ":filename"   =>  $filename,
            ":id"         =>  $id,
        ]);
    }


    /****************************************************************************
     *                                  AJOUTE UN PRODUIT A UN MENU 
     * 
     ***************************************************************************/

     public function addProducts($idMenu,$idProduct,$quantity){
         $pdo = (new Database())->getPdo();

         $req = $pdo->prepare("INSERT INTO menu_products
                                (menu_id,product_id,quantity)
                                VALUES (:idMenu,:idProduct,:quantity)");
        
        $req->execute([
            ":idMenu"   => $idMenu,
            ":idProduct"=> $idProduct,
            ":quantity" => $quantity,
        ]);

     }

       /**************************************************************
     *                  UPDATE DES PLATS A UN MENU
     * 
     * ************************************************************/

    public function updateProducts($idMenu,$idProduct,$quantity)
    {
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("UPDATE menu_products
                              SET product_id = :product_id , quantity = :quantity
                              WHERE menu_id = :idMenu and product_id = :product_id ");

        $req->execute([
            ":idMenu"      => $idMenu,
            ":product_id"  => $idProduct,
            ":quantity"    => $quantity,
        ]);

    }


      /**************************************************************
     *                  REMOVE DES PLATS A UN MENU
     * 
     * ************************************************************/

    public function deleteProducts($idMenu)
    {
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("DELETE FROM menu_products
                              WHERE menu_id = :idMenu ");

        $req->execute([
            ":idMenu"  => $idMenu,
        ]);

    }
    /****************************************************************************
     *                                  MODIFIER UN MENU
     * 
     ***************************************************************************/

     public function update($id,$name,$price){
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("UPDATE menus
                            SET name = :name, price = :price 
                            WHERE id = :id");
        
        $req->execute([
            ":id"        =>  $id,
            ":name"      =>  $name,
            ":price"     =>  $price,
        ]);



     }

    /****************************************************************************
     *                                  SUPPRIME UN MENU
     * 
     ***************************************************************************/

    public function delete($id){
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("DELETE from menus
                                WHERE id = :id ");
        
        $req->execute([
            "id"   =>  $id,
        ]);

     }

}