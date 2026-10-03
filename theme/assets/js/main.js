/* BlueDoc 0.3.0 — 原生分类折叠、移动导航抽屉及正文 H2/H3 目录。 */
(() => {
    'use strict';

    function buildToc() {
        const content = document.querySelector('[data-document-content]');
        const toc = document.querySelector('[data-document-toc]');
        if (!content || !toc) return;
        const empty = document.querySelector('[data-toc-empty]');
        const headings = [...content.querySelectorAll('h2, h3')];
        if (!headings.length) {
            if (empty) empty.hidden = false;
            return;
        }

        const idCounts = new Map();
        document.querySelectorAll('[id]').forEach((element) => {
            idCounts.set(element.id, (idCounts.get(element.id) || 0) + 1);
        });
        const usedIds = new Set(idCounts.keys());
        const list = document.createElement('ol');
        list.className = 'toc-list';
        const links = [];
        headings.forEach((heading) => {
            if (!heading.id || idCounts.get(heading.id) > 1) {
                const slug = heading.textContent.trim().toLowerCase().replace(/[^\p{L}\p{N}\s_-]/gu, '').replace(/\s+/g, '-');
                const base = 'doc-' + (slug || 'section');
                let id = base;
                let suffix = 2;
                while (usedIds.has(id)) id = base + '-' + suffix++;
                heading.id = id;
                usedIds.add(id);
            }
            const item = document.createElement('li');
            item.className = heading.tagName === 'H3' ? 'toc-subheading' : 'toc-heading';
            const link = document.createElement('a');
            link.href = '#' + encodeURIComponent(heading.id);
            link.textContent = heading.textContent.trim() || toc.dataset.untitled;
            item.append(link);
            list.append(item);
            links.push(link);
        });
        toc.append(list);
        toc.hidden = false;
        if (empty) empty.hidden = true;

        let scheduled = false;
        function updateActive() {
            scheduled = false;
            let index = 0;
            headings.forEach((heading, position) => {
                if (heading.getBoundingClientRect().top <= 100) index = position;
            });
            // 页尾章节无法滚动到视口顶部时，仍应标记最后一节。
            if (window.scrollY > 0 && window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 4) {
                index = headings.length - 1;
            }
            links.forEach((link, position) => {
                if (position === index) link.setAttribute('aria-current', 'location');
                else link.removeAttribute('aria-current');
            });
        }
        function schedule() {
            if (!scheduled) {
                scheduled = true;
                requestAnimationFrame(updateActive);
            }
        }
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule);
        updateActive();
        if (location.hash) {
            try {
                const id = decodeURIComponent(location.hash.slice(1));
                const heading = headings.find((element) => element.id === id);
                if (heading) heading.scrollIntoView();
            } catch { /* 保持页面可读，即使 URL 片段编码不合法。 */ }
        }
    }

    function initDrawer() {
        const sidebar = document.querySelector('#bluedoc-sidebar');
        const toggle = document.querySelector('.drawer-toggle');
        const close = document.querySelector('.drawer-close');
        const backdrop = document.querySelector('.drawer-backdrop');
        if (!sidebar || !toggle || !close || !backdrop) return;
        const mobile = window.matchMedia('(max-width: 767px)');
        const outside = [...document.querySelectorAll('.site-header, #main, .site-footer')];
        let open = false;
        let previousFocus = null;
        const originalInert = new Map();
        toggle.hidden = false;
        close.hidden = false;
        document.documentElement.classList.add('has-drawer');

        function setOpen(value, restoreFocus = true) {
            open = value && mobile.matches;
            toggle.setAttribute('aria-expanded', String(open));
            document.body.classList.toggle('is-drawer-open', open);
            backdrop.hidden = !open;
            if (open) {
                previousFocus = document.activeElement;
                sidebar.inert = false;
                sidebar.removeAttribute('aria-hidden');
                sidebar.setAttribute('role', 'dialog');
                sidebar.setAttribute('aria-modal', 'true');
                outside.forEach((element) => {
                    originalInert.set(element, element.inert);
                    element.inert = true;
                });
                close.focus();
            } else {
                outside.forEach((element) => {
                    if (originalInert.has(element)) element.inert = originalInert.get(element);
                });
                originalInert.clear();
                sidebar.removeAttribute('role');
                sidebar.removeAttribute('aria-modal');
                if (restoreFocus && previousFocus && mobile.matches) previousFocus.focus();
                sidebar.inert = mobile.matches;
                if (mobile.matches) sidebar.setAttribute('aria-hidden', 'true');
                else sidebar.removeAttribute('aria-hidden');
                previousFocus = null;
            }
        }
        toggle.addEventListener('click', () => setOpen(true));
        close.addEventListener('click', () => setOpen(false));
        backdrop.addEventListener('click', () => setOpen(false));
        sidebar.addEventListener('click', (event) => {
            if (event.target.closest('a') && mobile.matches) setOpen(false);
        });
        document.addEventListener('keydown', (event) => {
            if (!open) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                setOpen(false);
            } else if (event.key === 'Tab') {
                const focusable = [...sidebar.querySelectorAll('a[href], button:not([disabled]), summary, [tabindex="0"]')]
                    .filter((element) => {
                        if (!element.getClientRects().length) return false;
                        // 折叠 details 的后代可能仍有布局矩形，但不能接收焦点。
                        for (let parent = element.parentElement; parent && parent !== sidebar; parent = parent.parentElement) {
                            if (parent.tagName === 'DETAILS' && !parent.open && element !== parent.querySelector('summary')) return false;
                        }
                        return true;
                    });
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
        mobile.addEventListener('change', () => setOpen(false, false));
        setOpen(false, false);
    }

    initDrawer();
    buildToc();
})();
