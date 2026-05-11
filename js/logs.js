document.addEventListener('DOMContentLoaded', async function () {
    // Configuración de la API
    const apiUrl = '../api/logs.php?accion=general'; // Ajusta la ruta
    const logsContainer = document.querySelector('.ONUS');

    // Mostrar estado de carga
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'loading';
    loadingDiv.textContent = 'Cargando logs...';
    logsContainer.appendChild(loadingDiv);

    // Función para manejar errores
    function handleError(error) {
        console.error('Error:', error);

        // Remover loading si existe
        if (loadingDiv.parentNode === logsContainer) {
            logsContainer.removeChild(loadingDiv);
        }

        // Mostrar mensaje de error
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = `Error al cargar logs: ${error.message}`;
        logsContainer.appendChild(errorDiv);
    }
    try {
        const response = await fetch(apiUrl,{
            method: 'GET',
            headers:{
                'Accept':'application/json'
            }});
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }
        const data = await response.json();
        if (loadingDiv.parentNode === logsContainer) {
            logsContainer.removeChild(loadingDiv);
        }

        // Verificar estructura de datos
        if (!data || !data.status || !data.message) {
            throw new Error('Estructura de datos incorrecta');
        }

        // Mostrar logs en la tabla
        displayLogs(data.message);
    } catch (error) {
        handleError(error);
    }
});
function displayLogs(logs) {
    const tabla = document.getElementById('logsTable');
    let tbody = tabla.querySelector('tbody') || document.createElement('tbody');
    tbody.innerHTML = '';

    // 1. Ordenar por fecha (más reciente primero)
    const logsOrdenados = [...logs].sort((a, b) => {
        return new Date(b.Date) - new Date(a.Date);
    });

    // 2. Obtener los primeros 5 (que serán los más recientes)
    const logsLimitados = logsOrdenados.slice(0, 5);

    logsLimitados.forEach(log => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${log.UsuarioCorreo || 'N/A'}</td>
            <td>${log.Accion || 'N/A'}</td>
            <td>${log.OltName || 'N/A'}</td>
            <td>${log.Onu || 'N/A'}</td>
            <td>${log.Ip || 'N/A'}</td>
            <td>${log.Date || 'N/A'}</td>
        `;
        tbody.appendChild(row);
    });

    if (!tbody.parentNode) {
        tabla.appendChild(tbody);
    }
}