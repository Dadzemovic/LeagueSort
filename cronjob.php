<?php
// Ensure the PHP script will not time-out
set_time_limit(0);

// Connect to database (w/ persistent connection)
$user = 'root';
$pass = '';
$dbh = new PDO('mysql:host=127.0.0.1;dbname=cron', $user, $pass, array(
    PDO::ATTR_PERSISTENT => true
));

// CURL request in PHP
function curlFetch($url) {
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}

// Recurring function to grab and store player data
function storePlayerData($playerID = 1) {
	global $dbh;
	$count = $playerID;
	$dataStats = curlFetch("http://prod.api.pvp.net/api/lol/na/v1.2/stats/by-summoner/{$count}/summary?season=SEASON3&api_key=");
	$arrayStats = json_decode($dataStats, true);
	if (isset($arrayStats['playerStatSummaries'])) {
		foreach ($arrayStats['playerStatSummaries'] as $gameType) {
			if ($gameType['playerStatSummaryType'] == "RankedSolo5x5") {
				$wins = $gameType['wins'];
				$losses = $gameType['losses'];
			}
		}
	}

	if (isset($wins) & isset($losses)) {
		// STH means "statement handle"
		$sth = $dbh->prepare("INSERT INTO `wldata`(`id`, `wins`, `losses`) VALUES ($count,$wins,$losses)");
		$sth->execute();
	}

	$count++;

	// Sleep for 1.2 seconds to avoid hitting Riot's rate limit
	time_nanosleep(1, 200000000);

	storePlayerData($count);
}

// Call never-ending function
storePlayerData();
