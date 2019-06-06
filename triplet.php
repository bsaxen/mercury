<?php
session_start();
//=============================================
// File.......: triplet.php
// Date.......: 2019-06-06
// Author.....: Benny Saxen
// Description: 
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
$m_sub = array();
$m_pre = array();
$m_obj = array();
$do = '';
//=============================================
// Start of library
//=============================================
$admin_triplets = $_SESSION["admin_triplets"];
$admin_terms    = $_SESSION["admin_terms"];
$current_node   = $_SESSION["current_node"];
$number_of_triplets = $_SESSION["number_of_triplets"];
//=============================================
function addTerm($term)
//=============================================
{
  echo "Add term to vocabulary";
  $f_file = 'abc.txt';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$term->index $term->word\n");
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
        fwrite($doc, "$obj->subject $obj->predicate $obj->object\n");
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
function deleteTerm($row_number)
//=============================================
{
  $ok = 0;
  $filename1 = 'temp.txt';
  $filename2 = 'abc.txt';
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
      system("cp -f temp.txt abc.txt");
  }
}
//=============================================
function listNodeNeighbours($node)
//=============================================
{
  global $number_of_triplets;
  global $m_sub,$m_obj;

  echo "<br><br>Neighbour Nodes<table border=0>";
  for ($ii = 1;$ii <= $number_of_triplets ;$ii++)
  {
    if ($m_sub[$ii] == $node)
    {
      echo("<tr><td>$m_obj[$ii]</td></tr>");
    }
  }
    echo("</table>");
  return;
}
//=============================================
function listAllTriplets($vo)
//=============================================
{
  global $vocx;
  global $number_of_triplets;
  global $m_sub,$m_obj;

  $file = fopen('storage.rdf', "r");
  if ($file)
  {
    $row_number = 0;
    echo "<table border=1>";
    while(!feof($file))
    {
      $row_number++;
      $t1 = '-';
      $t2 = '-';
      $t3 = '-';
      $line = fgets($file);
      if (strlen($line) > 2)
      {
        $s = 0;$o = 0; $p = 0;
        sscanf($line, "%d %d %d", $ix_s,$ix_p,$ix_o );
        $m_sub[$row_number] = $ix_s;
        $m_pre[$row_number] = $ix_p;
        $m_obj[$row_number] = $ix_o;
        $t1 = $vocx[$ix_s];
        $t2 = $vocx[$ix_p];
        $t3 = $vocx[$ix_o];
        echo("<tr><td>$ix_s</td><td>$ix_p</td><td>$ix_o</td>");
        echo("<td>$t1</td><td>$t2</td><td>$t3</td>");
        echo "<td><a href=\"triplet.php?doget=delete_triplet&row=$row_number\"> X </a></td></tr>";
      }
    }
    $number_of_triplets = $row_number - 1;
    echo("</table> $number_of_triplets");
    $_SESSION["number_of_triplets"] = $number_of_triplets;
  }
  return;
}
//=============================================
function listAllTerms()
//=============================================
{
  $file = fopen('abc.txt', "r");
  if ($file)
  {
    $row_number = 0;
    echo "<table border=1>";
    while(!feof($file))
    {
      $row_number++;
      $t1 = '-';
      $t2 = '-';
      $t3 = '-';
      $line = fgets($file);
      if (strlen($line) > 2)
      {
        sscanf($line, "%d %s", $ix,$term);
        echo("<tr><td>$ix</td><td><a href=\"triplet.php?doget=select_node&node=$ix\">$term</a></td>");
        echo "<td><a href=\"triplet.php?doget=delete_term&row=$row_number\"> X </a></td></tr>";
      }
    }
    echo("</table>");
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
      //echo "<br>$line";
      sscanf($line, "%d %s", $ix, $word);
      $vocx[$ix] = $word;
    }
  }
  return;
}
//=============================================
// End of library
//=============================================
echo "<br>admin triplets $admin_triplets ";
echo "admin terms $admin_terms current_node $current_node<br>";
//=============================================
// GET 
//=============================================
if (isset($_GET['doget'])) // Mandatory
{
    $do = $_GET['doget']; 

    if ($do == 'select_node')
    {
       if (is_numeric($_GET['node']))
       {
           $current_node = $_GET['node'];
       }
    }

    if ($do == 'admin_triplets')
    {
      if ($admin_triplets == 0)
        $admin_triplets = 1;
      else
        $admin_triplets = 0;
    }

    if ($do == 'admin_terms')
    {
      if ($admin_terms == 0)
        $admin_terms = 1;
      else
        $admin_terms = 0;
    }

    if ($do == 'delete_triplet')
    {
       if (is_numeric($_GET['row']))
       {
           $row = $_GET['row'];
           deleteTriplet($row);
       }
    }

    if ($do == 'delete_term')
    {
       if (is_numeric($_GET['row']))
       {
           $row = $_GET['row'];
           deleteTerm($row);
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
$_SESSION["admin_triplets"] = $admin_triplets;
$_SESSION["admin_terms"] = $admin_terms;
$_SESSION["current_node"] = $current_node;
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
          $obj->predicate = $_POST['pre'];
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
echo "<a href=\"triplet.php?doget=admin_triplets\"> Triplets</a>";
echo "<a href=\"triplet.php?doget=admin_terms\"> Terms </a>";
//$doc_voc = 'abc.txt';



if ($admin_triplets == 1)
{
  echo "<table border=3>";
  echo "
  <form action=\"triplet.php\" method=\"post\">
  <input type=\"hidden\" name=\"dopost\" value=\"add_triplet\">
  <tr><td>Subject</td><td> <input type=\"text\" name=\"sub\" size=\"5\"></td>
  <td>Predicate</td><td> <input type=\"text\" name=\"pre\" size=\"5\"></td>
  <td>Object</td><td> <input type=\"text\" name=\"obj\"  size=\"5\"></td>
  <td><input type= \"submit\" value=\"Create Triplet\"></td></tr>
  </form>
  </table>";
  listAllTriplets($vocx);
}

if ($admin_terms == 1)
{
echo "<table border=0>";
echo "
<form action=\"triplet.php\" method=\"post\">
  <input type=\"hidden\" name=\"dopost\" value=\"add_term\">
  <tr><td>Index</td><td> <input type=\"text\" name=\"index\" size=\"10\"></td>
  <tr><td>Term</td><td> <input type=\"text\" name=\"term\" size=\"10\"></td>
  <td><input type= \"submit\" value=\"Create Term\"></td></tr>
</form>
</table>";
listAllTerms();
}

listNodeNeighbours($current_node);
//echo ("<iframe id= \"ilog\" style=\"background: #FFFFFF;\" src=$doc_voc width=\"100\" height=\"100\"></iframe>");
?>
