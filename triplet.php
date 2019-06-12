<?php
session_start();
//=============================================
// File.......: triplet.php
// Date.......: 2019-06-09
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

$vocx  = array();
$m_sub = array();
$m_pre = array();
$m_obj = array();
$m_3d  = array();
$v_error = array();

$do = '';
$file_abc = 'resources/abc.txt';
$file_rdf = 'resources/storage.rdf';
$g_class = 1;
$g_type = 3;
$dimension = 10;
//=============================================
// Start of library
//=============================================
$admin_triplets = $_SESSION["admin_triplets"];
$admin_terms    = $_SESSION["admin_terms"];
$current_node   = $_SESSION["current_node"];
$number_of_triplets = $_SESSION["number_of_triplets"];

function myfunction($value, $key) 
{ 
    echo "The key $key has the value $value<br>"; 
} 
//=============================================
function reasoning()
//=============================================
{
  global $g_class,$g_type;
  global $number_of_triplets;
  global $v_sub,$m_3d;
  echo "Reasoning";
  for ($ii = 1; $ii <= $number_of_triplets; $ii++)
  {
    $ix = $v_sub[$ii];
    if ($m_3d[$ix][$g_class][$g_type] == 1)
    {
        echo "<br>$ix is of type class";
    }
  }

 
  return;
}
//=============================================
function addTerm($f_abc,$term)
//=============================================
{
  echo "Add term to vocabulary";
  $f_file = $f_abc;
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$term->index $term->word\n");
        fclose($doc);
  }
  return;
}
//=============================================
function randomTriplet($f_rdf,$range, $total)
//=============================================
{
  echo "Add random triplet to storage";
  $f_file = $f_rdf;
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        for ($ii = 0; $ii < $total; $ii++)
        {
           $rand_sub = rand(1,$range);
           $rand_obj = rand(1,$range);
           $rand_pre = rand(1,$range);
           fwrite($doc, "$rand_sub $rand_pre $rand_obj\n");
        }
        fclose($doc);
  }
  return;
}
//=============================================
function addTriplet($f_rdf,$obj)
//=============================================
{
  echo "Add triplet to storage";
  $f_file = $f_rdf;
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->subject $obj->predicate $obj->object\n");
        fclose($doc);
  }
  return;
}
//=============================================
function clearTriplet($f_rdf)
//=============================================
{
  echo "Clear triplet storage";
  $f_file = $f_rdf;
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fclose($doc);
  }
  return;
}
//=============================================
function deleteTriplet($f_rdf,$row_number)
//=============================================
{
  $ok = 0;
  $filename1 = 'resources/temp.txt';
  $filename2 = $f_rdf;
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
      system("cp -f $filename1 $filename2");
  }
}
//=============================================
function deleteTerm($f_abc,$row_number)
//=============================================
{
  $ok = 0;
  $filename1 = 'resources/temp.txt';
  $filename2 = $f_abc;
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
      system("cp -f $filename1 $filename2");
  }
}
//=============================================
function listNodeNeighbours($node)
//=============================================
{
  global $number_of_triplets;
  global $v_sub,$v_obj;

  echo "<br><br>Neighbour Nodes<table border=0>";
  for ($ii = 1;$ii <= $number_of_triplets ;$ii++)
  {
    if ($v_sub[$ii] == $node)
    {
      echo("<tr><td>$v_obj[$ii]</td></tr>");
    }
  }
    echo("</table>");
  return;
}
//=============================================
function listAllTriplets()
//=============================================
{
  global $current_node;
  global $vocx;
  global $number_of_triplets;
  global $v_sub,$v_obj,$v_pre,$v_error;
  global $m_3d;
  global $dimension;

  echo("<p style=\"color:red;font-family:monospace;font-size: 10px;\"><table border=1>");
  for ($ii = 1; $ii <= $number_of_triplets;$ii++)
  {
        $ix_s = $v_sub[$ii];
        $ix_p = $v_pre[$ii];
        $ix_o = $v_obj[$ii];
        $ix_e = $v_error[$ii];

        $t1 = $vocx[$ix_s];
        $t2 = $vocx[$ix_p];
        $t3 = $vocx[$ix_o];

        if($current_node == $ix_s)
            echo("<tr><td>$ix_s</td>");
        else
            echo("<tr><td><a href=\"triplet.php?doget=select_node&node=$ix_s\">$ix_s</a></td>");  
        
        if($current_node == $ix_p)
            echo("<td>$ix_p</td>");
        else
            echo("<td><a href=\"triplet.php?doget=select_node&node=$ix_p\">$ix_p</a></td>");

        if($current_node == $ix_o)    
            echo("<td>$ix_o</td>");
        else
            echo("<td><a href=\"triplet.php?doget=select_node&node=$ix_o\">$ix_o</a></td>");

        echo("<td>$t1</td><td>$t2</td><td>$t3</td>");
        if ($ix_s > $dimension || $ix_o > $dimension)
          echo "<td><a style=\"color:red;\" href=\"triplet.php?doget=delete_triplet&row=$ii\"> X </a></td>";
        else 
          echo "<td><a href=\"triplet.php?doget=delete_triplet&row=$ii\"> X </a></td>";

        if ($ix_e == 0)
          echo("<td>ok</td></tr>");  
        else
          echo("<td>duplicates $ix_e</td></tr>"); 
    }
    fclose($file);
    echo("</table></p>");
  return;
}
//=============================================
function update($f_rdf)
//=============================================
{
  global $current_node;
  global $vocx;
  global $number_of_triplets;
  global $v_sub,$v_obj,$v_pre;
  global $m_3d;
  global $v_error;

  $file = fopen($f_rdf, "r");
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
      if (strlen($line) > 2)
      {
        sscanf($line, "%d %d %d", $ix_s,$ix_p,$ix_o );
        $v_sub[$row_number] = $ix_s;
        $v_pre[$row_number] = $ix_p;
        $v_obj[$row_number] = $ix_o;
        if ($m_3d[$ix_s][$ix_o][$ix_p] == 0)
        {
          $m_3d[$ix_s][$ix_o][$ix_p] = 1;
          $v_error[$row_number] = 0;
        }
        else
        {
          $v_error[$row_number]++;
        }
      }
    }
    fclose($file);
    $number_of_triplets = $row_number - 1;
    $_SESSION["number_of_triplets"] = $number_of_triplets;
  }
  return;
}
//=============================================
function listAllTerms($f_abc)
//=============================================
{
  global $dimension;

  $file = fopen($f_abc, "r");
  if ($file)
  {
    $row_number = 0;
    echo "<p style=\"color:red;font-family:monospace;font-size: 10px;\"><table border=1>";
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
        if ($ix <= $dimension)
          echo "<td><a href=\"triplet.php?doget=delete_term&row=$row_number\"> X </a></td></tr>";
        else
          echo "<td><a style=\"color:red;\" href=\"triplet.php?doget=delete_term&row=$row_number\"> X </a></td></tr>";
      }
    }
    echo("</table>");
    fclose($file);
  }
  return;
}
//=============================================
function readVocabulary($f_abc)
//=============================================
{
  global $vocx;
  $file = fopen($f_abc, "r");
  if ($file)
  {
    while(!feof($file))
    {
      $line = fgets($file);
      //echo "<br>$line";
      sscanf($line, "%d %s", $ix, $word);
      $vocx[$ix] = $word;
    }
    fclose($file);
  }
  return;
}

//=============================================
function listObjectsForThisNode($node) // x = a
//=============================================
{
  global $m_3d;
  global $dimension;
  global $adj_obj;
  echo "<p style=\"color:red;font-family:monospace;font-size: 10px;\"><br>";
  $counter = 0;
  for($yy=1;$yy<=$dimension;$yy++)
  {
    for($zz=1;$zz<=$dimension;$zz++)
    {
        $temp = $m_3d[$node][$yy][$zz];
        if ($temp == 1) 
        {
          $counter ++;
          echo("$node --($zz)--> $yy <br>");
          $adj_obj[$counter] = $yy;
        }
    }
  }
  $adj_obj[0] = $counter;
  echo "Number of adjacent objects: $counter</p>";
}
//=============================================
function listSubjectsForThisNode($node) // y = b
//=============================================
{
  global $m_3d;
  global $dimension;
  global $adj_sub;
  echo "<p style=\"color:blue;font-family:monospace;font-size: 10px;\"><br>";
  $counter = 0;
  for($xx=1;$xx<=$dimension;$xx++)
  {
    for($zz=1;$zz<=$dimension;$zz++)
    {
        $temp = $m_3d[$xx][$node][$zz];
        if ($temp == 1) 
        {
          $counter ++;
          echo("$node <--($zz)-- $xx <br>");
          $adj_sub[$counter] = $xx;
        }
    }
  }  
  $adj_sub[0] = $counter;
  echo "Number of adjacent subjects: $counter</p>";
}
//=============================================
function listNodesForThisPredicate($node) // z = c
//=============================================
{
  global $m_3d;
  global $dimension;
  echo "<p style=\"color:green;font-family:monospace;font-size: 10px;\">Subjects and Objects using this predicate<br>";
  for($xx=1;$xx<=$dimension;$xx++)
  {
    for($yy=1;$yy<=$dimension;$yy++)
    {
        $temp = $m_3d[$xx][$yy][$node];
        if ($temp == 1) echo("$xx --($node)--> $yy <br>");
    }
  }  
  echo("</p>");
}
//=============================================
function showMatrixA()
//=============================================
{
  global $m_3d;
  global $dimension;
  global $current_node;

  echo "<p class=\"mx\">Adjacency Matrix<br>";

  for($xx=0;$xx<=$dimension;$xx++)
  {
    echo sprintf("%03d&nbsp",$xx);
  }
  //echo "<br>";
  for($xx=1;$xx<=$dimension;$xx++)
  {
    echo sprintf("<br><br><a href=\"triplet.php?doget=select_node&node=$xx\">%03d</a>",$xx);
    for($yy=1;$yy<=$dimension;$yy++)
    {
      $temp = 0;
      for($zz=1;$zz<=$dimension;$zz++)
      {
        $val = $m_3d[$xx][$yy][$zz];
        if ($val == 1) $temp = $temp + $val;
      }
      if ($temp != 0 && $xx == $current_node) 
          echo sprintf("&nbsp<a style=\"color:red\" href=\"triplet.php?doget=select_node&node=$yy\">[%1d]</a>",$temp);
      else if ($temp != 0 && $yy == $current_node) 
          echo sprintf("&nbsp<a style=\"color:blue\" href=\"triplet.php?doget=select_node&node=$xx\">[%1d]</a>",$temp);
      else if ($temp != 0 && $xx != $current_node) 
          echo sprintf("&nbsp[%1d]",$temp);
      else
          printf("&nbsp&nbsp&nbsp.");
    }
  }  
  echo("</p>");
}
//=============================================
// End of library
//=============================================
//echo "<br>admin triplets $admin_triplets ";
//echo "admin terms $admin_terms current_node $current_node<br>";
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
           deleteTriplet($file_rdf,$row);
       }
    }
    if ($do == 'clear_triplet')
    {
        clearTriplet($file_rdf);
    }

    if ($do == 'random_triplet')
    {
       $npar = 0;
       if (is_numeric($_GET['range']))
       {
           $npar++;
           $range = $_GET['range'];
       }
       if (is_numeric($_GET['total']))
       {
           $npar++;
           $total = $_GET['total'];
       }
       if ($npar == 2) randomTriplet($file_rdf,$range, $total);
    }

    if ($do == 'delete_term')
    {
       if (is_numeric($_GET['row']))
       {
           $row = $_GET['row'];
           deleteTerm($file_abc,$row);
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
            addTriplet($file_rdf,$obj);
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
          addTriplet($file_rdf,$obj);
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
          addTerm($file_abc,$term);
      }
      else
      {
          echo "POST Missing term information";
      }
  }
}

readVocabulary($file_abc);
update($file_rdf);
//=============================================
// Front-End
//=============================================
echo("<html>");
echo("<head>");

echo("<style>
a:link {
  text-decoration: none;
  color: blue;
}
a:visited {
  text-decoration: none;
  color: green;
}
a:hover {
  text-decoration: none;
  color: red;
}
.mx {
  font-size: 10px;
  font-family: monospace;
}
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
</style>");

echo("</head>");
echo("<body>");

echo("<br><b>Graph Player Current Node: $current_node</b>");
echo "<div class=\"navbar\">";

    echo "<a href=\"triplet.php?doget=admin_triplets\">Show Triplets</a>";
    echo "<a href=\"triplet.php?doget=admin_terms\">Show Terms</a>";
    echo "<a href=\"triplet.php?doget=clear_triplet\">Clear Triplets</a>";
    echo "<a href=\"triplet.php?doget=random_triplet&range=$dimension&total=10\">Create Random Triplets</a>";

    echo "<div class=\"dropdown\">
             <button class=\"dropbtn\">Configure
             <i class=\"fa fa-caret-down\"></i>
             </button>
             <div class=\"dropdown-content\">
             ";
            echo "<a href=manager.php?do=select&domain=$space>Space</a>";
     echo "</div></div>";

echo "</div>";
echo "<br>";

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

  listAllTriplets($file_rdf);
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
listAllTerms($file_abc);

}
showMatrixA();
listObjectsForThisNode($current_node); // x = a
listSubjectsForThisNode($current_node); // y = b
listNodesForThisPredicate($current_node); // z = c
//echo ("<iframe id= \"ilog\" style=\"background: #FFFFFF;\" src=$doc_voc width=\"100\" height=\"100\"></iframe>");

//reasoning();
//$arr = array("a"=>"yellow", "b"=>"pink", "c"=>"purple"); 
  
// calling array_walk() with no extra parameter 
//array_walk($vocx, "myfunction"); 
?>
</body>
</html>
