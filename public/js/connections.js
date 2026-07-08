/* RailTicket – interactions specific to the connections page. */

document.addEventListener('DOMContentLoaded', () => {
    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    const placeWord = (count) => {
        if (count === 1) return 'miejsce';
        if (count >= 2 && count <= 4) return 'miejsca';

        return 'miejsc';
    };

    const cityPlaceholder = {
        from: 'Wybierz miasto początkowe',
        to: 'Wybierz miasto docelowe',
    };

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
    | BUY / SELECT CLASS BUTTONS
    |--------------------------------------------------------------------------
    */

    const activateWagon = (seatMap, wagonClass = null) => {
        const wagonTabs = seatMap.querySelectorAll('.wagon-tab');
        const wagonPanels = seatMap.querySelectorAll('[data-wagon-panel]');

        if (!wagonTabs.length || !wagonPanels.length) return;

        const targetTab =
            Array.from(wagonTabs).find((tab) => tab.dataset.wagonClass === wagonClass)
            || wagonTabs[0];

        const targetId = targetTab.dataset.wagonTarget;

        wagonTabs.forEach((tab) => {
            const isActive = tab === targetTab;

            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        wagonPanels.forEach((panel) => {
            panel.classList.toggle('active', panel.id === targetId);
        });
    };

    document.querySelectorAll('.select-class-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const card = button.closest('.connection-card');

            if (!card || button.disabled) return;

            const detailsButton = card.querySelector('.details-btn');
            const seatMap = card.querySelector('.train-seat-map');
            const travelClass = button.dataset.travelClass;

            card.classList.add('open');

            if (detailsButton) {
                detailsButton.textContent = '▲ Ukryj szczegóły połączenia';
                detailsButton.setAttribute('aria-expanded', 'true');
            }

            card.querySelectorAll('.select-class-btn').forEach((item) => {
                item.classList.toggle('active', item === button);
            });

            if (seatMap) {
                activateWagon(seatMap, travelClass);

                window.setTimeout(() => {
                    seatMap.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                }, 120);
            }
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

        const pickerType = picker.dataset.cityPicker;
        const fallbackLabel = cityPlaceholder[pickerType] || 'Wybierz miasto';

        hiddenInput.value = value || '';
        valueLabel.textContent = label || fallbackLabel;

        options.forEach((option) => {
            const isSelected = value !== '' && option.dataset.value === String(value);

            option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
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

            option.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    option.click();
                }
            });
        });

        trigger.addEventListener('keydown', (event) => {
            if (
                event.key === 'ArrowDown' ||
                event.key === 'Enter' ||
                event.key === ' '
            ) {
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
        if (!event.target.closest('.city-picker')) {
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

            const fromValue = fromCityInput.value;
            const toValue = toCityInput.value;

            const fromOption = fromPicker.querySelector(
                `.city-picker-option[data-value="${fromValue}"]`
            );

            const toOption = toPicker.querySelector(
                `.city-picker-option[data-value="${toValue}"]`
            );

            updatePickerValue(
                fromPicker,
                toValue,
                toOption?.dataset.label || cityPlaceholder.from
            );

            updatePickerValue(
                toPicker,
                fromValue,
                fromOption?.dataset.label || cityPlaceholder.to
            );

            closeAllCityPickers();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | WAGON TABS
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.train-seat-map').forEach((seatMap) => {
        const wagonTabs = seatMap.querySelectorAll('.wagon-tab');
        const wagonPanels = seatMap.querySelectorAll('[data-wagon-panel]');

        wagonTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const targetId = tab.dataset.wagonTarget;

                wagonTabs.forEach((item) => {
                    const isActive = item === tab;

                    item.classList.toggle('active', isActive);
                    item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                wagonPanels.forEach((panel) => {
                    panel.classList.toggle('active', panel.id === targetId);
                });

                const activePanel = seatMap.querySelector(`#${targetId}`);

                if (activePanel) {
                    activePanel.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest',
                    });
                }
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | SEAT SELECTION LIMIT BY PASSENGERS
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.train-seat-map').forEach((seatMap) => {
        const requiredSeats = Number.parseInt(
            seatMap.dataset.requiredSeats || '1',
            10
        );

        const selectedCounter = seatMap.querySelector('.selected-seats-count');
        const continueButton = seatMap.querySelector('.continue-with-seats');

        const availableSeats = seatMap.querySelectorAll(
            '.seat:not(:disabled):not(.seat-sold):not(.seat-locked)'
        );

        const getSelectedSeats = () => {
            return Array.from(seatMap.querySelectorAll('.seat.selected'));
        };

        const updateSeatState = () => {
            const selectedSeats = getSelectedSeats();
            const selectedCount = selectedSeats.length;
            const missingCount = Math.max(requiredSeats - selectedCount, 0);

            if (selectedCounter) {
                selectedCounter.textContent = String(selectedCount);
            }

            availableSeats.forEach((seat) => {
                const shouldBlock =
                    selectedCount >= requiredSeats &&
                    !seat.classList.contains('selected');

                seat.classList.toggle('selection-limit-reached', shouldBlock);
            });

            if (!continueButton) return;

            if (selectedCount === requiredSeats) {
                continueButton.disabled = false;
                continueButton.textContent = `Kontynuuj z ${selectedCount} ${placeWord(selectedCount)}`;
                return;
            }

            continueButton.disabled = true;
            continueButton.textContent = `Wybierz jeszcze ${missingCount} ${placeWord(missingCount)}`;
        };

        availableSeats.forEach((seat) => {
            seat.addEventListener('click', () => {
                const isAlreadySelected = seat.classList.contains('selected');
                const selectedCount = getSelectedSeats().length;

                if (!isAlreadySelected && selectedCount >= requiredSeats) {
                    seatMap.classList.remove('seat-limit-warning');

                    window.requestAnimationFrame(() => {
                        seatMap.classList.add('seat-limit-warning');
                    });

                    return;
                }

                seat.classList.toggle('selected', !isAlreadySelected);
                seat.setAttribute(
                    'aria-pressed',
                    !isAlreadySelected ? 'true' : 'false'
                );

                updateSeatState();
            });
        });

        continueButton?.addEventListener('click', async () => {
            const selectedSeats = getSelectedSeats();

            if (selectedSeats.length !== requiredSeats) return;

            const url = seatMap.dataset.seatLockUrl;
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

            if (!url || !csrfToken) {
                alert('Brakuje konfiguracji rezerwacji miejsc.');
                return;
            }

            const originalText = continueButton.textContent;

            continueButton.disabled = true;
            continueButton.textContent = 'Rezerwuję miejsca...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        passengers: requiredSeats,
                        seat_ids: selectedSeats.map((seat) => Number(seat.dataset.seatId)),
                    }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(data.message || 'Nie udało się zarezerwować miejsc.');
                }

                const summary = data.selected_seats
                    .map((seat) => `Wagon ${seat.wagon_number}, miejsce ${seat.seat_number}`)
                    .join(' · ');

                continueButton.textContent = 'Przechodzę do danych pasażera...';

                seatMap.classList.add('seats-locked-success');

                window.location.href = data.checkout_url;
            } catch (error) {
                continueButton.disabled = false;
                continueButton.textContent = originalText;

                alert(error.message);
            }
        });

        updateSeatState();
    });
});