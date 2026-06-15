<?php
    $form_fields_json = json_encode(!empty($fields) ? $fields : array());
?>
<style>
    .builder-shell {
        display: grid;
        grid-template-columns: 235px minmax(460px, 1fr) 320px;
        gap: 15px;
        align-items: start;
    }
    .builder-panel {
        background: #fff;
        border: 1px solid #dfe3ea;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .builder-panel-head {
        padding: 12px 14px;
        border-bottom: 1px solid #e6e9ef;
        font-weight: 700;
        color: #2f353b;
        background: #f9fafc;
    }
    .builder-panel-body {
        padding: 14px;
    }
    .field-palette {
        display: grid;
        gap: 8px;
    }
    .field-type-btn {
        width: 100%;
        text-align: left;
        border: 1px solid #d9dee7;
        background: #fff;
        color: #2f353b;
        padding: 10px 12px;
        border-radius: 4px;
        font-weight: 600;
        cursor: grab;
    }
    .field-type-btn:hover {
        border-color: #26a69a;
        color: #15877e;
        background: #f5fffd;
    }
    .builder-canvas {
        min-height: 470px;
        background: #f4f6f9;
        border: 1px dashed #b8c2d1;
        border-radius: 4px;
        padding: 14px;
    }
    .builder-empty {
        border: 1px dashed #b8c2d1;
        background: #fff;
        color: #777;
        padding: 36px 15px;
        text-align: center;
        border-radius: 4px;
    }
    .designer-row {
        background: #fff;
        border: 1px solid #dfe3ea;
        border-radius: 4px;
        margin-bottom: 14px;
    }
    .designer-row-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #edf0f5;
        padding: 8px 10px;
        background: #fbfcfd;
        color: #59616e;
        font-weight: 700;
    }
    .designer-row-body {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 0;
        min-height: 74px;
        padding: 10px 5px;
    }
    .designer-row-body.row-drop-active {
        background: #f1fbf9;
        outline: 1px dashed #26a69a;
    }
    .field-col {
        padding: 0 5px;
        min-width: 0;
    }
    .field-col.w12 { flex: 0 0 100%; max-width: 100%; }
    .field-col.w6 { flex: 0 0 50%; max-width: 50%; }
    .field-col.w4 { flex: 0 0 33.3333%; max-width: 33.3333%; }
    .field-col.w3 { flex: 0 0 25%; max-width: 25%; }
    .builder-field {
        position: relative;
        background: #fff;
        border: 1px solid #dfe3ea;
        border-left: 4px solid #26a69a;
        border-radius: 4px;
        padding: 12px 76px 12px 12px;
        cursor: pointer;
        min-height: 96px;
    }
    .builder-field.active {
        border-color: #26a69a;
        box-shadow: 0 0 0 2px rgba(38,166,154,.16);
    }
    .builder-field .field-label {
        display: block;
        font-weight: 700;
        margin-bottom: 7px;
        color: #2f353b;
        overflow-wrap: anywhere;
    }
    .builder-field .required-star {
        color: #e43a45;
    }
    .builder-field .field-preview {
        border: 1px solid #d9dee7;
        border-radius: 3px;
        background: #fafafa;
        color: #8a929f;
        padding: 8px 10px;
        min-height: 35px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .builder-field .field-actions {
        position: absolute;
        top: 9px;
        right: 9px;
        display: flex;
        gap: 5px;
    }
    .builder-field .field-actions button {
        width: 28px;
        height: 26px;
        padding: 0;
    }
    .drag-handle {
        color: #7b8492;
        cursor: grab;
    }
    .settings-muted {
        color: #777;
        padding: 18px 0;
        text-align: center;
    }
    .options-help {
        color: #777;
        font-size: 12px;
        margin-top: 5px;
    }
    .builder-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .builder-toolbar h4 {
        margin: 0;
        font-weight: 700;
    }
    .builder-count {
        color: #777;
    }
    .row-tools .btn {
        padding: 2px 7px;
        font-size: 12px;
    }
    @media (max-width: 1200px) {
        .builder-shell {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 700px) {
        .field-col.w6,
        .field-col.w4,
        .field-col.w3 {
            flex-basis: 100%;
            max-width: 100%;
        }
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <h3 class="page-title">Form Designer <small>build rows, columns and public application layouts</small></h3>

        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <form id="designerForm" class="form-horizontal" method="post" action="<?php echo site_url();?>/online_application/save_dynamic_form">
            <input type="hidden" name="form_id" value="<?php echo @$form['id']; ?>">
            <div id="compiledFields"></div>

            <div class="portlet box green">
                <div class="portlet-title">
                    <div class="caption"><i class="fa fa-pencil"></i> Form Details</div>
                </div>
                <div class="portlet-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars(@$form['title']); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars(@$form['slug']); ?>" placeholder="auto-generated if empty">
                        </div>
                        <div class="col-md-4">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1" <?php if(@$form['status'] != '0') echo 'selected'; ?>>Active</option>
                                <option value="0" <?php if(@$form['status'] == '0') echo 'selected'; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars(@$form['description']); ?></textarea>
                </div>
            </div>

            <div class="builder-shell">
                <div class="builder-panel">
                    <div class="builder-panel-head"><i class="fa fa-plus-circle"></i> Field Palette</div>
                    <div class="builder-panel-body field-palette">
                        <button type="button" class="field-type-btn" data-type="text"><i class="fa fa-font"></i> Text</button>
                        <button type="button" class="field-type-btn" data-type="textarea"><i class="fa fa-align-left"></i> Textarea</button>
                        <button type="button" class="field-type-btn" data-type="email"><i class="fa fa-envelope"></i> Email</button>
                        <button type="button" class="field-type-btn" data-type="mobile"><i class="fa fa-phone"></i> Mobile</button>
                        <button type="button" class="field-type-btn" data-type="number"><i class="fa fa-hashtag"></i> Number</button>
                        <button type="button" class="field-type-btn" data-type="date"><i class="fa fa-calendar"></i> Date</button>
                        <button type="button" class="field-type-btn" data-type="select"><i class="fa fa-caret-square-o-down"></i> Dropdown</button>
                        <button type="button" class="field-type-btn" data-type="radio"><i class="fa fa-dot-circle-o"></i> Radio Group</button>
                        <button type="button" class="field-type-btn" data-type="checkbox"><i class="fa fa-check-square-o"></i> Checkbox Group</button>
                        <button type="button" class="field-type-btn" data-type="file"><i class="fa fa-paperclip"></i> File Upload</button>
                    </div>
                </div>

                <div class="builder-panel">
                    <div class="builder-panel-head">
                        <div class="builder-toolbar">
                            <h4><i class="fa fa-columns"></i> Layout Canvas</h4>
                            <span class="builder-count" id="fieldCount">0 fields</span>
                        </div>
                    </div>
                    <div class="builder-panel-body">
                        <button type="button" class="btn blue" id="addRowBtn"><i class="fa fa-plus"></i> Add Row</button>
                        <br><br>
                        <div id="formCanvas" class="builder-canvas"></div>
                    </div>
                </div>

                <div class="builder-panel">
                    <div class="builder-panel-head"><i class="fa fa-sliders"></i> Field Settings</div>
                    <div class="builder-panel-body">
                        <div id="noFieldSelected" class="settings-muted">Select a field from the canvas to edit it.</div>
                        <div id="fieldSettings" style="display:none;">
                            <div class="form-group">
                                <label>Field Label</label>
                                <input type="text" id="settingLabel" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Field Type</label>
                                <select id="settingType" class="form-control">
                                    <option value="text">Text</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="email">Email</option>
                                    <option value="mobile">Mobile</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="select">Dropdown</option>
                                    <option value="radio">Radio Group</option>
                                    <option value="checkbox">Checkbox Group</option>
                                    <option value="file">File Upload</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Field Width</label>
                                <select id="settingWidth" class="form-control">
                                    <option value="12">Full row</option>
                                    <option value="6">Half row - 2 fields</option>
                                    <option value="4">One third - 3 fields</option>
                                    <option value="3">Quarter - 4 fields</option>
                                </select>
                            </div>
                            <div class="form-group" id="settingOptionsWrap">
                                <label>Options</label>
                                <textarea id="settingOptions" class="form-control" rows="4"></textarea>
                                <div class="options-help">Comma separated options, for example: D Pharmacy, Technician, Other</div>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" id="settingRequired"> Required field</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <button type="submit" class="btn green"><i class="fa fa-save"></i> Save Form</button>
            <a href="<?php echo site_url();?>/online_application/dynamic_forms" class="btn default">Back</a>
        </form>
    </div>
</div>

<script>
(function() {
    var existingFields = <?php echo $form_fields_json ? $form_fields_json : '[]'; ?>;
    var fields = [];
    var rows = [];
    var selectedId = null;
    var nextId = 1;
    var draggedFieldId = null;

    function byId(id) {
        return document.getElementById(id);
    }

    function labelForType(type) {
        var labels = {
            text: 'Text',
            textarea: 'Textarea',
            email: 'Email',
            mobile: 'Mobile',
            number: 'Number',
            date: 'Date',
            select: 'Dropdown',
            radio: 'Radio Group',
            checkbox: 'Checkbox Group',
            file: 'File Upload'
        };
        return labels[type] || 'Text';
    }

    function defaultOptions(type) {
        if (type === 'select' || type === 'radio' || type === 'checkbox') {
            return 'Option 1, Option 2, Option 3';
        }
        return '';
    }

    function makeField(type, label, options, required, rowIndex, width) {
        return {
            uid: 'field_' + nextId++,
            label: label || labelForType(type),
            type: type || 'text',
            options: options || defaultOptions(type),
            required: required ? 1 : 0,
            row: typeof rowIndex === 'number' ? rowIndex : getLastRowIndex(),
            width: parseInt(width || 12, 10)
        };
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text == null ? '' : text));
        return div.innerHTML;
    }

    function fieldByUid(uid) {
        for (var i = 0; i < fields.length; i++) {
            if (fields[i].uid === uid) {
                return fields[i];
            }
        }
        return null;
    }

    function needsOptions(type) {
        return type === 'select' || type === 'radio' || type === 'checkbox';
    }

    function getLastRowIndex() {
        if (!rows.length) {
            return 0;
        }
        return rows[rows.length - 1];
    }

    function ensureRows() {
        var seen = {};
        fields.forEach(function(field) {
            seen[field.row] = true;
        });
        rows.forEach(function(row) {
            seen[row] = true;
        });
        rows = Object.keys(seen).map(function(row) {
            return parseInt(row, 10);
        }).sort(function(a, b) {
            return a - b;
        });
        if (!rows.length) {
            rows = [0];
        }
    }

    function normalizeRows() {
        ensureRows();
        var map = {};
        rows.forEach(function(row, index) {
            map[row] = index;
        });
        fields.forEach(function(field) {
            field.row = map[field.row];
        });
        rows = rows.map(function(row, index) {
            return index;
        });
    }

    function fieldsForRow(rowIndex) {
        return fields.filter(function(field) {
            return field.row === rowIndex;
        });
    }

    function previewHtml(field) {
        if (field.type === 'textarea') {
            return '<div class="field-preview">Long answer text</div>';
        }
        if (field.type === 'select') {
            return '<div class="field-preview">Dropdown: ' + escapeHtml(field.options || 'No options') + '</div>';
        }
        if (field.type === 'radio') {
            return '<div class="field-preview">Radio choices: ' + escapeHtml(field.options || 'No options') + '</div>';
        }
        if (field.type === 'checkbox') {
            return '<div class="field-preview">Checkbox choices: ' + escapeHtml(field.options || 'No options') + '</div>';
        }
        if (field.type === 'file') {
            return '<div class="field-preview">Choose file</div>';
        }
        if (field.type === 'date') {
            return '<div class="field-preview">yyyy-mm-dd</div>';
        }
        if (field.type === 'email') {
            return '<div class="field-preview">name@example.com</div>';
        }
        if (field.type === 'mobile') {
            return '<div class="field-preview">03xxxxxxxxx</div>';
        }
        if (field.type === 'number') {
            return '<div class="field-preview">123</div>';
        }
        return '<div class="field-preview">Short answer text</div>';
    }

    function renderCanvas() {
        normalizeRows();
        var canvas = byId('formCanvas');
        var count = byId('fieldCount');
        count.innerHTML = fields.length + (fields.length === 1 ? ' field' : ' fields');

        var html = '';
        rows.forEach(function(rowIndex) {
            var rowFields = fieldsForRow(rowIndex);
            html += '<div class="designer-row" data-row="' + rowIndex + '">' +
                '<div class="designer-row-head">' +
                    '<span>Row ' + (rowIndex + 1) + '</span>' +
                    '<span class="row-tools">' +
                        '<button type="button" class="btn btn-xs default add-field-row" data-row="' + rowIndex + '"><i class="fa fa-plus"></i> Field</button> ' +
                        '<button type="button" class="btn btn-xs red remove-row" data-row="' + rowIndex + '"><i class="fa fa-trash"></i></button>' +
                    '</span>' +
                '</div>' +
                '<div class="designer-row-body" data-row="' + rowIndex + '">';

            if (rowFields.length === 0) {
                html += '<div class="field-col w12"><div class="builder-empty">Drop fields here or click a field from the left panel.</div></div>';
            }

            rowFields.forEach(function(field) {
                html += '<div class="field-col w' + field.width + '" data-uid="' + field.uid + '">' +
                    '<div class="builder-field ' + (field.uid === selectedId ? 'active' : '') + '" data-uid="' + field.uid + '" draggable="true">' +
                        '<div class="field-actions">' +
                            '<button type="button" class="btn btn-xs default drag-handle" title="Drag"><i class="fa fa-arrows"></i></button>' +
                            '<button type="button" class="btn btn-xs red remove-field" title="Remove"><i class="fa fa-trash"></i></button>' +
                        '</div>' +
                        '<span class="field-label">' + escapeHtml(field.label || 'Untitled Field') + (field.required ? ' <span class="required-star">*</span>' : '') + '</span>' +
                        previewHtml(field) +
                        '<small class="text-muted">' + labelForType(field.type) + ' | ' + widthLabel(field.width) + '</small>' +
                    '</div>' +
                '</div>';
            });

            html += '</div></div>';
        });
        canvas.innerHTML = html;
        bindCanvasEvents();
    }

    function widthLabel(width) {
        if (parseInt(width, 10) === 6) return 'Half';
        if (parseInt(width, 10) === 4) return 'Third';
        if (parseInt(width, 10) === 3) return 'Quarter';
        return 'Full';
    }

    function renderSettings() {
        var field = fieldByUid(selectedId);
        byId('noFieldSelected').style.display = field ? 'none' : 'block';
        byId('fieldSettings').style.display = field ? 'block' : 'none';
        if (!field) {
            return;
        }

        byId('settingLabel').value = field.label;
        byId('settingType').value = field.type;
        byId('settingWidth').value = String(field.width || 12);
        byId('settingOptions').value = field.options;
        byId('settingRequired').checked = field.required == 1;
        byId('settingOptionsWrap').style.display = needsOptions(field.type) ? 'block' : 'none';
    }

    function selectField(uid) {
        selectedId = uid;
        renderCanvas();
        renderSettings();
    }

    function removeField(uid) {
        fields = fields.filter(function(field) {
            return field.uid !== uid;
        });
        if (selectedId === uid) {
            selectedId = fields.length ? fields[0].uid : null;
        }
        renderCanvas();
        renderSettings();
    }

    function addRow() {
        rows.push(rows.length ? Math.max.apply(null, rows) + 1 : 0);
        renderCanvas();
    }

    function removeRow(rowIndex) {
        if (rows.length === 1) {
            return;
        }
        fields = fields.filter(function(field) {
            return field.row !== rowIndex;
        });
        rows = rows.filter(function(row) {
            return row !== rowIndex;
        });
        if (selectedId && !fieldByUid(selectedId)) {
            selectedId = fields.length ? fields[0].uid : null;
        }
        renderCanvas();
        renderSettings();
    }

    function addField(type, rowIndex) {
        var field = makeField(type, null, null, 0, typeof rowIndex === 'number' ? rowIndex : getLastRowIndex(), 12);
        fields.push(field);
        selectedId = field.uid;
        renderCanvas();
        renderSettings();
    }

    function moveFieldToRow(uid, rowIndex) {
        var field = fieldByUid(uid);
        if (!field) {
            return;
        }
        field.row = rowIndex;
        selectedId = uid;
        renderCanvas();
        renderSettings();
    }

    function bindCanvasEvents() {
        var fieldCards = document.querySelectorAll('.builder-field');
        for (var i = 0; i < fieldCards.length; i++) {
            fieldCards[i].onclick = function(e) {
                var uid = this.getAttribute('data-uid');
                if (e.target.closest && e.target.closest('.remove-field')) {
                    removeField(uid);
                    return;
                }
                selectField(uid);
            };
            fieldCards[i].ondragstart = function(e) {
                draggedFieldId = this.getAttribute('data-uid');
                e.dataTransfer.setData('existing-field', draggedFieldId);
            };
        }

        var rowBodies = document.querySelectorAll('.designer-row-body');
        for (var r = 0; r < rowBodies.length; r++) {
            rowBodies[r].ondragover = function(e) {
                e.preventDefault();
                this.classList.add('row-drop-active');
            };
            rowBodies[r].ondragleave = function() {
                this.classList.remove('row-drop-active');
            };
            rowBodies[r].ondrop = function(e) {
                e.preventDefault();
                this.classList.remove('row-drop-active');
                var rowIndex = parseInt(this.getAttribute('data-row'), 10);
                var paletteType = e.dataTransfer.getData('field-type');
                var existingUid = e.dataTransfer.getData('existing-field') || draggedFieldId;
                if (paletteType) {
                    addField(paletteType, rowIndex);
                } else if (existingUid) {
                    moveFieldToRow(existingUid, rowIndex);
                }
                draggedFieldId = null;
            };
        }

        var addFieldButtons = document.querySelectorAll('.add-field-row');
        for (var a = 0; a < addFieldButtons.length; a++) {
            addFieldButtons[a].onclick = function() {
                addField('text', parseInt(this.getAttribute('data-row'), 10));
            };
        }

        var removeRowButtons = document.querySelectorAll('.remove-row');
        for (var d = 0; d < removeRowButtons.length; d++) {
            removeRowButtons[d].onclick = function() {
                removeRow(parseInt(this.getAttribute('data-row'), 10));
            };
        }
    }

    function updateSelectedField() {
        var field = fieldByUid(selectedId);
        if (!field) {
            return;
        }
        field.label = byId('settingLabel').value;
        field.type = byId('settingType').value;
        field.width = parseInt(byId('settingWidth').value, 10);
        field.options = byId('settingOptions').value;
        field.required = byId('settingRequired').checked ? 1 : 0;
        if (!needsOptions(field.type)) {
            field.options = '';
        } else if (field.options === '') {
            field.options = defaultOptions(field.type);
            byId('settingOptions').value = field.options;
        }
        renderCanvas();
        renderSettings();
    }

    function compileFields() {
        normalizeRows();
        var holder = byId('compiledFields');
        holder.innerHTML = '';
        fields.forEach(function(field, index) {
            holder.innerHTML += '<input type="hidden" name="field_label[]" value="' + escapeHtml(field.label) + '">' +
                '<input type="hidden" name="field_type[]" value="' + escapeHtml(field.type) + '">' +
                '<input type="hidden" name="field_options[]" value="' + escapeHtml(field.options) + '">' +
                '<input type="hidden" name="field_row[]" value="' + parseInt(field.row, 10) + '">' +
                '<input type="hidden" name="field_width[]" value="' + parseInt(field.width || 12, 10) + '">' +
                (field.required ? '<input type="hidden" name="field_required[]" value="' + index + '">' : '');
        });
    }

    function init() {
        existingFields.forEach(function(field) {
            fields.push(makeField(
                field.field_type,
                field.label,
                field.options,
                field.is_required == 1,
                parseInt(field.row_index || 0, 10),
                parseInt(field.column_width || 12, 10)
            ));
        });
        if (fields.length) {
            selectedId = fields[0].uid;
        }
        ensureRows();

        var paletteButtons = document.querySelectorAll('.field-type-btn');
        for (var i = 0; i < paletteButtons.length; i++) {
            paletteButtons[i].setAttribute('draggable', 'true');
            paletteButtons[i].onclick = function() {
                addField(this.getAttribute('data-type'), getLastRowIndex());
            };
            paletteButtons[i].ondragstart = function(e) {
                e.dataTransfer.setData('field-type', this.getAttribute('data-type'));
            };
        }

        byId('addRowBtn').onclick = addRow;
        byId('settingLabel').oninput = updateSelectedField;
        byId('settingType').onchange = updateSelectedField;
        byId('settingWidth').onchange = updateSelectedField;
        byId('settingOptions').oninput = updateSelectedField;
        byId('settingRequired').onchange = updateSelectedField;
        byId('designerForm').onsubmit = function() {
            compileFields();
        };

        renderCanvas();
        renderSettings();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
