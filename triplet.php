<?php
//=============================================
// File.......: triplet.php
// Date.......: 2019-06-05
// Author.....: Benny Saxen
// Description: Mercury Gateway
//=============================================
class triplet {
    public $subject;
    public $object;
    public $predicate;
}
$obj = new triplet();

class term {
  public $index;
  public $word;
}
$term = new term();

$vocx = array();
$do = '';
//=============================================
// Start of library
//=============================================
//=============================================
function addTerm($term)
//=============================================
{
  echo "Add term to vocabulary";
  $f_file = 'abc.txt';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$term->index,$term->word\n");
        fclose($doc);
  }
  return;
}
//=============================================
function addTriplet($obj)
//=============================================
{
  echo "Add triplet to storage";
  $f_file = 'storage.rdf';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->subject,$obj->predicate,$obj->object\n");
        fclose($doc);
  }
  return;
}
//=============================================
function deleteTriplet($row_number)
//=============================================
{
  $ok = 0;
  $filename1 = 'temp.txt';
  $filename2 = 'storage.rdf';
  $fh1 = fopen($filename1, 'w') or die("Cannot write to file $filename1");
  $fh2 = fopen($filename2, 'r') or die("Cannot read file $filename2");
  $lines = 0;
  while(!feof($fh2))
  {
      $lines++;
      $line = fgets($fh2);
      if ($lines != $row_number)
      {
         fwrite($fh1, "$line");
      }
  }
  $ok = 1;
  fclose($fh1);
  fclose($fh2);
  if ($ok == 1)
  {
      system("cp -f temp.txt storage.rdf");
  }
}
//=============================================
function listAllTriplets($vo)
//=============================================
{
  global $vocx;
  $file = fopen('storage.rdf', "r");
  if ($file)
  {
    $row_number = 0;
    while(!feof($file))
    {
      $row_number++;
      $t1 = '-';
      $t2 = '-';
      $t3 = '-';
      $line = fgets($file);
      echo "<br>$line";
      if (strlen($line) > 2)
      {
        $s = 0;$o = 0; $p = 0;
        sscanf($line, "%d,%d,%d", $ix_s,$ix_p,$ix_o );
        $t1 = $vocx[$ix_s];
        $t2 = $vocx[$ix_p];
        $t3 = $vocx[$ix_o];
        echo(">> $t1 $t2 $t3");
        echo "<a href=\"triplet.php?doget=delete_triplet&row=$row_number\"> X </a>";
      }
    }
  }
  return;
}
//=============================================
function readVocabulary($voc)
//=============================================
{
  global $vocx;
  $file = fopen($voc, "r");
  if ($file)
  {
    while(!feof($file))
    {
      $line = fgets($file);
      echo "<br>$line";
      sscanf($line, "%d %s", $ix, $word);
      $vocx[$ix] = $word;
    }
  }
  return;
}
//=============================================
// End of library
//=============================================

//=============================================
// GET 
//=============================================
if (isset($_GET['doget'])) // Mandatory
{
    $do = $_GET['doget']; 
    if ($do == 'delete_triplet')
    {
      //echo "Delete triplet GET";
       if (is_numeric($_GET['row']))
       {
           $row = $_GET['row'];
           echo("row=$row<br>");
           deleteTriplet($row);
       }
    }
    if ($do == 'add_triplet')
    {
      //echo "Add triplet GET";
        $npar = 0;
        if (is_numeric($_GET['sub'])) // Mandatory
        {
            $npar++;
            $obj->subject = $_GET['sub'];
        }
        if (is_numeric($_GET['obj'])) // Mandatory
        {
            $npar++;
            $obj->object = $_GET['obj'];
        }
        if (is_numeric($_GET['pre'])) // Mandatory
        {
            $npar++;
            $obj->predicate = -$_GET['pre'];
        }
        if($npar == 3)
        {
            addTriplet($obj);
        }
        else
        {
            echo "GET Missing triplet information";
        }
    }
}

//=============================================
// POST
//=============================================
if (isset($_POST['dopost'])) 
{
  $do = $_POST['dopost'];
  if ($do == 'add_triplet')
  {
    //echo "Add triplet POST";
      $npar = 0;
      if (is_numeric($_POST['sub'])) // Mandatory
      {
          $npar++;
          $obj->subject = $_POST['sub'];
      }
      if (is_numeric($_POST['obj'])) // Mandatory
      {
          $npar++;
          $obj->object = $_POST['obj'];
      }
      if (is_numeric($_POST['pre'])) // Mandatory
      {
          $npar++;
          $obj->predicate = -$_POST['pre'];
      }
      if($npar == 3)
      {
          addTriplet($obj);
      }
      else
      {
          echo "POST Missing triplet information";
      }
  }
  if ($do == 'add_term')
  {
      $npar = 0;
      if (is_numeric($_POST['index'])) // Mandatory
      {
          $npar++;
          $term->index = $_POST['index'];
      }
      if (isset($_POST['term'])) // Mandatory
      {
          $npar++;
          $term->word = $_POST['term'];
      }
      if($npar == 2)
      {
          addTerm($term);
      }
      else
      {
          echo "POST Missing term information";
      }
  }
}

readVocabulary('abc.txt');

//=============================================
// Front-End
//=============================================
echo "<br>";
//$doc_voc = 'abc.txt';

echo "<table border=0>";
echo "
<form action=\"triplet.php\" method=\"post\">
  <input type=\"hidden\" name=\"dopost\" value=\"add_triplet\">
  <tr><td>Subject</td><td> <input type=\"text\" name=\"sub\" size=\"10\"></td>
  <tr><td>Predicate</td><td> <input type=\"text\" name=\"pre\" size=\"10\"></td>
  <tr><td>Object</td><td> <input type=\"text\" name=\"obj\"  size=\"10\"></td>
  <td><input type= \"submit\" value=\"New Triplet\"></td></tr>
</form>
</table>";

echo "<table border=0>";
echo "
<form action=\"triplet.php\" method=\"post\">
  <input type=\"hidden\" name=\"dopost\" value=\"add_term\">
  <tr><td>Index</td><td> <input type=\"text\" name=\"index\" size=\"10\"></td>
  <tr><td>Term</td><td> <input type=\"text\" name=\"term\" size=\"10\"></td>
  <td><input type= \"submit\" value=\"New Term\"></td></tr>
</form>
</table>";

listAllTriplets($vocx);
//echo ("<iframe id= \"ilog\" style=\"background: #FFFFFF;\" src=$doc_voc width=\"100\" height=\"100\"></iframe>");
?>
