<?php
// 检查服务是否安装
function checkServiceExist($serviceName) {
  // 检查服务是否存在的命令
  $checkServiceExistCommand = "systemctl status $serviceName > /dev/null 2>&1";
  // 执行命令检查服务是否存在
  exec($checkServiceExistCommand, $output, $returnVar);
  return $returnVar == 0;
}

// 检查服务是否运行
function checkServiceStatus($serviceName) {
  // 检查服务是否存在的命令
  $checkServiceRunningCommand = "systemctl status $serviceName | grep 'running'";
  // 执行命令检查服务是否运行
  exec($checkServiceRunningCommand, $output, $returnVar);
  return $returnVar == 0;
}

// 检查端口是否被占用
function isPortOccupied($port) {
  $fp = fsockopen("127.0.0.1", $port, $errno, $errstr, 2);
  if ($fp) {
    // 端口被占用
    fclose($fp);
    return true; 
  } else {
    // 端口未被占用
    return false;
  }
}
?>