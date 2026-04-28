function togglePassword(inputId) {
    var input = document.getElementById(inputId);
    var eye = document.getElementById(inputId + '-eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.replace('ti-eye', 'ti-eye-off');
    } else {
        input.type = 'password';
        eye.classList.replace('ti-eye-off', 'ti-eye');
    }
}
