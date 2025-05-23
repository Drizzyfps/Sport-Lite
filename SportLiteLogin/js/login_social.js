document.addEventListener('DOMContentLoaded', function() {
    // Inicialización del SDK de Facebook
    window.fbAsyncInit = function() {
        FB.init({
            appId      : '2064069124097642', 
            cookie     : true,
            xfbml      : true,
            version    : 'v13.0'
        });

        FB.AppEvents.logPageView();

        document.getElementById('boton-facebook-login').addEventListener('click', function() {
            FB.login(function(response) {
                if (response.authResponse) {
                    FB.api('/me', { fields: 'id,name,email' }, function(user) {
                        enviarDatosBackend({
                            provider: 'facebook',
                            id: user.id,
                            name: user.name,
                            email: user.email
                        });
                    });
                } else {
                    console.log('Inicio de sesión con Facebook cancelado o no autorizado.');
                }
            }, { scope: 'email' });
        });
    };

    // Inicialización de Google Sign-In
    google.accounts.id.initialize({
        client_id: '468632412888-f7igeh3ovnnfg5k7banibnpaahk547lm.apps.googleusercontent.com',
        callback: handleGoogleSignIn
    });

    google.accounts.id.renderButton(
        document.getElementById('boton-google-login'),
        { theme: 'filled', size: 'large' } 
    );

    function handleGoogleSignIn(response) {
        const credential = response.credential;
        const decodedToken = JSON.parse(atob(credential.split('.')[1]));
        enviarDatosBackend({
            provider: 'google',
            id: decodedToken.sub, 
            name: decodedToken.name,
            email: decodedToken.email
        });
    }

    function enviarDatosBackend(userData) {
        fetch('procesar_login_social.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            } else {
                alert('Error al iniciar sesión: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error de red:', error);
        });
    }
});
