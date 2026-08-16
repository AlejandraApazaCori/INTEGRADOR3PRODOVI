<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const title = document.getElementById('form-title');
        const subtitle = document.getElementById('form-subtitle');
        let currentForm = 'login';

        const switchForm = target => {
            if (target === currentForm) return;
            const current = currentForm === 'login' ? loginForm : registerForm;
            const next = target === 'login' ? loginForm : registerForm;
            current.classList.add('slide-left');
            setTimeout(() => {
                current.classList.add('hidden');
                current.classList.remove('slide-left');
                next.classList.remove('hidden');
                title.textContent = target === 'login' ? 'Iniciar Sesión' : 'Crear Cuenta';
                subtitle.textContent = target === 'login' ? 'Accede a tu cuenta para continuar' : 'Regístrate para comenzar tu experiencia';
                currentForm = target;
            }, 220);
        };

        document.getElementById('show-register')?.addEventListener('click', event => { event.preventDefault(); switchForm('register'); });
        document.getElementById('header-register-link')?.addEventListener('click', event => {
            event.preventDefault();
            window.history.replaceState(null, '', '#register');
            switchForm('register');
        });
        document.getElementById('show-login')?.addEventListener('click', event => { event.preventDefault(); switchForm('login'); });

        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const input = document.querySelector(toggle.getAttribute('toggle'));
                if (!input) return;
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                toggle.classList.toggle('fa-eye', visible);
                toggle.classList.toggle('fa-eye-slash', !visible);
            });
        });

        const phone = document.getElementById('register-phone');
        phone?.addEventListener('input', () => {
            let digits = phone.value.replace(/\D/g, '');
            if (digits.startsWith('591')) digits = digits.slice(3);
            phone.value = digits ? `+591 ${digits.slice(0, 8)}` : '';
        });

        if (window.location.hash === '#register' || document.querySelector('#register-form .invalid-feedback')) switchForm('register');
    });
</script>
