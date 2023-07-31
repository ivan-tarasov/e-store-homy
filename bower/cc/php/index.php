<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
session_start();
require('UploadHandler.php');
require($_SERVER['DOCUMENT_ROOT'] . '/class/cfg.class.php');

$id = $_SESSION['cc']['item_id'];
$img_path = HTTP_PROTOCOL.STATIC_URL.IMG_DIR . intval($id / 1000) . '/' . $id . '/';
$img_thumb = $img_path . 'thumbnail/';

$args = array(
    'upload_dir' => $_SERVER['DOCUMENT_ROOT'] . '/' . $img_path,
    'upload_url' => $img_path,
    'image_versions' => array(
      'big' => array(
         'crop' => true,
         'max_width' => 200,
         'max_height' => 200
      ),
      'thumbnail' => array(
         'upload_dir' => $_SERVER['DOCUMENT_ROOT'] . '/' . $img_thumb,
         'upload_url' => $img_thumb,
         'crop' => true,
         'max_width' => 73,
         'max_height' => 73
      )
   )
);

$upload_handler = new UploadHandler($args);
