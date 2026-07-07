document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contact-form');
    const status = document.getElementById('contact-form-status');

    if (!form || !status) return;

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            status.textContent = 'Uzupełnij wymagane pola formularza.';
            status.classList.add('is-error');
            return;
        }

        const name = form.elements.name.value.trim();
        status.textContent = `Dziękujemy${name ? `, ${name}` : ''}! To potwierdzenie jest częścią wersji demo — wiadomość nie została wysłana.`;
        status.classList.remove('is-error');
        form.reset();
    });
});
