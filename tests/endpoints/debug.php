<?php


$headers = ['Cookie'=>'PHPSESSID=test-endpoint-debug'];

p("Turning debug on <code>/debug/on</code>");
$data = request('GET', 'debug/on', [], $headers);
affirm($data == "true");


p("Verifying it's on <code>/debug</code>");
$data = request('GET', 'debug', [], $headers);
affirm($data == "true");


p("Turning debug off <code>/debug/off</code>");
$data = request('GET', 'debug/off', [], $headers);
affirm($data == "false");

p("Verifying it's off <code>/debug</code>");
$data = request('GET', 'debug', [], $headers);
affirm($data == "false");
