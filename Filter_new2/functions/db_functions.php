<?php
include(ROOT.'/components/ConnectToBD.php');

global $data;
$data["pass"] = include(ROOT."/config/db_params.php");

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
        echo $e->getMessage();
    }
}

// в PDO нельзя вывести $stmt -> execute() как результат, поэтому нельзя соответственно вызывать ее внутри
// функции meadb_select($query). meadb_select($query) - самодостаточная функция выборки данных

function meadb_select($query)
{
    try {
        $array=array();
        $conn = ConnectToBD::connect();
        // через публичный метод статического класса возвращается постоянное соединение
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($query);
        $stmt -> execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $array[]=$row;
        }
        $stmt = null;  // аналог $result->free();
        return $array;
    }
    catch(PDOException $e)
    {
        echo $e->getMessage(); // PDO перехватывает ошибку реализации запроса
    }
}

