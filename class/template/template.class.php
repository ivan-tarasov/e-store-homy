<?
namespace template;

class template {
   var $vars = array();
   var $content = "";
   var $stag = "{";
   var $etag = "}";
   var $root = "/templates";

   function design($folder,$tpl,$configs = NULL) {
      $this->root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/templates";

      $this->content = file_get_contents($this->root."/".$folder."/".$tpl.".tpl");

      if(isset($configs)) {
         foreach($configs as $key => $val) {
            @$this->content = str_replace($this->stag.$key.$this->etag, $val, $this->content);
         }
      }

      return $this->content;
   }
}
?>
