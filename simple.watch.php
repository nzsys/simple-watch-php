<?php
	if(php_sapi_name() !== 'cli')
	{
		die('usage: command line only.');
	}
	if(
		(empty($argv[1]) or empty($argv[2])) or
		(isset($argv[3]) and empty($argv[4]))
	)
	{
		die('usage: simple.watch.php [-host] url [[-hook] webhook url]'.PHP_EOL);
	}
	elseif(!filter_var($argv[2],FILTER_VALIDATE_URL))
	{
		die('-host is invalid.'.PHP_EOL);
	}
	elseif(isset($argv[4]) and !filter_var($argv[4],FILTER_VALIDATE_URL))
	{
		die('-hook is invalid.'.PHP_EOL);
	}

	$d = '/tmp/';
	$w = $argv[4] ?? 'https://hooks.slack.com/services/xxx/xxx/xxx';
	$u = parse_url($argv[2]);
	$l = 0;
	if(file_exists($d.$u['host'].'.live'))
	{
		$f = fopen($d.$u['host'].'.live', 'r');
		$l = (int) fgetc($f) ?? 0;
		fclose($f);
	}

	$c = curl_init($argv[2]);
	curl_setopt_array($c, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 5]
	);
	curl_exec($c);
	$i = curl_getinfo($c);
	$e = curl_errno($c);
	$s = $e === CURLE_OK ? ($i['http_code'] === 200? 1: 0) : 0;
	file_put_contents($d.$u['host'].'.live', $s);

	if($s !== $l)
	{
		if($s === 1)
		{
			$message = [
				'username'    => $argv[2],
				'attachments' => [[
					'fallback'  => $u['host'].' : 200',
					'color'     => 'good',
					'title'     => 'Recovery',
					'text'      => date('Y-m-d H:i:s')
				]]
			];
		}
		else
		{
			$message = [
				'username'    => $argv[2],
				'attachments' => [[
					'fallback'  => $u['host'].' : '.$i['http_code'],
					'color'     => '#990000',
					'title'     => 'Trouble',
					'text'      => date('Y-m-d H:i:s').PHP_EOL.'HTTP_STATUS : '.$i['http_code']
				]]
			];
		}
		$c = curl_init($w);
		curl_setopt_array($c, [
			CURLOPT_POST           => true,
			CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
			CURLOPT_POSTFIELDS     => json_encode($message),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 10
		]);
		curl_exec($c);
		$e = curl_errno($c);
		$i = curl_getinfo($c);
		curl_close($c);
		if($e !== CURLE_OK)
		{
			echo date('Y-m-d H:i:s').' webhook_error : '.$i['http_code'].PHP_EOL;
		}
	}
