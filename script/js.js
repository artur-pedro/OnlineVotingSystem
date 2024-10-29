
// Validação do formulário Bootstrap
(() => {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
        // JavaScript para validação do formulário
        (function () {
            'use strict';
            const form = document.getElementById('loginForm');

            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault(); // Previne o envio do formulário
                    event.stopPropagation(); // Para a propagação do evento
                }
                form.classList.add('was-validated'); // Adiciona a classe de validação
            }, false);
        })();