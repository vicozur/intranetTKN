<?php echo $this->extend('template/layout'); ?>
<?php $this->section('content'); ?>

<script>
    const CITY_URL = "<?= base_url('ciudad') ?>";
</script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<div class="row">
    <div>
        <button class="btn btn-primary btn-flat float-end" onclick="openFormModal()"><b>Nueva Ciudad</b></button>
    </div>
    <hr>
</div>

<div class="table-responsive mt-3">
    <table class="table table-bordered table-hover table-sm display nowrap w-100" id="cityTable">
        <thead>
            <tr>
                <th>#</th>
                <th>País</th>
                <th>Nombre</th>
                <th>Cod. Ciudad</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- 🟢 Modal -->
<div class="modal fade" id="cityModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="cityForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Nueva Ciudad</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="city_id" name="city_id">

                    <div class="mb-3">
                        <label for="country_id" class="form-label">País/Estado <b style="color: red;"> (*)</b></label>
                        <select class="form-select" id="country_id" name="country_id" required>
                            <option value="">Seleccione un país</option>
                            <?php foreach ($countries as $country): ?>
                                <option value="<?= $country['country_id'] ?>"><?= $country['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre de ciudad <b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="city_code" class="form-label">Código &Aacute;erea <b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" id="city_code" name="city_code">
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

<script src="<?= base_url('assets/aditional/city.js') ?>"></script>

<?php $this->endSection(); ?>