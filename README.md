This PHP class lets you perform multiple cURL requests in parallel. Is also provides a max connections per second setting, so you can start multiple parallel requests at different intervals.

For example you want to make 50 requests, which all take 5 seconds, but you only want to start 5 requests per second. The max connections per second setting will start every second 5 new connections, so your total requests take 14 seconds (at t9, the last 5 connections will start).

## Requirements
The class is written in PHP5 and uses the [curl_multi_init()](http://php.net/curl_multi_init) function.

## Usage
A quick example how to use php-mcurl:

    $mcurl = Mcurl::factory();
    $mcurl->max_per_second = 1;
    $mcurl->add_url('http://example.com/foo');
    $mcurl->add_url('http://example.com/bar');
    $mcurl->execute();
    $responses = $mcurl->get_content();
    echo $responses[0]['content']; // Print response from first URL

2 URLs are get in this example with the first one starting at t0 and the second at t1 (max connections per second = 1)

## Example
The example scripts are currently online at http://experiments.bonnevoy.com/mcurl/example/client.php