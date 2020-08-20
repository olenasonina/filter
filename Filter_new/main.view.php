<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="templates/main.css">
    <title>Filter</title>
</head>
<body>
    <?php
    $a = get_filter_data();
    var_dump($a);
    echo "<br><br>";    
    if(isset($_SESSION['cat'])) {
       echo "cat =>" . $_SESSION['cat']; 
    }
    echo "<br><br>"; 

    if(isset($_SESSION['ids'])) {
        var_dump($_SESSION['ids']); 
     }
    ?>
<div style="width: 80%; margin: 50px auto">
    <div class="container-fluid">
        <div class="row">
           
        <!-- Вывод категорий -->

            <div class="col-3">

            <!-- скрипт для вывода категорий в виде дерева категорий -->

            <?php
                $categories = get_category();
                $tree = form_tree($categories);
                $cat_tree = build_tree($tree, "0");
                echo $cat_tree;
            ?>
           
            </div>

            <!-- Вывод фильтров -->


            <div class="col-9">
                <div class="row">
                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="sort1">
                            <option value="popular">По популярности</option>
                            <option value="date">По новизне</option>
                            <option value="price_asc">По возрастанию цен</option>
                            <option value="price_desc">По убыванию цен</option>
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>   
                    
                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="sort2">
                            <option value="all">Все товары</option>
                            <option value="new">New</option>
                            <option value="limited">Limited</option>
                            <option value="sale">Sale</option>
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">   
                    <?php 
                    $start = price("MIN");
                    $end = price("MAX");
                    ?>
                            <label>Цена</label>
                            <label for="start">От</label>
                            <input id="start" type="number" name="price_start" value="" placeholder="<?php print_r($start) ?>">
                            <label for="end">До</label>
                            <input id="end" type="number" name="price_end" value="" placeholder="<?php print_r($end) ?>">            
                        
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="color[]" multiple>
                            <option value="all">Цвет</option>
                            <?= build_color_list() ?>
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="size[]" multiple>
                            <option value="all">Размер</option>
                            <?= build_size_list() ?>
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="textile[]" multiple>
                            <option value="all">Ткань</option>
                            <?= build_textile_list() ?>
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="style[]" multiple>
                            <option value="all">Стиль</option>                               
                            <?= build_style_list() ?>
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="line[]" multiple>
                            <option value="all">Линия</option>  
                            <?= build_line_list() ?>                  
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="season[]" multiple>
                            <option value="all">Сезон</option> 
                            <?= build_season_list() ?>                   
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="pattern[]" multiple>
                            <option value="all">Узор</option>                             
                            <?= build_pattern_list() ?>
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="fashion[]" multiple>
                            <option value="all">Фасон</option>
                            <?= build_fashion_list() ?>                
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>

                    <form action="" method="GET" style="display: flex; flex-direction: column">
                        <select class="select" name="details[]" multiple>
                            <option value="all">Детали</option>  
                            <?= build_detail_list() ?>                  
                        </select>
                        <button class="mt-5" type="submit">Применить</button>
                    </form>
                </div>

                <!-- Вывод товара -->

                <div class="row">

                <!-- скрипт для вывода товара согласно выбранной категории из дерева категорий с учетом их вложенности -->

                    <?php 
                    $products = build_product_view();
                    echo $products;
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="templates/jquery-3.4.1.min.js"></script>
<script src="templates/main.js"></script>
</body>
</html>