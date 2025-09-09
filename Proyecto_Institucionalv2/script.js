 document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');

            const usuariosValidos = {
                'admin': '123',
                'ana': '456',
                'jorge': '789'
            };

            if (usuariosValidos[username] === password) {
                localStorage.setItem('currentUser', username);
                messageDiv.textContent = '¡Inicio de sesión exitoso! Redirigiendo...';
                messageDiv.className = 'message success';
                
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);

            } else {
                messageDiv.textContent = 'Nombre de usuario o contraseña incorrectos.';
                messageDiv.className = 'message error';
            }
        });