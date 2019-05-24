<?php
//=============================================
// File.......: gateway.php
// Date.......: 2019-05-24
// Author.....: Benny Saxen
// Description: Mercury Gateway
//=============================================
// Publish
// http://iot.domain.com/gateway?id=123&no=123&do=config/meta/payload&json={}
// Log
// http://iot.domain.com/gateway?id=123&no=123&do=log&log=sdfdfdfg
// Ping
// http://iot.domain.com/gateway?id=123&no=123&do=ping
// Feedback
// Add fb = 1 to GET request
// http://iot.domain.com/gateway?id=123&no=123&do=ping&fb=1
// Feedback file format
// [n]:message:
// n = number of pending feedback files
// message = the message to the device 
//=============================================
// Library
class model {
    public $sys_ts;
    public $id;
    public $no;
    public $do;
    public $msg;
    public $error;
}

$obj = new model();
$obj->error = "NO_ERROR";
//=============================================
$date         = date_create();
$obj->sys_ts  = date_format($date, 'Y-m-d H:i:s');

//=============================================
function systemError($msg)
//=============================================
{
  $f_file = 'system_errors.txt';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->sys_ts $msg\n");
        fclose($doc);
  }
  return;
}
//=============================================
function systemWarning($msg)
//=============================================
{
  $f_file = 'system_warnings.txt';
  $doc = fopen($f_file, "a");
  if ($doc)
  {
        fwrite($doc, "$obj->sys_ts $msg\n");
        fclose($doc);
  }
  return;
}
//=============================================
function errorManagement($obj)
//=============================================
{
  if ($obj->error != "NO_ERROR")
  {
    $f_file = 'errors.txt';
    $doc = fopen($f_file, "a");
    if ($doc)
    {
        fwrite($doc, "$obj->sys_ts $obj->id $obj->no $obj->do $obj->error\n");
        fclose($doc);
    }
  }
  return;
}
//=============================================
function initLog($obj)
//=============================================
{
  $error = "NO_ERROR";
  $f_file = 'devices/'.$obj->id.'/log.txt';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "$obj->sys_ts initialized\n");
        fclose($doc);
  }
  else
  {
      $error = "ERROR_INIT_LOG";
  }
  return $error;
}
//=============================================
function initNo($obj)
//=============================================
{
  $error = "NO_ERROR";
  $f_file = 'devices/'.$obj->id.'/no.txt';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "0\n");
        fclose($doc);
  }
  else
  {
      $error = "ERROR_INIT_NO";
  }
  return $error;
}
//=============================================
function saveLog($obj)
//=============================================
{
  $error = "NO_ERROR";
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
      $error = "ERROR_SAVE_LOG";
  }
  return $error;
}

//=============================================
function writeNo($obj)
//=============================================
{
  $error = "NO_ERROR";
  $f_file = 'devices/'.$obj->id.'/no.txt';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
        fwrite($doc, "$obj->no");
        fclose($doc);
  }
  else
  {
      $error = "ERROR_WRITE_NO";
  }
  return $error;
}
//=============================================
function readNo($obj)
//=============================================
{
  $error = "NO_ERROR";
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
      $error = "ERROR_READ_NO";
  }
  return $error;
}

//=============================================
function readFeedbackFile($fb_file)
//=============================================
{
  $file = fopen($fb_file, "r");
  if ($file)
  {
      $result = ":";
      while(! feof($file))
      {
        $line = fgets($file);
        $line = trim($line);
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
function feedback($id)
//=============================================
{
  $result = ' ';
  $do = "ls devices/".$id."/"."*.feedback > devices/".$id."/feedback.work";
  system($do);
  $list_file = 'devices/'.$id.'/feedback.work';
  $no_of_lines = count(file($list_file));
  $file = fopen($list_file, "r");
  if ($file)
  {
      // Read first line only
      $line = fgets($file);
      if (strlen($line) > 2)
      {
          $line = trim($line);
          $result = readFeedbackFile($line);
      }
  }
  $no_of_lines = $no_of_lines - 1;
  $result = "[$no_of_lines]".$result;
  return $result;
}

//=============================================
function publish($obj)
//=============================================
{
  $error = "NO_ERROR";
  $obj->msg = "{\"no_data\":\"0\"}";
  if (isset($_GET['json'])) {
      $obj->msg = $_GET['json'];
  }
    
  $f_file = 'devices/'.$obj->id.'/'.$obj->do.'.json';
  $doc = fopen($f_file, "w");
  if ($doc)
  {
      fwrite($doc, "{\n");
      fwrite($doc, "   \"sys_ts\":   \"$obj->sys_ts\",\n");
      fwrite($doc, "   \"no\":   \"$obj->no\",\n");
      fwrite($doc, "   \"data\": $obj->msg\n");
      fwrite($doc, "}\n ");
      fclose($doc);
  }
  else
  {
     $error = "ERROR_PUBLISH"; 
  }
    
  return $error;
}

//=============================================
// End of library
//=============================================

if (isset($_GET['do'])) // Mandatory
{

    $obj->do = $_GET['do']; 

    if (isset($_GET['id'])) // Mandatory
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
        systemError("Gateway Error: device registration not complete");
        exit();
      }
    }
    else
    {
      systemError("Gateway Error: no device id");
      exit();
    }

    if (isset($_GET['no'])) // Not mandatory
    {
      $new_no = $_GET['no'];
      $missed = $new_no - $obj->no; 
      if ($missed != 1)
      {
          $format = 'device %s missed messages %d \n';
          $msg = sprintf($format, $obj->id, $missed); 
          systemWarning($msg);
      }
    }

    if ($obj->do == 'log')
    {
       $obj->error = saveLog($obj);
       errorManagement($obj);
    }
    
    if ($obj->do == 'ping')
    {
       echo "ok";
    }

    if ($obj->do == 'config' || $obj->do == 'meta' || $obj->do == 'payload')
    {
       $obj->error = $publish($obj);
       errorManagement($obj);
    }
    
    if (isset($_GET['fb'])) // Not mandatory
    {
      echo feedback($obj->id);
    }

} // do
else
  echo "Gateway ok - ready for use";

//===========================================
// End of file
//===========================================
?>
