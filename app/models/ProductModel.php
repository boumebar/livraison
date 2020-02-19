<?php


class ProductModel{

     /****************************************************************************
     *                                 TROUVER TOUT LES PRODUITS
     * 
     ***************************************************************************/

    public function findAll(){

        $pdo = (new Database())->getPdo();

        $result = $pdo->query("SELECT *
                                FROM products
                                ORDER BY `products`.`category` asc");
        
        return $result->fetchAll();
    }

     /****************************************************************************
     *                                 TROUVER 3 PRODUITS AU HASARD
     * 
     ***************************************************************************/

    public function findRandomProducts(){

        $pdo = (new Database())->getPdo();

        $result = $pdo->query("SELECT `id`,`name`,`description`,`image`
                               FROM products
                               ORDER BY RAND()
                               LIMIT 3");
        
        return $result->fetchAll();
    }


    
    /****************************************************************************
     *                                  TROUVER UN PRODUIT SELON SON ID
     * 
     ***************************************************************************/

    public function findDetails($id){

        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("SELECT *
                              FROM products
                              WHERE id = :id ");
        
        $req->execute([
            ":id"  => $id,
        ]);
        return $req->fetch();
    }

     /****************************************************************************
     *                         TOUVE TOUTES LES CATEGORIES DE PRODUIT
     * 
     ***************************************************************************/

     public function findCategories(){

        $pdo = (new Database())->getPdo();
        $result = $pdo->query("SELECT category
                               FROM products");
        
        return $result->fetchAll();
     }

      /****************************************************************************
     *                         TOUVE TOUTES LES PRODUITS PAR CATEGORIE
     * 
     ***************************************************************************/

    public function findByCategory($category){

        $pdo = (new Database())->getPdo();
        
        $req = $pdo->prepare("SELECT id,name,description
                               FROM products
                               WHERE category = :category");
        
        $req->execute([
            ":category"    => $category,
        ]);
        
        return $req->fetchAll();
     }

    /****************************************************************************
     *                         TROUVE TOUTES LES PRODUITS PAR CATEGORIE 
     *                                  QUI SONT POUR LES MENUS
     * 
     ***************************************************************************/

    public function findByCategoryAndIsMenu($category){

        $pdo = (new Database())->getPdo();
        
        $req = $pdo->prepare("SELECT id,name,description
                               FROM products
                               WHERE category = :category AND forMenu = 1");
        
        $req->execute([
            ":category"    => $category,
        ]);
        
        return $req->fetchAll();
     }

      /****************************************************************************
     *                         TROUVE TOUTES LES PRODUITS PAR CATEGORIE 
     *                                  ET PAR IDMENU
     * 
     ***************************************************************************/

    public function findByCategoryAndMenu($idMenu,$category){

        $pdo = (new Database())->getPdo();
        
        $req = $pdo->prepare("SELECT name,id,quantity
                              FROM `menu_products`
                              JOIN products on `product_id` = products.id 
                              WHERE `menu_id` = :idMenu and products.category = :category");
        
        $req->execute([
            ":category"    => $category,
            ":idMenu"      => $idMenu,
        ]);
        
        return  $req->fetchAll();
        // return $result['name'];
     }

    /****************************************************************************
     *       
     *                   QUANtITE DE PRODUIT PAR MENU               
     * 
     ***************************************************************************/


     public function quantityProductsByMenu($idMenu,$idProduct){
        $pdo = (new Database())->getPdo();
        
        $req = $pdo->prepare("SELECT `quantity` 
                              FROM `menu_products`
                              WHERE `menu_id`=:idMenu and `product_id`=:idProduct");
        
        $req->execute([
            ":idMenu"       => $idMenu,
            ":idProduct"    => $idProduct,
        ]);
        
        return $req->fetch();
     }


     /****************************************************************************
     *                         TOUVE TOUT LES Ids DE PRODUIT
     * 
     ***************************************************************************/

    public function findAllIds(){

        $pdo = (new Database())->getPdo();
        $result = $pdo->query("SELECT id
                               FROM products");
        
        return $result->fetchAll();
     }

     /************************************************************************
     *                  TROUVE TOUT LES PRODUITS CORRESPONDAND A UN MENU
     * 
     * ************************************************************************/

    public function findByMenu($idMenu)
    {
        $pdo = (new Database())->getPdo();
        $req = $pdo->prepare("SELECT `menu_id`,`quantity`,menus.name,menus.price,products.name,products.category 
                            FROM menu_products 
                            JOIN menus ON menu_id = menus.id 
                            JOIN products ON menu_products.product_id = products.id
                            WHERE menu_id = :menu_id");

        $req->execute([
            ':menu_id'         => $idMenu,
        ]);
        return $req->fetchAll();
    }

      /************************************************************************
     *                  TROUVE TOUT LES PLATS CORRESPONDAND A UN MENU + tous les plats
     * 
     * ************************************************************************/

    public function findByMenuAndChecked($idMenu)
    {
        $pdo = (new Database())->getPdo();
        $req = $pdo->prepare("SELECT `id`,`name` ,`category`, (menu_products.menu_id is not null) as checked 
                              FROM products 
                              LEFT JOIN menu_products ON products.id = menu_products.product_id AND menu_products.menu_id = :idMenu");

        $req->execute([
            ':idMenu'         => $idMenu,
        ]);
        return $req->fetchAll();
    }


     /****************************************************************************
     *                                  CREE UN NOUVEAU PRODUIT
     * 
     ***************************************************************************/

    public function create($name,$description,$price,$category){

        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("INSERT INTO products
                            (name,description,price,image,category,forMenu)
                            VALUES (:name,:description,:price,'0',:category,0) ");

        $req->execute([
                ":name"         =>  $name,
                ":description"  =>  $description,
                ":price"        =>  floatval($price),
                ":category"     =>  $category,
        ]);

         // return le dernier Id cree
        return $pdo->lastInsertId();
    }


     /****************************************************************************
     *                         MODIFIE UNE IMAGE D'UN PRODUIT SELON SON ID
     * 
     ***************************************************************************/

    public function updateImg($id,$filename){
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("UPDATE products
                             SET image = :filename
                             WHERE id = :id");

        $req->execute([
            ":filename"   => $filename,
            ":id"         => $id,
        ]);
    }

    

    /****************************************************************************
     *                                 MODIFIE UN PRODUIT
     * 
     ***************************************************************************/

     public function update($id,$name,$description,$price,$category){
         $pdo = (new Database())->getPdo();

         $req = $pdo->prepare("UPDATE products
                               SET name = :name, description = :description, price = :price, category = :category
                               WHERE id = :id");

        $req->execute([
            ":id"            => $id,
            ":name"          => $name,
            ":description"   => $description,
            ":price"         => $price,
            ":category"      => $category,
        ]);
     }

     /****************************************************************************
     *                                  SUPPRIME UN PRODUIT
     * 
     ***************************************************************************/

     public function delete($id){
        $pdo = (new Database())->getPdo();

        $req = $pdo->prepare("DELETE from products
                                WHERE id = :id ");
        
        $req->execute([
            "id"   =>  $id,
        ]);

     }
}   