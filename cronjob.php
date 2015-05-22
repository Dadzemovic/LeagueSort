<?php

// REQUIRES that curl is enabled in your php.ini file, or alternatively use file_get_contents()

// Your Riot API key here
$api_key = '';

// Connect to database (w/ persistent connection)
$user = 'root';
$pass = '';
$dbh = new PDO('mysql:host=127.0.0.1;dbname=cron', $user, $pass, array(
    PDO::ATTR_PERSISTENT => true
));

// Ensure the PHP script will not time-out
set_time_limit(0);

// CURL request in PHP
function curlFetch($url) {
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	
	// Allow CURL requests to be made over SSL (requirement for Riot's API)
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}


$count = 1;

while(true)
{
	$dataStats = curlFetch("https://na.api.pvp.net/api/lol/na/v1.3/stats/by-summoner/{$count}/summary?season=SEASON2015&api_key=34305cf4-4529-4834-89da-089b7ba0466f");
	/*
	* Can use file_get_contents() function below if CURL is disabled
	*
	* $dataStats = file_get_contents("https://na.api.pvp.net/api/lol/na/v1.3/stats/by-summoner/{$count}/summary?season=SEASON2015&api_key={$api_key}");
	*/

	$arrayStats = json_decode($dataStats, true);
	if (isset($arrayStats['playerStatSummaries'])) {
		foreach ($arrayStats['playerStatSummaries'] as $gameType) {
			if ($gameType['playerStatSummaryType'] == "RankedSolo5x5") {
				$wins = $gameType['wins'];
				$losses = $gameType['losses'];
				
				// STH == "statement handle"
				$sth = $dbh->prepare("INSERT INTO `wldata`(`id`, `wins`, `losses`) VALUES ($count,$wins,$losses)");
				$sth->execute();
			}
		}
	}
	
	++$count;

	// Sleep for 1.2 seconds to avoid hitting Riot's rate limit (adjust if you have obtained a developer's key)
	time_nanosleep(1, 200000000);
}
