#!/bin/bash

# INSTALL_PATH='/opt/alist'
VERSION='latest'

if [[ $1 == */ ]]; then
  INSTALL_PATH=${1%?}
else
  INSTALL_PATH=$1
fi

if [[ $2 == */ ]]; then
  CONFIG_PATH=${2%?}
else
  CONFIG_PATH=$2
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

if [ ! -f "$INSTALL_PATH/alist" ]; then
  echo -e "\r\n${RED_COLOR}出错了${RES}，当前系统未安装 Alist\r\n"
  exit 1
fi

# 创建 systemd
cat >/etc/systemd/system/alist.service <<EOF
[Unit]
Description=UNAS Alist service
Wants=network.target
After=network.target network.service

[Service]
Type=simple
WorkingDirectory=$INSTALL_PATH
ExecStart=$INSTALL_PATH/alist server --data $CONFIG_PATH
KillMode=process

[Install]
WantedBy=multi-user.target
EOF

# 添加开机启动
systemctl daemon-reload
systemctl enable alist >/dev/null 2>&1
# 启动服务
systemctl start alist >/dev/null 2>&1

