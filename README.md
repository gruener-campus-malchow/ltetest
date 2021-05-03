# ltetest

contains an ultra simple api for receiving and publishing csv-data

it is not build for security purposes (e.g. keys in code), be careful...

## Installation Server

1. edit the index.php and change the keys
2. push it to your webspace (e.g. https://myownsite.de)

## Installation Client (Debian)

1. download the latest(!) Version of speedtest-cli
~~~
git clone https://github.com/sivel/speedtest-cli.git
~~~
1. install curl
~~~
sudo apt install curl
~~~
1. edit crontab
~~~
crontab -e
~~~
paste at the end but look for changes
~~~
*/5 *    * * *   /usr/bin/curl --silent -d "api=secretAPIkey&testobject=myTestInstance&record='$(/usr/bin/python3 /home/username/speedtest-cli/speedtest.py --csv)'" -H "Content-Type: application/x-www-form-urlencoded" -X POST https://myownsite.de/
~~~