document.addEventListener('DOMContentLoaded', () => {
    const clock = document.getElementById('deal-clock');
    const end = Number(clock?.dataset.end || 0);

    if (clock && end) {
        const tick = () => {
            let diff = Math.max(0, end - Date.now());
            const hours = Math.floor(diff / 3600000);
            diff %= 3600000;
            const minutes = Math.floor(diff / 60000);
            diff %= 60000;
            const seconds = Math.floor(diff / 1000);
            clock.textContent = [hours, minutes, seconds].map((n) => String(n).padStart(2, '0')).join(':');
        };

        tick();
        window.setInterval(tick, 1000);
    }

    document.addEventListener('click', (event) => {
        const thumb = event.target.closest('[data-thumb]');
        if (thumb) {
            const main = document.getElementById('gallery-main');
            if (main) {
                main.src = thumb.dataset.thumb;
            }
            document.querySelectorAll('[data-thumb]').forEach((button) => button.setAttribute('aria-pressed', 'false'));
            thumb.setAttribute('aria-pressed', 'true');
        }

        const choice = event.target.closest('.choice');
        if (choice) {
            choice.parentElement.querySelectorAll('.choice').forEach((button) => button.setAttribute('aria-pressed', 'false'));
            choice.setAttribute('aria-pressed', 'true');
        }
    });

    const syncPay = () => {
        const delivery = document.getElementById('pay-delivery')?.checked;
        const fields = document.getElementById('card-fields');
        if (!fields) {
            return;
        }
        fields.hidden = Boolean(delivery);
        fields.querySelectorAll('input').forEach((input) => {
            input.required = !delivery;
        });
    };

    document.querySelectorAll('input[name="pay"]').forEach((input) => {
        input.addEventListener('change', syncPay);
    });
    syncPay();

    document.querySelectorAll('.toast').forEach((el) => {
        if (window.bootstrap) {
            window.bootstrap.Toast.getOrCreateInstance(el, { delay: 2600 }).show();
        }
    });
});
