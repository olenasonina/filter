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
    $ids = get_product_from_tree_s($id);
    $dop_where = "p.product_cat_id IN($ids) AND fv.feature_id=1 AND v.image_id > 0 AND p.product_not_active=0 AND p.product_published=1";
    $query = "SELECT DISTINCT fv.feature_base_color_id, c.color_value, p.product_cat_id FROM variations AS v 
    INNER JOIN products AS p ON (v.product_id=p.product_id) 
    INNER JOIN feature_variations AS fvt ON (v.variation_id=fvt.variation_id) 
    INNER JOIN feature_values AS fv ON (fvt.feature_value_id=fv.feature_value_id)
    INNER JOIN colors AS c ON (fv.feature_base_color_id=c.color_id)";
    $dop_sort = " ORDER BY c.color_value";
    if($dop_where) {
        $query .= " WHERE $dop_where $dop_sort";
    } 
    $res = meadb_select($query);
    return $res;
}

// Функция формирует список цветов из выбранной категории

function build_color_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $colors_list = get_color_list($item);
        $colors = "";
        for($i=0; $i < count($colors_list); $i++) {
            $colors .= 
            '<div><input class="check" type="checkbox" id="color' . $i . '" name="color[]" value="' . $colors_list[$i]['feature_base_color_id'] . '" />
            <label for="color' . $i . '">' . $colors_list[$i]['color_value'] . '</label></div>';
        }
        return $colors;
    }
}

// Функция получает из БД варианты согласно переданных параметров 

function get_filter_list($id, $i, $i_sort) {
    $ids = get_product_from_tree_s($id);
    $dop_where = "p.product_cat_id IN($ids) AND fv.feature_id=$i AND v.image_id > 0 AND p.product_not_active=0 AND p.product_published=1";
    $query = "SELECT DISTINCT fv.feature_value_id, fv.feature_value, p.product_cat_id FROM variations AS v 
              INNER JOIN products AS p ON (v.product_id=p.product_id) 
              INNER JOIN feature_variations AS fvt ON (v.variation_id=fvt.variation_id) 
              INNER JOIN feature_values AS fv ON (fvt.feature_value_id=fv.feature_value_id)";
    $dop_sort = " ORDER BY fv.feature_value $i_sort";
    if($dop_where) {
        $query .= " WHERE $dop_where $dop_sort";
    } 
    $res = meadb_select($query);
    return $res;
}

// Функция получает из БД варианты детальных характеристик согласно переданных параметров 

function get_filter_list2($id, $i, $i_sort) {
    $ids = get_product_from_tree_s($id);
    $dop_where = "p.product_cat_id IN($ids) AND fdv.feature_detail_id='" . $i . "' AND v.image_id > 0 AND p.product_not_active=0 AND p.product_published=1";
    $query = "SELECT DISTINCT fdv.feature_detail_value, fdv.feature_details_values_id FROM products_details AS pd 
              INNER JOIN products AS p ON (pd.product_id=p.product_id)
              INNER JOIN variations AS v ON (p.product_id=v.product_id)              
              INNER JOIN feature_details_values AS fdv ON (pd.feature_details_values_id=fdv.feature_details_values_id)
              INNER JOIN feature_details AS fd ON (fdv.feature_detail_id=fd.feature_detail_id)";
    $dop_sort = " ORDER BY fdv.feature_detail_value $i_sort";
    if($dop_where) {
        $query .= " WHERE $dop_where $dop_sort";
    } 
    $res = meadb_select($query);
    return $res;
}

// НЕДОПИСАННАЯ ФУНКЦИЯ

function build_filter_list2($filter) {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $filter_list = get_filter_list2($item, $filter, "DESC");
        $filters = "";
        foreach($filter_list as $f) {
            $filters .= '<option value="' . $f['feature_value'] . '">' . $f['feature_value']  . '</option>';
        }
        return $filters;
    }
}

// Функция формирует список размеров из выбранной категории

function build_size_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $size_list = get_filter_list($item, 2, "DESC");
        $sizes = "";
        for($i=0; $i < count($size_list); $i++) {
            $sizes .= 
            '<div><input class="check" type="checkbox" id="size' . $i . '" name="size[]" value="' . $size_list[$i]['feature_value_id'] . '" />
            <label for="size' . $i . '">' . $size_list[$i]['feature_value'] . '</label></div>';
        }
        
        return $sizes;
    }
}

// Функция формирует список тканей из выбранной категории

function build_textile_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $textile_list = get_filter_list($item, 3, "ASC");
        $textiles = "";
        for($i=0; $i < count($textile_list); $i++) {
            $textiles .= 
            '<div><input class="check" type="checkbox" id="textile' . $i . '" name="textile[]" value="' . $textile_list[$i]['feature_value_id'] . '" />
            <label for="textile' . $i . '">' . $textile_list[$i]['feature_value'] . '</label></div>';
        }
        
        return $textiles;
    }
}

// Функция формирует список узоров из выбранной категории

function build_pattern_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $patterns_list = get_filter_list($item, 4, "ASC");
        $patterns = "";
        for($i=0; $i < count($patterns_list); $i++) {
            $patterns .= 
            '<div><input class="check" type="checkbox" id="pattern' . $i . '" name="pattern[]" value="' . $patterns_list[$i]['feature_value_id'] . '" />
            <label for="pattern' . $i . '">' . $patterns_list[$i]['feature_value'] . '</label></div>';
        }
       
        return $patterns;
    }
}

// Функция формирует список стилей из выбранной категории

function build_style_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $style_list = get_filter_list2($item, 21, "ASC");
        $styles = "";
        for($i=0; $i < count($style_list); $i++) {
            $styles .= 
            '<div><input class="check" type="checkbox" id="style' . $i . '" name="style[]" value="' . $style_list[$i]['feature_details_values_id'] . '" />
            <label for="style' . $i . '">' . $style_list[$i]['feature_detail_value'] . '</label></div>';
        }
    
        return $styles;
    }
}

// Функция формирует список линий из выбранной категории

function build_line_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $line_list = get_filter_list2($item, 22, "ASC");
        $lines = "";
        for($i=0; $i < count($line_list); $i++) {
            $lines .= 
            '<div><input class="check" type="checkbox" id="line' . $i . '" name="line[]" value="' . $line_list[$i]['feature_details_values_id'] . '" />
            <label for="line' . $i . '">' . $line_list[$i]['feature_detail_value'] . '</label></div>';
        }
        return $lines;
    }
}

// Функция формирует список сезонов из выбранной категории

function build_season_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $season_list = get_filter_list2($item, 23, "ASC");
        $seasons = "";
        for($i=0; $i < count($season_list); $i++) {
            $seasons .= 
            '<div><input class="check" type="checkbox" id="season' . $i . '" name="season[]" value="' . $season_list[$i]['feature_details_values_id'] . '" />
            <label for="season' . $i . '">' . $season_list[$i]['feature_detail_value'] . '</label></div>';
        }
       
        return $seasons;
    }
}

// Функция формирует список фасонов из выбранной категории

function build_fashion_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $fashion_list = get_filter_list2($item, 9, "ASC");
        $fashions = "";
        for($i=0; $i < count($fashion_list); $i++) {
            $fashions .= 
            '<div><input class="check" type="checkbox" id="fashion' . $i . '" name="fashion[]" value="' . $fashion_list[$i]['feature_details_values_id'] . '" />
            <label for="fashion' . $i . '">' . $fashion_list[$i]['feature_detail_value'] . '</label></div>';
        }
        
        return $fashions;
    }
}

// Функция формирует список деталей из выбранной категории

function build_detail_list() {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $detail_list = get_filter_list2($item, 24, "ASC");
        $details = "";
        for($i=0; $i < count($detail_list); $i++) {
            $details .= 
            '<div><input class="check" type="checkbox" id="details' . $i . '" name="details[]" value="' . $detail_list[$i]['feature_details_values_id'] . '" />
            <label for="details' . $i . '">' . $detail_list[$i]['feature_detail_value'] . '</label></div>';
        }
        
        return $details;
    }
}

// Функция получает из БД минимальную и максимальную цену в выбранной категории

function get_price($i, $item) {
    $query = "SELECT " . $i . "(p.product_price_and_discount) FROM products AS p WHERE p.product_cat_id=$item AND p.product_not_active=0 AND p.product_published=1";
    $res = meadb_select($query);
    return $res;
}

// Функция выводит минимальную и максимальную цену в выбранной категории

function price($i) {
    if(isset($_GET['category'])) {
        $item = (int)$_GET['category'];
        $res = get_price($i, $item);
        $price = "";
        foreach($res as $pr) {
            $price .= $pr[$i . "(p.product_price_and_discount)"];
        }
        return $price;
    }
}

// Функция формирует список id категорий согласно их вложенности

function get_product_from_tree() {
    $categories = get_category();
    $item = (int)$_GET['category'];
    $data = $item ."," . cat_id($categories, $item);
    $data = !$data ? $item : rtrim($data, ",");
    return $data;
}

function get_product_from_tree_s() {
    $categories = get_category();
    $item = (int)$_GET['category'];
    $data = $item ."," . cat_id($categories, $item);
    $data = !$data ? $item : rtrim($data, ",");
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
    return "SELECT DISTINCT p.product_title, i.image_src FROM feature_variations AS fvt
            INNER JOIN variations AS v ON (fvt.variation_id=v.variation_id) 
            INNER JOIN products AS p ON (v.product_id=p.product_id)                       
            INNER JOIN images AS i ON (v.image_id=i.image_id)             
            INNER JOIN feature_values AS fv ON (fvt.feature_value_id=fv.feature_value_id)
            LEFT OUTER JOIN products_details AS pd ON (p.product_id = pd.product_id)
            LEFT OUTER JOIN feature_details_values AS fdv ON (pd.feature_details_values_id=fdv.feature_details_values_id)";
}

// Функция преобразует массив в строку

function to_string($items) {
    $data = "";
    foreach($items as $item) {
        $data .= $item . ',';
    }
    $data = rtrim($data, ","); 
    return $data;
}

function to_string2($items) {
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

// Функция получает данные GET запросов и формирует полный запрос к БД

function get_filter_data() {
    $sql = get_main_query();
    $where = "p.product_not_active=0 AND p.product_published=1 ";
    $sort = "";
    $datasort2 = [
        'sale' => 1,
        'new' => 2,
        'limited' => 3
    ];
    if(isset($_GET)) {
        if(isset($_GET['category'])) {
                $cats = get_product_from_tree();
                $where = add_filter_condition($where, "p.product_cat_id IN(" . $_GET['category'] . ")");
        }
        
        if(isset($_GET['sort1'])) {
            $sort_cond = "";
            if($_GET['sort1'] == "price_asc") $sort_cond = "p.product_price_and_discount ASC";
            if($_GET['sort1'] == "price_desc") $sort_cond = "p.product_price_and_discount DESC";
            if($_GET['sort1'] == "popular") $sort_cond = "p.product_hits DESC";
            if($_GET['sort1'] == "date") $sort_cond = "p.product_date DESC";
            $sort = add_filter_sort($sort, $sort_cond);
        } else $sort = add_filter_sort($sort, "p.product_hits DESC");

        if(isset($_GET['sort2'])) {
            $data = 0;
            if($_GET['sort2'] <> "all") {
                $item = $_GET['sort2'];
                $data = $datasort2[$item];
                $where = add_filter_condition($where, "p.product_top LIKE '%$data%'");
            }
        }

        if(isset($_GET['price_start']) || isset($_GET['price_end'])) {
            $start = test_input($_GET['price_start']);
            $end = test_input($_GET['price_end']);
            if($end == 0 && $start <> 0) {
                $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN " . $start . " AND 1000000");
            } elseif($start == 0 && $end <> 0) {
                $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN 0 AND " . $end);
            } elseif($start == 0 && $end == 0) {
                $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN 0 AND 1000000");
            } else $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN " . $start . " AND " . $end);
        } 
        

        if(isset($_GET['color'])) {
            if($_GET['color'][0] <> "all") {
                $items = $_GET['color'];
                $data = to_string($items);
                $sql_dop = "SELECT fvt.variation_id FROM feature_values AS fv INNER JOIN feature_variations AS fvt 
                            ON(fv.feature_value_id=fvt.feature_value_id) WHERE fv.feature_base_color_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array);
                $where = add_filter_condition($where, "fvt.variation_id IN($data2)");            
            } 
        }


        if(isset($_GET['size'])) {
            if($_GET['size'][0] <> "all") {
                $items = $_GET['size'];
                $data = to_string($items);
                $sql_dop = "SELECT fvt.variation_id FROM feature_values AS fv INNER JOIN feature_variations AS fvt 
                            ON(fv.feature_value_id=fvt.feature_value_id) WHERE fv.feature_value_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array);
                $where = add_filter_condition($where, "fvt.variation_id IN($data2)");
            }    
        }


        if(isset($_GET['textile'])) {
            if($_GET['textile'][0] <> "all") {
                $items = $_GET['textile'];
                $data = to_string($items);
                $sql_dop = "SELECT fvt.variation_id FROM feature_values AS fv INNER JOIN feature_variations AS fvt 
                            ON(fv.feature_value_id=fvt.feature_value_id) WHERE fv.feature_value_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array);               
                $where = add_filter_condition($where, "fvt.variation_id IN($data2)");
            } 
        }


        if(isset($_GET['style'])) {
            if($_GET['style'][0] <> "all") {
                $items = $_GET['style'];
                $data = to_string($items);
                $sql_dop = "SELECT v.variation_id FROM variations AS v INNER JOIN products AS p ON (v.product_id = p.product_id) INNER JOIN products_details AS pd 
                ON (p.product_id = pd.product_id) WHERE pd.feature_details_values_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array); 
                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            }
        }


        if(isset($_GET['line'])) {
            if($_GET['line'][0] <> "all") {
                $items = $_GET['line'];
                $data = to_string($items);
                $sql_dop = "SELECT v.variation_id FROM variations AS v INNER JOIN products AS p ON (v.product_id = p.product_id) INNER JOIN products_details AS pd 
                ON (p.product_id = pd.product_id) WHERE pd.feature_details_values_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array);  
                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            } 
        }


        if(isset($_GET['season'])) {
            if($_GET['season'][0] <> "all") {
                $items = $_GET['season'];
                $data = to_string($items);
                $sql_dop = "SELECT v.variation_id FROM variations AS v INNER JOIN products AS p ON (v.product_id = p.product_id) INNER JOIN products_details AS pd 
                ON (p.product_id = pd.product_id) WHERE pd.feature_details_values_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array);
                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            } 
        }

        if(isset($_GET['pattern'])) {
            if($_GET['pattern'][0] <> "all") {
                $items = $_GET['pattern'];
                $data = to_string($items);
                $sql_dop = "SELECT fvt.variation_id FROM feature_values AS fv INNER JOIN feature_variations AS fvt 
                            ON(fv.feature_value_id=fvt.feature_value_id) WHERE fv.feature_value_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array);                
                $where = add_filter_condition($where, "fvt.variation_id IN($data2)");
            } 
        }

        if(isset($_GET['fashion'])) {
            if($_GET['fashion'][0] <> "all") {
                $items = $_GET['fashion'];
                $data = to_string($items);
                $sql_dop = "SELECT v.variation_id FROM variations AS v INNER JOIN products AS p ON (v.product_id = p.product_id) INNER JOIN products_details AS pd 
                ON (p.product_id = pd.product_id) WHERE pd.feature_details_values_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array);  
                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            }  
        } 

        if(isset($_GET['details'])) {
            if($_GET['details'][0] <> "all") {
                $items = $_GET['details'];
                $data = to_string($items);
                $sql_dop = "SELECT v.variation_id FROM variations AS v INNER JOIN products AS p ON (v.product_id = p.product_id) INNER JOIN products_details AS pd 
                ON (p.product_id = pd.product_id) WHERE pd.feature_details_values_id IN($data)";
                $res_dop = meadb_select($sql_dop);
                $array = [];
                foreach($res_dop as $res_item) {
                   foreach($res_item as $value) {
                       $array[] = $value;
                   }
                }
                $data2 = to_string($array);
                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            } 
        }

    }
    
    if($where) {
        $sql .= " WHERE $where $sort, p.product_order DESC LIMIT 30";
    } else $sql .= "$sort, p.product_order DESC LIMIT 30";

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