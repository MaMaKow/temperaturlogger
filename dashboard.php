<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperatur Dashboard mit Diagramm</title>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: auto; }
        h1 { color: #2c3e50; }
        .controls { margin: 20px 0; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        select, button { padding: 8px 12px; font-size: 1rem; }
        .chart-container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        canvas { max-height: 400px; width: 100%; }
        table { border-collapse: collapse; width: 100%; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .last-update { margin-bottom: 10px; font-style: italic; }
        .table-container { overflow-x: auto; }
        .sensor-select { font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>📈 Temperaturlogger Dashboard</h1>
    <div class="controls">
        <label>Sensor auswählen:</label>
        <select id="sensorSelect">
            <option value="">-- Bitte Sensor wählen --</option>
        </select>
        <label>Zeitraum:</label>
        <input type="date" name="dateFrom" value="<?= (new DateTime('yesterday'))->format('Y-m-d') ?>">
        <input type="date" name="dateTo" value="<?= (new DateTime('today'))->format('Y-m-d') ?>">
    </div>
    <div class="chart-container">
        <canvas id="tempChart"></canvas>
    </div>
    <div class="last-update" id="lastUpdate"></div>
</div>

<script>
    let chartInstance = null;
    let allData = [];     // alle Rohdaten (für die Tabelle)
    let sensors = new Map(); // Map<ident_nummer, name>
    let selectedSensorId = null;

    async function fetchDataByDate(dateFrom, dateTo) {
        try {
            const url = `/temperaturlogger/api-ausgabe.php?dateFrom=${dateFrom}&dateTo=${dateTo}`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Netzwerkfehler');
            const data = await response.json();
            allData = data;
            // Sensorenliste aktualisieren
            updateSensorList(data);
            // Diagramm aktualisieren (falls ein Sensor ausgewählt ist)

            const select = document.getElementById('sensorSelect');

            if (!selectedSensorId && select.options.length > 1) {
                selectedSensorId = select.options[1].value;
                select.value = selectedSensorId;
            }

            if (selectedSensorId) {
                select.value = selectedSensorId;
                updateChart(selectedSensorId, data);
            } else {
                if (chartInstance) {
                    chartInstance.destroy();
                    chartInstance = null;
                }
            }
            document.getElementById('lastUpdate').innerText = 'Letzte Aktualisierung: ' + new Date().toLocaleString();
        } catch (error) {
            console.error('Fehler:', error);
            document.getElementById('lastUpdate').innerText = 'Fehler beim Laden der Daten';
        }
    }

    function updateSensorList(data) {
        const select = document.getElementById('sensorSelect');
        const currentValue = select.value;
        // Map füllen
        data.forEach(row => {
            const id = row.ident_nummer;
            if (!sensors.has(id)) {
                sensors.set(id, row.name);
            }
        });
        // Dropdown neu aufbauen (ohne Event-Listener kurzzeitig)
        const oldListener = select.onchange;
        select.onchange = null;
        select.innerHTML = '<option value="">-- Bitte Sensor wählen --</option>';
        for (let [id, name] of sensors.entries()) {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = `${name} (ID ${id})`;
            select.appendChild(option);
        }
        if (currentValue && sensors.has(currentValue)) select.value = currentValue;
        select.onchange = oldListener;
    }


    function updateChart(sensorId, data) {
        // Daten für diesen Sensor filtern und nach Zeit aufsteigend sortieren
        const sensorData = data
            .filter(row => row.ident_nummer == sensorId)
            .sort((a,b) => new Date(a.zeit) - new Date(b.zeit));
        
        if (sensorData.length === 0) {
            if (chartInstance) chartInstance.destroy();
            const ctx = document.getElementById('tempChart').getContext('2d');
            ctx.clearRect(0,0,400,200);
            ctx.font = '16px Arial';
            ctx.fillStyle = 'gray';
            ctx.fillText('Keine Daten für diesen Sensor im gewählten Zeitraum', 20, 50);
            return;
        }

        const labels = sensorData.map(row => new Date(row.zeit).toLocaleString());
        const temperatures = sensorData.map(row => parseFloat(row.wert));

        const ctx = document.getElementById('tempChart').getContext('2d');
        if (chartInstance) chartInstance.destroy();
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: `Temperatur – ${sensors.get(sensorId)} (ID ${sensorId})`,
                    data: temperatures,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        title: { display: true, text: 'Temperatur (°C)' },
                        beginAtZero: false
                    },
                    x: {
                        title: { display: true, text: 'Zeit' },
                        ticks: { maxRotation: 45, autoSkip: true }
                    }
                },
                plugins: {
                    tooltip: { mode: 'index', intersect: false },
                    zoom: false // optional
                }
            }
        });
    }

    // Event Listener
    document.getElementById('sensorSelect').addEventListener('change', (e) => {
        selectedSensorId = e.target.value;
        if (selectedSensorId && allData.length) {
            updateChart(selectedSensorId, allData);
        }
    });
    const dateFrom = document.querySelector('input[name="dateFrom"]');
    const dateTo = document.querySelector('input[name="dateTo"]');

    dateFrom.addEventListener('change', updateDateRange);
    dateTo.addEventListener('change', updateDateRange);

    function updateDateRange() {
        if (!dateFrom.value || !dateTo.value) {
            return;
        }

        console.log('Von:', dateFrom.value);
        console.log('Bis:', dateTo.value);

        fetchDataByDate(dateFrom.value, dateTo.value);
    }

    // Initialer Aufruf (Standard: 24 Stunden)
    fetchDataByDate(dateFrom, dateTo);
</script>
</body>
</html>
