document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('corte-pon-root');
    if (!root) return; // esta vista no tiene el panel Corte PON, no hacer nada

    cargarCortePon();
    // Refresco periódico, alineado con el ciclo del cron (~7-10 min).
    setInterval(cargarCortePon, 5 * 60 * 1000);

    async function cargarCortePon() {
        try {
            const resp = await fetch('../controllers/load_corte_pon.php');
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            const data = await resp.json();

            renderVariacionesSenal(data.variaciones_senal);
            renderOutageSeccion('los_parcial', data.los_parcial, {
                etiquetaUnidad: 'ONUs',
                resumenSelector: '[data-resumen-los-parcial]',
            });
            renderOutageSeccion('los_total', data.los_total, {
                etiquetaUnidad: 'ONUs',
                resumenSelector: '[data-resumen-los-total]',
                singularPon: true,
            });
            renderOutageSeccion('fallo_energia', data.fallo_energia, {});
            renderOutageSeccion('offline', data.offline, {});

            document.getElementById('cp-actualizado').textContent =
                'actualizado ' + new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
        } catch (error) {
            console.error('Error al cargar Corte PON:', error);
            document.querySelectorAll('.cp-panel__body[data-body]').forEach(el => {
                el.innerHTML = '<div class="cp-loading">Error al cargar los datos.</div>';
            });
        }
    }

    // =====================================================================
    // Variaciones de señal
    // =====================================================================
    function renderVariacionesSenal(seccion) {
        const panel = document.querySelector('[data-panel="variaciones_senal"]');
        const body = panel.querySelector('[data-body]');

        panel.querySelector('[data-resumen="total_pons"]').textContent = `${seccion.resumen.total_pons} PONs`;
        panel.querySelector('[data-resumen="criticos"]').textContent = `${seccion.resumen.criticos} Crítico`;
        panel.querySelector('[data-resumen="advertencias"]').textContent = `${seccion.resumen.advertencias} Advertencia`;
        panel.querySelector('[data-resumen="inestables"]').textContent = `${seccion.resumen.inestables} Inestable`;

        if (seccion.grupos.length === 0) {
            body.innerHTML = vacioHtml('Sin variaciones de señal relevantes en las últimas 6 horas.');
            return;
        }

        let html = `<table class="cp-tabla">
            <thead><tr>
                <th>OLT ID - OLT name</th><th>Tarjeta/Puerto</th><th>Avg var</th>
                <th>Max var (dB)</th><th>Eventos (6h)</th><th>Último escaneo</th>
            </tr></thead><tbody>`;

        seccion.grupos.forEach((g, idx) => {
            const grupoId = `vs-${idx}`;
            html += `
                <tr class="cp-fila-grupo" data-toggle-fila="${grupoId}">
                    <td class="cp-fila-grupo__nombre">
                        <span class="cp-chevron-fila material-symbols-outlined">expand_more</span>
                        <span class="cp-dot cp-dot--${g.severidad}"></span>
                        ${g.olt_id} - ${escapeHtml(g.olt_name)}
                    </td>
                    <td>${g.tarjeta} / ${g.puerto}</td>
                    <td>${g.avg_var.toFixed(1)}</td>
                    <td>${g.max_var.toFixed(1)}</td>
                    <td>${g.eventos_6h}</td>
                    <td>${escapeHtml(g.ultimo_escaneo)}</td>
                </tr>
                <tr class="cp-fila-detalle-header" data-fila-detalle="${grupoId}">
                    <td>ONU</td><td>Previous</td><td>Current</td><td>Var (dBm)</td><td>Eventos (6h)</td><td></td>
                </tr>`;

            g.onus.forEach(onu => {
                const varClase = onu.var > 0 ? 'cp-var--pos' : (onu.var < 0 ? 'cp-var--neg' : 'cp-var--neutro');
                const varTexto = (onu.var > 0 ? '+' : '') + onu.var.toFixed(1);
                html += `
                    <tr class="cp-fila-onu" data-fila-detalle="${grupoId}">
                        <td>${escapeHtml(onu.interfaz)} (<a href="ont.php?id=${onu.onu_id}">${escapeHtml(onu.serial)}</a>)</td>
                        <td>${onu.previous.toFixed(1)}</td>
                        <td>${onu.current.toFixed(1)}</td>
                        <td class="${varClase}">${varTexto}</td>
                        <td>${onu.eventos_6h}</td>
                        <td></td>
                    </tr>`;
            });
        });

        html += '</tbody></table>';
        body.innerHTML = html;
        activarTogglesFila(body);
    }

    // =====================================================================
    // LOS parcial / LOS total / Fallo de energía / Offline
    // (mismo formato: agrupado por OLT -> Tarjeta/Puerto)
    // =====================================================================
    function renderOutageSeccion(nombrePanel, seccion, opciones) {
        const panel = document.querySelector(`[data-panel="${nombrePanel}"]`);
        if (!panel) return;
        const body = panel.querySelector('[data-body]');

        if (opciones.resumenSelector) {
            const elResumen = panel.querySelector(opciones.resumenSelector);
            if (elResumen) {
                const etiquetaPon = opciones.singularPon && seccion.resumen.total_pons === 1 ? 'PON' : 'PONs';
                elResumen.textContent = `${seccion.resumen.total_pons} ${etiquetaPon} / ${seccion.resumen.total_onus} ONUs`;
            }
        }

        if (seccion.grupos.length === 0) {
            body.innerHTML = vacioHtml('Todo en orden. Sin alertas activas.');
            return;
        }

        let html = `<table class="cp-tabla">
            <thead><tr><th>OLT</th><th>PONs</th><th>ONUs</th><th>Desde</th></tr></thead><tbody>`;

        seccion.grupos.forEach((g, idx) => {
            const grupoId = `${nombrePanel}-${idx}`;
            html += `
                <tr class="cp-fila-grupo" data-toggle-fila="${grupoId}">
                    <td class="cp-fila-grupo__nombre">
                        <span class="cp-chevron-fila material-symbols-outlined">expand_more</span>
                        ${g.olt_id} - ${escapeHtml(g.olt_name)}
                    </td>
                    <td>${g.total_pons} PON${g.total_pons !== 1 ? 's' : ''}</td>
                    <td>${g.total_onus} ONUs</td>
                    <td>${escapeHtml(g.desde)}</td>
                </tr>`;

            g.puertos.forEach(p => {
                html += `
                    <tr class="cp-fila-onu" data-fila-detalle="${grupoId}">
                        <td>Tarjeta/Puerto&nbsp;&nbsp;${p.tarjeta} / ${p.puerto}</td>
                        <td></td>
                        <td>${p.onus_afectadas}/${p.onus_total} ONUs (${p.porcentaje}%)</td>
                        <td>${escapeHtml(p.desde)}</td>
                    </tr>`;
            });
        });

        html += '</tbody></table>';
        body.innerHTML = html;
        activarTogglesFila(body);
    }

    // =====================================================================
    // Toggles (colapsar/expandir)
    // =====================================================================

    // Colapsar/expandir panel completo (nivel superior)
    document.querySelectorAll('[data-toggle-panel]').forEach(header => {
        header.addEventListener('click', () => {
            header.classList.toggle('is-collapsed');
            const chevron = header.querySelector('.cp-panel__chevron');
            chevron.textContent = header.classList.contains('is-collapsed') ? 'chevron_right' : 'expand_more';
        });
    });

    // Colapsar/expandir un grupo (fila de PON u OLT) dentro de una tabla ya renderizada
    function activarTogglesFila(contenedor) {
        contenedor.querySelectorAll('[data-toggle-fila]').forEach(fila => {
            fila.addEventListener('click', () => {
                const id = fila.getAttribute('data-toggle-fila');
                const chevron = fila.querySelector('.cp-chevron-fila');
                const colapsando = !chevron.classList.contains('is-collapsed');
                chevron.classList.toggle('is-collapsed', colapsando);
                contenedor.querySelectorAll(`[data-fila-detalle="${id}"]`).forEach(detalle => {
                    detalle.style.display = colapsando ? 'none' : '';
                });
            });
        });
    }

    // =====================================================================
    // Helpers
    // =====================================================================
    function vacioHtml(mensaje) {
        return `<div class="cp-vacio">
            <span class="material-symbols-outlined">check_circle</span>
            <span>${escapeHtml(mensaje)}</span>
        </div>`;
    }

    function escapeHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto ?? '';
        return div.innerHTML;
    }
});