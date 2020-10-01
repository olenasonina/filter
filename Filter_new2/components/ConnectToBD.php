<?php


class ConnectToBD
{
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