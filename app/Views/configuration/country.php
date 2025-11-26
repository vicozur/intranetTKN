<?php echo $this->extend('template/layout'); ?>
<?php $this->section('content'); ?>
<script>
    const COUNTRY_URL = "<?= base_url('pais') ?>";
</script>

<!-- Dependencias de DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Botón para abrir modal -->
<div class="row">
    <!-- Start row Registro fisico de poste-->
    <div>
        <button class="btn btn-primary btn-flat float-end" onclick="openFormModal()"><b>Nuevo Pais/Estado</b></button>
    </div>
    <hr>
</div>
<div class="table-responsive mt-3">
    <!-- Tabla -->
    <table class="table table-bordered table-hover table-sm display nowrap w-100" id="countryTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Pais/Estado</th>
                <th>Cod. Pais</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
</div>


<!-- 🟢 Modal para Crear/Editar Rubros -->
<div class="modal fade" id="countryModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="countryForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Nuevo Pais/Estado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <input type="hidden" id="country_id" name="country_id">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de Pais/Estado <b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="country_code" class="form-label">C&oacute;digo Pais <b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" id="country_code" name="country_code" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>




<script src="<?= base_url('assets/aditional/country.js') ?>"></script>
<?php $this->endSection(); ?>