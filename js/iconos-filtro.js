$(document).ready(function () {
    // Inicializar solo el select de estado con iconos
    $('#status').select2({
        templateResult: formatOption,
        templateSelection: formatOption,
        width: '200px'
    });

    function formatOption(option) {
        if (!option.id) return option.text;

        const icon = $(option.element).data('icon');
        const iconClass = $(option.element).data('icon-class') || '';

        return $(
            '<span class="icon-option">' +
            '<span class="material-symbols-outlined ' + iconClass + '">' + icon + '</span> ' +
            option.text +
            '</span>'
        );
    }
});