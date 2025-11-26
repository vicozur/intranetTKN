function openFileUploadModal() {
        $("#uploadForm")[0].reset();
        const fileList = document.getElementById("fileList");
        fileList.innerHTML = ""; // Limpia la lista previa
        $("#uploadModalTitle").text("Subir Archivos para importar tarjetas");
        $("#fileModal").modal("show");
    }

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('archivo_datos');
    const modal = document.getElementById('fileModal');
    // Asumiendo que usas Bootstrap, puedes obtener la instancia del modal:
    const bsModal = new bootstrap.Modal(modal); 
    
    // Obtener el token CSRF de CodeIgniter 4
    // CI4 usa un campo oculto o meta tag para esto.
    // Buscaremos el valor del token desde un campo oculto si no lo incluiste en el form:
    const csrfTokenName = document.querySelector('input[name="csrf_token_name"]'); // Ejemplo, ajusta el nombre
    const csrfTokenValue = document.querySelector('input[name="csrf_token_value"]'); // Ejemplo, ajusta el nombre

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Detiene el envío estándar del formulario
        
        // --- 1. Preparar la Petición ---
        const formData = new FormData(form);
        
        // Si el token CSRF no está automáticamente en FormData (porque no está en el HTML del modal),
        // añádelo manualmente si es necesario (asumiendo que tienes los inputs ocultos en tu vista principal):
        // if (csrfTokenName && csrfTokenValue) {
        //     formData.append(csrfTokenName.value, csrfTokenValue.value);
        // }
        
        // Opcional: Deshabilitar botón para evitar doble clic y mostrar feedback
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Procesando...';
        url = `${DIRECTORY_URL}/importar`
        // --- 2. Enviar la Petición AJAX (Fetch) ---
        fetch(url, {
            method: 'POST',
            body: formData, // FormData maneja el encabezado 'Content-Type' automáticamente
            // Si necesitas pasar encabezados adicionales (como el CSRF en algunos casos)
            // headers: {
            //     'X-Requested-With': 'XMLHttpRequest'
            // }
        })
        .then(response => {
            if (!response.ok) {
                // Si el servidor devuelve un error de HTTP (4xx, 5xx), aún si contiene el Excel
                // Puede ser que haya un error de validación de CI4.
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // 3. Manejo de la Respuesta
            
            // Si el controlador genera y devuelve un archivo Excel (como lo configuramos), 
            // la respuesta será de tipo blob (archivo) y necesitamos forzar la descarga.
            
            // Intentamos obtener el nombre del archivo del encabezado (si el servidor lo envía)
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = 'Resultado_Descarga.xlsx'; 
            if (contentDisposition && contentDisposition.indexOf('filename=') !== -1) {
                filename = contentDisposition.split('filename=')[1].replace(/"/g, '');
            }

            return response.blob().then(blob => ({ blob, filename }));
        })
        .then(({ blob, filename }) => {
            // Crear una URL temporal para el blob
            const url = window.URL.createObjectURL(blob);
            
            // Crear un enlace temporal para la descarga
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click(); // Simular clic para iniciar la descarga
            a.remove(); // Limpiar el enlace
            
            window.URL.revokeObjectURL(url); // Liberar la URL

            // 4. Cerrar Modal y Restaurar Botón
            bsModal.hide(); 
            alert('¡Procesamiento completado! El archivo se ha descargado.');

        })
        .catch(error => {
            // Manejo de errores de red o errores lanzados desde el servidor
            console.error('Error durante la importación:', error);
            alert('Ocurrió un error al procesar el archivo. Revisa la consola para más detalles.');
        })
        .finally(() => {
            // Restaurar el botón independientemente del resultado
            submitButton.disabled = false;
            submitButton.textContent = 'Guardar';
            // Opcional: Resetear el formulario (limpiar el input file)
            form.reset(); 
        });
    });
});

function openForm(data = null) {
    // DIRECTORY_URL
    var url;
    if (data) {
        console.log(data);
        url = `${DIRECTORY_URL}/clienteForm/${data}`;
    } else {
        url = `${DIRECTORY_URL}/clienteForm/`
    }
    //const url = data.directory_id ? `${DIRECTORY_URL}/clienteForm/${data.directory_id}` : `${DIRECTORY_URL}/clienteForm/`;
    console.log(url);
    window.location.href = url;
}

let directoryTable;

$(document).ready(function() {
    // 🔑 Solución CSRF: Añadir el token a cada petición AJAX de DataTables
    const csrfData = {};
    csrfData[CI_CSRF_NAME] = CI_CSRF_HASH; // Usando las constantes definidas en la vista

    directoryTable = $("#directoryTable").DataTable({
        // 1. Configuración de DataTables
        processing: true,
        serverSide: true, // Crucial para la búsqueda/filtrado masivo
        responsive: true, // Para el responsive
        
        // 2. Configuración AJAX
        ajax: { 
            url: `${DIRECTORY_URL}/getData`, 
            type: "POST", 
            // 💡 Pasamos el token CSRF a la data
            data: function(d) {
                // Combina los datos de paginación/búsqueda de DataTables (d) con el token
                return $.extend({}, d, csrfData);
            }
        },

        // 3. Definición de Columnas (DEBE COINCIDIR EXACTAMENTE CON EL SELECT DEL MODELO)
        columns: [
            // Índice 0: Contador de Fila (data: null, no ordenable en el servidor)
            { data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + 1 + meta.settings._iDisplayStart },
            
            // Índice 1-13: Columnas de datos reales
            { data: "company_name" },      // 1
            { data: "client_name" },       // 2
            { data: "client_post" },       // 3
            { data: "email" },             // 4
            { data: "city_name" },         // 5 (Alias del join)
            { data: "country_name" },      // 6 (Alias del join)
            { data: "category_name" },     // 7 (Alias del join)
            { data: "phones", orderable: false },    // 8 (STRING_AGG, no es seguro ordenar, mejor evitar)
            { data: "addresses", orderable: false }, // 9 (STRING_AGG, no es seguro ordenar, mejor evitar)
            { data: "created_user" },      // 10
            { data: "created_at" },        // 11
            {  // 12: Status
                data: "status",
                render: (data) => data ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'
            },
            {  // 13: Acciones (data: null, no ordenable ni searchable)
                data: null,
                orderable: false,
                searchable: false,
                render: (data, type, row) => {
                    if (row.status === true || row.status === "t") {
                        return `
                        <button class="btn btn-sm btn-primary" onclick="openForm(${row.directory_id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="toggleStatus(${row.directory_id}, true)">
                            <i class="bi bi-x-circle"></i>
                        </button>`;
                    } else {
                        return `<button class="btn btn-sm btn-success" onclick="toggleStatus(${row.directory_id}, false)">
                            <i class="bi bi-check-circle"></i>
                        </button>`;
                    }
                }
            }
        ],
        // Opcional: Configuración de idioma
        language: {
            // Usamos base_url() para generar la ruta completa a tu activo local
            url: DATATABLES_LANGUAGE_URL
        },
        // Orden inicial (ej. por ID descendente)
        order: [[1, 'desc']] 
    });
});

// Toggle status
function toggleStatus(id, current) {
    fetch(`${DIRECTORY_URL}/toggleStatus/${id}`, { method: "POST" })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success") {
                directoryTable.ajax.reload(null, false);
                Swal.fire("Éxito", res.message, "success");
            } else {
                Swal.fire("Error", res.message, "error");
            }
        });
}

// Abrir modal para edición (implementar modal aparte)
function openEditModal(data) {
    console.log(data);
}
