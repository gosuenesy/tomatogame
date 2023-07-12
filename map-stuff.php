<?php

//database connection stuff
$host = 'localhost';
$db = 'tomatodb';
$user = 'root';
$password = '';

//connect to database
$connection = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);

//query database, fetch speeds
$query = $connection->query("SELECT speed FROM speeds");
$speeds = $query->fetchAll(PDO::FETCH_COLUMN);

$speed1 = 0; 
$speed2 = 0; 

if (count($speeds) === 2) {
    $speed1 = $speeds[0];
    $speed2 = $speeds[1];
}

$lat1 = "55.677855";
$lon1 = "12.569008";
$lat2 = "55.677922";
$lon2 = "12.571465";
$lat3 = "55.677069";
$lon3 = "12.571208";
$lat4 = "55.677057";
$lon4 = "12.568837";

$str = '{ "type": "FeatureCollection",
            "features": [
                { "type": "Feature",
                    "geometry": {
                        "type": "Polygon",
                        "coordinates": [
                            [ 
                                ['.$lon1.', '.$lat1.'],
                                ['.$lon2.', '.$lat2.'],
                                ['.$lon3.', '.$lat3.'],
                                ['.$lon4.', '.$lat4.']
                            ]
                        ]

                    },
                    "properties": {
                        "prop0": "value0",
                        "prop1": {"this": "that"}
                    }
                }
            ]
        }';

//check for ajax request
if (isset($_POST['sessionID']) && isset($_POST['timeToComplete'])) {
    $sessionID = $_POST['sessionID'];
    $timeToComplete = $_POST['timeToComplete'];
    storeGameStats($sessionID, $timeToComplete);
}

function storeGameStats($sessionID, $time) {
    $host = 'localhost';
    $db = 'tomatodb';
    $user = 'root';
    $password = '';

    try {
        $connection = new PDO("mysql:host=$host;dbname=$db", $user, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //prepare insert
        $statement = $connection->prepare("INSERT INTO gamestats (sessionID, time) VALUES (:sessionID, :time)");

        //bind parameters
        $statement->bindParam(':sessionID', $sessionID);
        $statement->bindParam(':time', $time);

        $statement->execute();

        //close connection
        $connection = null;
    } catch (PDOException $e) {
        echo "Error storing game statistics: " . $e->getMessage();
    }
}
function geoJson($str) {
    return "JSON.parse(".json_encode($str).");";
}