<?php
ini_set("display_errors", "On"); // вывод ошибок в браузер
ini_set("display_startup_errors", "On"); // вывод ошибок в браузер
ini_set("error_reporting", "-1");  // аналог error_reporting(E_ALL);
ini_set("log_errors", "Off"); // запрет записи ошибок в файл логов


header('Content-Type: text/html; charset=utf-8');

global $data;

$data["pass"]["db_host"]="localhost";
$data["pass"]["db_user"]="db_user";
$data["pass"]["db_pass"]="Mrh9uZSri3wwQHGh";
$data["pass"]["db_table"]="arjen_ua_db";

// подключение к БД реализовано через статический класс - его публичные методы являются доступными в глобальной области видимости

class ConnectToBD {
    private static $conn = null;

    final private function __construct() { }
    final private function __clone() { }

    // приватная переменная conn и финальные функции конструктора и клонирования не позволят извне повлиять на подключение
    // и переопределить их

    public static function connect() {
        global $data;
        if(self::$conn == null){
            self::init($data);
        }
        return self::$conn;
        // функция проверяет, установлено ли соединение. Если да, то возвращает его, если потеряно, то восстанавливает
    }
        
    private static function init($data) {
        try {
            self::$conn = new PDO('mysql:host='.$data["pass"]["db_host"].';dbname='.$data["pass"]["db_table"].';charset=utf8', 
                                  $data["pass"]["db_user"], $data["pass"]["db_pass"], 
                                  array(PDO::ATTR_PERSISTENT => true));

                                  // PDO::ATTR_PERSISTENT => true - устанавливает постоянное подключение к БД

        } catch (PDOException $e) {
            print "Error! : " . $e->getMessage() . "<br/>"; // PDO перехватывает ошибку подключения к БД
        }
    }
        
    public static function disconnect() {
        return $conn = null; // метод закрывает соединение. В этом файле не используется, но может пригодиться в других
    }
}

// meadb_query('SET NAMES utf8') - в PDO charset=utf8 прописывается внутри подключения к БД, см. функцию init 
// поэтому сама функция meadb_query($query) в данном контексте не нужна, но я ее тоже переписала на PDO

function meadb_query($query)
{
    try {
        $conn = ConnectToBD::connect();
        // через публичный метод статического класса возвращается постоянное соединение
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($query);
        $stmt -> execute();
    }
    catch(PDOException $e)
    {
        echo $sql . "<br>" . $e->getMessage();
    }
}

// в PDO нельзя вывести $stmt -> execute() как результат, поэтому нельзя соответственно вызывать ее внутри
// функции meadb_select($query). meadb_select($query) - самодостаточная функция выборки данных

function meadb_select($query)
{
    $array=array();
    try {
        $conn = ConnectToBD::connect();
         // через публичный метод статического класса возвращается постоянное соединение
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($query);
        $stmt -> execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if($table_id=="") {
                $array[]=$row;
            }
          }
          $stmt = null;  // аналог $result->free();
          return $array;
    }
    catch(PDOException $e)
    {
        echo $sql . "<br>" . $e->getMessage(); // PDO перехватывает ошибку реализации запроса
    }
}

$list=meadb_select("select * from varieties_variations vv inner join products p on (vv.product_id=p.product_id)");

foreach($list as $p) {
    print_r($p);
    echo "<hr>";
}

?>