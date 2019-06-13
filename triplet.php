<?php
session_start();
//=============================================
// File.......: triplet.php
// Date.......: 2019-06-13
// Author.....: Benny Saxen
// Description: 
//=============================================
class triplet {
    public $subject;
    public $object;
    public $predicate;
}
$obj = new triplet();

class configuration {
  public $dimension;
  public $filename;
  public $direction;
  public $file_abd;
  public $file_rdf;
  public $n_rnd_tpl;
}
$config = new configuration();
$config->filename = "resources/configuration.txt";

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

$m_graphs = array();

$do = '';
$config->file_abc = 'resources/abc.txt';
$config->file_rdf = 'resources/storage.rdf';

$config->dimension = 10;
$config->direction = 2; // 1=one direction, 2 =bidirectional
$config->n_rnd_tpl = 10;

readConfig($config);

//=============================================
// Start of library
//=============================================
$admin_triplets = $_SESSION["admin_triplets"];
$admin_terms    = $_SESSION["admin_terms"];
$current_node   = $_SESSION["current_node"];
$set_configuration  = $_SESSION["set_configuration"];
//$dimension      = $_SESSION["dimension"];
//$direction      = $_SESSION["direction"];
$number_of_triplets = $_SESSION["number_of_triplets"];


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
function writeConfig($co)
//=============================================
{
  echo "Write configuration";
  $f_file = $co->filename;
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "DIMENSION $co->dimension\n");
        fwrite($doc, "DIRECTION $co->direction\n");
        fwrite($doc, "RANDOM_N $co->n_rnd_tpl\n");

        fwrite($doc, "FILE_ABC $co->file_abc\n");
        fwrite($doc, "FILE_RDF $co->file_rdf\n");
 
        fclose($doc);
  }
  return;
}
//=============================================
function readConfig($co)
//=============================================
{
  //echo "Read configuration";
  $f_file = $co->filename;
  $doc = fopen($f_file, "r");
  if ($doc)
  {
    while(!feof($doc))
    {
        $line = fgets($doc);
        sscanf($line, "%s %d", $key,$value );
        if ($key == "DIMENSION") $co->dimension = $value;
        if ($key == "DIRECTION") $co->direction = $value;
        if ($key == "RANDOM_N") $co->n_rnd_tpl = $value;
    }
    fclose($doc);
  }
}
//=============================================
function addTerm($co,$term)
//=============================================
{
  echo "Add term to vocabulary";
  $f_file = $co->file_abc;
  $doc = fopen($f_file, "a");
  if ($doc)
  {
      if($term->index < $co->dimension && $term->index > 0)
      {
        fwrite($doc, "$term->index $term->word\n");
      }
      else
        echo("Index out of range $term->index<br>");

      fclose($doc);
  }
  return;
}
//=============================================
function randomTriplet($co)
//=============================================
{
  echo "Add random triplet to storage";
  $f_file = $co->file_rdf;
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        for ($ii = 1; $ii <= $co->n_rnd_tpl; $ii++)
        {
           $rand_sub = rand(1,$co->dimension);
           $rand_obj = rand(1,$co->dimension);
           $rand_pre = rand(1,$co->dimension);
           fwrite($doc, "$rand_sub $rand_pre $rand_obj\n");
           if ($co->direction == 2)
            fwrite($doc, "$rand_obj $rand_pre $rand_sub\n");
        }
        fclose($doc);
  }
  return;
}
//=============================================
function addTriplet($co,$obj)
//=============================================
{
  echo "Add triplet to storage";
  $temp = $obj->subject;
  if($temp > $co->dimension || $temp < 1)
  {
    echo "<h1>Subject index out of range: $temp</h1>";
    return;
  } 
  $temp = $obj->object;
  if($temp > $co->dimension || $temp < 1)
  {
    echo "<h1>Object index out of range: $temp</h1>";
    return;
  } 
  $temp = $obj->predicate;
  if($temp > $co->dimension || $temp < 1)
  {
    echo "<h1>Predicate index out of range: $temp</h1>";
    return;
  } 
  $f_file = $co->file_rdf;
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->subject $obj->predicate $obj->object\n");
        if ($co->direction == 2)
          fwrite($doc, "$obj->object $obj->predicate $obj->subject\n");
        fclose($doc);
  }
  return;
}
//=============================================
function clearTriplet($co)
//=============================================
{
  echo "Clear triplet storage";
  $f_file = $co->file_rdf;
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fclose($doc);
  }
  return;
}
//=============================================
function deleteTriplet($co,$row_number)
//=============================================
{
  $ok = 0;
  $filename1 = 'resources/temp.txt';
  $filename2 = $co->file_rdf;
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
function deleteTerm($co,$row_number)
//=============================================
{
  $ok = 0;
  $filename1 = 'resources/temp.txt';
  $filename2 = $co->file_abc;
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
function listAllTriplets($co)
//=============================================
{
  global $current_node;
  global $vocx;
  global $number_of_triplets;
  global $v_sub,$v_obj,$v_pre,$v_error;
  global $m_3d;

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
        if ($ix_s > $co->dimension || $ix_o > $co->dimension)
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
function update($co)
//=============================================
{
  global $current_node;
  global $vocx;
  global $number_of_triplets;
  global $v_sub,$v_obj,$v_pre;
  global $m_3d;
  global $v_error;

  $file = fopen($co->file_rdf, "r");
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
          $m_3d[$ix_s][$ix_o][0] = 1;
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
function listAllTerms($co)
//=============================================
{
  global $dimension;

  $file = fopen($co->file_abc, "r");
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
        if ($ix <= $co->dimension)
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
function readVocabulary($co)
//=============================================
{
  global $vocx;
  $file = fopen($co->file_abc, "r");
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
function listObjectsForThisNode($co,$node) // x = a
//=============================================
{
  global $m_3d;
  global $adj_obj;
  echo "<p style=\"color:red;font-family:monospace;font-size: 10px;\"><br>";
  $counter = 0;
  for($yy=1;$yy<=$co->dimension;$yy++)
  {
    for($zz=1;$zz<=$co->dimension;$zz++)
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
function listSubjectsForThisNode($co,$node) // y = b
//=============================================
{
  global $m_3d;
  global $adj_sub;
  echo "<p style=\"color:blue;font-family:monospace;font-size: 10px;\"><br>";
  $counter = 0;
  for($xx=1;$xx<=$co->dimension;$xx++)
  {
    for($zz=1;$zz<=$co->dimension;$zz++)
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
function listNodesForThisPredicate($co,$node) // z = c
//=============================================
{
  global $m_3d;

  echo "<p style=\"color:green;font-family:monospace;font-size: 10px;\">Subjects and Objects using this predicate<br>";
  for($xx=1;$xx<=$co->dimension;$xx++)
  {
    for($yy=1;$yy<=$co->dimension;$yy++)
    {
        $temp = $m_3d[$xx][$yy][$node];
        if ($temp == 1) echo("$xx --($node)--> $yy <br>");
    }
  }  
  echo("</p>");
}
//=============================================
function iterateGraphs($co) 
//=============================================
{
  global $m_3d;
  global $m_graphs;

  for($xx=1;$xx<=$co->dimension;$xx++)
  {
    for($yy=1;$yy<=$co->dimension;$yy++)
    {
      $m_graphs[$xx][$yy] =  $m_3d[$xx][$yy][0];
    }
  }  

  for ($dd = 1; $dd <= $co->n_rnd_tpl+1; $dd++)
  {
    for($xx=1;$xx<=$co->dimension;$xx++)
    {
      for($yy=1;$yy<=$co->dimension;$yy++)
      {
        $sum = 0;
        for($tt=1;$tt<=$co->dimension;$tt++) 
        {  
          $sum = $sum + $m_graphs[$xx][$tt]*$m_graphs[$tt][$yy];
        }
        //echo("dd=$dd xx=$xx yy=$yy sum=$sum<br>");
        $m_graphs[$xx][$yy] = $m_graphs[$xx][$yy] + $sum;
        if ($m_graphs[$xx][$yy] > 1)$m_graphs[$xx][$yy] = 1;
      }
    }  
  }
  echo("</p>");
}
//=============================================
function showMatrixA($co)
//=============================================
{
  global $m_3d;
  global $current_node;
  global $m_graphs;

  echo "<p class=\"mx\">Adjacency Matrix<br>";

  for($xx=0;$xx<=$co->dimension;$xx++)
  {
    echo sprintf("%03d&nbsp",$xx);
  }
  //echo "<br>";
  for($xx=1;$xx<=$co->dimension;$xx++)
  {
    echo sprintf("<br><br><a href=\"triplet.php?doget=select_node&node=$xx\">%03d</a>",$xx);
    for($yy=1;$yy<=$co->dimension;$yy++)
    {
      $temp = 0;
      for($zz=1;$zz<=$co->dimension;$zz++)
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


  echo "<br><br>";
  for($xx=0;$xx<=$co->dimension;$xx++)
  {
    echo sprintf("%03d&nbsp",$xx);
  }
  //echo "<br>";
  for($xx=1;$xx<=$co->dimension;$xx++)
  {
    echo sprintf("<br><br><a href=\"triplet.php?doget=select_node&node=$xx\">%03d</a>",$xx);
    for($yy=1;$yy<=$co->dimension;$yy++)
    {
        $temp = 0;
        $temp = $m_graphs[$xx][$yy];
      if ($temp != 0 ) 
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


    if ($do == 'set_configuration')
    {
      if ($set_configuration == 0)
        $set_configuration = 1;
      else
        $set_configuration = 0;
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
           deleteTriplet($config,$row);
       }
    }
    if ($do == 'clear_triplet')
    {
        clearTriplet($config);
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
       if ($npar == 2) randomTriplet($config);
    }

    if ($do == 'delete_term')
    {
       if (is_numeric($_GET['row']))
       {
           $row = $_GET['row'];
           deleteTerm($config,$row);
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
            addTriplet($config,$obj);
        }
        else
        {
            echo "GET Missing triplet information";
        }
    }
}
$_SESSION["admin_triplets"] = $admin_triplets;
$_SESSION["admin_terms"]    = $admin_terms;
$_SESSION["set_configuration"]  = $set_configuration;
$_SESSION["current_node"]   = $current_node;

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
          addTriplet($config,$obj);
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
          addTerm($config,$term);
      }
      else
      {
          echo "POST Missing term information";
      }
  }
  if ($do == 'set_configuration')
  {
      $npar = 0;
      if (is_numeric($_POST['dimension'])) // Mandatory
      {
          $npar++;
          $config->dimension = $_POST['dimension'];
          $_SESSION["dimension"]  = $config->dimension;
      }
      if (is_numeric($_POST['direction'])) // Mandatory
      {
          $npar++;
          $config->direction = $_POST['direction'];
          $_SESSION["direction"]  = $config->direction;
      }

      if (is_numeric($_POST['n_rnd_tpl'])) // Mandatory
      {
          $npar++;
          $config->n_rnd_tpl = $_POST['n_rnd_tpl'];
          $_SESSION["n_rnd_tpl"]  = $config->n_rnd_tpl;
      }

      if($npar == 3)
      {
          writeConfig($config);
      }
  }
}

readVocabulary($config);
update($config);
//=============================================
// Front-End
//=============================================
echo("<html>");
echo("<head>");

echo("<style>
a:link {
  text-decoration: none;
  color: green;
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

    echo "<a href=\"triplet.php?doget=admin_triplets\">Triplets</a>";
    echo "<a href=\"triplet.php?doget=admin_terms\">Terms</a>";
    echo "<a href=\"triplet.php?doget=set_configuration\">Configuration</a>";
    echo "<a href=\"triplet.php?doget=clear_triplet\">Clear Triplets</a>";
    echo "<a href=\"triplet.php?doget=random_triplet&range=$config->dimension&total=$config->n_rnd_tpl\">Create Random Triplets</a>";

    echo "<div class=\"dropdown\">
             <button class=\"dropbtn\">Void
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

  listAllTriplets($config,$file_rdf);
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
listAllTerms($config,$file_abc);

}
if ($set_configuration == 1)
{
echo "<table border=0>";
echo "
<form action=\"triplet.php\" method=\"post\">
  <input type=\"hidden\" name=\"dopost\" value=\"set_configuration\">
  <tr><td>Dimension</td><td> <input type=\"text\" name=\"dimension\" size=\"5\" value=$config->dimension></td>
  <tr><td>Direction</td><td> <input type=\"text\" name=\"direction\" size=\"5\" value=$config->direction></td>
  <tr><td>Random N</td><td> <input type=\"text\" name=\"n_rnd_tpl\" size=\"5\" value=$config->n_rnd_tpl></td>
  <td><input type= \"submit\" value=\"Set\"></td></tr>
</form>
</table>";

}
iterateGraphs($config);
showMatrixA($config);
listObjectsForThisNode($config,$current_node); // x = a
listSubjectsForThisNode($config,$current_node); // y = b
listNodesForThisPredicate($config,$current_node); // z = c
//echo ("<iframe id= \"ilog\" style=\"background: #FFFFFF;\" src=$doc_voc width=\"100\" height=\"100\"></iframe>");

//reasoning();
//$arr = array("a"=>"yellow", "b"=>"pink", "c"=>"purple"); 
  
// calling array_walk() with no extra parameter 
//array_walk($vocx, "myfunction"); 
?>
</body>
</html>
