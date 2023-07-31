<?php

function spr4_autoloader($className) {
   $fileName  = null;
   $namespace = null;
   $className = ltrim($className, '\\');

   if ($lastNsPos = strrpos($className, '\\')) {
      $namespace = substr($className, 0, $lastNsPos);
      $className = substr($className, $lastNsPos + 1);
      $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
   }

   $fileName = sprintf('%s/class/%s%s.class.php',
      DOCUMENT_ROOT,
      $fileName,
      str_replace('_', DIRECTORY_SEPARATOR, $className)
   );

   //error_log($fileName, 0);
   require_once($fileName);
}

spl_autoload_register('spr4_autoloader');
