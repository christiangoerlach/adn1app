<!DOCTYPE html>
<html lang="de">
<head>
    <title>3a2 Consulting - Standort Pohlheim (Kellereigasse)</title>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Azure Maps SDK Version 3 laden -->
    <link rel="stylesheet" href="https://atlas.microsoft.com/sdk/javascript/mapcontrol/3/atlas.min.css" type="text/css" />
    <script src="https://atlas.microsoft.com/sdk/javascript/mapcontrol/3/atlas.min.js"></script>

    <style>
        html, body { margin: 0; padding: 0; height: 100%; width: 100%; }
        #myMap { height: 100vh; width: 100vw; }
        .info-box {
            background: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 250px;
        }
        .info-box h3 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 16px;
        }
        .info-box p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body onload="InitMap()">
    <div id="myMap"></div>

    <script type="text/javascript">
        function InitMap() {
            // ADN Consulting Standort: Kellereigasse 1, 35415 Pohlheim, Deutschland
            // Exakte Koordinaten: 8.73397, 50.53225 (Kellereigasse 1, Pohlheim)
            var adnLocation = [8.73397, 50.53225]; // Exakte Koordinaten für Kellereigasse 1, Pohlheim
            
            var map = new atlas.Map('myMap', {
                center: adnLocation,
                zoom: 19, // Maximale Zoom-Stufe für Gebäudesicht
                language: 'de-DE',
                view: 'Auto',
                pitch: 45, // Leichte Neigung für bessere 3D-Sicht
                bearing: 0, // Norden oben
                authOptions: {
                    authType: 'subscriptionKey',
                    subscriptionKey: '<?php echo $azureMapsKey; ?>'
                }
            });

            map.events.add('ready', function () {
                // Kartensteuerung hinzufügen
                map.controls.add(new atlas.control.ZoomControl(), { position: 'top-right' });
                map.controls.add(new atlas.control.CompassControl(), { position: 'top-right' });
                map.controls.add(new atlas.control.PitchControl(), { position: 'top-right' });
                map.controls.add(new atlas.control.FullscreenControl(), { position: 'top-right' });
                map.controls.add(new atlas.control.StyleControl({
                    mapStyles: [
                        'road',
                        'satellite',
                        'satellite_road_labels'
                    ],
                    layout: 'list'
                }), { position: 'top-left' });
                map.controls.add(new atlas.control.ScaleControl(), { position: 'bottom-right' });

                // Marker für ADN Consulting hinzufügen - exakte Position
                var marker = new atlas.HtmlMarker({
                    htmlContent: '<div style="background-color: #0078d4; color: white; padding: 8px 12px; border-radius: 20px; font-weight: bold; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); border: 2px solid white;">📍 ADN</div>',
                    position: adnLocation
                });

                map.markers.add(marker);

                // Popup mit genauer Adresse und Koordinaten von ADN Consulting
                var popup = new atlas.Popup({
                    content: '<div class="info-box">' +
                             '<h3>ADN Consulting</h3>' +
                             '<p><strong>Adresse:</strong><br>Kellereigasse 1<br>35415 Pohlheim<br>Deutschland</p>' +
                             '<p><strong>Koordinaten:</strong><br>8.73397, 50.53225</p>' +
                             '<p><strong>Geschäftsbereich:</strong><br>IT-Beratung & Entwicklung</p>' +
                             '</div>',
                    position: adnLocation
                });

                // Popup beim Klick auf Marker öffnen
                marker.events.add('click', function() {
                    popup.open(map);
                });

                // Popup beim Klick auf die Karte schließen
                map.events.add('click', function() {
                    popup.close();
                });
            });

            // Fehlerbehandlung
            map.events.add('error', function(e) {
                console.error('Kartenfehler:', e);
            });
        }
    </script>
</body>
</html>

