/**
 * Runs synchronously in <head> before CSS renders to prevent theme flash (FOUC).
 * Must be loaded as a blocking script (no defer/async).
 */
(function () {
    try {
        var s = localStorage,
            h = document.documentElement;

        if (!s.getItem('theme')) s.setItem('theme', 'light');
        if (!s.getItem('pc_preset')) s.setItem('pc_preset', 'preset-1');
        if (!s.getItem('pc_contrast')) s.setItem('pc_contrast', 'false');
        if (!s.getItem('pc_caption')) s.setItem('pc_caption', 'true');
        if (!s.getItem('pc_mainLayout')) s.setItem('pc_mainLayout', 'vertical');
        if (!s.getItem('pc_rtl')) s.setItem('pc_rtl', 'false');
        if (!s.getItem('pc_container')) s.setItem('pc_container', 'false');

        h.className = s.getItem('pc_preset');
        h.setAttribute('data-pc-theme', s.getItem('theme'));
        h.setAttribute('data-pc-theme_contrast', s.getItem('pc_contrast') === 'true' ? 'true' : '');
        h.setAttribute('data-pc-sidebar-caption', s.getItem('pc_caption'));
        h.setAttribute('data-pc-layout', s.getItem('pc_mainLayout'));
        var rtl = s.getItem('pc_rtl') === 'true';
        h.setAttribute('data-pc-direction', rtl ? 'rtl' : 'ltr');
        h.setAttribute('dir', rtl ? 'rtl' : 'ltr');
    } catch (e) {}
})();
