<?php echo $this->extend('template/layout'); ?>
<?php $this->section('content'); ?>

<script>
    const PROYECTO_URL = "<?= base_url('proyecto') ?>";
    const BASE_URL = "<?= base_url() ?>";
</script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<div class="row">
    <div>
        <button class="btn btn-primary btn-flat float-end" onclick="openFormModal()"><b>Nuevo Proyecto</b></button>
    </div>
    <hr>
</div>

<div class="table-responsive mt-3">
    <table class="table table-bordered table-hover table-sm display nowrap w-100" id="libraryTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Identificador</th>
                <th>Nombre Proyecto</th>
                <th>Categoria</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th>Respaldos</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- 🟢 Modal -->
<div class="modal fade" id="libraryModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="libraryForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Nuevo Proyecto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="library_id" name="library_id">

                    <div class="mb-3">
                        <label for="categoryId" class="form-label">Categor&iacute;a <b style="color: red;"> (*)</b></label>
                        <select class="form-select" id="categoryId" name="categoryId" required>
                            <option value="">Seleccione una categor&iacute;a</option>
                            <?php foreach ($categoryList as $category): ?>
                                <option value="<?= $category['category_id'] ?>"><?= $category['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="identifier" class="form-label">C&oacute;digo identificador <b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" id="identifier" name="identifier" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de proyecto <b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" id="name" name="name" required>
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

<!-- 🟢 Modal -->
<div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="uploadModalTitle">Nuevo Proyecto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="upload_library_id" name="upload_library_id">
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

<script src="<?= base_url('assets/aditional/library.js') ?>"></script>

<?php $this->endSection(); ?>