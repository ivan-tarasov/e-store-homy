<?php
namespace helper;

use \debug\dBug;

class git {

   /**
   * Номер ревизии git и номер коммита
   *
   * @uses git::version() для получения текущей версии git и ссылки на последний коммит
   * @return string номер версии
   *
   * @version 1.0
   */
    static function version() {
       exec('git describe --long',$git_commit);
       $git_commit = explode('-',$git_commit[0]);
       $git_version = explode('/',$git_commit[0]);
       $git_version = $git_version[2];
       $version = '<abbr title="git commit #'.substr($git_commit[2],1).'"><a href="http://bitbucket.org/homysu/homy.su/commits/'.substr($git_commit[2],1).'" target="_blank">v.'.$git_version.'</a></abbr>';
       return $version;
    }

}
