/* 尽早应用配色，存储不可用时仍跟随系统。 */
(() => {
    'use strict';
    const root = document.documentElement;
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    let preference = 'system';
    try {
        const stored = localStorage.getItem('bluedoc-color');
        if (['system', 'light', 'dark'].includes(stored)) preference = stored;
    } catch { /* 隐私模式或禁用存储不会影响阅读。 */ }
    const apply = () => {
        root.dataset.colorPreference = preference;
        root.dataset.color = preference === 'system' ? (media.matches ? 'dark' : 'light') : preference;
    };
    media.addEventListener('change', apply);
    window.addEventListener('bluedoc:color', event => {
        if (!['system', 'light', 'dark'].includes(event.detail)) return;
        preference = event.detail;
        try { localStorage.setItem('bluedoc-color', preference); } catch { /* 可继续在当前页面切换。 */ }
        apply();
    });
    window.addEventListener('storage', event => {
        if (event.key !== 'bluedoc-color') return;
        preference = ['system', 'light', 'dark'].includes(event.newValue) ? event.newValue : 'system';
        apply();
        const select = document.querySelector('#bluedoc-color');
        if (select) select.value = preference;
    });
    apply();
})();
