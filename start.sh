#!/bin/sh

while getopts s: flag
do
    case "${flag}" in
        s) shards=${OPTARG};;
    esac
done

if [ -z "$shards" ]
then
    shards="1";
fi

for i in `seq 1 $shards`;
do
    id=$(expr $i - 1);
    $(pm2 start bot.php -f --name "hiro-bot" -- --shard-id $id --shard-count $shards) 2> /dev/null
done
