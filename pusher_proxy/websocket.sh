#!/bin/bash
#cronjob /bin/sh ~/public_html/pusher_proxy/websocket.sh > /dev/null 2>&1
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
echo "Script directory: $SCRIPT_DIR"
SERVICE="websocket:serve"
if ps -ef | grep -v grep | grep $SERVICE >/dev/null
then
    echo "$SERVICE is running"
else
    echo "$SERVICE stopped"
   
    cd $SCRIPT_DIR && cd ../
    php artisan websocket:serve > websocket.log &
fi
