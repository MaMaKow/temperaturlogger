<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperaturlogger – Aktuelle Werte</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        table { border-collapse: collapse; width: 100%; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .container { max-width: 1200px; margin: auto; }
        .last-update { margin-bottom: 10px; font-style: italic; }
    </style>
</head>
<body>
<div class="container">
    <h1>Temperaturlogger – Echtzeitdaten</h1>
    <div class="last-update" id="lastUpdate">Lade Daten...</div>
    <div style="overflow-x: auto;">
        <table id="dataTable">
            <thead>
                <tr>
                    <th>Zeit</th><th>Name</th><th>Wert (°C)</th><th>RSSI</th><th>ID</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr><td colspan="5">Daten werden geladen…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function fetchData() {
        try {
            const response = await fetch('/temperaturlogger/api-ausgabe.php');
            if (!response.ok) throw new Error('Netzwerkfehler');
            const data = await response.json();
            displayData(data);
            document.getElementById('lastUpdate').innerText = 'Letzte Aktualisierung: ' + new Date().toLocaleTimeString();
        } catch (error) {
            console.error('Fehler beim Laden:', error);
            document.getElementById('tableBody').innerHTML = '<tr><td colspan="5">Fehler beim Laden der Daten</td></tr>';
        }
    }

    function displayData(data) {
        const tbody = document.getElementById('tableBody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5">Keine Daten im Zeitraum</td></tr>';
            return;
        }
        // Sortieren nach Zeit, neueste zuerst
        data.sort((a,b) => new Date(b.zeit) - new Date(a.zeit));
        const rows = data.map(row => `
            <tr>
                <td>${new Date(row.zeit).toLocaleString()}</td>
                <td>${escapeHtml(row.name)}</td>
                <td>${row.wert.toFixed(2)} °C</td>
                <td>${row.rssi_value} dBm</td>
                <td>${row.ident_nummer}</td>
            </tr>
        `).join('');
        tbody.innerHTML = rows;
    }

    // Helper gegen XSS
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    // Alle 10 Sekunden aktualisieren
    setInterval(fetchData, 10000);
    fetchData();
</script>
</body>
</html>
