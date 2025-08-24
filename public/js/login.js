document.addEventListener('DOMContentLoaded', function() {
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const icon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', function() {
        // Toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Toggle the eye icon
        if (type === 'password') {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            togglePassword.setAttribute('aria-label', 'Show password');
        } else {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            togglePassword.setAttribute('aria-label', 'Hide password');
        }
    });
});
