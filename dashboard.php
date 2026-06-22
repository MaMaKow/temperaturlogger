<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temperatur Dashboard mit Diagramm</title>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@3.6.0/locale/de/index.min.js"></script>
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

<script type="text/javascript" src="javascript.js"></script>
</body>
</html>
