<?php
//=============================================
// File.......: manager.php
// Date.......: 2019-05-15
// Author.....: Benny Saxen
// Description: Mercury Device Manager
//=============================================
session_start();

$sel_domain = $_SESSION["domain"];
$sel_domain = readDomainUrl($sel_domain);

$sel_device = $_SESSION["device"];
$doc = 'http://'.$sel_domain.'/devices/'.$sel_device;
$sel_desc = getDesc($doc);

// Configuration
//=============================================
// No configuration needed
//=============================================
$date         = date_create();
$ts           = date_format($date, 'Y-m-d H:i:s');
$now          = date_create('now')->format('Y-m-d H:i:s');
$g_rssi       = 0;
$g_action     = 0;
//echo "<br>$ts $now<br>";
//=============================================
// library
//=============================================
//=============================================
function readDomainUrl($file)
//=============================================
{
  $file = $file.'.domain';
  //echo $file;
  $fh = fopen($file, "r");
  if ($fh)
  {
      while(! feof($fh))
      {
        $url = fgets($fh);
      }
      fclose($fh);
  }
  //echo $url;
  return $url;
}

//=============================================
function generateRandomString($length = 15)
//=============================================
{
    return substr(sha1(rand()), 0, $length);
}
//=============================================
function prettyTolk( $json )
//=============================================
{
    global $rank,$g_nn;
    $result = '';
    $level = 0;
    $nn = 1;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' )
        {
            $in_quotes = !$in_quotes;
        }
        else if( ! $in_quotes )
        {
            if($word)
            {
              $tmp = $rank[$nn];
              //echo ("$word nn=$nn level=$level<br>");
              //if($tmp > 0 && $tmp != $level) echo "JSON Error: $word<br>";
              $rank[$nn] = $level;
            }

            $word = '';
            switch( $char )
            {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;

                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $nn++;
                    //echo "nn=$nn<br>";
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        else {
          $word .= $char;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
        //echo "$level $char<br>";
    }
    $g_nn = $nn-1;
    return $result;
}

//=============================================
function generateForm($inp,$color)
//=============================================
{
  global $rank,$g_nn;

  $id = 'void';

  $jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($inp, TRUE)),RecursiveIteratorIterator::SELF_FIRST);
  echo("<table border=0>");
  $nn = 0;
  foreach ($jsonIterator as $key => $val) {
    $nn++;
    if ($key == 'id') $id = $val;
    echo "<tr>";
    for($ii=1;$ii<$rank[$nn];$ii++)echo "<td></td>";

      if(is_array($val))
      {
        echo "<td color=\"#C5FD69\">$key</td>";
      }
      else
      {
          echo "<td>$key</td><td bgcolor=\"$color\">$val</td><tr>";
      }
      echo "</tr>";
   }
   echo "</table>";
   //if ($id == 'void') $id = generateRandomString(12);
   if ($g_nn != $nn)echo("ERROR: Key duplicate in JSON structure: $nn $g_nn<br>");
   return $id;
}
//=============================================
function addDomain($domain)
//=============================================
{
  echo("[$domain]");
  $filename = str_replace("/","_",$domain);
  $filename = $filename.'.domain';
  $fh = fopen($filename, 'w') or die("Can't add domain $domain");
  fwrite($fh, "$domain");
  fclose($fh);
}
//=============================================
function restApi($api,$domain,$device)
//=============================================
{
  //echo("RestApi [$api] domain=$domain device=$device<br>");
  $call = 'http://'.$domain.'/gateway.php?do='.$api.'&id='.$device;
  //echo $call;
  $res = file_get_contents($call);
}

//=============================================
function getDesc($uri)
//=============================================
{
  $url       = $uri.'/static.json';
  $json      = file_get_contents($url);
  $json      = utf8_encode($json);
  $dec       = json_decode($json, TRUE);
  $desc      = $dec['msg']['desc'];
  return $desc;
}

//=============================================
function sendMessage($url,$device,$msg,$tag)
//=============================================
{
  echo "Send message $msg tag=$tag to $url/$device<br>";
  $call = 'http://'.$url.'/service.php?do=feedback&id='.$device.'&msg='.$msg.'&tag='.$tag;
  $res = file_get_contents($call);
}
//=============================================
function listAllDevices()
//=============================================
{
  $do = "ls register"."/"."*.reg > register/register.work";
  //echo $do;
  system($do);
  $result = '';
  $file = fopen('register/register.work', "r");
  if ($file)
  {
    while(!feof($file))
    {
      $line = fgets($file);
      //echo $line;
      if (strlen($line) > 2)
      {
          $line = trim($line);
          $file2 = fopen($line, "r");
          if ($file2)
          {
                  $line2 = fgets($file2);
                  $line2 = trim($line2);
                  //echo $line2;
                  $result = $result.$line2.':';
          }
      }
    }
  }
  //echo $result;
  return $result;
}
//=============================================
// End of library
//=============================================


//=============================================
// Back-End
//=============================================

if (isset($_GET['do'])) 
{

  $do = $_GET['do'];

  if($do == 'add_domain')
  {
    $form_add_domain = 1;
  }

  if($do == 'send_action')
  {
    $form_send_action = 1;
  }

  if($do == 'select')
  {
    if (isset($_GET['domain']))
    {
      $sel_domain = $_GET['domain'];
      $_SESSION["domain"] = $sel_domain;
      $sel_domain = readDomainUrl($sel_domain);
    }
    if (isset($_GET['device']))
    {
      $sel_device = $_GET['device'];
      $_SESSION["device"]   = $sel_device;
      $doc = 'http://'.$sel_domain.'/devices/'.$sel_device;
      $sel_desc = getDesc($doc);
    }
  }


  if($do == 'delete')
  {
    $what   = $_GET['what'];
    if ($what == 'domain')
    {
      $fn = $sel_domain.'.domain';
      if (file_exists($fn)) unlink($fn);
    }
    if ($what == 'device')
    {
      restApi('delete',$sel_domain,$sel_device);
    }
    if ($what == 'log')
    {
      restApi('clearlog',$sel_domain,$sel_device);
    }
  }

  if($do == 'rest')
  {
    $api   = $_GET['api'];
    $url   = $_GET['url'];
    $topic = $_GET['topic'];
    restApi($api,$url,$topic);
  }
}
  

if (isset($_POST['do'])) 
{
  $do = $_POST['do'];

  if ($do == 'add_domain')
  {
    $dn = $_POST['domain'];
    if (strlen($dn) > 2)addDomain($dn);
  }

  if ($do == 'send_message')
  {
    $domain   = $_POST['domain'];
    $device   = $_POST['device'];
    $msg      = $_POST['message'];
    $tag      = $_POST['tag'];
    if (strlen($msg) > 2)
      sendMessage($domain,$device,$msg,$tag);
    else
      echo "Message to short";
  }
}

//=============================================
// Front-End
//=============================================
$data = array();

   echo "<html>
      <head>
      <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
      <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js\"></script>
      <style>

      #container {
      color: #336600;
      background-color: cornsilk;
      float: left;
      width: 1000px;
      height: 900px;
      }

      #config {
      color: #336600;
      //background-color: grey;
      float: left;
      width: 400px;
      }

      #meta {
      color: #336600;
      //background-color: red;
      float: left;
      width: 400px;
      }

      #payload {
      color: #336600;
      //background-color: blue;
      float: left;
      width: 400px;
      }


      #log {
      color: #336600;
      //background-color: yellow;
      float: left;
      width: 600px;
      }

      html {
          min-height: 100%;
      }

      body {
          background: -webkit-linear-gradient(left, #93B874, #C9DCB9);
          background: -o-linear-gradient(right, #93B874, #C9DCB9);
          background: -moz-linear-gradient(right, #93B874, #C9DCB9);
          background: linear-gradient(to right, #93B874, #C9DCB9);
          background-color: #93B874;
      }
      /* Navbar container */
   .navbar {
     overflow: hidden;
     background-color: #333;
     font-family: Arial;
   }

   /* Links inside the navbar */
   .navbar a {
     float: left;
     font-size: 16px;
     color: white;
     text-align: center;
     padding: 14px 16px;
     text-decoration: none;
   }

   /* The dropdown container */
   .dropdown {
     float: left;
     overflow: hidden;
   }

   /* Dropdown button */
   .dropdown .dropbtn {
     font-size: 16px;
     border: none;
     outline: none;
     color: white;
     padding: 14px 16px;
     background-color: inherit;
     font-family: inherit; /* Important for vertical align on mobile phones */
     margin: 0; /* Important for vertical align on mobile phones */
   }

   /* Add a red background color to navbar links on hover */
   .navbar a:hover, .dropdown:hover .dropbtn {
     background-color: red;
   }

   /* Dropdown content (hidden by default) */
   .dropdown-content {
     display: none;
     position: absolute;
     background-color: #f9f9f9;
     min-width: 160px;
     box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
     z-index: 1;
   }

   /* Links inside the dropdown */
   .dropdown-content a {
     float: none;
     color: black;
     padding: 12px 16px;
     text-decoration: none;
     display: block;
     text-align: left;
   }

   /* Add a grey background color to dropdown links on hover */
   .dropdown-content a:hover {
     background-color: #ddd;
   }

   /* Show the dropdown menu on hover */
   .dropdown:hover .dropdown-content {
     display: block;
   }
      </style>
         <title>Manager</title>
      </head>
      <body > ";

//=========================================================================
// body
//=========================================================================
echo("<b>Mercury Device Manager $sel_domain $sel_desc $now</b>");
echo "<div class=\"navbar\">";

    echo "<a href=\"manager.php?do=add_domain\">Add Domain</a>";

    echo "<div class=\"dropdown\">
             <button class=\"dropbtn\">Select Domain
             <i class=\"fa fa-caret-down\"></i>
             </button>
             <div class=\"dropdown-content\">
             ";
                $do = 'ls *.domain > domain.list';
                system($do);
                $file = fopen('domain.list', "r");
                if ($file)
                {
                  while(!feof($file))
                  {
                    $line = fgets($file);
                    if (strlen($line) > 2)
                    {
                       $line = trim($line);
                       $domain = str_replace(".domain", "", $line);
                       echo "<a href=manager.php?do=select&domain=$domain>$domain</a>";
                    }
                  }
                }
     echo "</div></div>";

     echo "<div class=\"dropdown\">
               <button class=\"dropbtn\">Select Device
               <i class=\"fa fa-caret-down\"></i>
               </button>
               <div class=\"dropdown-content\">
               ";
                   $res = listAllDevices();
                   $data = explode(":",$res);
                   $num = count($data);

                   for ($ii = 0; $ii < $num; $ii++)
                   {
                      $device = str_replace(".reg", "", $data[$ii]);
                      if (strlen($device) > 2)
                      {
                          //$doc = 'http://'.$sel_domain.'/devices/'.$device;
                          //$desc = getDesc($doc);
                          echo "<a style=\"background: cornsilk;\" href=manager.php?do=select&device=$device>$device</a>";
                      }
                   }
      echo "</div></div>";

      echo "<a href=\"manager.php?do=send_action\">Send Action</a>";

      echo "<div class=\"dropdown\">
               <button class=\"dropbtn\">Delete
               <i class=\"fa fa-caret-down\"></i>
               </button>
               <div class=\"dropdown-content\">
               ";            
                   echo "<a href=manager.php?do=delete&what=domain>$sel_domain</a>";
                   echo "<a href=manager.php?do=delete&what=device>$sel_device</a>";
                   echo "<a href=manager.php?do=delete&what=log>clear log $sel_device</a>";
      echo "</div></div>";
      
      echo "<a href=\"status.php\" target=\"_blank\">Status</a>";

      echo "</div>";

   //=============================================

if ($form_send_action == 1)
{
  //$doc = 'http://'.$sel_domain.'/device/'.$sel_device;
  echo "<br><br>
  <table border=0>";
  echo "
  <form action=\"#\" method=\"post\">
    <input type=\"hidden\" name=\"do\" value=\"send_message\">
    <tr><td>Domain</td><td> <input type=\"text\" name=\"domain\" value=$sel_domain></td>
    <tr><td>Device</td><td> <input type=\"text\" name=\"device\" value=$sel_device></td>
    <tr><td>Tag</td><td> <input type=\"text\" name=\"tag\" ></td>
    <tr><td>Message</td><td> <input type=\"text\" name=\"message\"></td>
    <td><input type= \"submit\" value=\"Send\"></td></tr>
  </form>
  </table>";
}
              
if ($form_add_domain == 1)
{
  echo "
  <form action=\"#\" method=\"post\">
    <input type=\"hidden\" name=\"do\" value=\"add_domain\">
    Add Domain<input type=\"text\" name=\"domain\">
    <input type= \"submit\" value=\"Add Domain\">
    </form> ";
}


  echo "<div id=\"config\">";
  echo "Config";
  $doc = 'http://'.$sel_domain.'/devices/'.$sel_device.'/config.json';
  $json   = file_get_contents($doc);
  if ($json)
  {
    $result = prettyTolk( $json);
    $id = generateForm($json,"white");
  }
  //echo ("<br>static<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
  echo "</div>";



  echo "<div id=\"meta\">";
  echo "Meta";
  $doc = 'http://'.$sel_domain.'/devices/'.$sel_device.'/meta.json';
  $json   = file_get_contents($doc);
  if ($json)
  {
    $result = prettyTolk( $json);
    $id = generateForm($json,"#FF5733");
  }
  //echo ("<br>dynamic<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
    echo "</div>";


  echo "<div id=\"payload\">";
  echo "Payload";
  $doc = 'http://'.$sel_domain.'/devices/'.$sel_device.'/payload.json';
  $json   = file_get_contents($doc);
  if ($json)
  {
    $result = prettyTolk( $json);
    $id = generateForm($json,"#A2D5F8");
  }
  //echo ("<br>payload<br><iframe style=\"background: #FFFFFF;\" src=$doc width=\"400\" height=\"300\"></iframe>");
    echo "</div>";


  $rnd = generateRandomString(3);
  echo "<div id=\"log\">";
  $doc = 'http://'.$sel_domain.'/devices/'.$sel_device.'/log.txt?ignore='.$rnd;
  echo ("<br>log<br><iframe id= \"ilog\" style=\"background: #FFFFFF;\" src=$doc width=\"500\" height=\"600\"></iframe>");
  echo "</div>";


echo "</body></html>";
// End of file
?>
