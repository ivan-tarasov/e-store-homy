<?php
namespace tmp;

/**
* Обработчик загружаемых фоторгафий
*
* @author Ivan Karapuzoff <ivan@karapuzoff.net>
* @version 0.2
*/

use \db\mysql\mysqlcrud;
use \debug\dBug;

class photoupload {
   public function __construct() {
      $this->db = new mysqlcrud();
      $this->db->connect();
   }

   public function test($id,$photos) {
      //new dBug($photos);

      if (isset($photos['tmp_name'])) {
         for($i=0; $i<count($photos); $i++) {
            if ($photos['tmp_name'][$i] != '')
               $urls[] = $photos['tmp_name'][$i];
         }
      } else {
         foreach ($photos as $photo) {
            if ($photo['url'] != '')
               $urls[] = $photo['url'];
         }
      }
      if ($urls) {
         foreach ($urls as $photo) {
            $file = file_get_contents($photo);
            $hash = md5($file);
            echo '<br />' . $photo . ' | ' . $hash;
         }
      }

      new dBug($url);

   }

   public function photos($id,$photos) {
      new dBug($photos);

      $path = intval($id / 1000) . '/' . $id;
      $structure = '../../../'.STATIC_URL.IMG_DIR.$path;

      // проверяем путь на валидность. создаем нужные папки при отсутствии
      if (!is_dir($structure))
         mkdir($structure,0777,true);
      if (!is_dir($structure.'/thumbnail'))
         mkdir($structure.'/thumbnail',0777,true);
      if (!is_dir($structure.'/big'))
         mkdir($structure.'/big',0777,true);

      if (isset($photos['tmp_name'])) {
         for($i=0; $i<count($photos['tmp_name']); $i++) {
            if ($photos['tmp_name'][$i] != '')
               $urls[] = $photos['tmp_name'][$i];
         }
      } else {
         foreach ($photos as $photo) {
            if ($photo['url'] != '')
               $urls[] = $photo['url'];
         }
      }

      $insert['onmain'] = 1;

      foreach ($urls as $photo) {
         $file = file_get_contents($photo);
         $hash = md5($file);

         $img['orig']  = $structure . '/' . $hash . '.jpg';
         $img['big']   = $structure . '/big/' . $hash . '.jpg';
         $img['small'] = $structure . '/thumbnail/' . $hash . '.jpg';

         $insert['1c_id'] = $id;

         if (file_put_contents($img['orig'], $file, LOCK_EX))
            $insert['path_orig'] = $path . '/' . $hash . '.jpg';
         if ($this->resizeImg($img['big'], $img['orig'], IMG_BIG_HEIGHT))
            $insert['path_big'] = $path . '/big/' . $hash . '.jpg';
         if ($this->resizeImg($img['small'], $img['orig'], IMG_SM_HEIGHT))
            $insert['path_thumbnail'] = $path . '/thumbnail/' . $hash . '.jpg';

         $this->db->insert('images',$insert);
         unset($insert['onmain']);
      }
   }

   private function resizeImg($filename,$img,$resize) {
      $targetFile = $img;
      $targetThumb = $filename;

      $background = 'white';

      $img = new \Imagick($targetFile);

      $img->scaleImage($resize,$resize,true);
      $img->setImageBackgroundColor($background);
      $w = $img->getImageWidth();
      $h = $img->getImageHeight();
      $img->extentImage($resize,$resize,($w-$resize)/2,($h-$resize)/2);
      $img->writeImage($targetThumb);

      return true;
   }

}
