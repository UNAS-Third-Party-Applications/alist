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

// 获取homes下的apps目录
function getHomesAppsDir() {
  require_once("/unas/wmi/core/wy2account.php");
  $homesDir = WY2Account::homesDir();
  return $homesDir . "/homes/.unas/apps";
}

// 获取默认配置文件目录
function getDefaultConfigDir() {
  return getHomesAppsDir() . "/alist";
}

// 获取处于共享状态的共享目录
function getSharedFolder() {
  require_once("/unas/api/php/folder.php");
  return \UNAS\Folder\GetAllSharedFolders();
}

// 获取所有共享目录，包括lvm， zfs 和网络卷的
function getAllSharefolder() 
{
  $sharefolders = [];
  require_once("/unas/wmi/core/wy2folder.php");
  require_once("/unas/wmi/core/wy2volume.php");
  $groups = WY2Volume::groups();
  foreach( $groups as $group )
  {
      $lvs = WY2Volume::volumes($group["name"]);
      foreach( $lvs as $lv )
      {
          $mnt = "/mnt/" . $group["name"] . "/" . $lv["name"];
          $files = scandir($mnt);
          foreach( $files as $file )
          {
              if ( is_dir($mnt . "/" . $file) && ! WY2Folder::isNameReserved($file) )
              {
                  $sharefolders[] = [
                      "folder" => $file,
                      "path"   => $mnt . "/" . $file,
                  ];
              }
          }
      }
  }

  //now handle zfs. should actually useing the interfac. But seems to much of difference.
  $zfs = \UNAS\Volume\Volume::CreateVolumeObject("zfs");
  if(is_null($zfs)){
      //zfs not defined.
  }else{
      $vgs=$zfs->GetAllVolumeGroups();
      for($i=0; $i<count($vgs); $i++){
          $lvs = $zfs->GetVolumesInVolumeGroup($vgs[$i]['name']); //get all volumes (datasets or zvols) of the zpool
          $lvlist = array();
          $lv_list = array(); //list of LV names;
          for($j=0; $j<count($lvs); $j++){
              $lv_list[]=$lvs[$j]['name']; //
              if($lvs[$j]['type'] =="volume"){ //only datasets
                  continue;
              }
              $mountPath = "/mnt/" .  trim($lvs[$j]["path"]);
              $files = scandir($mountPath);
              $folders= array();
              foreach($files as $file){
                  if($file === '.' || $file === '..') {continue;}
                  if ( WY2Folder::validShareName($file) == ERR_SUCCEED )
                  {
                      $exists = false;
                      $filePath = $mountPath . "/" . $file;
                      if ( is_dir($filePath) )
                      {
                          $sharefolders[] = [
                              "path" => $filePath,
                              "folder" => $file,
                          ];
                      }
                  }

              }
          }

          //get all mounted snapshots of the pool (vg).
          $dir_content_list = scandir('/mnt/' . $vgs[$i]['name']); //find all sub folders in /mnt/VG;
          foreach($dir_content_list as $value)
          {
              if($value === '.' || $value === '..') {continue;}
              // check if we have directory
              if (is_dir('/mnt/' . $vgs[$i]['name'] . '/' . $value)) {
                  if (in_array($value, $lv_list)) {
                      //not a snapshot
                  }else{
                      //snapshot
                      $mountDir ="/mnt/" . $vgs[$i]['name'] . '/' . $value;
                      $dirs = array_filter(glob($mountDir . '/*'), 'is_dir'); //get all directories under the volume.
                      foreach($dirs as $dir){
                          if(WY2Folder::validShareName(basename($dir)) == ERR_SUCCEED){
                              $sharefolders[] = [
                                  "path" => $dir,
                                  "folder" => basename($dir),
                              ];
                          }
                      }

                  }
              }
          }
      }
  }

  //now handle netdrive.
  $netdrive_obj = \UNAS\Volume\Volume::CreateVolumeObject("netdrive");
  if(is_null($netdrive_obj)){
      //netdrive not defined.
  }else{
      //$types = $netdrive_obj->GetAllVolumeGroups();
      $netdrives = $netdrive_obj->GetAllVolumes();
      foreach ($netdrives as $netdrive){
          $name = $netdrive["name"];
          $type = $netdrive["vg"];
          if ($netdrive_obj->IsVolumeMounted($name)){
              $sharefolders[] = [
                  "folder" => $name,
                  "path" => $netdrive_obj->get_mountpoint($name)
              ];
          }
      }
      $netdrive_obj->close();
  }

  require_once("/unas/wmi/core/wy2samba.php");
  $conf = WY2Samba::loadConfig();
  foreach( $sharefolders as $sf )
  {
      $sf["shared"] = 0;
      foreach( $conf as $name=>$node )
      {
          if ( $name == "homes" || $name == "printers" || $name == "global" )
          {
          }else
          if ( $conf[$name]["path"] == $sf["path"] )
          {
              $sf["sharename"] = $conf[$name]["comment"];
              $sf["shared"] = 1;
              break;
          }
      }
  }

  return $sharefolders;
}

?>