<?php
/**
 * 
 * File:
 *    randservice.php
 * 
 * Purpose:
 *    provide entropic random number service
 *    similar to random.org's API
 * 
 * Created:
 *    7/25/2012 by Doug Bird
 * 
 * License / Distribution:
 *    'FreeBSD License' (see LICENSE.txt)
 *
 * Project Homepage
 *    http://katmore.com/sft/randapi
 *
 * Social:
 *    randapi@katmore.com
 *    twitter.com/katmore 
 *    github.com/katmore/randapi
 * 
 * Usage:
 * 
 *    display random strings: http://example.com/randpi.php?strings 
 *    
 *    display random integers: http://example.com/randpi.php?integers 
 * 
 *    display random bytes: http://example.com/randpi.php?bytes 
 * 
 * 
 */



define("srand_num_max",100);
define("srand_len_max",100);

define("srand_min_max",65534);
define("srand_max_max",65535);

define("srand_col_max",100);

define("srand_unique_try_time",30);

define("srand_allow_unqiue",true);

define("srand_default_num",8);

define("srand_default_min",0);

define("srand_default_max",999);

define("srand_default_col",16);

define("srand_default_base",10);

define("srand_default_len",8);

define("srand_default_format","plain");

define("srand_default_encode","none");

define("srand_default_type","none");

define("srand_default_disposition","inline");

define("srand_default_unique",false);

function getRandom($min, $max) {
   $fp = fopen('/dev/urandom','rb');
   //taken from magneto crypt_random function
   extract(unpack('Nrandom', fread($fp, 4)));
   fclose($fp);
   
   return abs($random) % (($max-$min)+1) + $min;
}

$num = srand_default_num;
$min = srand_default_min;
$max = srand_default_max;
$col = srand_default_col;
$base = srand_default_base;
$len = srand_default_len;
$format = srand_default_format;
$encode = srand_default_encode;
$type = srand_default_type;
$disposition = srand_default_disposition;
$unique = srand_default_unique;

if (isset($_GET["col"])) {
   $coleval = filter_var($_GET["col"], FILTER_SANITIZE_NUMBER_INT);
   if ( ($coleval > 0) && ($coleval <= srand_col_max) ) {
      $col = $coleval;
   }
}

if (isset($_GET["len"])) {
   $leneval = filter_var($_GET["len"], FILTER_SANITIZE_NUMBER_INT);
   if ( ($leneval > 0) && ($leneval <= srand_len_max) ) {
      $len = $leneval;
   }
}

if (isset($_GET["num"])) {
   $numeval = filter_var($_GET["num"], FILTER_SANITIZE_NUMBER_INT);
   if ( ($numeval > 0) && ($numeval <= srand_num_max) ) {
      $num = $numeval;
   }
}

if (isset($_GET["min"])) {
   $mineval = filter_var($_GET["min"], FILTER_SANITIZE_NUMBER_INT);
   if ( ($mineval > 0) && ($mineval <= srand_min_max) ) {
      $min = $mineval;
   }
}

if (isset($_GET["max"])) {
   $maxeval = filter_var($_GET["max"], FILTER_SANITIZE_NUMBER_INT);
   if ( ($maxeval > 0) && ($maxeval <= srand_max_max) ) {
      $max = $maxeval;
   }
}



if (isset($_GET["strings"])) {
   $type = "strings";
}
if (isset($_GET["bytes"])) {
   $type = "bytes";
}

if (isset($_GET["what"])) {
   if ($_GET["what"] == "strings") {
      $type = "strings";
   }
   if ($_GET["what"] == "bytes") {
      $type = "bytes";
   }
   if ($_GET["what"] == "integers") {
      $type = "integers";
   }
}

if ($type == "bytes") {
   $encode = "hex";
}

if (isset($_GET["encode"])) {
   if ($_GET["encode"] == "base64") {
      $encode = "base64";
   } else
   if ($_GET["encode"] == "none") {
      $encode = "none";
   } else
   if ($_GET["encode"] == "hex") {
      $encode = "hex";
   }
}

if (($type == "bytes") && ($encode == "none")){
   $disposition = "inline";
}

if (isset($_GET["disposition"])) {
   if ($_GET["disposition"] == "inline") {
      $disposition = "inline";
   } else
   if ($_GET["disposition"] == "attachment") {
      $disposition = "attachment";
   } 
}


if (isset($_GET["unique"])) {

   if (($_GET["unique"] == "on") || ($_GET["unique"] == "true") || ($_GET["unique"] == true)) {
      if (!srand_allow_unqiue) {
         header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
         echo "error: unique feature not allowed";
         die();
      }
      $unique = true;
   }
   
}


if ($type == "bytes") {
   
    $fp = fopen('/dev/urandom','rb');
   $bytes = "";
    if ($fp !== FALSE) {
        $bytes .= fread($fp,$num);
        fclose($fp);
    } else {
       echo "fail:bytes"; die();
    }
    if ($disposition == "attachment") {
       header("Content-Type: application/octet-stream");
    } else {
       header("Content-Type: text/plain"); 
    }
    if ($encode == "base64") {
       echo bin2hex($bytes);
    } else
    if ($encode == "base64") {
      
      echo base64_encode($bytes);
      
    } else {
       
       echo $bytes;
    }
    
    die();
}

if ($type == "strings") {
   
   $use_upperalpha = true;
   $use_loweralpha = true;
   $use_digits = true;
   
   if (isset($_GET["upperalpha"]))
   if (($_GET["upperalpha"]=="off"))
      $use_upperalpha = false;
   
   if (isset($_GET["loweralpha"]))
   if ($_GET["loweralpha"]=="off")
      $use_loweralpha = false;
   if (isset($_GET["digits"]))
   if ($_GET["digits"]=="off")
      $use_digits = false;
   
   $loweralpha = "abcdefghijklmnopqrstuvwxyz";
   $upperalpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
   $digits = "0123456789";
   $allvalid = "";
   
   if ($use_upperalpha)
      $allvalid .= $upperalpha;
   
   if ($use_loweralpha)
      $allvalid .= $loweralpha;
   
   if ($use_digits)
      $allvalid .= $digits;
   
   if (isset($_GET["charpool"])) {
      $allvalid = filter_var($_GET["charpool"],FILTER_UNSAFE_RAW,FILTER_FLAG_STRIP_LOW+FILTER_FLAG_STRIP_HIGH);
   }
   
   if (strlen($allvalid)<2) {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      echo "error: not enough valid characters";
      die();
   }
   $allvalid = str_split($allvalid);
}

if (($type == "integers") && ($unique == true)) {

   for($i=$min;$i<$max;$i++) {
      $allvalid[] = $i;
   }
   
}

if ($type == "strings") {

   header("Content-Type: text/plain"); 

   if ($unique) {

      if (count($allvalid)<$len) {
         header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
         echo "error: len shorter than char pool for string";
         die();
      }
      
      
      $validmax = count($allvalid) - 1;
      //echo "validmax=$validmax\n";
      $c = 0;
      for ($i=0;$i<$num;$i++) {
         for ($l=0;$l<$len;$l++) {
            $charidx = getRandom(0, $validmax);
            $char = $allvalid[$charidx];
            echo $char;
            //remove $charidx from $allvalid
            $newvalid = array();
            foreach($allvalid as $validchar) {
               if ($validchar != $char) {
                  //echo "yes\n";
                  $newvalid[] = $validchar;
               }
            }
            $allvalid = $newvalid;
            //recalculate validmax
            $validmax = count($allvalid) - 1;
         }
         $c++;
         if ($c==$col) {
            echo "\n";
            $c=0;
         } else {
            echo "\t";
         }
         
      }
      echo "\n";
      die();
      
   }
   
   $validmax = strlen($allvalid) - 1;
   //echo "validmax=$validmax\n";
   $c = 0;
   for ($i=0;$i<$num;$i++) {
      for ($l=0;$l<$len;$l++) {
         echo $allvalid[getRandom(0, $validmax) ];
      }
      $c++;
      if ($c==$col) {
         echo "\n";
         $c=0;
      } else {
         echo "\t";
      }
      
   }
   echo "\n";
   die();
}



//default to 'integers'
header("Content-Type: text/plain");




if ( 
      (($min <0) || ($max <0)) || 
      (($max - $min) < 1)
   ) {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
      echo "error: bad min-max";
      die();
   }
   


   
$c = 0;
for ($i = 0;$i<$num;$i++) {
   echo getRandom($min,$max);
   $c++;
   if ($c==$col) {
      echo "\n";
      $c=0;
   } else {
      echo "\t";
   }

}



