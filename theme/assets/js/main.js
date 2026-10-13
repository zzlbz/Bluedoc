/* BlueDoc 0.4.0 — 配色、导航状态、移动抽屉及阅读辅助。 */
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
        const outside = [...document.querySelectorAll('.workspace')];
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

    function initColorControl() {
        const select = document.querySelector('#bluedoc-color');
        if (!select || !document.documentElement.dataset.color) return;
        select.closest('.color-control').hidden = false;
        select.value = document.documentElement.dataset.colorPreference || 'system';
        select.addEventListener('change', () => window.dispatchEvent(new CustomEvent('bluedoc:color', { detail: select.value })));
    }

    function initTreeMemory() {
        const tree = document.querySelector('.document-tree');
        if (!tree) return;
        const key = 'bluedoc-tree:' + tree.dataset.siteRoot;
        let saved = {};
        try {
            const value = JSON.parse(localStorage.getItem(key) || '{}');
            if (value && typeof value === 'object' && !Array.isArray(value)) saved = value;
        } catch { /* 损坏或不可用的存储不阻止原生折叠。 */ }
        tree.querySelectorAll('details[data-category-mid]').forEach(node => {
            const id = node.dataset.categoryMid;
            if (node.classList.contains('is-active-branch')) node.open = true;
            else if (typeof saved[id] === 'boolean') node.open = saved[id];
            node.addEventListener('toggle', () => {
                saved[id] = node.open;
                try { localStorage.setItem(key, JSON.stringify(saved)); } catch { /* 保持当前页面可操作。 */ }
            });
        });
    }

    function initCopyButtons() {
        document.querySelectorAll('[data-document-content] pre').forEach(pre => {
            const code = pre.querySelector('code');
            if (!code) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'code-block';
            pre.before(wrapper);
            wrapper.append(pre);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'code-copy';
            button.textContent = '复制代码';
            button.setAttribute('aria-label', '复制此代码块');
            const status = document.createElement('span');
            status.className = 'sr-only';
            status.setAttribute('role', 'status');
            wrapper.append(button, status);
            button.addEventListener('click', async () => {
                button.disabled = true;
                try {
                    if (navigator.clipboard?.writeText) await navigator.clipboard.writeText(code.textContent);
                    else {
                        const input = document.createElement('textarea');
                        input.value = code.textContent;
                        input.className = 'copy-buffer';
                        document.body.append(input);
                        input.select();
                        const copied = document.execCommand('copy');
                        input.remove();
                        button.focus();
                        if (!copied) throw new Error('Copy unavailable');
                    }
                    button.textContent = '已复制';
                    status.textContent = '代码已复制';
                } catch {
                    button.textContent = '请手动复制';
                    status.textContent = '复制失败，请选中代码手动复制';
                } finally {
                    button.disabled = false;
                    setTimeout(() => { button.textContent = '复制代码'; }, 2200);
                }
            });
        });
    }

    function initCallouts() {
        const labels = { NOTE: '说明', TIP: '提示', IMPORTANT: '重要', WARNING: '注意', CAUTION: '警告' };
        document.querySelectorAll('[data-document-content] blockquote').forEach(block => {
            // HyperDown 可直接输出文本 + br，其他 Markdown 渲染器可能包一层 p。
            const paragraph = block.firstElementChild?.tagName === 'P' ? block.firstElementChild : block;
            const text = paragraph.firstChild;
            if (text?.nodeType !== Node.TEXT_NODE) return;
            const match = text.nodeValue.match(/^\s*\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*/);
            if (!match) return;
            text.nodeValue = text.nodeValue.slice(match[0].length);
            if (!text.nodeValue && text.nextSibling?.nodeName === 'BR') text.nextSibling.remove();
            block.classList.add('doc-callout', 'callout-' + match[1].toLowerCase());
            const title = document.createElement('p');
            title.className = 'callout-title';
            title.textContent = labels[match[1]];
            block.prepend(title);
        });
    }

    initColorControl();
    initTreeMemory();
    initDrawer();
    buildToc();
    initCopyButtons();
    initCallouts();
})();
