<?php
require("map-stuff.php");

?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <title>A super cool map</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.2/dist/leaflet.css"
          integrity="sha256-sA+zWATbFveLLNqWO2gtiw3HL/lh1giY/Inf1BJ0z14="
          crossorigin=""/>

    <script src="https://unpkg.com/leaflet@1.9.2/dist/leaflet.js"
            integrity="sha256-o9N1jGDZrf5tS+Ft4gbIK7mYMipq9lqpVJ91xHSyKhg="
            crossorigin=""></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        #map {
            height:100%;
        }
        #timer {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 20px;
            font-weight: bold;
            color: #ffffff;
            background-color: #000000;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div id='map'></div>
    <div id="timer">Timer: 0 seconds</div>
    <script>
        var map = L.map('map').setView([<?=$lat1;?>, <?=$lon1;?>], 18);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var geojson = <?=$str;?>;
        // Show polygon at the Trophy Office
        L.geoJson(geojson).addTo(map);
        
        //tomato
        var tomatoIcon = L.icon({
            iconUrl: 'tomato.png',
            iconSize: [32, 32], 
            iconAnchor: [16, 16] 
        });
        var tomatoMarker = L.marker([55.683081, 12.571155], { icon: tomatoIcon }).addTo(map);

        //flag
        var flagIcon = L.icon({
            iconUrl: 'flag.png',
            iconSize: [64, 64],
            iconAnchor: [32, 32]
        });
        var flagMarker = L.marker([55.232816, 11.767130], { icon: flagIcon }).addTo(map);

        //speed and direction
        var speedSlow = <?=$speed1;?>;
        var speedFast = <?=$speed2;?>;
        var currentSpeed = speedSlow;
        var direction = null;
        var timer = 0;
        
        var keyStates = {
            ArrowUp: false,
            ArrowDown: false,
            ArrowLeft: false,
            ArrowRight: false,
            Space: false,
            ShiftLeft: false
        };

        //simultaneous inputs
        document.addEventListener('keydown', function(event) {
            //console.log(event.code, "keydown")
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Space', 'ShiftLeft'].includes(event.code)) {
            event.preventDefault();
            keyStates[event.code] = true;
            handleKeyStates();
        }
        });

        document.addEventListener('keyup', function(event) {
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Space', 'ShiftLeft'].includes(event.code)) {
            event.preventDefault();
            keyStates[event.code] = false;
            handleKeyStates();
        }
        });

        function handleKeyStates() {
            var arrowKeysPressed = Object.values(keyStates).slice(0, 5).some(function(keyPressed) {
                return keyPressed;
            });

            var speedkeyPressed = keyStates.Space || keyStates.ShiftLeft;

            if (arrowKeysPressed) {
                var direction = '';

                if (keyStates.ArrowUp) {
                    direction += 'ArrowUp';
                }

                if (keyStates.ArrowDown) {
                    direction += 'ArrowDown';
                }

                if (keyStates.ArrowLeft) {
                    direction += 'ArrowLeft';
                }

                if (keyStates.ArrowRight) {
                    direction += 'ArrowRight';
                }

                moveTomatoMarker(direction, speedkeyPressed);
            }
        }

        function moveTomatoMarker(direction, speedkey) {
        if (direction) {
            var currentSpeed = speedkey ? speedFast : speedSlow;
            //console.log(currentSpeed, "currentSpeed")
            var msInAnHour = 3600000;
            var latLng = tomatoMarker.getLatLng();

            //latitude and longitude based on direction
            if (direction.includes('ArrowUp')) {
                latLng.lat += (currentSpeed / msInAnHour);
            } else if (direction.includes('ArrowDown')) {
                latLng.lat -= (currentSpeed / msInAnHour);
            }

            if (direction.includes('ArrowLeft')) {
                latLng.lng -= (currentSpeed / msInAnHour);
            } else if (direction.includes('ArrowRight')) {
                latLng.lng += (currentSpeed / msInAnHour);
            }


            tomatoMarker.setLatLng(latLng);

            //center map on tomato
            map.panTo(latLng);

            //win condition
            if (tomatoMarker.getLatLng().distanceTo(flagMarker.getLatLng()) <= 1000) {
                var sessionID = Date.now();
                $.ajax({
                    url: 'map-stuff.php',
                    method: 'POST',
                    data: {
                        sessionID: sessionID,
                        timeToComplete: timer
                    },
                    success: function(response) {
                        alert('You beat the game! Game stats have been saved.');
                    },
                    error: function() {
                        alert('An error occurred while saving the game stats.');
                    }
                });
            }
        }
        }

        //timer
        setInterval(function() {
            timer += 0.016;
            document.getElementById('timer').textContent = 'Timer: ' + Math.round(timer) + ' seconds';
        }, 16);
    </script>
</body>
</html>