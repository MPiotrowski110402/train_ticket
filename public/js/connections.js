/* RailTicket – interactions specific to the connections page. */

document.addEventListener('DOMContentLoaded', () => {
    /*
    |--------------------------------------------------------------------------
    | DETAILS ON CONNECTION CARD
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.details-btn').forEach((button) => {
        const card = button.closest('.connection-card');

        if (!card) return;

        button.setAttribute(
            'aria-expanded',
            card.classList.contains('open') ? 'true' : 'false'
        );

        button.addEventListener('click', () => {
            const isOpen = card.classList.toggle('open');

            button.textContent = isOpen
                ? '▲ Ukryj szczegóły połączenia'
                : '▼ Pokaż szczegóły połączenia';

            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | SEAT SELECTION
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.seat:not(.occupied)').forEach((seat) => {
        seat.addEventListener('click', () => {
            const isSelected = seat.classList.toggle('selected');

            seat.setAttribute(
                'aria-pressed',
                isSelected ? 'true' : 'false'
            );
        });
    });

    /*
    |--------------------------------------------------------------------------
    | CUSTOM CITY PICKERS
    |--------------------------------------------------------------------------
    */

    const cityPickers = document.querySelectorAll('.city-picker');

    const closeAllCityPickers = (exceptPicker = null) => {
        cityPickers.forEach((picker) => {
            if (picker === exceptPicker) return;

            picker.classList.remove('is-open');

            const trigger = picker.querySelector('.city-picker-trigger');

            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    };

    const updatePickerValue = (picker, value, label) => {
        const hiddenInput = picker.querySelector('input[type="hidden"]');
        const valueLabel = picker.querySelector('.city-picker-value');
        const options = picker.querySelectorAll('.city-picker-option');

        if (!hiddenInput || !valueLabel) return;

        hiddenInput.value = value;
        valueLabel.textContent = label;

        options.forEach((option) => {
            const isSelected = option.dataset.value === String(value);

            option.setAttribute(
                'aria-selected',
                isSelected ? 'true' : 'false'
            );

            option.classList.toggle('is-selected', isSelected);
        });
    };

    cityPickers.forEach((picker) => {
        const trigger = picker.querySelector('.city-picker-trigger');
        const options = picker.querySelectorAll('.city-picker-option');

        if (!trigger) return;

        trigger.addEventListener('click', () => {
            const isOpen = picker.classList.contains('is-open');

            closeAllCityPickers(picker);

            picker.classList.toggle('is-open', !isOpen);
            trigger.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
        });

        options.forEach((option) => {
            option.addEventListener('click', () => {
                updatePickerValue(
                    picker,
                    option.dataset.value,
                    option.dataset.label
                );

                picker.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
                trigger.focus();
            });
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();

                closeAllCityPickers(picker);

                picker.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');

                const selectedOption = picker.querySelector(
                    '.city-picker-option[aria-selected="true"]'
                );

                const firstOption = picker.querySelector('.city-picker-option');

                (selectedOption || firstOption)?.focus();
            }
        });
    });

    document.addEventListener('click', (event) => {
        const clickedInsidePicker = event.target.closest('.city-picker');

        if (!clickedInsidePicker) {
            closeAllCityPickers();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllCityPickers();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | SWAP CITIES
    |--------------------------------------------------------------------------
    */

    const swapButton = document.querySelector('#swapCities');
    const fromCityInput = document.querySelector('#fromCity');
    const toCityInput = document.querySelector('#toCity');

    if (swapButton && fromCityInput && toCityInput) {
        swapButton.addEventListener('click', () => {
            const fromPicker = fromCityInput.closest('.city-picker');
            const toPicker = toCityInput.closest('.city-picker');

            if (!fromPicker || !toPicker) return;

            const fromOption = fromPicker.querySelector(
                `.city-picker-option[data-value="${fromCityInput.value}"]`
            );

            const toOption = toPicker.querySelector(
                `.city-picker-option[data-value="${toCityInput.value}"]`
            );

            const fromValue = fromCityInput.value;
            const toValue = toCityInput.value;

            const fromLabel = fromOption?.dataset.label || 'Wybierz miasto początkowe';
            const toLabel = toOption?.dataset.label || 'Wybierz miasto docelowe';

            updatePickerValue(fromPicker, toValue, toLabel);
            updatePickerValue(toPicker, fromValue, fromLabel);

            closeAllCityPickers();
        });
    }
});