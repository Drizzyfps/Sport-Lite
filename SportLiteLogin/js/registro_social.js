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

        document.getElementById('boton-facebook-registro').addEventListener('click', function() {
            FB.login(function(response) {
                if (response.authResponse) {
                    FB.api('/me', { fields: 'id,name,email' }, function(user) {
                        enviarDatosBackendRegistro({
                            provider: 'facebook',
                            id: user.id,
                            name: user.name,
                            email: user.email
                        });
                    });
                } else {
                    console.log('Registro con Facebook cancelado o no autorizado.');
                }
            }, { scope: 'email' });
        });
    };

    // Inicialización de Google Sign-In
    google.accounts.id.initialize({
        client_id: '468632412888-f7igeh3ovnnfg5k7banibnpaahk547lm.apps.googleusercontent.com', 
        callback: handleGoogleSignInRegistro
    });

    google.accounts.id.renderButton(
        document.getElementById('boton-google-registro'),
        { theme: 'filled', size: 'large' }
    );

    function handleGoogleSignInRegistro(response) {
        const credential = response.credential;
        const decodedToken = JSON.parse(atob(credential.split('.')[1]));
        enviarDatosBackendRegistro({
            provider: 'google',
            id: decodedToken.sub,
            name: decodedToken.name,
            email: decodedToken.email
        });
    }

    function enviarDatosBackendRegistro(userData) {
        fetch('procesar_registro_social.php', { 
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
                alert('Error al registrarse: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error de red:', error);
        });
    }
});