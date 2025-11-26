<?php echo $this->extend('template/layout'); ?>
<?php $this->section('content'); ?>

<script>
    const USER_URL = "<?= base_url('user') ?>";
</script>

<div class="row">
    <div>
        <button class="btn btn-primary btn-flat float-end" onclick="openUserModal()"><b>Nuevo Usuario</b></button>
    </div>
    <hr>
</div>

<div class="table-responsive mt-3">
    <table id="userTable" class="table table-bordered table-hover table-sm display nowrap w-100">
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Perfil</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="userForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="user_id" name="user_id">
                    <input type="hidden" id="assign_id" name="assign_id">

                    <div class="mb-2">
                        <label>Usuario <b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" name="username" required>
                    </div>

                    <div class="mb-2">
                        <label>Nombres<b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="mb-2">
                        <label>Apellidos<b style="color: red;"> (*)</b></label>
                        <input type="text" class="form-control" name="lastname" required>
                    </div>

                    <div class="mb-2">
                        <label>Correo<b style="color: red;"> (*)</b></label>
                        <input type="email" class="form-control" name="email">
                    </div>

                    <div class="mb-2">
                        <label>Teléfono<b style="color: red;"> (*)</b></label>
                        <input type="number" class="form-control" name="phone">
                    </div>

                    <div class="mb-2">
                        <label>Perfil<b style="color: red;"> (*)</b></label>
                        <select name="profileId" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($profiles as $p): ?>
                                <option value="<?= $p['profile_id'] ?>"><?= $p['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
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

<script src="<?= base_url('assets/aditional/user.js') ?>"></script>

<?php $this->endSection(); ?>
