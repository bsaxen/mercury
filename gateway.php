<?php
//=============================================
// File.......: gateway.php
// Date.......: 2019-05-15
// Author.....: Benny Saxen
// Description: Mercury Gateway
//=============================================
int NO_ERROR              =    0;
int ERROR_READING_NO_FILE =  101;
//=============================================
// Library
class model {
    public $sys_ts;
    public $id;
    public $no;
    public $msg_config;
    public $msg_meta;
    public $msg_payload;
    public $msg;
}

$obj = new model();

//=============================================
$date         = date_create();
$obj->sys_ts  = date_format($date, 'Y-m-d H:i:s');

//=============================================
function errorManagement($error)
//=============================================
{
  $f_file = 'errors.txt';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->sys_ts $error\n");
        fclose($doc);
  }
  return;
}
//=============================================
function initLog($obj)
//=============================================
{
  $error = NO_ERROR;
  $f_file = 'devices/'.$obj->id.'/log.txt';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "$obj->sys_ts initialized\n");
        fclose($doc);
  }
  else
  {
      $error = ERROR_INIT_LOG;
  }
  return $error;
}
//=============================================
function saveLog($obj)
//=============================================
{
  $error = NO_ERROR;
  $log   = $_GET['log'];
  $log   = str_replace(" ","_",$log);  
  $f_file = 'devices/'.$obj->id.'/log.txt';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->sys_ts $log\n");
        fclose($doc);
  }
  else
  {
      $error = ERROR_SAVE_LOG;
  }
  return $error;
}

//=============================================
function writeNo($obj)
//=============================================
{
  $error = NO_ERROR;
  $f_file = 'devices/'.$obj->id.'/no.txt';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "$obj->no");
        fclose($doc);
  }
  else
  {
      $error = ERROR_WRITE_NO;
  }
  return $error;
}
//=============================================
function readNo($obj)
//=============================================
{
  $error = NO_ERROR;
  $file = 'devices/'.$obj->id.'/no.txt';
  if ($file)
  {
      while(!feof($file))
      {
        $result = fgets($file);
        $obj->no = trim($result);
      }
      fclose($file);
  }
  else
  {
      $error = ERROR_READ_NO;
  }
  return $error;
}
//=============================================
function readFeedbackFile($fb_file)
//=============================================
{
  $error = NO_ERROR;
  $file = fopen($fb_file, "r");
  if ($file)
  {
      $result = ":";
      while(! feof($file))
      {
        $line = fgets($file);
        //sscanf($line,"%s",$work);
        $result = $result.$line;
      }
      fclose($file);
      $result = $result.":";
      // Delete file
      if (file_exists($fb_file)) unlink($fb_file);
  }
  else
  {
      $result = ":void:";
  }
  return $result;
}
//=============================================
function readFeedbackFileList($id)
//=============================================
{
  $error = NO_ERROR;
  $result = ' ';
  $do = "ls devices/".$id."/"."*.feedback > devices/".$id."/feedback.work";
  //echo $do;
  system($do);
  $list_file = 'devices/'.$id.'/feedback.work';
  $no_of_lines = count(file($list_file));
  $file = fopen($list_file, "r");
  if ($file)
  {
      // Read first line only
      $line = fgets($file);
      //echo "line:".$line;
      if (strlen($line) > 2)
      {
          $line = trim($line);
          //$line = 'devices/'.$line;
          $result = readFeedbackFile($line);
      }
  }
  $result = "[$no_of_lines]".$result;
  return $result;
}

//=============================================
function listAllFeedback($id)
//=============================================
{
  $do = "ls devices/".$id."/"."*.feedback > devices/".$id."/feedback.work";
  system($do);
  $list_file = 'devices/'.$id.'/feedback.work';
  $no_of_lines = count(file($list_file));
  echo $no_of_lines;
}
//=============================================
function publish($obj,$name)
//=============================================
{
  $error = NO_ERROR;
  $obj->msg = "{\"no_data\":\"0\"}";
  if (isset($_GET['json'])) {
      $obj->msg = $_GET['json'];
  }
    
  $f_file = 'devices/'.$obj->id.'/'.$name.'.json';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
      fwrite($doc, "{\n");
      fwrite($doc, "   \"sys_ts\":   \"$obj->sys_ts\",\n");
      fwrite($doc, "   \"data\": $obj->msg\n");
      fwrite($doc, "}\n ");
      fclose($doc);
  }
  else
  {
     $error = ERROR_PUBLISH; 
  }
  return $error;
}
//=============================================
// End of library
//=============================================

$error = NO_ERROR;
if (isset($_GET['do']))
{

    $do = $_GET['do'];

    if (isset($_GET['id']))
    {

      // Create device register
      $obj->id = $_GET['id'];
      $obj->id  = str_replace(":","_",$obj->id);
        
      $ok = 0;
      $dir = 'devices/'.$obj->id;
      if (is_dir($dir)) $ok++;
      $file = 'register/'.$obj->id.'.reg';
      if (file_exists($file)) $ok++;

      if ($ok == 0) // New device - register!
      {
         mkdir($dir, 0777, true);

        // Create register directory if not exist
        $dir = 'register';
        if (!is_dir($dir))
        {
           mkdir($dir, 0777, true);
        }
          
        $filename = 'register/'.$obj->id.'.reg';
        $doc = fopen($filename, "w");
        fwrite($doc, "$gs_ts $ts $obj->id");
        fclose($doc);
        $obj->error = initLog($obj);
        errorManagement($obj);
      }
        
      if ($ok == 1) // un-complete register
      {
        echo "Gateway Error: device registration not complete";
        exit();
      }
    }
    else
    {
      echo "Gateway Error: no device id";
      exit();
    }


    if ($do == 'log')
    {
       $obj->log   = $_GET['log'];
       $obj->error = saveLog($obj);
       errorManagement($obj);
    }

    if ($do == 'config' || $do == 'meta' || $do == 'payload')
    {
       $$obj->error = $publish($obj,$do);
       errorManagement($obj);
    }

} // do
else
  echo "Gateway ok - ready for use";

//===========================================
// End of file
//===========================================
?>
