<?php

class InsertDataToBd {
    public static function insert($items) {
        try {
            $conn = ConnectToBD::connect();
            $conn->beginTransaction();
            $sql = "INSERT INTO insta (ins_photo) VALUES (:photo)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':photo', $photo);
            foreach($items as $item) {
                $photo = $item;
                $stmt -> execute();
            }
            $conn->commit();
            
        }
        catch(PDOException $e)
        {
            $conn->rollback();
            echo $e->getMessage();
            $conn = ConnectToBD::disconnect();
            exit;
        }
        $conn = ConnectToBD::disconnect();
    }
}