let userTable;

function openUserModal(data = null) {
    $("#userForm")[0].reset();
    $("#user_id").val("");
    $("#assign_id").val("");
console.log(data);
    if (data) {
        $("#user_id").val(data.user_id);
        $("#assign_id").val(data.assign_id);
        $("input[name='username']").val(data.username);
        $("input[name='name']").val(data.name);
        $("input[name='lastname']").val(data.lastname);
        $("input[name='email']").val(data.email);
        $("input[name='phone']").val(data.phone);
        $("select[name='profileId']").val(data.profile_id);
        $(".modal-title").text("Editar Usuario");
    } else {
        $(".modal-title").text("Nuevo Usuario");
    }

    $("#userModal").modal("show");
}

$(document).ready(function () {
    userTable = $("#userTable").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: `${USER_URL}/getData`,
            type: "POST"
        },
        columns: [
            { data: null, render: (d, t, r, m) => m.row + 1 },
            { data: "username" },
            { data: null, render: r => `${r.name} ${r.lastname}` },
            { data: "email" },
            { data: "phone" },
            { data: "profile" },
            {
                data: "status",
                render: d => d ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>'
            },
            {
                data: null,
                render: (data, type, row) => {
                    const json = JSON.stringify(row).replace(/"/g, "&quot;");
                    if (row.status === true || row.status === "t") {
                        return `
                            <button class="btn btn-sm btn-warning btn-edit" 
                                data-row="${json}">
                                <i class="bi bi-pencil"></i> 
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                onclick="toggleStatus(${row.user_id}, true)">
                                <i class="bi bi-x-circle"></i> 
                            </button>
                        `;
                    } else {
                        return `
                            <button class="btn btn-sm btn-success" 
                                onclick="toggleStatus(${row.user_id}, false)">
                                <i class="bi bi-check-circle"></i> 
                            </button>
                        `;
                    }
                },
                orderable: false
            }
        ]
    });

    $("#userTable").on("click", ".btn-edit", function () {
        openUserModal($(this).data("row"));
    });

    $("#userForm").on("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = formData.get("user_id");
        const isEdit = id !== "";
        console.log(formData);
        const actionText = isEdit ? "actualizar" : "registrar";
        const userName = formData.get('username') +" asignado a " + formData.get('name')+" "+formData.get('lastname');
        Swal.fire({
            title: `¿Está seguro de ${actionText} el usuario ${userName}?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, continuar",
        }).then((res) => {
            if (res.isConfirmed) {
                fetch(`${USER_URL}/save`, {
                    method: "POST",
                    body: formData
                })
                    .then(res => res.json())
                    .then(res => {
                        Swal.fire("Info", res.message, res.status);
                        $("#userModal").modal("hide");
                        userTable.ajax.reload();
                    });
            }
        });
    });
});

function toggleStatus(id, currentStatus) {
    const isActive = currentStatus === true || currentStatus === "t";
    const actionText = isActive ? "dar de baja" : "activar";
    const newStatus = !isActive;

    Swal.fire({
        title: `¿Deseas ${actionText} el usuario elegido?`,
        text: isActive
            ? "El usuario elegido será desactivado y no podrá usar el sistema temporalmente."
            : "El usuario elegido será reactivado y estará disponible nuevamente para uso de sistema.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí",
    }).then((res) => {
        if (res.isConfirmed) {
            fetch(`${USER_URL}/toggleStatus/${id}`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ status: newStatus }),
            })
                .then((res) => res.json())
                .then((res) => {
                    Swal.fire("Info", res.message, res.status);
                    userTable.ajax.reload();
                });
        }
    });
}
