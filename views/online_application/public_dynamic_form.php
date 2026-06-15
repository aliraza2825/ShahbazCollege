<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($form['title']); ?></title>
    <link rel="stylesheet" href="<?php echo base_url();?>assets/global/plugins/bootstrap/css/bootstrap.min.css">
    <style>
        body { background:#f3f5f8; padding:30px 0; }
        .form-wrap { max-width:760px; margin:0 auto; background:#fff; padding:25px; border-radius:4px; box-shadow:0 2px 12px rgba(0,0,0,.12); }
        .form-title { margin-top:0; font-weight:bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="form-wrap">
        <h2 class="form-title"><?php echo htmlspecialchars($form['title']); ?></h2>
        <?php if($form['description']): ?>
            <p><?php echo nl2br(htmlspecialchars($form['description'])); ?></p>
        <?php endif; ?>

        <?php if($this->session->flashdata('message')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?php echo site_url();?>/online_application/submit_dynamic_form/<?php echo $form['slug']; ?>">
            <?php
                $grouped_fields = array();
                foreach($fields as $field) {
                    $row_index = isset($field['row_index']) ? (int) $field['row_index'] : 0;
                    if (!isset($grouped_fields[$row_index])) {
                        $grouped_fields[$row_index] = array();
                    }
                    $grouped_fields[$row_index][] = $field;
                }
                ksort($grouped_fields);
            ?>
            <?php foreach($grouped_fields as $row_fields): ?>
                <div class="row">
                <?php foreach($row_fields as $field): ?>
                    <?php
                        $column_width = isset($field['column_width']) ? (int) $field['column_width'] : 12;
                        if (!in_array($column_width, array(12, 6, 4, 3))) {
                            $column_width = 12;
                        }
                    ?>
                    <div class="col-sm-<?php echo $column_width; ?>">
                        <div class="form-group">
                            <label>
                                <?php echo htmlspecialchars($field['label']); ?>
                                <?php if($field['is_required']==1): ?><span style="color:red">*</span><?php endif; ?>
                            </label>

                            <?php
                                $name = 'field_' . $field['id'];
                                $required = $field['is_required']==1 ? 'required' : '';
                                $options = array_filter(array_map('trim', explode(',', $field['options'])));
                            ?>

                            <?php if($field['field_type']=='textarea'): ?>
                                <textarea name="<?php echo $name; ?>" class="form-control" rows="4" <?php echo $required; ?>></textarea>
                            <?php elseif($field['field_type']=='select'): ?>
                                <select name="<?php echo $name; ?>" class="form-control" <?php echo $required; ?>>
                                    <option value="">Select</option>
                                    <?php foreach($options as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif($field['field_type']=='radio'): ?>
                                <div>
                                    <?php foreach($options as $option): ?>
                                        <label class="radio-inline">
                                            <input type="radio" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($option); ?>" <?php echo $required; ?>> <?php echo htmlspecialchars($option); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif($field['field_type']=='checkbox'): ?>
                                <div>
                                    <?php foreach($options as $option): ?>
                                        <label class="checkbox-inline">
                                            <input type="checkbox" name="<?php echo $name; ?>[]" value="<?php echo htmlspecialchars($option); ?>"> <?php echo htmlspecialchars($option); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif($field['field_type']=='file'): ?>
                                <input type="file" name="<?php echo $name; ?>" class="form-control" <?php echo $required; ?>>
                            <?php else: ?>
                                <?php
                                    $inputType = 'text';
                                    if($field['field_type']=='email') $inputType = 'email';
                                    if($field['field_type']=='number') $inputType = 'number';
                                    if($field['field_type']=='date') $inputType = 'date';
                                    if($field['field_type']=='mobile') $inputType = 'tel';
                                ?>
                                <input type="<?php echo $inputType; ?>" name="<?php echo $name; ?>" class="form-control" <?php echo $required; ?>>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    </div>
</div>
</body>
</html>
