<?php

require __DIR__.'/shared.php';

function benchmark(/* arguments */)
{
	$args = func_get_args();
	$argc = count($args);
	if($argc <= 1) return;
	$classname = $args[0];
	$funcname = $args[1];

	$requests = 1000;
	$i = 0;
	
	$startTime = explode(' ', microtime());
	try {
		switch ($argc) {
		case 2:
			for($i=0; $i < $requests; $i++) {
				$classname->$funcname();
				echo $funcname . " test: " . $i . " / " . $requests . "\r";
			}
			break;
		case 3:
			for(!$i=0; $i < $requests; $i++) {
				$classname->$funcname($args[2]);
				echo $funcname . " test: " . $i . " / " . $requests . "\r";
			}
			break;
		case 4:
			for($i=0; $i < $requests; $i++) {
				$classname->$funcname($args[2], $args[3]);
				echo $funcname . " test: " . $i . " / " . $requests . "\r";
			}
			break;
		case 5:
			for($i=0; $i < $requests; $i++) {
				$classname->$funcname($args[2], $args[3], $args[4]);
				echo $funcname . " test: " . $i . " / " . $requests . "\r";
			}
			break;
		default:
			for($i=0; $i < $requests; $i++) {
				$classname->$funcname($args[2], $args[3], $args[4]);
				echo $funcname . " test: " . $i . " / " . $requests . "\r";
			}
		}
	}
	catch (Exception $e) {
		echo $funcname . " exception: " . $e . "\n";
	}
	
	$endTime = explode(' ', microtime());
	$wasteTime = $endTime[0] + $endTime[1] - ($startTime[0] + $startTime[1]);
	$wasteTime = round($wasteTime, 3);
	$nqps = round($i / $wasteTime);
	
	echo $funcname . " : " . $wasteTime . " seconds " . $nqps . " qps\n";
}

function randomInt($prefix, $suffix="")
{
	if($suffix == "") {
		return $prefix . ":" . rand(0, 100);
	}
	return $prefix . ":" . rand() . ":" . $suffix;
}

$client = new Predis\Client();
if($client) {
	$datasize = 3;
	$data = str_repeat('x', $datasize);
	
	//benchmark($client, "ping");
	benchmark($client, "set", randomInt("key"), $data);
	benchmark($client, "get", randomInt("key"));
	benchmark($client, "incr", randomInt("counter"));
	
	benchmark($client, "lpush", "mylist", $data);
	benchmark($client, "rpush", "mylist", $data);
	benchmark($client, "lpop", "mylist");
	benchmark($client, "rpop", "mylist");
	
	benchmark($client, "sadd", "myset", randomInt("element"));
	benchmark($client, "spop", "myset");
	
	benchmark($client, "lpush", "mylist", $data);
	benchmark($client, "lrange", "mylist", 0, 99);
	benchmark($client, "lrange", "mylist", 0, 299);
	benchmark($client, "lrange", "mylist", 0, 499);
	benchmark($client, "lrange", "mylist", 0, 599);
	
	$argv = array();
	for($i = 0; $i < 10; $i++) {
		$argv[randomInt("{mykey}")] = $data;
	}
	benchmark($client, "mset", $argv);
}
