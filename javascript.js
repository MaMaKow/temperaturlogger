let chartInstance = null;
let allData = [];
let sensors = new Map();
let selectedSensorId = null;

async function fetchDataByDate(dateFrom, dateTo) {
    try {
        const url = `/temperaturlogger/api-ausgabe.php?dateFrom=${dateFrom}&dateTo=${dateTo}`;

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error('Netzwerkfehler');
        }

        const data = await response.json();

        allData = data;

        updateSensorList(data);

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

        document.getElementById('lastUpdate').innerText =
            'Letzte Aktualisierung: ' +
            new Intl.DateTimeFormat('de-DE', {
                dateStyle: 'medium',
                timeStyle: 'medium'
            }).format(new Date());

    } catch (error) {
        console.error(error);
        document.getElementById('lastUpdate').innerText =
            'Fehler beim Laden der Daten';
    }
}

function updateSensorList(data) {
    const select = document.getElementById('sensorSelect');
    const currentValue = selectedSensorId || select.value;

    data.forEach(row => {
        const id = String(row.ident_nummer);

        if (!sensors.has(id)) {
            sensors.set(id, row.name);
        }
    });

    select.innerHTML =
        '<option value="">-- Bitte Sensor wählen --</option>';

    for (const [id, name] of sensors.entries()) {
        const option = document.createElement('option');
        option.value = id;
        option.textContent = `${name} (ID ${id})`;
        select.appendChild(option);
    }

    if (currentValue && sensors.has(currentValue)) {
        select.value = currentValue;
    }
}

function updateChart(sensorId, data) {

    const sensorData = data
        .filter(row => String(row.ident_nummer) === String(sensorId))
        .sort((a, b) => new Date(a.zeit) - new Date(b.zeit));

    if (sensorData.length === 0) {

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        const ctx = document
            .getElementById('tempChart')
            .getContext('2d');

        ctx.clearRect(
            0,
            0,
            ctx.canvas.width,
            ctx.canvas.height
        );

        ctx.font = '16px Arial';
        ctx.fillStyle = '#666';
        ctx.fillText(
            'Keine Daten für diesen Sensor im gewählten Zeitraum',
            20,
            50
        );

        return;
    }

    const first = new Date(sensorData[0].zeit);
    const last = new Date(sensorData[sensorData.length - 1].zeit);

    const diffDays =
        (last.getTime() - first.getTime()) /
        (1000 * 60 * 60 * 24);

    let unit = 'hour';

    if (diffDays > 180) {
        unit = 'month';
    } else if (diffDays > 30) {
        unit = 'week';
    } else if (diffDays > 3) {
        unit = 'day';
    }

    const chartData = sensorData.map(row => ({
        x: new Date(row.zeit).getTime(),
        y: Number(row.wert)
    }));

    const ctx = document
        .getElementById('tempChart')
        .getContext('2d');

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(ctx, {
        type: 'line',

        data: {
            datasets: [{
                label: `Temperatur – ${sensors.get(String(sensorId))} (ID ${sensorId})`,
                data: chartData,

                borderColor: '#1976d2',
                backgroundColor: 'rgba(25,118,210,0.15)',

                borderWidth: 2,
                fill: true,
                tension: 0.25,

                pointRadius: 0,
                pointHoverRadius: 5,
                pointHitRadius: 10
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: true,

            interaction: {
                mode: 'nearest',
                intersect: false
            },

            scales: {
                x: {
                    type: 'time',

                    time: {
                        unit,

                        displayFormats: {
                            hour: 'HH:mm',
                            day: 'dd.MM',
                            week: 'dd.MM',
                            month: 'MM.yyyy'
                        },

                        tooltipFormat: 'dd.MM.yyyy HH:mm:ss'
                    },

                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 10,
                        maxRotation: 0
                    },

                    title: {
                        display: true,
                        text: 'Zeit'
                    }
                },

                y: {
                    beginAtZero: false,

                    ticks: {
                        callback(value) {
                            return value.toFixed(1) + ' °C';
                        }
                    },

                    title: {
                        display: true,
                        text: 'Temperatur (°C)'
                    }
                }
            },

            plugins: {
                legend: {
                    display: true
                },

                tooltip: {
                    callbacks: {
                        title(items) {
                            return new Intl.DateTimeFormat(
                                'de-DE',
                                {
                                    dateStyle: 'full',
                                    timeStyle: 'medium'
                                }
                            ).format(
                                new Date(items[0].parsed.x)
                            );
                        },

                        label(context) {
                            return `Temperatur: ${context.parsed.y.toFixed(2)} °C`;
                        }
                    }
                }
            }
        }
    });
}

document
    .getElementById('sensorSelect')
    .addEventListener('change', e => {

        selectedSensorId = e.target.value;

        if (selectedSensorId && allData.length) {
            updateChart(selectedSensorId, allData);
        }
    });

const dateFrom =
    document.querySelector('input[name="dateFrom"]');

const dateTo =
    document.querySelector('input[name="dateTo"]');

dateFrom.addEventListener('change', updateDateRange);
dateTo.addEventListener('change', updateDateRange);

function updateDateRange() {

    if (!dateFrom.value || !dateTo.value) {
        return;
    }

    fetchDataByDate(
        dateFrom.value,
        dateTo.value
    );
}

fetchDataByDate(
    dateFrom.value,
    dateTo.value
);
