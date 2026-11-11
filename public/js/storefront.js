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

    document.querySelectorAll('.toast').forEach((el) => {
        if (window.bootstrap) {
            window.bootstrap.Toast.getOrCreateInstance(el, { delay: 2600 }).show();
        }
    });

    document.querySelectorAll('[data-dept-slider]').forEach((slider) => {
        const track = slider.querySelector('.dept-slider-track');
        const prev = slider.querySelector('.dept-slider-prev');
        const next = slider.querySelector('.dept-slider-next');

        if (!track || !prev || !next) {
            return;
        }

        const originals = [...track.children];
        const preferred = Number(slider.dataset.perView) || 8;
        const loops = slider.dataset.loop !== 'false';
        const clones = slider.dataset.clone !== 'false';
        let index = 0;
        let page = 0;
        let visible = preferred;
        let pending = 0;
        let canSlide = false;
        let usesPages = false;
        let startX = 0;
        let deltaX = 0;
        let dragging = false;
        let suppressClick = false;
        let animating = false;
        let settleTimer = 0;

        const visibleCount = () => {
            const fitted = Math.max(1, Math.floor(slider.clientWidth / 104));

            return Math.min(preferred, fitted);
        };

        const mod = (value, length) => ((value % length) + length) % length;

        const cloneTile = (node) => {
            const copy = node.cloneNode(true);
            copy.dataset.clone = 'true';
            copy.hidden = false;
            copy.setAttribute('aria-hidden', 'true');
            copy.tabIndex = -1;

            return copy;
        };

        const clearClones = () => {
            track.querySelectorAll('[data-clone]').forEach((node) => node.remove());
        };

        const applyTransform = () => {
            const shiftTiles = !canSlide ? 0 : (usesPages ? visible * (1 + pending) : visible + pending);
            track.style.transform = `translateX(calc(${shiftTiles} * -100% / var(--dept-per-view, 8)))`;
        };

        const jump = () => {
            track.style.transition = 'none';
            applyTransform();
            track.getBoundingClientRect();
            track.style.transition = '';
        };

        const appendSlot = (fragment, originalIndex, allowOriginal) => {
            const source = originals[originalIndex];

            if (allowOriginal && source && !source.dataset.placed) {
                source.dataset.placed = '1';
                source.hidden = false;
                fragment.append(source);

                return;
            }

            fragment.append(cloneTile(source));
        };

        const pageSlots = (start) => {
            const slots = [];

            for (let slot = 0; slot < visible; slot += 1) {
                const realIndex = start + slot;
                slots.push(realIndex < originals.length ? realIndex : mod(realIndex - originals.length, originals.length));
            }

            return slots;
        };

        const buildStrip = () => {
            clearClones();
            originals.forEach((node) => {
                delete node.dataset.placed;
            });

            const fragment = document.createDocumentFragment();
            const pages = Math.max(1, Math.ceil(originals.length / visible));

            if (!canSlide) {
                pageSlots(0).forEach((originalIndex) => appendSlot(fragment, originalIndex, true));
            } else if (usesPages) {
                [mod(page - 1, pages), page, mod(page + 1, pages)].forEach((pageNumber, position) => {
                    pageSlots(pageNumber * visible).forEach((originalIndex) => {
                        appendSlot(fragment, originalIndex, position === 1);
                    });
                });
            } else {
                for (let slot = -visible; slot < visible * 2; slot += 1) {
                    appendSlot(fragment, mod(index + slot, originals.length), slot >= 0 && slot < visible);
                }
            }

            originals.forEach((node) => {
                if (node.dataset.placed) {
                    return;
                }

                node.hidden = true;
                fragment.append(node);
            });

            track.replaceChildren(fragment);
        };

        const render = () => {
            window.clearTimeout(settleTimer);
            animating = false;
            pending = 0;
            visible = visibleCount();
            slider.style.setProperty('--dept-per-view', String(visible));
            canSlide = clones && loops && originals.length > 1;
            usesPages = originals.length > visible;
            const pages = Math.max(1, Math.ceil(originals.length / visible));
            page = mod(page, pages);
            index = originals.length ? mod(index, originals.length) : 0;
            buildStrip();
            jump();
            prev.disabled = !canSlide;
            next.disabled = !canSlide;
        };

        const go = (direction) => {
            if (!canSlide || animating) {
                return;
            }

            animating = true;
            pending = direction;
            applyTransform();
            settleTimer = window.setTimeout(() => {
                if (usesPages) {
                    const pages = Math.max(1, Math.ceil(originals.length / visible));
                    page = mod(page + direction, pages);
                } else {
                    index = mod(index + direction, originals.length);
                }

                pending = 0;
                buildStrip();
                jump();
                animating = false;
            }, 400);
        };

        prev.addEventListener('click', () => {
            go(-1);
        });

        next.addEventListener('click', () => {
            go(1);
        });

        track.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'mouse') {
                return;
            }

            dragging = true;
            startX = event.clientX;
            deltaX = 0;
            track.setPointerCapture(event.pointerId);
        });

        track.addEventListener('pointermove', (event) => {
            if (!dragging) {
                return;
            }

            deltaX = event.clientX - startX;
        });

        const finishDrag = () => {
            if (!dragging) {
                return;
            }

            dragging = false;

            if (Math.abs(deltaX) < 40) {
                return;
            }

            suppressClick = true;
            go(deltaX < 0 ? 1 : -1);
        };

        track.addEventListener('pointerup', finishDrag);
        track.addEventListener('pointercancel', finishDrag);
        track.addEventListener('click', (event) => {
            if (!suppressClick) {
                return;
            }

            suppressClick = false;
            event.preventDefault();
        }, true);

        window.addEventListener('resize', render);
        render();
    });
});
