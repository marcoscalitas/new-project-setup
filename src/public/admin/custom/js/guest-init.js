/**
 * Restores theme settings and wires customizer persistence on guest pages (login, register, etc.).
 * Runs immediately (IIFE) since all vendor scripts are already loaded before this file.
 */
(function () {
    var s = localStorage;

    layout_theme_contrast_change(s.getItem('pc_contrast') || 'false');
    change_box_container(s.getItem('pc_container') || 'false');
    layout_caption_change(s.getItem('pc_caption') || 'true');
    layout_rtl_change(s.getItem('pc_rtl') || 'false');
    preset_change(s.getItem('pc_preset') || 'preset-1');
    main_layout_change(s.getItem('pc_mainLayout') || 'vertical');

    document.querySelectorAll('.preset-color > a').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var t = e.target;
            var val = ('I' === t.tagName ? t.parentNode : t).getAttribute('data-value');
            if (val) s.setItem('pc_preset', val);
        });
    });
    document.querySelectorAll('.theme-contrast .btn').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_contrast', this.getAttribute('data-value') || 'false');
        });
    });
    document.querySelectorAll('.theme-main-layout > a').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_mainLayout', this.getAttribute('data-value') || 'vertical');
        });
    });
    document.querySelectorAll('.theme-nav-caption .btn').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_caption', this.getAttribute('data-value') || 'true');
        });
    });
    document.querySelectorAll('.theme-direction .btn').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_rtl', this.getAttribute('data-value') || 'false');
        });
    });
    document.querySelectorAll('.theme-container .btn').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_container', this.getAttribute('data-value') || 'false');
        });
    });
})();
