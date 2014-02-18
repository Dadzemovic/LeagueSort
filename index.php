<?php
$user = 'root';
$pass = '';
try {
    $dbh = new PDO('mysql:host=127.0.0.1;dbname=cron', $user, $pass);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
}

// using the shortcut ->query() method here since there are no variable
// values in the select statement.
$sth = $dbh->query("SELECT id, wins, losses, ((wins + 1.9208) / (wins + losses) - 1.96 * SQRT((wins * losses) / (wins + losses) + 0.9604) / (wins + losses)) / (1 + 3.8416 / (wins + losses)) AS ci_lower_bound FROM wldata ORDER BY ci_lower_bound DESC LIMIT 5;");
 
// setting the fetch mode
$sth->setFetchMode(PDO::FETCH_ASSOC);
 
while($row = $sth->fetch()) {
    echo $row['id'] . "<br>";
    echo $row['wins'] . "<br>";
    echo $row['losses'] . "<br><br>";
}