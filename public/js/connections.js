/* RailTicket – interactions specific to the connections page. */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.details-btn').forEach((button) => {
        const card = button.closest('.connection-card');

        if (!card) return;

        button.setAttribute('aria-expanded', card.classList.contains('open') ? 'true' : 'false');

        button.addEventListener('click', () => {
            const isOpen = card.classList.toggle('open');
            button.textContent = isOpen ? '▲ Ukryj szczegóły połączenia' : '▼ Pokaż szczegóły połączenia';
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    document.querySelectorAll('.seat:not(.occupied)').forEach((seat) => {
        seat.addEventListener('click', () => {
            const isSelected = seat.classList.toggle('selected');
            seat.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });
    });

    const swapButton = document.querySelector('.swap-button');

    if (!swapButton) return;

    swapButton.addEventListener('click', () => {
        const [origin, destination] = document.querySelectorAll('.route-input input');

        if (!origin || !destination) return;

        [origin.value, destination.value] = [destination.value, origin.value];
        origin.focus();
    });
});
