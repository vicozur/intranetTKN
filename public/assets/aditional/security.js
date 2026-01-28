document.getElementById('formCambiarPassword').addEventListener('submit', function(e) {
    // 1. Detenemos el envío automático siempre
    e.preventDefault();
    
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.querySelector("input[name='confirm_password']").value;
    const errorDiv = document.getElementById('passwordError');
    const submitButton = this.querySelector('button[type="submit"]');

    // 2. Definición de la Regla (Mayúscula, Minúscula, Número, Símbolo, Min 8)
    const fuerteRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&./#])[A-Za-z\d@$!%*?&./#]{8,}$/;

    // --- BLOQUE DE EXCEPCIONES (VALIDACIÓN LOCAL) ---
    
    let mensajeError = "";

    if (newPass !== confirmPass) {
        mensajeError = "Las contraseñas no coinciden.";
    } else if (!fuerteRegex.test(newPass)) {
        mensajeError = "La contraseña debe tener: Al menos 8 caracteres, una Mayúscula, una Minúscula, un Número y un Símbolo.";
    }

    // Si hay algún error, lo mostramos y DETENEMOS la ejecución aquí
    if (mensajeError !== "") {
        errorDiv.textContent = mensajeError;
        errorDiv.style.display = 'block';
        errorDiv.classList.add('alert', 'alert-danger'); // Si usas Bootstrap
        return; // <--- ESTO ES LO QUE FALTA: Detiene el script y NO llega al fetch
    }

    // --- SI LLEGA AQUÍ, LA CONTRASEÑA ES VÁLIDA ---
    errorDiv.style.display = 'none';
    submitButton.disabled = true;
    submitButton.textContent = 'Actualizando...';

    const formData = new FormData(this);
    
    fetch(`${AUTHPAS}`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en la red o servidor');
        return response.json();
    })
    .then(data => {
        if(data.status === 'success') {
            alert('Contraseña actualizada correctamente');
            location.reload();
        } else {
            alert('Error del servidor: ' + data.message);
            submitButton.disabled = false;
            submitButton.textContent = 'Cambiar Contraseña';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('No se pudo conectar con el servidor.');
        submitButton.disabled = false;
    });
});