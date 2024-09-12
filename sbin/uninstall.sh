#!/bin/bash

if [[ $1 == */ ]]; then
  INSTALL_PATH=${1%?}
else
  INSTALL_PATH=$1
fi

RED_COLOR='\e[1;31m'
GREEN_COLOR='\e[1;32m'
YELLOW_COLOR='\e[1;33m'
BLUE_COLOR='\e[1;34m'
PINK_COLOR='\e[1;35m'
SHAN='\e[1;33;5m'
RES='\e[0m'
clear

if [ "$(id -u)" != "0" ]; then
  echo -e "\r\n${RED_COLOR}出错了，请使用 root 权限重试！${RES}\r\n" 1>&2
  exit 1
fi

echo -e "\r\n${GREEN_COLOR}卸载 Alist ...${RES}\r\n"
echo -e "${GREEN_COLOR}停止进程${RES}"
systemctl disable alist >/dev/null 2>&1
systemctl stop alist >/dev/null 2>&1
echo -e "${GREEN_COLOR}清除残留文件${RES}"
# rm -rf $INSTALL_PATH /etc/systemd/system/alist.service
rm -rf /etc/systemd/system/alist.service
# 兼容之前的版本
rm -f /lib/systemd/system/alist.service
systemctl daemon-reload
echo -e "\r\n${GREEN_COLOR}Alist 已在系统中移除！${RES}\r\n"

