let libraryTable;

$(document).ready(function() {
    // Escucha cambios en los radios por su nombre
    $('input[name="tipo_carga"]').on('change', function() {
        if ($('#optCarpeta').is(':checked')) {
            $('#divCarpetaName').fadeIn();
        } else {
            $('#divCarpetaName').fadeOut();
            $('#carpetaName').val(''); // Borra el contenido al ocultar
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("files");
    const fileList = document.getElementById("fileList");

    // 🟡 Evento al seleccionar archivos
    fileInput.addEventListener("change", function () {
        fileList.innerHTML = ""; // Limpia la lista previa

        if (fileInput.files.length === 0) {
            const li = document.createElement("li");
            li.className = "list-group-item text-muted";
            li.textContent = "No se seleccionaron archivos";
            fileList.appendChild(li);
            return;
        }

        // 🟢 Mostrar los nombres de los archivos seleccionados
        Array.from(fileInput.files).forEach((file, index) => {
            const li = document.createElement("li");
            li.className = "list-group-item d-flex justify-content-between align-items-center";
            li.textContent = file.name;

            // 🔹 Botón para eliminar archivo de la lista antes de enviar
            const removeBtn = document.createElement("button");
            removeBtn.type = "button";
            removeBtn.className = "btn btn-sm btn-danger";
            removeBtn.textContent = "Quitar";

            removeBtn.addEventListener("click", () => {
                removeFile(index);
            });

            li.appendChild(removeBtn);
            fileList.appendChild(li);
        });
    });

    // 🧹 Función para eliminar un archivo antes de enviar
    function removeFile(index) {
        const dt = new DataTransfer(); // Nuevo FileList
        Array.from(fileInput.files)
            .filter((_, i) => i !== index)
            .forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
        fileInput.dispatchEvent(new Event("change"));
    }
});


function openFormModal(data = null) {
    $("#libraryForm")[0].reset();

    if (data) {
        $("#library_id").val(data.library_id);
        $("#categoryId").val(data.category_id);
        $("#identifier").val(data.identifier);
        $("#name").val(data.name);
        $("#modalTitle").text("Editar Info. Proyecto");
    } else {
        $("#library_id").val("");
        $("#modalTitle").text("Nueva Info. Proyecto");
    }

    $("#libraryModal").modal("show");
}

$(document).ready(function () {
    console.log(`${PROYECTO_URL}/getData`);

    libraryTable = $("#libraryTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: { url: `${PROYECTO_URL}/getData`, type: "POST" },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: "identifier" },
            { data: "name" },
            { data: "category_name" },
            { data: "created_user" },
            {
                data: "status",
                render: (data) =>
                    data
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>',
            },
            {
                data: "files",
                render: function (files) {
    if (!Array.isArray(files) || files.length === 0) {
        return `<span class="text-muted">Sin archivos</span>`;
    }

    let list = `<ul class="list-unstyled mb-0">`;
    files.forEach((f) => {
        let displayNameHTML = '';
        let titleText = ''; // Para el atributo title (sin HTML)

        if (f.carpeta && f.carpeta.trim() !== "") {
            // Versión con HTML para mostrar en la tabla
            displayNameHTML = `<span style="color: #004a99; font-weight: bold;">${f.carpeta}</span>/${f.name}`;
            // Versión texto plano para el atributo title (evita que se rompa el HTML)
            titleText = `${f.carpeta}/${f.name}`;
        } else {
            displayNameHTML = f.name;
            titleText = f.name;
        }

        list += `
        <li class="mb-1">
            <a href="${BASE_URL}/${f.url}" target="_blank" title="${titleText}" class="text-decoration-none">
                <i class="bi bi-paperclip text-secondary"></i> ${displayNameHTML}
            </a>
        </li>`;
    });
    list += `</ul>`;
    return list;
}
            },
            {
                data: null,
                render: (data, type, row) => {
                    const json = JSON.stringify(row).replace(/"/g, "&quot;");
                    if (row.status === true || row.status === "t") {
                        return `
                            <button class="btn btn-sm btn-warning btn-edit" data-row="${json}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm" style="background-color: #4b078bff; color: white" onClick="openFileUploadModal(${json})">
                                <i class="bi bi-cloud-upload-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="toggleStatus(${row.library_id}, true)">
                                <i class="bi bi-x-circle"></i>
                            </button>`;
                    } else {
                        return `
                            <button class="btn btn-sm btn-success" onclick="toggleStatus(${row.library_id}, false)">
                                <i class="bi bi-check-circle"></i>
                            </button>`;
                    }
                },
            },
        ],
    });


    $("#libraryTable").on("click", ".btn-edit", function () {
        openFormModal($(this).data("row"));
    });

    $("#libraryForm").on("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = formData.get("library_id");
        const libraryName = formData.get("name");
        const isEdit = id !== "";
        const actionText = isEdit ? "actualizar" : "registrar";
        console.log(formData);
        Swal.fire({
            title: `¿Desea ${actionText} Informacion de proyecto  ${libraryName}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "Cancelar",
        }).then((r) => {
            if (r.isConfirmed) {
                fetch(`${PROYECTO_URL}/save`, { method: "POST", body: formData })
                    .then((res) => res.json())
                    .then((res) => {
                        if (res.status === "success") {
                            $("#libraryModal").modal("hide");
                            libraryTable.ajax.reload();
                            Swal.fire("Éxito", res.message, "success");
                        } else {
                            Swal.fire("Error", res.message, "error");
                        }
                    });
            }
        });
    });
});

function openFileUploadModal(data) {
        $("#uploadForm")[0].reset();
        const fileList = document.getElementById("fileList");
        fileList.innerHTML = ""; // Limpia la lista previa

        $("#upload_library_id").val(data.library_id);
        $("#uploadModalTitle").text("Subir Archivos para: " + data.name);
        $("#fileModal").modal("show");
    }

$("#uploadForm").on("submit", function (e) {
    e.preventDefault();

    let formData = new FormData(this);

    Swal.fire({
        title: '¿Desea registrar los archivos?',
        text: "La subida puede tardar dependiendo del tamaño de los archivos.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, subir",
        cancelButtonText: "Cancelar",
    }).then((r) => {
        if (r.isConfirmed) {
                $.ajax({
                url: `${PROYECTO_URL}/file`,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                // Agregamos esto para manejar archivos grandes sin que la UI se congele
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener(
                    "progress",
                    function (evt) {
                        if (evt.lengthComputable) {
                        var percentComplete = Math.round(
                            (evt.loaded / evt.total) * 100,
                        );
                        // Opcional: Actualizar un texto o barra de progreso
                        $("#uploadForm button[type='submit']").text(
                            `Subiendo... ${percentComplete}%`,
                        );
                        }
                    },
                    false,
                    );
                    return xhr;
                },
                beforeSend: function () {
                    $("#uploadForm button[type='submit']")
                    .prop("disabled", true)
                    .text("Iniciando subida...");
                },
                success: function (response) {
                    let res =
                    typeof response === "object"
                        ? response
                        : JSON.parse(response);

                    if (res.success) {
                    // 1. Refrescar la tabla manteniendo posición, filtros y página (índice)
                    // Esto evita que el usuario vuelva a la página 1
                    if (typeof libraryTable !== "undefined") {
                        libraryTable.ajax.reload(null, false);
                    }

                    // 2. Notificar éxito
                    Swal.fire(
                        "Éxito",
                        "Archivos subidos correctamente",
                        "success",
                    );

                    // 3. Limpiar y cerrar
                    $("#fileModal").modal("hide");
                    $("#uploadForm")[0].reset();
                    $("#fileList").html("");
                    } else {
                    Swal.fire("Error", response.message, "error");
                    }
                },
                error: function (xhr) {
                    let errorMsg = "Ocurrió un error en el servidor";
                    if (xhr.status === 413)
                    errorMsg =
                        "El archivo es demasiado grande para el servidor (Límite excedido)";

                    Swal.fire("Error", errorMsg, "error");
                    console.error(xhr.responseText);
                },
                complete: function () {
                    $("#uploadForm button[type='submit']")
                    .prop("disabled", false)
                    .text("Guardar");
                },
                });
        }
    });
});


function toggleStatus(id, currentStatus) {
    const isActive = currentStatus === true || currentStatus === "t";
    const newStatus = !isActive;
    const actionText = isActive ? "dar de baja" : "activar";

    Swal.fire({
        title: `¿Deseas ${actionText} la Informacion del proyecto elegido?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: `Sí, ${actionText}`,
        cancelButtonText: "Cancelar",
        confirmButtonColor: isActive ? "#d33" : "#28a745",
    }).then((r) => {
        if (r.isConfirmed) {
            fetch(`${PROYECTO_URL}/toggleStatus/${id}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ status: newStatus }),
            })
                .then((res) => res.json())
                .then((res) => {
                    if (res.status === "success") {
                        Swal.fire("Éxito", res.message, "success");
                        libraryTable.ajax.reload(null, false);
                    } else {
                        Swal.fire("Error", res.message, "error");
                    }
                });
        }
    });
}
