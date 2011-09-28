This PHP class lets you perform multiple cURL requests in parallel. Is also provides a max connections per second setting, so you can start multiple parallel requests at different intervals.

For example you want to make 50 requests, which all take 5 seconds, but you only want to start 5 requests per second. The max connections per second setting will start every second 5 new connections, so your total requests take 14 seconds (at t9, the last 5 connections will start).

## Requirements
The class is written in PHP5 and uses the [curl_multi_init()](http://php.net/curl_multi_init) function.