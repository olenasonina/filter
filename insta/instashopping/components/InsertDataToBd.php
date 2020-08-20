<?php

class InsertDataToBd {
    public static function insert($items) {
        try {
            $conn = ConnectToBD::connect();
            $conn->beginTransaction();
            $sql = "INSERT INTO insta (ins_photo, ins_time) VALUES (:photo, :time)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':photo', $photo);
            $stmt->bindParam(':time', $time);
            foreach($items as $item) {
                $photo = $item['url'];
                $time = $item['date'];
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