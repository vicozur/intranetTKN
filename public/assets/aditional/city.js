let cityTable;

function openFormModal(data = null) {
    $("#cityForm")[0].reset();

    if (data) {
        $("#city_id").val(data.city_id);
        $("#country_id").val(data.country_id);
        $("#city_code").val(data.city_code);
        $("#name").val(data.name);
        $("#modalTitle").text("Editar Ciudad");
    } else {
        $("#city_id").val("");
        $("#modalTitle").text("Nueva Ciudad");
    }

    $("#cityModal").modal("show");
}

$(document).ready(function () {
    console.log(`${CITY_URL}/getData`);
    cityTable = $("#cityTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: { url: `${CITY_URL}/getData`, type: "POST" },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: "country_name" },
            { data: "name" },
            { data: "city_code" },
            { data: "created_user" },
            {
                data: "status",
                render: (data) =>
                    data
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>',
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
                            <button class="btn btn-sm btn-danger" onclick="toggleStatus(${row.city_id}, true)">
                                <i class="bi bi-x-circle"></i>
                            </button>`;
                    } else {
                        return `
                            <button class="btn btn-sm btn-success" onclick="toggleStatus(${row.city_id}, false)">
                                <i class="bi bi-check-circle"></i>
                            </button>`;
                    }
                },
            },
        ],
    });

    $("#cityTable").on("click", ".btn-edit", function () {
        openFormModal($(this).data("row"));
    });

    $("#cityForm").on("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = formData.get("city_id");
        const cityName = formData.get("name");
        const isEdit = id !== "";
        const actionText = isEdit ? "actualizar" : "registrar";

        Swal.fire({
            title: `¿Desea ${actionText} la ciudad ${cityName}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "Cancelar",
        }).then((r) => {
            if (r.isConfirmed) {
                fetch(`${CITY_URL}/save`, { method: "POST", body: formData })
                    .then((res) => res.json())
                    .then((res) => {
                        if (res.status === "success") {
                            $("#cityModal").modal("hide");
                            cityTable.ajax.reload();
                            Swal.fire("Éxito", res.message, "success");
                        } else {
                            Swal.fire("Error", res.message, "error");
                        }
                    });
            }
        });
    });
});

function toggleStatus(id, currentStatus) {
    const isActive = currentStatus === true || currentStatus === "t";
    const newStatus = !isActive;
    const actionText = isActive ? "dar de baja" : "activar";

    Swal.fire({
        title: `¿Deseas ${actionText} esta ciudad?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: `Sí, ${actionText}`,
        cancelButtonText: "Cancelar",
        confirmButtonColor: isActive ? "#d33" : "#28a745",
    }).then((r) => {
        if (r.isConfirmed) {
            fetch(`${CITY_URL}/toggleStatus/${id}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ status: newStatus }),
            })
                .then((res) => res.json())
                .then((res) => {
                    if (res.status === "success") {
                        Swal.fire("Éxito", res.message, "success");
                        cityTable.ajax.reload(null, false);
                    } else {
                        Swal.fire("Error", res.message, "error");
                    }
                });
        }
    });
}
