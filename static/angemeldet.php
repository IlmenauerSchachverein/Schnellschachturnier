<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmeldungen ISST 2025</title>
    <style>
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #4CAF50;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e9e9e9;
        }
        .highlight {
            background-color: #ffcccc; /* Hellrot für Übereinstimmungen */
        }
    </style>
</head>
<body>
    <h1>Anmeldungen ISST 2025</h1>

    <?php
    // Pfad zur CSV-Datei
    $csvFile = '/var/private/isv/isst25.csv';
    $csvNames = [];

    // Öffne die CSV-Datei und extrahiere die Namen im Format "Nachname, Vorname"
    if (file_exists($csvFile)) {
        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Kombiniere Nachname und Vorname im Format "Nachname, Vorname"
                $csvNames[] = trim($data[3]) . ', ' . trim($data[2]); // Annahme: Nachname in $data[3] und Vorname in $data[2]
            }
            fclose($handle);
        }
    } else {
        echo '<p style="color: red; text-align: center;">Fehler: Die CSV-Datei existiert nicht.</p>';
    }

    // Lade die HTML-Inhalte von der Webseite
    $url = 'https://chess-results.com/tnr1056111.aspx?lan=0';
    $html = file_get_contents($url);
    $webNames = [];

    if ($html !== FALSE) {
        // Nutze DOMDocument und DOMXPath, um die Namen aus der Webtabelle zu extrahieren
        $dom = new DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Finde alle Namen in der Tabelle und speichere sie im Array $webNames
        $rows = $xpath->query("//table[@class='CRs1']//tr");
        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Überspringe die Header-Zeile
            $cells = $row->getElementsByTagName('td');
            if ($cells->length >= 4) {
                $webNames[] = trim($cells->item(3)->nodeValue); // Name in der vierten Zelle im Format "Nachname, Vorname"
            }
        }
    } else {
        echo '<p style="color: red; text-align: center;">Fehler: Die Webtabelle konnte nicht geladen werden.</p>';
    }

    // Finde Namen, die auf ChessResults gemeldet sind, aber nicht in der CSV
    $notInCsv = array_diff($webNames, $csvNames);

    // Tabelle erstellen und CSV-Daten anzeigen
    echo '<table>';
    echo '<tr><th>Datum</th><th>Zeit</th><th>Vorname</th><th>Nachname</th><th>Verein</th><th>Geburtsdatum</th><th>Handynummer</th><th>Email</th><th>Rabattberechtigung</th><th>Bestätigung</th><th>AGB Zustimmung</th><th>ChessResults</th></tr>';

    if (file_exists($csvFile)) {
        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Erstelle den vollständigen Namen im Format "Nachname, Vorname" aus der CSV
                $fullName = trim($data[3]) . ', ' . trim($data[2]);

                // Prüfe, ob der Name in der Webliste vorkommt
                $highlightClass = in_array($fullName, $webNames) ? 'highlight' : '';
                $chessResultsMatch = in_array($fullName, $webNames) ? 'X' : '';

                // Zeile in die Tabelle ausgeben
                echo "<tr class='{$highlightClass}'>";
                echo '<td>' . htmlspecialchars($data[0]) . '</td>'; // Datum
                echo '<td>' . htmlspecialchars($data[1]) . '</td>'; // Zeit
                echo '<td>' . htmlspecialchars($data[2]) . '</td>'; // Vorname
                echo '<td>' . htmlspecialchars($data[3]) . '</td>'; // Nachname
                echo '<td>' . htmlspecialchars($data[4]) . '</td>'; // Verein
                echo '<td>' . htmlspecialchars($data[5]) . '</td>'; // Geburtsdatum
                echo '<td>' . htmlspecialchars($data[6]) . '</td>'; // Handynummer
                echo '<td>' . htmlspecialchars($data[7]) . '</td>'; // Email
                echo '<td>' . htmlspecialchars($data[8]) . '</td>'; // Rabattberechtigung
                echo '<td>' . htmlspecialchars($data[9]) . '</td>'; // Bestätigung
                echo '<td>' . htmlspecialchars($data[10]) . '</td>'; // AGB Zustimmung
                echo "<td>{$chessResultsMatch}</td>"; // ChessResults-Spalte mit 'X' bei Übereinstimmung
                echo '</tr>';
            }
            fclose($handle);
        }
    }

    echo '</table>';

    // Liste der Namen, die auf ChessResults stehen, aber nicht in der CSV-Datei sind
    echo '<h2>Auf ChessResults gemeldet, aber nicht in der CSV-Datei:</h2>';
    if (!empty($notInCsv)) {
        echo '<ul>';
        foreach ($notInCsv as $name) {
            echo "<li>{$name}</li>";
        }
        echo '</ul>';
    } else {
        echo '<p>Alle gemeldeten Spieler sind auch in der CSV-Datei vorhanden.</p>';
    }
    ?>
</body>
</html>
