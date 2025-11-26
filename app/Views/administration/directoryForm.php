<?php echo $this->extend('template/layout'); ?>

<?php $this->section('content'); ?>

<script>
    const CLIENT_URL = "<?= base_url('directorio') ?>";
</script>

<!-- Start row container-->
<div class="row">
    <!-- Start row Registro fisico de poste-->
    <form id="myForm">
        <div>
            <p>Formulario de registro de información básica de clientes. Todos los campos señalados con <b style="color: red;">*</b> son requeridos.</p>

            <!-- Rubro -->
            <div class="mb-3">
                <input type="hidden" id="directory_id" name="directory_id"
                    value="<?= isset($directory['directory_id']) ? esc($directory['directory_id']) : '' ?>" />
                <select id="category" name="category" class="form-select">
                    <?php foreach ($categoryList as $item): ?>
                        <option value="<?= esc($item['category_id']) ?>"
                            <?= isset($directory['category_id']) && $directory['category_id'] == $item['category_id'] ? 'selected' : '' ?>>
                            <?= esc($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- País y Ciudad -->
            <div class="row">
                <div class="mb-3 col-sm-12 col-md-6">
                    <label for="country" class="form-label"><b>Pais</b> <b style="color: red;">*</b></label>
                    <select id="country" name="country" class="form-select" onchange="loadCities(this.value)">
                        <?php foreach ($countryList as $item): ?>
                            <option value="<?= esc($item['country_id']) ?>"
                                <?= isset($directory['country_id']) && $directory['country_id'] == $item['country_id'] ? 'selected' : '' ?>>
                                <?= esc($item['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3 col-sm-12 col-md-6">
                    <label for="city" class="form-label"><b>Ciudad</b> <b style="color: red;">*</b></label>
                    <select id="city" name="city" class="form-select">
                        <?php foreach ($cityList as $item): ?>
                            <option value="<?= esc($item['city_id']) ?>"
                                <?= isset($directory['city_id']) && $directory['city_id'] == $item['city_id'] ? 'selected' : '' ?>>
                                <?= esc($item['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Compañía y cliente -->
            <div class="mb-3">
                <label for="company_name" class="form-label"><b>Nombre de Compa&ntilde;ía</b> <b style="color: red;">*</b></label>
                <input type="text" class="form-control" id="company_name" name="company_name"
                    value="<?= isset($directory['company_name']) ? esc($directory['company_name']) : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="client_name" class="form-label"><b>Nombre del cliente</b> <b style="color: red;">*</b></label>
                <input type="text" class="form-control" id="client_name" name="client_name"
                    value="<?= isset($directory['client_name']) ? esc($directory['client_name']) : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="client_post" class="form-label"><b>Cargo del cliente</b> <b style="color: red;">*</b></label>
                <input type="text" class="form-control" id="client_post" name="client_post"
                    value="<?= isset($directory['client_post']) ? esc($directory['client_post']) : '' ?>" required>
            </div>
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label"><b>Correo del cliente</b> <b style="color: red;">*</b></label>
                <input type="text" class="form-control" id="email" name="email"
                    value="<?= isset($directory['email']) ? esc($directory['email']) : '' ?>" required>
            </div>

            <!-- Phones dinámicos -->
            <div class="mb-3">
                <label class="form-label"><b>Tel&eacute;fonos del cliente</b> <b style="color: red;">*</b></label>
                <div id="phoneContainer">
                    <?php if (!empty($phones)): ?>
                        <?php $contador = 1; ?>
                        <?php foreach ($phones as $p): ?>
                            <div class="input-group mb-2">
                                <input type="text" name="phonelist[]" class="form-control" value="<?= esc($p['number']) ?>" required>
                                <input type="text" name="internal_code[]" class="form-control" value="<?= esc($p['internal_code']) ?>">
                                <?php if ($contador == 1): ?>
                                    <button type="button" class="btn btn-success" onclick="addPhone()">+</button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-danger" onclick="removeElement(this)">-</button>
                                <?php endif; ?>
                            </div>
                            <?php $contador++; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="input-group mb-2">
                            <input type="text" name="phonelist[]" class="form-control" placeholder="Número telef&oacute;nico" required>
                            <input type="text" name="internal_code[]" class="form-control" placeholder="Nro. Interno (opcional)">
                            <button type="button" class="btn btn-success" onclick="addPhone()">+</button>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Addresses dinámicos -->
            <div class="mb-3">
                <label class="form-label"><b>Direcciones del cliente</b> <b style="color: red;">*</b></label>
                <div id="addressContainer">
                    <?php if (!empty($addresses)): ?>
                        <?php $contador2 = 1; ?>
                        <?php foreach ($addresses as $a): ?>
                            <?php if ($contador2 == 1): ?>
                                <div class="input-group mb-2">
                                    <input type="text" name="address[]" class="form-control" value="<?= esc($a['name']) ?>" required>
                                    <button type="button" class="btn btn-success" onclick="addAddress()">+</button>
                                </div>
                            <?php else: ?>
                                <div class="input-group mb-2">
                                    <input type="text" name="address[]" class="form-control" value="<?= esc($a['name']) ?>" required>
                                    <button type="button" class="btn btn-danger" onclick="removeElement(this)">-</button>
                                </div>
                            <?php endif; ?>
                            <?php $contador2++; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="input-group mb-2">
                            <input type="text" name="address[]" class="form-control" required>
                            <button type="button" class="btn btn-success" onclick="addAddress()">+</button>
                        </div>
                    <?php endif; ?>
                </div>

            </div>



            <!-- Submit -->
            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </form>

    <hr>
</div>

<script src="<?= base_url('assets/aditional/client.js') ?>"></script>
<?php $this->endSection(); ?>