#!/bin/sh
svn st | grep ? | sed s/?/svn\ add/ >> /tmp/mycommand.sh;chmod ugo+wrx /tmp/mycommand.sh;echo "start" > /tmp/result ;/tmp/mycommand.sh >> /tmp/result; echo "finished" >> /tmp/result; cat /tmp/result;rm /tmp/mycommand.sh;rm /tmp/result;
