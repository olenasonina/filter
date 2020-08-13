<?php

//Конфигурационные данные зашиты в приватные статические переменные класса

class Data {
    
    private static $username = "arjen_official_page";
    private static $password = "Evgena2323Terevega";
    private static $bd_data = [
        "db_host" => "localhost",
        "db_user" => "root",
        "db_pass" => "15081986",
        "db_table" => "insta_work"
    ];

    public static function getLogin() {
        return $login = self::$username;    
    }

    public static function getPass() {
        return $pass = self::$password;    
    }

    public static function getBdData() {
        return $data = self::$bd_data;
    }
}