<?php
class mongocrud {
   static function connect() {
      $host = cfg::MONGO_HOST;
      $port = cfg::MONGO_PORT;
      $user = cfg::MONGO_USER;
      $pass = cfg::MONGO_PASS;
      $base = cfg::MONGO_BASE;
      
      $mongo = new MongoClient('mongodb://'.$user.':'.$pass.'@'.$host.$port.'/'.$base);
      $mongo_db = $mongo->$base;
      
      return $mongo_db;
   }
}
