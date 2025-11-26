function addPhone() {
    const container = document.getElementById('phoneContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="phonelist[]" class="form-control" required>
        <input type="text" name="internal_code[]" class="form-control" placeholder="Nro. Interno (opcional)">
        <button type="button" class="btn btn-danger" onclick="removeElement(this)">-</button>
    `;
    container.appendChild(div);
}

function addAddress() {
    const container = document.getElementById('addressContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="address[]" class="form-control" required>
        <button type="button" class="btn btn-danger" onclick="removeElement(this)">-</button>
    `;
    container.appendChild(div);
}

function removeElement(button) {
    button.parentElement.remove();
}

function openForm(data = null) {
    // Limpiar formulario
    document.getElementById('myForm').reset();
    //document.getElementById('country_id').value = '';
    document.getElementById('city_id').value = '';
    document.getElementById('formModalLabel').innerText = data ? 'Editar Registro' : 'Nuevo Registro';

    if (data) {
        //document.getElementById('country_id').value = data.country_id;
        document.getElementById('category').value = data.category_id;
        document.getElementById('city').value = data.city_id;
        document.getElementById('country').value = data.country_id;
        document.getElementById('company_name').value = data.company_name;
        document.getElementById('client_name').value = data.client_name;
        document.getElementById('client_post').value = data.client_post;
        document.getElementById('phone').value = data.phone;
        document.getElementById('email').value = data.email;
    }

    new bootstrap.Modal(document.getElementById('formModal')).show();
}

function loadCities(countryId) {
    const citySelect = document.getElementById("city");
    console.log(countryId);
    if (!countryId) {
        citySelect.innerHTML = "<option value=''>Seleccione un país primero</option>";
        return;
    }
    const url = `${CLIENT_URL}/getcityList/${countryId}`;
    console.log(url);
    fetch(url)
        .then(response => response.json())
        .then(data => {
            citySelect.innerHTML = ""; // limpiar opciones anteriores
            console.log(data);
            if (data.length > 0) {
                data.forEach(city => {
                    let option = document.createElement("option");
                    option.value = city.city_id;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
            } else {
                citySelect.innerHTML = "<option value=''>No hay ciudades disponibles</option>";
            }
        })
        .catch(error => console.error("Error cargando ciudades:", error));
}

document.getElementById('myForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('directory_id');
    const url = id ? `${CLIENT_URL}/clienteForm/update/${id}` : `${CLIENT_URL}/clienteForm/create`;

    // 🔥 obtener arrays completos
    const phones = formData.getAll("phonelist[]");
    const internals = formData.getAll("internal_code[]");

    console.log("Todos los teléfonos:", phones);
    console.log("Todos los internos:", internals);

    Swal.fire({
        title: "Estas seguro?",
        text: "Usted, registrara datos personales del "+formData.get("client_name")+" en plataforma",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, registrar!",
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(url, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(result => {
                console.log(result);
                if (result.status === 'success') {
                    Swal.fire({
                        title: "Exitoso!",
                        text: "Se registro el cliente de manera eficiente.",
                        icon: "success",
                    }).then(() => {
                        const endUrl = `${CLIENT_URL}`;
                        window.location.href = endUrl;
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: result.message,
                        icon: "warning",
                    })
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
});
