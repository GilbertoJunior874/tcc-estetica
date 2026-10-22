document.querySelectorAll('[data-geolocate]').forEach((button) => {
    if (!('geolocation' in navigator)) {
        return;
    }

    const form = button.closest('form');
    const feedback = button.parentElement.querySelector('[data-geolocate-feedback]');

    const field = (selector, name) => {
        let input = form.querySelector(selector);

        if (!input) {
            input = Object.assign(document.createElement('input'), { type: 'hidden', name });
            form.append(input);
        }

        return input;
    };

    button.hidden = false;

    button.addEventListener('click', () => {
        button.disabled = true;
        feedback.textContent = 'Obtendo sua localização…';

        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                field(button.dataset.latitude, 'lat').value = coords.latitude.toFixed(6);
                field(button.dataset.longitude, 'lng').value = coords.longitude.toFixed(6);
                feedback.textContent = 'Localização obtida.';
                button.disabled = false;

                if ('submit' in button.dataset) {
                    form.requestSubmit();
                }
            },
            () => {
                feedback.textContent = 'Não foi possível obter sua localização. A busca e os filtros continuam funcionando normalmente.';
                button.disabled = false;
            },
            { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 },
        );
    });
});
