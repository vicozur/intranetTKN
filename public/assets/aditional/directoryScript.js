/*
function openFileUploadModal() {
        $("#uploadForm")[0].reset();
        const fileList = document.getElementById("fileList");
        fileList.innerHTML = ""; // Limpia la lista previa
        $("#uploadModalTitle").text("Subir Archivos para importar tarjetas");
        $("#fileModal").modal("show");
    }

*/

function openFileUploadModal(data) {
        $("#uploadForm")[0].reset();
        const fileList = document.getElementById("fileList");
        fileList.innerHTML = ""; // Limpia la lista previa

        $("#upload_library_id").val(data);
        $("#uploadModalTitle").text("Subir Archivos para: " + data);
        $("#fileModal").modal("show");
    }

/*
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
*/

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
      destroy: true, // Añade esto: Destruye cualquier instancia previa
      autoWidth: false, // Añade esto: Evita cálculos de ancho que causan el error de estilo
      // 1. Configuración de DataTables
    processing: true,
      serverSide: true, // Crucial para la búsqueda/filtrado masivo
      responsive: true, // Para el responsive

      // 2. Configuración AJAX
    ajax: {
        url: `${DIRECTORY_URL}/getData`,
        type: "POST",
        // 💡 Pasamos el token CSRF a la data
        data: function (d) {
          // Combina los datos de paginación/búsqueda de DataTables (d) con el token
        return $.extend({}, d, csrfData);
        },
    },

    columns: [
        // Posición 0: Cliente (Ahora es la primera)
        { data: "client_name" },
        // Posición 1: Empresa (Ahora es la segunda)
        { data: "company_name" },
        // Posición 2 en adelante: Resto de datos
        { data: "client_post" },
        { data: "email" },
        { data: "city_name" },
        { data: "country_name" },
        { data: "category_name" },
        { data: "phones", orderable: false },
        { data: "addresses", orderable: false },
        {
            data: "status",
            render: (data) =>
            data
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>',
        },
        {
            data: "imagenes_data",
            orderable: false,
            render: function (data, type, row) {
            if (!data)
                return '<span class="text-muted small">Sin imágenes</span>';

            // 1. Separamos los distintos archivos
            const archivos = data.split("|");
            let links = '<div class="d-flex flex-column">';

            archivos.forEach((item) => {
              // 2. Separamos el nombre de la URL (usando el delimitador :::)
            const [nombre, urlDescarga] = item.split(":::");

              // 3. Creamos el link: muestra 'name' pero apunta a 'url'
            links += `
                <a href="${urlDescarga}" 
                target="_blank" 
                rel="noopener noreferrer"
                class="text-primary mb-1" 
                style="text-decoration: none; font-size: 0.85rem;" 
                title="Descargar ${nombre}">
                    <i class="bi bi-file-earmark-image"></i> ${nombre}
                </a>`;
            });

            links += "</div>";
            return links;
        },
    },
    {
        data: null,
        orderable: false,
        searchable: false,
        render: (data, type, row) => {
            if (row.status === true || row.status === "t") {
                return `
                        <button class="btn btn-sm btn-primary" onclick="openForm(${row.directory_id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm" style="background-color: #4b078bff; color: white" onClick="openFileUploadModal(${row.directory_id})">
                                <i class="bi bi-cloud-upload-fill"></i>
                            </button>
                        <button class="btn btn-sm btn-danger" onclick="toggleStatus(${row.directory_id}, true)">
                            <i class="bi bi-x-circle"></i>
                        </button>`;
            } else {
                return `<button class="btn btn-sm btn-success" onclick="toggleStatus(${row.directory_id}, false)">
                            <i class="bi bi-check-circle"></i>
                        </button>`;
            }
        },
    },
],
      // Opcional: Configuración de idioma
    language: {
        // Usamos base_url() para generar la ruta completa a tu activo local
        url: DATATABLES_LANGUAGE_URL,
    },
      // Orden inicial (ej. por ID descendente)

    order: [[0, "desc"]],
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

// 2. USA ESTE BLOQUE UNIFICADO para el submit:
$("#uploadForm").on("submit", function (e) {
    e.preventDefault();
    let formData = new FormData(this);
    const submitButton = $(this).find("button[type='submit']");

    Swal.fire({
        title: `¿Desea procesar los archivos para este cliente?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, continuar",
        cancelButtonText: "Cancelar",
    }).then((r) => {
        if (r.isConfirmed) {
            $.ajax({
                // USA LA URL CORRECTA SEGÚN TU CONTROLADOR
                url: `${DIRECTORY_URL}/file`, 
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    submitButton.prop("disabled", true).text("Procesando...");
                },
                success: function (response) {
                    // SI EL SERVIDOR RESPONDE JSON (Éxito de subida)
                    if (response.success) {
                        directoryTable.ajax.reload(null, false);
                        Swal.fire("Éxito", "Archivos procesados correctamente", "success");
                        $("#fileModal").modal("hide");
                        $("#uploadForm")[0].reset();
                        $("#fileList").html("");
                    } 
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    Swal.fire("Error", "Ocurrió un error en el servidor", "error");
                },
                complete: function () {
                    submitButton.prop("disabled", false).text("Guardar");
                }
            });
        }
    });
});