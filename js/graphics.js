document.addEventListener('DOMContentLoaded', async function () {
    const user = new URLSearchParams(window.location.search);
    const id = user.get('id');

    const response = await fetch(`../api/bandWith.php?accion=all&id=${id}`);
    const result = await response.json();
    const data = result.message;

    // Invertir para mostrar de más viejo a más reciente
    data.reverse();

    const labels = data.map(d => {
        const date = new Date(d.date);  // Aseguramos que se interprete como UTC
        let hours = date.getHours();
        let minutes = date.getMinutes().toString().padStart(2, '0'); // Asegurarse de que los minutos tengan dos dígitos

        const label = `${hours}:${minutes}`;
        console.log('Hora procesada:', label);
        return label;
    });

    const convertToKilobytes = (value, unit) => {
        switch (unit) {
            case 'B': return value / 1024;
            case 'K': return value;
            case 'M': return value * 1024 * 10;
            default: return value;
        }
    };

    const convertToMegabytes = (value, unit) => {
        switch (unit) {
            case 'B': return value / (1024 * 1024);
            case 'K': return value / 1024;
            case 'M': return value * 10;
            default: return value;
        }
    };


    const rxDataKB = data.map(d => convertToKilobytes(parseFloat(d.Rx), d.typeRx));
    const txDataKB = data.map(d => convertToKilobytes(parseFloat(d.Tx), d.typeTx));
    const rxDataMB = data.map(d => convertToMegabytes(parseFloat(d.Rx), d.typeRx));
    const txDataMB = data.map(d => convertToMegabytes(parseFloat(d.Tx), d.typeTx));

    const ctxKB = document.getElementById('graphic-min').getContext('2d');
    const ctxMB = document.getElementById('graphic-hour').getContext('2d');

    new Chart(ctxKB, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: `Upload (KB)`,
                    data: rxDataKB,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.2)',
                    fill: true
                },
                {
                    label: `Download (KB)`,
                    data: txDataKB,
                    borderColor: 'green',
                    backgroundColor: 'rgba(0, 255, 0, 0.2)',
                    fill: true
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => value + ' KB'
                    },
                    title: {
                        display: true,
                        text: 'Consumo en Kilobytes'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Gráfico de Consumo (KB)'
                }
            }
        }
    });

    new Chart(ctxMB, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: `Upload (MB)`,
                    data: rxDataMB,
                    borderColor: 'purple',
                    backgroundColor: 'rgba(128, 0, 128, 0.2)',
                    fill: true
                },
                {
                    label: `Download (MB)`,
                    data: txDataMB,
                    borderColor: 'orange',
                    backgroundColor: 'rgba(255, 165, 0, 0.2)',
                    fill: true
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => value + ' MB'
                    },
                    title: {
                        display: true,
                        text: 'Consumo en Megabytes'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Gráfico de Consumo (MB)'
                }
            }
        }
    });

});
