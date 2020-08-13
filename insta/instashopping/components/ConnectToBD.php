<?php


class ConnectToBD
{
    private static $servername;
    private static $username;
    private static $password;
    private static $dbname;
    private static $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    final public function __construct($data) {
        extract($data);
        self::$servername = $db_host;
        self::$username = $db_user;
        self::$password = $db_pass;
        self::$dbname = $db_table;
    }
    final private function __clone() { }

    public static function connect() { // метод открывает соединение.
        try {
            return $conn = new PDO('mysql:host='.self::$servername.';dbname='.self::$dbname.';charset=utf8',
                self::$username, self::$password, self::$options);
        } catch (PDOException $e) {
            echo $e->getMessage(); // PDO перехватывает ошибку подключения к БД
        }
    }


    public static function disconnect() {
        return $conn = null; // метод закрывает соединение.
    }
}