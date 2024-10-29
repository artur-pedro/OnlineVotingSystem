(() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');

    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            const labels = form.querySelectorAll('.label');
            const invalidFeedbacks = form.querySelectorAll('.invalid-feedback');
            const roleSelect = form.querySelector('select[name="role"]'); // Obtém o campo de seleção do role

            // Oculta todos os rótulos inicialmente
            labels.forEach(label => label.style.display = 'none');

            // Remove a classe de erro do campo de seleção
            roleSelect.classList.remove('is-invalid');
            roleSelect.classList.remove('is-valid');

            // Se o formulário não for válido
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Mostra apenas os textos de feedback para campos inválidos
                invalidFeedbacks.forEach(feedback => {
                    const input = feedback.previousElementSibling; // Obtém o input correspondente
                    if (input && input.classList.contains('form-control') && !input.checkValidity()) {
                        feedback.style.display = 'block'; // Mostra o feedback se o input estiver inválido
                    }
                });

                // Verifica o campo de seleção do role
                if (!roleSelect.value) {
                    roleSelect.classList.add('is-invalid'); // Adiciona classe de erro se não for selecionado
                    roleSelect.classList.remove('is-valid');
                    invalidFeedbacks[2].style.display = 'block'; // Mostra o feedback do campo role
                } else {
                    roleSelect.classList.remove('is-invalid');
                    roleSelect.classList.add('is-valid');
                }
            }

            form.classList.add('was-validated');
        }, false);
    });
})();
