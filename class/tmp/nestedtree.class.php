<?php

namespace tmp;

use \db\mysql\mysqlcrud;

/**
 *
 * Класс для работы с Nested Tree
 *
 * @link     http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/
 * @author   karapuzoff
 *
 */
class nestedtree {
  /**
   *
   * Вывод всего дерева с иерархией подкатегорий
   *
   * @return   array    $res     массив с категориями и подкатегориями
   *
   */
   static function show_tree() {
      $db = new mysqlcrud();
      $db->connect();

      $db->sql('
         SELECT
            CONCAT( REPEAT( "- ", (COUNT(parent.name) - 1) ), node.name) AS name
         FROM
            category AS node,
            category AS parent
         WHERE
            node.lft BETWEEN parent.lft AND parent.rgt
         GROUP BY
            node.name
         ORDER BY
            node.lft;
      ');
      $res = $db->getResult();

      return $res;
   }

  /**
   *
   *
   *
   * @return   void
   *
   */
   static function single_path($url) {
      $db = new mysqlcrud();
      $db->connect();

      if (!$url)
         $url = 'main';

      $db->sql('
               SELECT
                  parent.cat_id, parent.name, parent.url, parent.lft, parent.rgt
               FROM
                  category AS node,
                  category AS parent
               WHERE
                  node.lft BETWEEN parent.lft AND parent.rgt AND node.url = "' . $url . '"
               ORDER BY
                  parent.lft
              ');

      $res = $db->getResult();
      return $res;
   }

   function add_subnode($category_name,$category_id=null) {
      $db = new mysqlcrud();
      $db->connect();
      //$prefix = master::get_config('db','prefix');

      if (!$category_id)
         $category_id = 1;

      $sql = 'LOCK TABLE category WRITE;'; $db->sql($sql);
      $sql = 'SELECT @myLeft := lft, lvl FROM category WHERE cat_id = '.$category_id.';'; $db->sql($sql);
      $lvl = $db->getResult();
      //new dBug($lvl);
      echo $lvl = $lvl[0]['lvl'] + 1;
      $sql = 'UPDATE category SET rgt = rgt + 2 WHERE rgt > @myLeft;'; $db->sql($sql);
      $sql = 'UPDATE category SET lft = lft + 2 WHERE lft > @myLeft;'; $db->sql($sql);
      $sql = 'INSERT INTO category(name, lft, rgt, lvl) VALUES("'.$category_name.'", @myLeft + 1, @myLeft + 2, '.$lvl.');'; $db->sql($sql);
      $sql = 'UNLOCK TABLES'; $db->sql($sql);

      $res = $db->getResult();
      return $res;
   }

   function rename_node($name,$category_id) {
      $db = new mysqlcrud();
      $db->connect();
      //$prefix = master::get_config('db','prefix');

      $update['name'] = $name;
      $db->update('category',$update,'category_id = '.$category_id);

      $res = $db->getResult();
      return $res;
   }

   function del_node($category_id) {
      $db = new mysqlcrud();
      $db->connect();
      //$prefix = master::get_config('db','prefix');

      $sql = 'LOCK TABLE category WRITE'; $db->sql($sql);
      $sql = 'SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1 FROM category WHERE cat_id = '.$category_id; $db->sql($sql);
      $sql = 'DELETE FROM category WHERE lft = @myLeft'; $db->sql($sql);
      $sql = 'UPDATE category SET rgt = rgt - 1, lft = lft - 1 WHERE lft BETWEEN @myLeft AND @myRight';
      $sql = 'UPDATE category SET rgt = rgt - 2 WHERE rgt > @myRight';
      $sql = 'UPDATE category SET lft = lft - 2 WHERE lft > @myRight';
      $sql = 'UNLOCK TABLES';  $db->sql($sql);

      $res = $db->getResult();
      return $res;
   }

   function del_node_all($category_id) {
      $db = new mysqlcrud();
      $db->connect();
      //$prefix = master::get_config('db','prefix');

      $sql = 'LOCK TABLE category WRITE'; $db->sql($sql);
      $sql = 'SELECT @myLeft := lft, @myRight := rgt, @myWidth := rgt - lft + 1 FROM category WHERE cat_id = '.$category_id; $db->sql($sql);
      $sql = 'DELETE FROM category WHERE lft BETWEEN @myLeft AND @myRight'; $db->sql($sql);
      $sql = 'UPDATE category SET rgt = rgt - @myWidth WHERE rgt > @myRight'; $db->sql($sql);
      $sql = 'UPDATE category SET lft = lft - @myWidth WHERE lft > @myRight'; $db->sql($sql);
      $sql = 'UNLOCK TABLES';  $db->sql($sql);

      $res = $db->getResult();
      return $res;
   }


   //new dBug(add_subnode("4.5.2",17));
   //new dBug(del_node_all(2));
   //new dBug(del_node(2));
   //new dBug(rename_node("Бытовая техника",1));
   //new dBug(single_path(19));

   //new dBug(show_tree());
}
