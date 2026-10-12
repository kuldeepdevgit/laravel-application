document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.site-nav');
    const onScroll = () => nav?.classList.toggle('scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    const monthBtn = document.getElementById('bill-month');
    const yearBtn = document.getElementById('bill-year');

    const setBilling = (yearly) => {
        monthBtn?.setAttribute('aria-pressed', yearly ? 'false' : 'true');
        yearBtn?.setAttribute('aria-pressed', yearly ? 'true' : 'false');
        document.querySelectorAll('[data-month]').forEach((node) => {
            node.textContent = yearly ? node.dataset.year : node.dataset.month;
        });
    };

    monthBtn?.addEventListener('click', () => setBilling(false));
    yearBtn?.addEventListener('click', () => setBilling(true));

    const form = document.getElementById('composer');
    const thread = document.getElementById('messages');
    const field = document.getElementById('prompt');
    if (!form || !thread || !field) {
        return;
    }

    const replyFor = (text) => {
        const q = text.toLowerCase();
        if (q.includes('summar') || q.includes('brief') || q.includes('notes')) {
            return {
                title: 'One-page brief',
                html: '<p>Here is a short brief from the notes you pasted. This reply is scripted for the template.</p><ul><li>The decision is still open: ship the smaller plan this month.</li><li>Two risks: a late vendor and a thin support rota on Fridays.</li><li>Next step: confirm the date, then send the one-page version.</li></ul>',
            };
        }
        if (q.includes('email') || q.includes('reply') || q.includes('write')) {
            return {
                title: 'Draft',
                html: '<p>Thanks for the note. I can do Thursday after 2, and I will send the outline the evening before. Tell me if another day is easier.</p><p>This draft stays in the browser. Nothing was sent.</p>',
            };
        }
        return {
            title: 'Desk note',
            html: `<p>I can turn “${text.replace(/[<>&]/g, '')}” into a checklist, a reply, or a one-page brief. Ask for one of those and this demo will answer from a fixed script.</p>`,
        };
    };

    const add = (role, html) => {
        const item = document.createElement('article');
        item.className = `msg ${role}`;
        item.innerHTML = html;
        thread.appendChild(item);
        item.scrollIntoView({ block: 'nearest' });
    };

    const ask = (text) => {
        const clean = text.trim();
        if (!clean) {
            return;
        }
        add('user', `<p>${clean.replace(/[<>&]/g, '')}</p>`);
        const answer = replyFor(clean);
        window.setTimeout(() => {
            add('model', `<div class="who">Keel · demo</div><h2 class="h4 serif">${answer.title}</h2>${answer.html}`);
        }, 450);
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        ask(field.value);
        field.value = '';
    });

    document.querySelectorAll('[data-starter]').forEach((button) => {
        button.addEventListener('click', () => ask(button.dataset.starter || ''));
    });

    const fromUrl = new URLSearchParams(window.location.search).get('q') || '';
    if (!field.dataset.prefill && fromUrl) {
        field.dataset.prefill = fromUrl;
    }

    if (field.dataset.prefill) {
        ask(field.dataset.prefill);
        field.dataset.prefill = '';
    }
});
