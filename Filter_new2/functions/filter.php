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
    // $dop_where = get_filter_where ($dop_where);
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
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
        $colors_list = get_color_list($item);
        $colors = "";
        foreach($colors_list as $c) {
            $colors .= '<option value="' . $c['feature_base_color_id'] . '">' . $c['color_value']  . '</option>';
        }
        return $colors;
    }
}

// Функция дописывает условие для того, чтобы в фильтрах отражались только актуальные параметры для уже выбранных фильтров 

// function get_filter_where ($dop_where) {   
//     if(isset($_SESSION['ids']['price_start']) || isset($_SESSION['ids']['price_end'])) {
//         $start = $_SESSION['ids']['price_start'];
//         $end = $_SESSION['ids']['price_end'];
//         if($end == 0 && $start <> 0) {
//             $dop_where = add_filter_condition($dop_where, "p.product_price_and_discount BETWEEN " . $start . " AND 1000000");
//         } elseif($start == 0 && $end <> 0) {
//             $dop_where = add_filter_condition($dop_where, "p.product_price_and_discount BETWEEN 0 AND " . $end);
//         } elseif($start == 0 && $end == 0) {
//             $dop_where = add_filter_condition($dop_where, "p.product_price_and_discount BETWEEN 0 AND 1000000");
//         } else $dop_where = add_filter_condition($dop_where, "p.product_price_and_discount BETWEEN " . $start . " AND " . $end);
//     }
//     if(isset($_SESSION['variations']['color'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['color'] . ")");
//     }
//     if(isset($_SESSION['variations']['size'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['size'] . ")");
//     }
//     if(isset($_SESSION['variations']['textile'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['textile'] . ")");
//     }
//     if(isset($_SESSION['variations']['pattern'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['pattern'] . ")");
//     }
//     if(isset($_SESSION['variations']['style'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['style'] . ")");
//     }
//     if(isset($_SESSION['variations']['line'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['line'] . ")");
//     }
//     if(isset($_SESSION['variations']['fashion'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['fashion'] . ")");
//     }
//     if(isset($_SESSION['variations']['season'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['season'] . ")");
//     }
//     if(isset($_SESSION['variations']['details'])) {
//         $dop_where = add_filter_condition($dop_where, "v.variation_id IN (" . $_SESSION['variations']['details'] . ")");
//     }
//     return $dop_where;
// }

// // Функция получает из БД варианты согласно переданных параметров 

function get_filter_list($id, $i, $i_sort) {
    $ids = get_product_from_tree_s($id);
    $dop_where = "p.product_cat_id IN($ids) AND fv.feature_id=$i AND v.image_id > 0 AND p.product_not_active=0 AND p.product_published=1";
    // $dop_where = get_filter_where ($dop_where);
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
    // $dop_where = get_filter_where ($dop_where);
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
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
        $size_list = get_filter_list($item, 2, "DESC");
        $sizes = "";
        foreach($size_list as $s) {
            $sizes .= '<option value="' . $s['feature_value_id'] . '">' . $s['feature_value']  . '</option>';
        }
        return $sizes;
    }
}

// Функция формирует список тканей из выбранной категории

function build_textile_list() {
    if(isset($_SESSION['cat'])) {// $start = price("MIN");
        // $end = price("MAX");
        $item = (int)$_SESSION['cat'];
        $textile_list = get_filter_list($item, 3, "ASC");
        $textiles = "";
        foreach($textile_list as $s) {
            $textiles .= '<option value="' . $s['feature_value_id'] . '">' . $s['feature_value']  . '</option>';
        }
        return $textiles;
    }
}

// Функция формирует список узоров из выбранной категории

function build_pattern_list() {
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
        $patterns_list = get_filter_list($item, 4, "ASC");
        $patterns = "";
        foreach($patterns_list as $p) {
            $patterns .= '<option value="' . $p['feature_value_id'] . '">' . $p['feature_value']  . '</option>';
        }
        return $patterns;
    }
}

// Функция формирует список стилей из выбранной категории

function build_style_list() {
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
        $style_list = get_filter_list2($item, 21, "ASC");
        $styles = "";
        foreach($style_list as $s) {
            $styles .= '<option value="' . $s['feature_details_values_id'] . '">' . $s['feature_detail_value']  . '</option>';
        }
        return $styles;
    }
}

// Функция формирует список линий из выбранной категории

function build_line_list() {
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
        $line_list = get_filter_list2($item, 22, "ASC");
        $lines = "";
        foreach($line_list as $l) {
            $lines .= '<option value="' . $l['feature_details_values_id'] . '">' . $l['feature_detail_value']  . '</option>';
        }
        return $lines;
    }
}

// Функция формирует список сезонов из выбранной категории

function build_season_list() {
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
        $season_list = get_filter_list2($item, 23, "ASC");
        $seasons = "";
        foreach($season_list as $s) {
            $seasons .= '<option value="' . $s['feature_details_values_id'] . '">' . $s['feature_detail_value']  . '</option>';
        }
        return $seasons;
    }
}

// Функция формирует список фасонов из выбранной категории

function build_fashion_list() {
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
        $fashion_list = get_filter_list2($item, 9, "ASC");
        $fashions = "";
        foreach($fashion_list as $f) {
            $fashions .= '<option value="' . $f['feature_details_values_id'] . '">' . $f['feature_detail_value']  . '</option>';
        }
        return $fashions;
    }
}

// Функция формирует список деталей из выбранной категории

function build_detail_list() {
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
        $detail_list = get_filter_list2($item, 24, "ASC");
        $details = "";
        foreach($detail_list as $d) {
            $details .= '<option value="' . $d['feature_details_values_id'] . '">' . $d['feature_detail_value']  . '</option>';
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
    if(isset($_SESSION['cat'])) {
        $item = (int)$_SESSION['cat'];
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
    $item = (int)$_SESSION['cat'];
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
                $_SESSION['cat'] = $cats;
                // unset($_SESSION['variations']);
                // unset($_SESSION['ids']);
                $where = add_filter_condition($where, "p.product_cat_id IN(" . $_SESSION['cat'] . ")");
        } elseif (isset($_SESSION['cat'])) {
            $where = add_filter_condition($where, "p.product_cat_id IN(" . $_SESSION['cat'] . ")");
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
            // $_SESSION['ids']['price_start'] = $start;
            // $_SESSION['ids']['price_end'] = $end;
            if($end == 0 && $start <> 0) {
                $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN " . $start . " AND 1000000");
            } elseif($start == 0 && $end <> 0) {
                $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN 0 AND " . $end);
            } elseif($start == 0 && $end == 0) {
                $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN 0 AND 1000000");
            } else $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN " . $start . " AND " . $end);
        } 
        // elseif (isset($_SESSION['ids']['price_start']) || isset($_SESSION['ids']['price_end'])) {
        //     $start = $_SESSION['ids']['price_start'];
        //     $end = $_SESSION['ids']['price_end'];
        //     if($end == 0 && $start <> 0) {
        //         $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN " . $start . " AND 1000000");
        //     } elseif($start == 0 && $end <> 0) {
        //         $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN 0 AND " . $end);
        //     } elseif($start == 0 && $end == 0) {
        //         $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN 0 AND 1000000");
        //     } else $where = add_filter_condition($where, "p.product_price_and_discount BETWEEN " . $start . " AND " . $end);
        // } 

        if(isset($_GET['color'])) {
            if($_GET['color'][0] <> "all") {
                $items = $_GET['color'];
                $data = to_string($items);
                // $_SESSION['ids']['color'] = $data;
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
                // $_SESSION['variations']['color'] = $data2;
                $where = add_filter_condition($where, "fvt.variation_id IN($data2)");            
            } 
            // else { 
            //     unset($_SESSION['variations']['color']); 
            //     unset($_SESSION['ids']['color']);
            // }
        }
        // elseif (isset($_SESSION['variations']['color'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['color'] . ")");
        // }


        if(isset($_GET['size'])) {
            if($_GET['size'][0] <> "all") {
                $items = $_GET['size'];
                $data = to_string($items);
                // $_SESSION['ids']['size'] = $data;
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
                // $_SESSION['variations']['size'] = $data2;
                $where = add_filter_condition($where, "fvt.variation_id IN($data2)");
            } 
            // else {
            //     unset($_SESSION['variations']['size']);
            //     unset($_SESSION['ids']['size']);   
            // }    
        }
        // elseif (isset($_SESSION['variations']['size'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['size'] . ")");
        // }


        if(isset($_GET['textile'])) {
            if($_GET['textile'][0] <> "all") {
                $items = $_GET['textile'];
                $data = to_string($items);
                // $_SESSION['ids']['textile'] = $data;
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
                // $_SESSION['variations']['textile'] = $data2;                
                $where = add_filter_condition($where, "fvt.variation_id IN($data2)");
            }
            // else {
            //     unset($_SESSION['variations']['textile']); 
            //     unset($_SESSION['ids']['textile']); 
            // } 
        }
        // elseif (isset($_SESSION['variations']['textile'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['textile'] . ")");
        // }


        if(isset($_GET['style'])) {
            if($_GET['style'][0] <> "all") {
                $items = $_GET['style'];
                $data = to_string($items);
                // $_SESSION['ids']['style'] = $data;
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
                // $_SESSION['variations']['style'] = $data2;     

                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            }
            // else {
            //     unset($_SESSION['variations']['style']);
            //     unset($_SESSION['ids']['style']);
            // } 
        }
        // elseif (isset($_SESSION['variations']['style'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['style'] . ")");
        // }


        if(isset($_GET['line'])) {
            if($_GET['line'][0] <> "all") {
                $items = $_GET['line'];
                $data = to_string($items);
                // $_SESSION['ids']['style'] = $data;
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
                // $_SESSION['variations']['line'] = $data2;     

                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            } 
            // else {
            //     unset($_SESSION['variations']['line']);
            //     unset($_SESSION['ids']['line']);
            // } 
        }
        // elseif (isset($_SESSION['variations']['line'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['line'] . ")");
        // }


        if(isset($_GET['season'])) {
            if($_GET['season'][0] <> "all") {
                $items = $_GET['season'];
                $data = to_string($items);
                // $_SESSION['ids']['season'] = $data;
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
                // $_SESSION['variations']['season'] = $data2;     

                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            }  
            // else {
            //     unset($_SESSION['variations']['season']);
            //     unset($_SESSION['ids']['season']);
            // } 
        }
        // elseif (isset($_SESSION['variations']['season'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['season'] . ")");
        // }

        if(isset($_GET['pattern'])) {
            if($_GET['pattern'][0] <> "all") {
                $items = $_GET['pattern'];
                $data = to_string($items);
                // $_SESSION['ids']['pattern'] = $data;
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
                // $_SESSION['variations']['pattern'] = $data2;                
                $where = add_filter_condition($where, "fvt.variation_id IN($data2)");
            } 
            // else {
            //     unset($_SESSION['variations']['pattern']);
            //     unset($_SESSION['ids']['pattern']);
            // }  
        }
        // elseif (isset($_SESSION['variations']['pattern'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['pattern'] . ")");
        // }

        if(isset($_GET['fashion'])) {
            if($_GET['fashion'][0] <> "all") {
                $items = $_GET['fashion'];
                $data = to_string($items);
                // $_SESSION['ids']['fashion'] = $data;
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
                // $_SESSION['variations']['fashion'] = $data2;     

                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            } 
            // else {
            //     unset($_SESSION['variations']['fashion']);
            //     unset($_SESSION['ids']['fashion']);
            // } 
        } 
        // elseif (isset($_SESSION['variations']['fashion'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['fashion'] . ")");
        // }

        if(isset($_GET['details'])) {
            if($_GET['details'][0] <> "all") {
                $items = $_GET['details'];
                $data = to_string($items);
                // $_SESSION['ids']['details'] = $data;
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
                // $_SESSION['variations']['details'] = $data2;     

                $where = add_filter_condition($where, "fvt.variation_id IN(" . $data2 . ")");
            } 
            // else {
            //     unset($_SESSION['variations']['details']);
            //     unset($_SESSION['ids']['details']);
            // }  
        }
        // elseif (isset($_SESSION['variations']['details'])) {
        //     $where = add_filter_condition($where, "fvt.variation_id IN(" . $_SESSION['variations']['details'] . ")");
        // }

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