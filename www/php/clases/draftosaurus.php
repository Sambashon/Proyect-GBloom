<?php
include "gbloomdb.php";
class Draftosaurus extends GBloomDB {

    private const SUCCESS = "success";

    public function __construct() {
        parent::__construct("database", "gbloomer", "gbloom_db", "goldenblosser", 3306);
    }
    
}