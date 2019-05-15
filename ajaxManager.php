<?php
//=========================================================================
// File.......: ajaxManager.php
// Date.......: 2019-05-15
// Author.....: Benny Saxen
// Description: Mercury Ajax Device Manager
//=========================================================================
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
function getStatus($uri)
//=============================================
{
  $url       = $uri.'/config.json';
  $json      = file_get_contents($url);
  $json      = utf8_encode($json);
  $dec       = json_decode($json, TRUE);
  $period    = $dec['data']['period'];
  $url       = $uri.'/meta.json';
  $json      = file_get_contents($url);
  $json      = utf8_encode($json);
  $dec       = json_decode($json, TRUE);
  $timestamp = $dec['sys_ts'];
  
  $now       = date_create('now')->format('Y-m-d H:i:s');
  $diff = strtotime($now) - strtotime($timestamp);
  $res = 999;
  $bias = 1;
  if ($diff > $period + $bias)
  {
    $res = $diff - $period - $bias;
  }
  else {
    $res = 0;
  }
  return ($res);
}

//=========================================================================
function getJsonDevicePar($url,$par)
//=========================================================================
{

  $options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'GET'
    )
  );
  $context  = stream_context_create($options);
  $result = file_get_contents($url, false, $context);
  $streams = json_decode($result,true);
  $ts = $streams['data'][$par];
  return $ts;
}
//=========================================================================
function getJsonDomain($sel_domain)
//=========================================================================
{
  $res = listAllDevices();
  $data = explode(":",$res);
  $num = count($data)-1;
  //echo  "num=".$num." ";
  $answer = '';
  for ($ii = 0; $ii < $num; $ii++)
  {
    $device = $data[$ii];
    if (strlen($device) > 2)
    {
      $doc = 'http://'.$sel_domain.'/devices/'.$device;
      $status = getStatus($doc);
      $answer = $answer.'='.$status;
     }
   }
  //echo $answer;
  return $answer;
}

//=========================================================================

$domain = $_GET['domain'];
$device = $_GET['device'];

$url = 'http://'.$domain.'/devices/'.$device.'/dynamic.json';

//echo $url;
//$no = getJsonData($url,'no');
//$ts = getJsonData($url,'sys_ts');
$answer = getJsonDomain($domain);
echo $answer;

?>
