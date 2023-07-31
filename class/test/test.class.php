<?php
namespace test;

use \db\mysql\mysqlcrud;
use \debug\dBug;

class test {

   public function __construct() {
      //$this->db = new mysqlcrud();
      //$this->db->connect();

      $path = '../'.STATIC_URL.IMG_DIR;
      echo realpath($path);

      //new dBug($result);
   }

}
