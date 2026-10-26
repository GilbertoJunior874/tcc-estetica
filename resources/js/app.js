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

const selection = document.querySelector('[data-service-selection]');

if (selection) {
    const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    const empty = document.querySelector('[data-selection-empty]');
    const summary = document.querySelector('[data-selection-summary]');
    const list = document.querySelector('[data-selection-list]');
    const total = document.querySelector('[data-selection-total]');

    const update = () => {
        const checked = [...selection.querySelectorAll('input[name="servicos[]"]:checked')];
        const cents = checked.reduce((sum, input) => sum + Number(input.dataset.price), 0);

        list.replaceChildren(
            ...checked.map((input) => {
                const item = document.createElement('li');
                item.className = 'flex justify-between gap-4';
                item.append(
                    Object.assign(document.createElement('span'), { textContent: input.dataset.name }),
                    Object.assign(document.createElement('span'), { textContent: currency.format(Number(input.dataset.price) / 100), className: 'shrink-0 text-zinc-600' }),
                );

                return item;
            }),
        );

        total.textContent = currency.format(cents / 100);
        empty.hidden = checked.length > 0;
        summary.hidden = checked.length === 0;
    };

    selection.addEventListener('change', update);
    update();
}
