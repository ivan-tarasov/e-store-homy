<?php
namespace mail;

class sendmail {
   var $parts;
   var $to;
   var $from;
   var $headers;
   var $subject;
   var $body;

   function Lib_Sent() {
      $this->parts = array();
      $this->to =  "";
      $this->from =  "";
      $this->subject =  "";
      $this->body =  "";
      $this->headers =  "";
   }

   function add_attachment($message, $name = "", $ctype = "application/octet-stream", $cid='', $encode='') {
      $this->parts [] = array (
         "ctype" => $ctype,
         "message" => $message,
         "encode" => $encode,
         "name" => $name,
         "cid" => $cid
      );
   }

   function build_message($part) {
      $message = $part["message"];
      if ($part["ctype"] == "image/jpeg") {
         $message = chunk_split(base64_encode($message));
         $encoding = "base64";
         $hdr = "Content-Type: ".$part["ctype"]."\n";
         $hdr .= "Content-Transfer-Encoding: $encoding\n";
         $hdr .= ($part["name"]? "Content-Disposition: attachment; filename = \"".$part["name"]."\"\n" : "\n");
         $hdr .= "Content-ID: <".$part["cid"].">\n";
      } else {
         $hdr = "Content-Type: text/html; charset=utf-8\n";
         $hdr.= "Content-Transfer-Encoding: Quot-Printed\n\n";
      }
      $hdr.= "\n$message\n";
      return $hdr;
   }

   function build_multipart() {
      $boundary = "--b".md5(uniqid(time()));
      $multipart = "Content-Type: multipart/mixed; boundary=\"$boundary\"\n\n--$boundary";
      for($i = sizeof($this->parts)-1; $i>=0; $i--) {
         $multipart .= "\n".$this->build_message($this->parts[$i]). "--$boundary";
      }
      return $multipart.=  "--\n";
   }

   function send() {
      $mime = "";
      if (!empty($this->from)) {
         $mime .= "From: ".$this->from. "\n";
      }
      if (!empty($this->headers)) {
         $mime .= $this->headers. "\n";
      }
      if (!empty($this->body)) {
         $this->add_attachment($this->body, "", "text/html;charset=utf-8");
      }
      $mime .= "MIME-Version: 1.0\n".$this->build_multipart();

      foreach ($this->to as $value) {
         mail($value, $this->subject, "", $mime);
      }
   }
}
