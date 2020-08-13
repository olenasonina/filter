<?php


class SelectDataFromBD
{
    
    public static function select($query)
    {
        $conn = ConnectToBD::connect();
        try {
            $result = array();
            $my_result = array();
            $stmt = $conn->prepare($query);
            $stmt -> execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[]=$row;
            }
            foreach($result as $value) {
                    $my_result[] = $value['ins_photo'];
            }
            $stmt = null;
            return $my_result;
        }
        catch(PDOException $e)
        {
            echo $e->getMessage(); // PDO перехватывает ошибку реализации запроса
            $conn = ConnectToBD::disconnect();
        }
        $conn = ConnectToBD::disconnect();
    }
}