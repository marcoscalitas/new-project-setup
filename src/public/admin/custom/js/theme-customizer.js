/**
 * Restores customizer active states and persists every interaction to localStorage.
 * Deferred to DOMContentLoaded because the customizer offcanvas HTML is injected
 * after this script in the DOM — querySelectorAll would return nothing otherwise.
 */
document.addEventListener('DOMContentLoaded', function () {
    var s = localStorage;

    preset_change(s.getItem('pc_preset'));
    layout_theme_contrast_change(s.getItem('pc_contrast'));
    layout_caption_change(s.getItem('pc_caption'));
    layout_rtl_change(s.getItem('pc_rtl'));
    main_layout_change(s.getItem('pc_mainLayout'));
    change_box_container(s.getItem('pc_container'));

    document.querySelectorAll('.preset-color > a').forEach(function (el) {
        el.addEventListener('click', function (e) {
            var t = e.target;
            var val = ('I' === t.tagName ? t.parentNode : t).getAttribute('data-value');
            if (val) s.setItem('pc_preset', val);
        });
    });
    document.querySelectorAll('.theme-contrast .btn').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_contrast', this.getAttribute('data-value'));
        });
    });
    document.querySelectorAll('.theme-main-layout > a').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_mainLayout', this.getAttribute('data-value'));
        });
    });
    document.querySelectorAll('.theme-nav-caption .btn').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_caption', this.getAttribute('data-value'));
        });
    });
    document.querySelectorAll('.theme-direction .btn').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_rtl', this.getAttribute('data-value'));
        });
    });
    document.querySelectorAll('.theme-container .btn').forEach(function (el) {
        el.addEventListener('click', function () {
            s.setItem('pc_container', this.getAttribute('data-value'));
        });
    });
});
