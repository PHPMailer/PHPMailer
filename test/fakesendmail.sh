#!/bin/bash
#Fake sendmail script, adapted from:
#https://github.com/mrded/MNPP/blob/ee64fb2a88efc70ba523b78e9ce61f9f1ed3b4a9/init/fake-sendmail.sh
numPath="/tmp/fakemail"

mkdir -p $numPath

if [ ! -f $numPath/num ]; then
echo "0" > $numPath/num
fi
num=`cat $numPath/num`
num=$(($num + 1))
echo $num > $numPath/num

name="$numPath/letter_$num.txt"
while read line
do
echo $line >> $name
done
chmod 777 $name
/bin/true
