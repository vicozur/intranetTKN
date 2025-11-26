let categoryTable;


// === Definición del método ===
function openFormModal(data = null) {
    $("#categoryForm")[0].reset();

    if (data) {
        // Modo edición
        $("#category_id").val(data.category_id);
        $("#clasifier").val(data.clasifier);
        $("#name").val(data.name);
        $("#modalTitle").text("Editar categoría");
    } else {
        // Modo nuevo
        $("#category_id").val(""); // 🔹 Muy importante
        $("#modalTitle").text("Nueva categoría");
    }

    $("#categoryModal").modal("show");
}

// === Exponer al ámbito global ===
window.openFormModal = openFormModal;

$(document).ready(function () {
    const categoryTable = $("#categoryTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: `${CATEGORY_URL}/getData`,
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
            { data: "clasifier" },
            { data: "name" },
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
                                onclick="toggleStatus(${row.category_id}, true)">
                                <i class="bi bi-x-circle"></i> 
                            </button>
                        `;
                    } else {
                        return `
                            <button class="btn btn-sm btn-success" 
                                onclick="toggleStatus(${row.category_id}, false)">
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
    $("#categoryTable").on("click", ".btn-edit", function () {
        const data = $(this).data("row");
        openFormModal(data);
    });

    // Submit con confirmación SweetAlert
    $("#categoryForm").on("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Determinar acción
        const id = formData.get('category_id');
        const categoryName = formData.get('name') +" para clasificador " + formData.get('clasifier')
        const isEdit = id !== "";
        const actionText = isEdit ? "actualizar" : "registrar";
        console.log(formData);
        Swal.fire({
            title: `¿Está seguro de ${actionText} la categoría ${categoryName}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch( `${CATEGORY_URL}/save`, {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        $("#categoryModal").modal("hide");
                        categoryTable.ajax.reload();
                        Swal.fire("Éxito", res.message, "success");
                    } else {
                        Swal.fire("Error", res.message || "No se pudo guardar la categoría", "error");
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
function toggleStatus(category_id, currentStatus) {
    const isActive = currentStatus === true || currentStatus === "t";
    const actionText = isActive ? "dar de baja" : "activar";
    const newStatus = !isActive;
    console.log(isActive);
    console.log(newStatus);
    console.log(`${CATEGORY_URL}/toggleStatus/${category_id}`);
    Swal.fire({
        title: `¿Deseas ${actionText} este rubro?`,
        text: isActive
            ? "El rubro será desactivado y no podrá ser utilizado temporalmente."
            : "El rubro será reactivado y estará disponible nuevamente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: `Sí, ${actionText}`,
        cancelButtonText: "Cancelar",
        confirmButtonColor: isActive ? "#d33" : "#28a745",
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${CATEGORY_URL}/toggleStatus/${category_id}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ status: newStatus }),
            })
                .then((res) => res.json())
                .then((res) => {
                    if (res.status === "success") {
                        Swal.fire("Éxito", res.message, "success");
                        $("#categoryTable").DataTable().ajax.reload(null, false);
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

