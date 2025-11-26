let countryTable;


// === Definición del método ===
function openFormModal(data = null) {
    $("#countryForm")[0].reset();

    if (data) {
        // Modo edición
        $("#country_id").val(data.country_id);
        $("#country_code").val(data.country_code);
        $("#name").val(data.name);
        $("#modalTitle").text("Editar Pais/Estado");
    } else {
        // Modo nuevo
        $("#country_id").val("");
        $("#modalTitle").text("Nuevo Pais/Estado");
    }

    $("#countryModal").modal("show"); // ✅ Este es el correcto
}


// === Exponer al ámbito global ===
window.openFormModal = openFormModal;

$(document).ready(function () {
    const countryTable = $("#countryTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: `${COUNTRY_URL}/getData`,
            type: "POST",
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                },
                orderable: false,
                searchable: false
            },
            { data: "name" },
            { data: "country_code" },
            { data: "created_user" },
            {
                data: "status",
                render: function (data) {
                    return data
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>';
                },
            },
            {
                data: null,
                render: function (data, type, row) {
                    // Convertir el objeto a JSON escapando comillas dobles
                    const json = JSON.stringify(row).replace(/"/g, "&quot;");

                    if (row.status === true || row.status === "t") {
                        return `
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-row="${json}">
                                <i class="bi bi-pencil"></i> 
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                onclick="toggleStatus(${row.country_id}, true)">
                                <i class="bi bi-x-circle"></i> 
                            </button>
                        `;
                    } else {
                        return `
                            <button class="btn btn-sm btn-success" 
                                onclick="toggleStatus(${row.country_id}, false)">
                                <i class="bi bi-check-circle"></i> 
                            </button>
                        `;
                    }
                },
                orderable: false,
                searchable: false
            }
        ]
    });

    // Evento delegado para abrir modal con datos
    $("#countryTable").on("click", ".btn-edit", function () {
        const data = $(this).data("row");
        openFormModal(data);
    });

    // Submit con confirmación SweetAlert
    $("#countryForm").on("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Determinar acción
        const id = formData.get('country_id');
        const countryName = formData.get('name') +" para codigo Pais " + formData.get('country_code')
        const isEdit = id !== "";
        const actionText = isEdit ? "actualizar" : "registrar";
        console.log(formData);
        Swal.fire({
            title: `¿Está seguro de ${actionText} el Pais/Estado ${countryName}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch( `${COUNTRY_URL}/save`, {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        $("#countryModal").modal("hide");
                        countryTable.ajax.reload();
                        Swal.fire("Éxito", res.message, "success");
                    } else {
                        Swal.fire("Error", res.message || "No se pudo guardar el Pais/Estado", "error");
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire("Error", "Ocurrió un problema al enviar la solicitud", "error");
                });
            }
        });
    });

});

// 🟢 Cambiar estado (activar / desactivar)
function toggleStatus(country_id, currentStatus) {
    const isActive = currentStatus === true || currentStatus === "t";
    const actionText = isActive ? "dar de baja" : "activar";
    const newStatus = !isActive;
    console.log(isActive);
    console.log(newStatus);
    console.log(`${COUNTRY_URL}/toggleStatus/${country_id}`);
    Swal.fire({
        title: `¿Deseas ${actionText} este Pais/Estado?`,
        text: isActive
            ? "El Pais/Estado será desactivado y no podrá ser utilizado temporalmente."
            : "El Pais/Estado será reactivado y estará disponible nuevamente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: `Sí, ${actionText}`,
        cancelButtonText: "Cancelar",
        confirmButtonColor: isActive ? "#d33" : "#28a745",
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${COUNTRY_URL}/toggleStatus/${country_id}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ status: newStatus }),
            })
                .then((res) => res.json())
                .then((res) => {
                    if (res.status === "success") {
                        Swal.fire("Éxito", res.message, "success");
                        $("#countryTable").DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire("Error", res.message, "error");
                    }
                })
                .catch((err) => {
                    console.error(err);
                    Swal.fire("Error", "No se pudo cambiar el estado.", "error");
                });
        }
    });
}

