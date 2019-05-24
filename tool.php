<?php
session_start();
//=============================================
// File.......: tool.php
// Date.......: 2019-05-24
// Author.....: Benny Saxen
// Description: Mercury Test Tool
//=============================================
// Configuration
//=============================================
// No configuration needed
//=============================================
$date         = date_create();
$ts           = date_format($date, 'Y-m-d H:i:s');
//=============================================
// library
//=============================================

//=============================================
function generateHtml($inp)
//=============================================
{
  $jsonIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(json_decode($inp, TRUE)),RecursiveIteratorIterator::SELF_FIRST);
  echo("<table border=1>");
  foreach ($jsonIterator as $key => $val) {
      if(is_array($val)) {
          echo "<tr><td>$key</td><td></td></tr>";
      } else {
          //echo "     $key = $val";
          if($val != -1)echo "<tr><td>$key</td><td>$val</td><tr>";
      }
   }
   echo "</table>";
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

  if($do == 'something')
  {

  }
}

if (isset($_POST['action']))
{
  $action = $_POST['action'];

  if ($action == 'generateHtml')
  {
      $id = $_POST['id'];
      $no = $_POST['no'];
      $do = $_POST['do'];
      $log = $_POST['log'];
      $fb = $_POST['fb'];
      $json = $_POST['json'];
      generateHtml($json);
      $call = "http://localhost/git/mercury/gateway.php?id=$id&no=$no&do=$do&json=$json";
      echo $call;
      $res = file_get_contents($call);
  }
  if ($do == 'abcd')
  {
      $a = $_POST['a'];
      echo("$a");
  }
}
//=============================================
// Front-End
//=============================================
echo "<html>
   <head>
      <title>Tool</title>
   </head>
   <body> ";

echo("<h1>Tool</h1>");
echo ("<br><a href=tool.php?do=some&a=x>test_link</a>");

echo "<br><br>
   <table border=0>";
echo "
   <form action=\"#\" method=\"post\">
     <input type=\"hidden\" name=\"do\" value=\"abcd\">
     <tr><td>A</td><td> <input type=\"text\" name=\"a\" value=$a></td>
     <tr><td>B</td><td> <input type=\"text\" name=\"b\" value=$b></td>
     <tr><td>C</td><td> <input type=\"text\" name=\"c\" ></td>
     <tr><td>D</td><td> <input type=\"text\" name=\"d\"></td>
     <td><input type= \"submit\" value=\"Send\"></td></tr>
   </form>
   </table>";
echo "<br><br>
      <table border=1>";
echo "
      <form action=\"#\" method=\"post\" name=\"jjss\">
        <input type=\"hidden\" name=\"action\" value=\"generateHtml\">
        <tr><td>id</td><td>   <input type=\"text\" name=\"id\"></td>
        <tr><td>no</td><td>   <input type=\"text\" name=\"no\"></td>
        <tr><td>do</td><td>   <input type=\"text\" name=\"do\"></td>
        <tr><td>json</td><td> <textarea name=\"json\" rows=\"4\" cols=\"50\" >{\"json\":1,\"benny\":123}</textarea></td></tr>
        <tr><td>log</td><td>  <input type=\"text\" name=\"log\"></td>
        <tr><td>fb</td><td>   <input type=\"text\" name=\"fb\"></td>
        <td><input type= \"submit\" value=\"Send\"></td></tr>
      </form>
      </table>";

echo ("<iframe src=tool.php width=\"400\" height=\"300\"></iframe>");
//=============================================
// End of file
//=============================================
echo "</body></html>";
?>
