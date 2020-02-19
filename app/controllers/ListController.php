<!-- <?php

require '../../lib/Router.php';

    function findProductsByCategory($category){
        $pdo = new PDO(
            "mysql:host=localhost;dbname=petitdej;charset=UTF8",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
                    );

        $req = $pdo->prepare("SELECT *
                                FROM products
                                WHERE category = :category ");

        $req->execute([
            "category"   => $category
        ]);

        $products = $req->fetchAll();

        return $products;
    }

    $products = findProductsByCategory($_POST['category']);

    $router = new Router();

    foreach ($products as $product):?>
    <article>
        <div>
            <a href="<?=$router->generateUrl('dej_products_showDetails').'?id='.$product['id']?>">
                <?php if($product['image'] === "0"):?>
                    <img src="../www/uploads/products/default_product.jpg" alt="default_picture">
                <?php else:?>
                    <img src="../www/uploads/products/<?=$product['image']?>" alt="product_picture">
                <?php endif;?>
                <h3><?=$product['name']?></h3>
            </a>
        </div>
        <div class="price">
            <p><?=$product['price']?>,00 €</p>
            <a href="">Ajouter au panier</a>
        </div> 
        <div class="price">
            <p><?=$product['description']?></p>
        </div>         
    </article>        
<?php endforeach;?>
 -->
