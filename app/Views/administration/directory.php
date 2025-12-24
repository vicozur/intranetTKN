<?php echo $this->extend('template/layout'); ?>
<?php $this->section('content'); ?>
<script>
    const DIRECTORY_URL = "<?= base_url('directorio') ?>";
    // 🔑 Generamos las variables CSRF de forma segura
    const CI_CSRF_NAME = '<?= csrf_token() ?>';
    const CI_CSRF_HASH = '<?= csrf_hash() ?>';
    // ✅ CORRECCIÓN CLAVE: Define la URL del JSON de idioma aquí.
    const DATATABLES_LANGUAGE_URL = "<?= base_url('assets/datatables/es-ES.json') ?>";
</script>

<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- Start row container-->
<div class="row">
    <div>
        <button class="btn  btn-flat float-end" style="background-color: purple; color:white;" onclick="openFileUploadModal()"><b>Importar</b></button>
        <button class="btn btn-primary btn-flat float-end" onclick="openForm()"><b>Nuevo cliente</b></button>
    </div>
    <hr>
</div>

<div class="table-responsive mt-3">
    <table class="table table-bordered table-hover table-sm display nowrap w-100" id="directoryTable">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Empresa</th>
                <th>Cargo</th>
                <th>Email</th>
                <th>Ciudad</th>
                <th>País</th>
                <th>Categoría</th>
                <th>Teléfonos</th>
                <th>Direcciones</th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- 🟢 Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="uploadModalTitle">Importar Archivo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="files" class="form-label">Archivos</label>
                        <input type="file" class="form-control" id="files" name="files[]" multiple>
                    </div>
                    <!-- 🔹 Aquí se mostrarán los nombres -->
                    <ul id="fileList" class="list-group mt-2"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/aditional/directoryScript.js') ?>"></script>
<?php $this->endSection(); ?>