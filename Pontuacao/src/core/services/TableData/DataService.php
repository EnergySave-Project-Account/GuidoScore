<?php

require_once HANDLERS_PATH . "DataService/RefreshOnce.php";
require_once HANDLERS_PATH . "DataService/IncrementPoints.php";
require_once HANDLERS_PATH . "DataService/UpdatePoints.php";

class DataService {

    public static function refreshTableData(){
        $status = (new RefreshOnce())->handle();

        return $status;
    }

    public static function IncrementPoints($data){
        $status = (new IncrementPoints())->handle($data);

        return $status;
    }

    public static function UpdatePoints($data){
        $status = (new UpdatePoints())->handle($data);

        return $status;
    }
}