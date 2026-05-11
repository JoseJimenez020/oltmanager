$(document).ready( async function () {
    const oltId = getUrlParameter('id'); 
    try {
        const response = await fetch(`../api/oltProfile.php?accion=one&olt=${oltId}`);
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
          }
      
          const data = await response.json();
          if (data.status === true) {
            // Obtén la información del OLT de la respuesta
            const olt = data.message;

            // Llenamos las celdas del <tbody> según el índice
            const tbody = $('#oneOltTables');
            const rows = tbody.find('tr');

            // Actualizamos las celdas en el orden correspondiente
            rows.eq(0).find('td').eq(1).text(olt.OltName);
            rows.eq(1).find('td').eq(1).text(olt.OltIpPrivate);
            rows.eq(2).find('td').eq(1).text('no');
            rows.eq(3).find('td').eq(1).text(olt.OltTelnetPort);
            rows.eq(4).find('td').eq(1).find('input').val(olt.UserTelnet);
            rows.eq(5).find('td').eq(1).find('input').val(olt.PassTelnet);
            rows.eq(6).find('td').eq(1).find('input').val(olt.ReadComm);
            rows.eq(7).find('td').eq(1).find('input').val(olt.WriteComm);
            rows.eq(8).find('td').eq(1).text(olt.OltSnmpPort);
            rows.eq(9).find('td').eq(1).text('disable');
            rows.eq(10).find('td').eq(1).text(olt.OltHardVer);
            rows.eq(11).find('td').eq(1).text(olt.SoftVer);
            rows.eq(12).find('td').eq(1).text('GPON');
            rows.eq(13).find('td').eq(1).find('select').val('no');
        } else {
            console.error('No se pudo obtener la información del OLT');
        }
    } catch (error) {
        console.error('Error al obtener los datos:', error);
    }
    // Función para obtener parámetros de la URL
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
});