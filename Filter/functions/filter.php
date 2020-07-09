<?php

// Функция получает массив данных из таблицы категорий

function get_category() {
    $query = "SELECT * FROM cats";
    $list_catagories = meadb_select($query);
    $array_cat = array();
    foreach ($list_catagories as $cat) {
        $array_cat[$cat['cat_id']] = $cat;
    }
    return $array_cat;
}

// Функция форматирует массив и готовит его к преобразованию в дерево категорий

function form_tree($mess)
{
    if (!is_array($mess)) {
        return false;
    }
    $tree = array();
    foreach ($mess as $value) {
        $tree[$value['parent_id']][] = $value;
    }
    return $tree;
}

// Функция строит дерево категорий любой степени вложенности

function build_tree($cats, $parent_id)
{
    if (is_array($cats) && isset($cats[$parent_id])) {
        $tree = '<ul>';
        foreach ($cats[$parent_id] as $cat) {
            $tree .= '<li class="item">';
            $tree .= '<a class="nav-link" href="?category=' . $cat['cat_id'] . '">' . $cat['cat_title'] . '</a>';
            $tree .= build_tree($cats, $cat['cat_id']);
            $tree .= '</li>';
        }
        $tree .= '</ul>';
    } else {
        return false;
    }
    return $tree;
}

// Для вывода всех товаров из дерева категорий функция получает id всех потомков

function cat_id($array, $id) {
    if(!$id) {
        return false;
    }
    $data = "";
    foreach ($array as $item) {
        if($item['parent_id'] == $id) {
            $data.= $item['cat_id'] . ",";
            $data.= cat_id($array, $item['cat_id']);
        }
    }
    return $data;
}

// Функция получает из БД возможные цвета в выбранной категории

function get_color_list($id) {
    $query = "SELECT DISTINCT aa.feature_base_color, p.product_cat_id FROM variations AS v 
    INNER JOIN products AS p ON (v.product_id=p.product_id) 
    INNER JOIN feature_variations AS fv ON (v.variation_id=fv.variation_id) 
    INNER JOIN feature_values AS aa ON (fv.feature_value_id=aa.feature_value_id) 
    WHERE p.product_cat_id=$id AND aa.feature_id=1 AND v.image_id > 0 ORDER BY aa.feature_base_color";
    $res = meadb_select($query);
    return $res;
}

// Функция формирует список цветов из выбранной категории

function build_color_list() {
    if(isset($_GET['category'])) {
        $item = $_GET['category'];
        $colors_list = get_color_list($item);
        $colors = "";
        foreach($colors_list as $c) {
            $colors .= '<option value="' . $c['feature_base_color'] . '">' . $c['feature_base_color']  . '</option>';
        }
        return $colors;
    }
}

// Функция получает из БД варианты согласно переданных параметров 

function get_filter_list($id, $i, $i_sort) {
    $query = "SELECT DISTINCT aa.feature_value, p.product_cat_id FROM variations AS v 
              INNER JOIN products AS p ON (v.product_id=p.product_id) 
              INNER JOIN feature_variations AS fv ON (v.variation_id=fv.variation_id) 
              INNER JOIN feature_values AS aa ON (fv.feature_value_id=aa.feature_value_id) 
              WHERE p.product_cat_id=$id AND aa.feature_id=$i AND v.image_id > 0 ORDER BY aa.feature_value $i_sort";
    $res = meadb_select($query);
    return $res;
}

// Функция формирует список размеров из выбранной категории

function build_size_list() {
    if(isset($_GET['category'])) {
        $item = $_GET['category'];
        $size_list = get_filter_list($item, 2, "DESC");
        $sizes = "";
        foreach($size_list as $s) {
            $sizes .= '<option value="' . $s['feature_value'] . '">' . $s['feature_value']  . '</option>';
        }
        return $sizes;
    }
}

// Функция формирует список тканей из выбранной категории

function build_textile_list() {
    if(isset($_GET['category'])) {
        $item = $_GET['category'];
        $textile_list = get_filter_list($item, 3, "ASC");
        $textiles = "";
        foreach($textile_list as $s) {
            $textiles .= '<option value="' . $s['feature_value'] . '">' . $s['feature_value']  . '</option>';
        }
        return $textiles;
    }
}

// Функция формирует список узоров из выбранной категории

function build_pattern_list() {
    if(isset($_GET['category'])) {
        $item = $_GET['category'];
        $patterns_list = get_filter_list($item, 4, "ASC");
        $patterns = "";
        foreach($patterns_list as $p) {
            $patterns .= '<option value="' . $p['feature_value'] . '">' . $p['feature_value']  . '</option>';
        }
        return $patterns;
    }
}

// Функция получает из БД минимальную и максимальную цену в выбранной категории

function get_price($i, $item) {
    $query = "SELECT " . $i . "(p.product_price) FROM products AS p WHERE p.product_cat_id=$item";
    $res = meadb_select($query);
    return $res;
}

// Функция выводит минимальную и максимальную цену в выбранной категории

function price($i) {
    if(isset($_GET['category'])) {
        $item = $_GET['category'];
        $res = get_price($i, $item);
        $price = "";
        foreach($res as $pr) {
            $price .= $pr[$i . "(p.product_price)"];
        }
        return $price;
    }
}

// Функция формирует список id категорий согласно их вложенности

function get_product_from_tree() {
    $categories = get_category();
    $item = $_GET['category'];
    $data = $item ."," . cat_id($categories, $item);
    $data = !$data ? $_GET['category'] : rtrim($data, ",");
    return $data;
}

// Функция получает данные товаров из БД согласно сформированному запросу

function get_product() {
    $query = get_filter_data();
    $res = meadb_select($query);
    return $res;
}

// Функция преобразует запросы в условие выборки данных

function add_filter_condition($where, $add, $and = true) {
    if($where) {
        if($and) $where .= " AND $add";
        else $where .= " OR $add";
    } else $where = $add;
    return $where;
}

// Функция преобразует запросы в условие сортировки данных

function add_filter_sort($sort, $add) {
    $sort .= " ORDER BY $add";
    return $sort;
}

// Функция формирует основную часть запроса к БД по товарам

function get_main_query() {
    return "SELECT DISTINCT p.product_title, i.image_src FROM variations AS v 
            INNER JOIN products AS p ON (v.product_id=p.product_id)  
            INNER JOIN images AS i ON (v.image_id=i.image_id) 
            INNER JOIN feature_variations AS fvt ON (v.variation_id=fvt.variation_id)
            INNER JOIN feature_values AS fv ON (fvt.feature_value_id=fv.feature_value_id)";
}

// Функция преобразует массив в строку

function to_string($items) {
    $data = "";
    foreach($items as $item) {
        $data .= '"' . $item . '",';
    }
    $data = rtrim($data, ","); 
    return $data;
}

// Функция валидирует введенное значение

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Функция получает данные POST и GET запросов и формирует полный запрос к БД

function get_filter_data() {
    $sql = get_main_query();
    $where = "";
    $sort = "";
    $data = array();
    if(isset($_GET)) {
        if(isset($_GET['category'])) {
            $cats = get_product_from_tree();
            $where = add_filter_condition($where, "p.product_cat_id IN(" . $cats . ")");
        }
    }
    if(isset($_POST)) {
        if(isset($_POST['sort1'])) {
            $data[] = $_POST['sort1'];
            $sort_cond = "";
            if($_POST['sort1'] == "price_asc") $sort_cond = "p.product_price ASC";
            if($_POST['sort1'] == "price_desc") $sort_cond = "p.product_price DESC";
            if($_POST['sort1'] == "popular") $sort_cond = "p.product_hits DESC";
            if($_POST['sort1'] == "date") $sort_cond = "p.product_date DESC";
            $sort = add_filter_sort($sort, $sort_cond);
        }
        if(isset($_POST['sort2'])) {
            $data[] = $_POST['sort2'];
            
        }
        if(isset($_POST['price_start']) || isset($_POST['price_end'])) {
            $start = test_input($_POST['price_start']);
            $end = test_input($_POST['price_end']);
            if($end == 0) {
                $where = add_filter_condition($where, "p.product_price BETWEEN " . $start . " AND 1000000");
            } elseif($start == 0) {
                $where = add_filter_condition($where, "p.product_price BETWEEN 0 AND " . $end);
            } else $where = add_filter_condition($where, "p.product_price BETWEEN " . $start . " AND " . $end);
        }
        if(isset($_POST['color'])) {
            if($_POST['color'][0] <> "all") {
                $items = $_POST['color'];
                $data = to_string($items);  
                $where = add_filter_condition($where, "fv.feature_base_color IN(" . $data . ")");
            }
            
        }
        if(isset($_POST['size'])) {
            if($_POST['size'][0] <> "all") {
                $items = $_POST['size'];
                $data = to_string($items);
                $where = add_filter_condition($where, "fv.feature_value IN(" . $data . ")");
            }            
        }
        if(isset($_POST['textile'])) {
            if($_POST['textile'][0] <> "all") {
                $items = $_POST['textile'];
                $data = to_string($items);
                $where = add_filter_condition($where, "fv.feature_value IN(" . $data . ")");
            }
        }
        if(isset($_POST['style'])) {
            if($_POST['style'][0] <> "all") {
                $data[] = $_POST['style'];
            }
        }
        if(isset($_POST['line'])) {
            if($_POST['line'][0] <> "all") {
                $data[] = $_POST['line'];
            }
        }
        if(isset($_POST['season'])) {
            if($_POST['season'][0] <> "all") {
                $data[] = $_POST['season'];
            }
        }
        if(isset($_POST['pattern'])) {
            if($_POST['pattern'][0] <> "all") {
                $items = $_POST['pattern'];
                $data = to_string($items);
                $where = add_filter_condition($where, "fv.feature_value IN(" . $data . ")");
            }
        }
        if(isset($_POST['fashion'])) {
            if($_POST['fashion'][0] <> "all") {
                $data[] = $_POST['fashion'];
            }
        }
        if(isset($_POST['details'])) {
            if($_POST['details'][0] <> "all") {
                $data[] = $_POST['details'];
            }
        }
    }
    if($where) {
        $sql .= " WHERE $where $sort LIMIT 30";
    } else $sql .= "$sort LIMIT 30";

    return $sql;
}

// Функция для вывода товаров согласно запроса из дерева категорий с учетом вложенности

function build_product_view() {
    $list_products = get_product();
    $products = "";
    foreach($list_products as $p) {
        $products .= '<div class="col-4">';
        $products .= '<div class="card text-center">';
        $products .= '<img class="card-img-top" width="100%" src="https://arjen.com.ua/images/products/' . $p['image_src'] . '" alt="' . $p['product_id'] . '" alt="' . $p['product_id'] . '">';
        $products .= '<div class="card-body">' . $p['product_title'] . '</div>';
        $products .= '</div></div>';
    }
    return $products;
}