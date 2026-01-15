document.getElementById('formCambiarPassword').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.querySelector('input[name="confirm_password"]').value;
    const errorDiv = document.getElementById('passwordError');

    if (newPass !== confirmPass) {
        errorDiv.style.display = 'block';
        return;
    }

    // Enviar por AJAX
    const formData = new FormData(this);
    console.log(`${AUTHPAS}`);
    fetch(`${AUTHPAS}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            alert('Contraseña actualizada correctamente');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
});