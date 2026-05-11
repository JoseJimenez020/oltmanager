const fetchData = async () => {
    try {
        const response = await fetch('../api/refreshDb.php');
        
        if (!response.ok) {
            throw new Error(`Error: ${response.statusText}`);
        }
        const data = await response.json();
        
        console.log('Respuesta del servidor:', data.message);
    } catch (error) {
        console.error('Error al ejecutar el fetch:', error);
    }
};

// Ejecutar la función inmediatamente y cada 10 minutos
fetchData();
setInterval(fetchData, 360000); // 600000ms = 6 minutos
