#!/bin/bash
SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
DATE=$(date +"%Y-%m-%d_%H%M")
# Timelapse
PICS_RESOLUTION="1920/1080"
PICS_EXT="jpg"
LOCAL_PICS_PATH="/tmp/timelapse"
# FTP
FTP_HOST="mafreebox.free.fr"        #This is the FTP servers host or IP address.
FTP_USER="freebox"                  #This is the FTP user that has access to the server.
FTP_PASS="hWaJT86hfCFdKAcn"         #This is the password for the FTP user.
FTP_PICS_PATH="/Disque dur/_timelapse"

# 1 - timelapse part
fswebcam -r ${PICS_RESOLUTION} --no-banner ${LOCAL_PICS_PATH}/$DATE.${PICS_EXT}

# 2 - FTP part
# Call 1. Uses the ftp command with the -inv switches.
#-i turns off interactive prompting.
#-n Restrains FTP from attempting the auto-login feature.
#-v enables verbose and progress.

ftp -inv ${FTP_HOST} << EOF

# Call 2. Here the login credentials are supplied by calling the variables.

user ${FTP_USER} ${PASS}

# Call 3.  Here you will tell FTP to put or get the file.
put ${LOCAL_PICS_PATH}/$DATE.${PICS_EXT} ${FTP_PICS_PATH}/$DATE.${PICS_EXT}"

# End FTP Connection
bye
EOF

sleep 2

rm -f ${LOCAL_PICS_PATH}/$DATE.${PICS_EXT}
