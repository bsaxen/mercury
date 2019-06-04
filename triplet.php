<?php
//=============================================
// File.......: gateway.php
// Date.......: 2019-06-04
// Author.....: Benny Saxen
// Description: Mercury Gateway
//=============================================
class triplet {
    public $subject;
    public $object;
    public $predicate;
}
$obj = new triplet();
//=============================================
// Start of library
//=============================================
//=============================================
function addTriplet($obj)
//=============================================
{
  echo "Add triplet to storage";
  $f_file = 'storage.rdf';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->subject,$obj->object,$obj->predicate\n");
        fclose($doc);
  }
  return;
}
//=============================================
function listAllTriplets()
//=============================================
{
 
  $file = fopen('storage.rdf', "r");
  if ($file)
  {
    while(!feof($file))
    {
      $line = fgets($file);
      echo "<br>$line";
    }
  }
  return;
}
//=============================================
// End of library
//=============================================
if (isset($_GET['do'])) // Mandatory
{
    $npar = 0;
    $do = $_GET['do']; 
    if ($do == 'add')
    {
        if (isset($_GET['sub'])) // Mandatory
        {
            $npar++;
            $obj->subject = $_GET['sub'];
        }
        if (isset($_GET['obj'])) // Mandatory
        {
            $npar++;
            $obj->object = $_GET['obj'];
        }
        if (isset($_GET['pre'])) // Mandatory
        {
            $npar++;
            $obj->predicate = $_GET['pre'];
        }
        if($npar == 3)
        {
            addTriplet($obj);
        }
        else
        {
            echo "Missing triplet information";
        }
    }
}
//=============================================
// GET POST
//=============================================
if (isset($_POST['do'])) 
{
  $do = $_POST['do'];
  if ($do == 'add_triplet')
  {
    $obj->subject   = $_POST['subject'];
    $obj->object    = $_POST['object'];
    $obj->predicate = $_POST['predicate'];
    addTriplet($obj);
  }
}
//=============================================
// Front-End
//=============================================
echo "<br><br>
<table border=0>";
echo "
<form action=\"#\" method=\"post\">
  <input type=\"hidden\" name=\"do\" value=\"add_triplet\">
  <tr><td>Subject</td><td> <input type=\"text\" name=\"subject\"></td>
  <tr><td>Predicate</td><td> <input type=\"text\" name=\"predicate\"></td>
  <tr><td>Object</td><td> <input type=\"text\" name=\"object\" ></td>
  <td><input type= \"submit\" value=\"Create\"></td></tr>
</form>
</table>";

listAllTriplets();

?>
