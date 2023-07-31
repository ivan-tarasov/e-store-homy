<?php
namespace db\mongo;

class mongo {
   const dbHost = 'ds039331.mongolab.com';
   const dbPort = ':39331';
   const dbUser = 'test';
   const dbPass = 'pass';
   const dbName = 'homysu';

   private static $instance;
   private static $dbs;
   private function __construct() { }

   public static function connect() {
      try {
         $mongo = new MongoClient('mongodb://'.self::dbUser.':'.self::dbPass.'@'.self::dbHost.self::dbPort.'/'.self::dbName);
         self::$dbs = $mongo->selectDB(self::dbName);
         self::$instance = new mongo();
      }
      catch(MongoConnectionException $e) {
         die('Connection Failed' );
      }

      return self::$instance;
   }


    public function selectDocument($collectionName, $fields = array(), $where = array(), $sort = array(), $limit = 0)
    {
        $cur = self::$dbs->$collectionName->find($where, $fields)->limit($limit);
        $cur->sort($sort);

        $this->docs = null;
        while( $docs = $cur->getNext())
        {
        $this->docs[] = $docs;
        }
        return $this->docs;
    }

    public function insertDocument($obj, $collectionName)
    {
        $collection = self::$dbs->$collectionName;
        try{
            $collection->insert($obj, array('w'=>true) );
            return  ( !empty($obj['_id']) )?1:0;
        } catch (MongoException $e) {
            return "Can't insert!n";
        }
    }

    public function updateDocument($collectionName, $criteria, $update, $confirm)
    {

        $collection = self::$dbs->$collectionName;
        try
        {
            $collection->update($criteria,$update, array("multiple" => true));
            $num_rows = $collection->find($confirm)->count();
            return ( !empty($num_rows) )?$num_rows:0;
        } catch (MongoException $e) {
            return "Can't update!n";
        }
    }

    public function removeDocument($collectionName, $criteria)
    {

        $collection = self::$dbs->$collectionName;
        try
        {
            $collection->remove($criteria);
            $num_rows = $collection->find($criteria)->count();
            return ( empty($num_rows) )?1:0;
        } catch (MongoException $e) {
            return "Can't update!n";
        }
    }

}
