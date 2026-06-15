<?php $myAccess = checkUserAccess(); ?>
<div class="page-content-wrapper">
    <div class="page-content">
        <h3 class="page-title">Dynamic Forms <small>create and manage public forms</small></h3>

        <?php if($this->session->flashdata('message')): ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?php echo $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <p>
            <a href="<?php echo site_url();?>/online_application/form_builder" class="btn green">
                <i class="fa fa-plus"></i> Create Form
            </a>
        </p>

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-list"></i> Forms</div>
            </div>
            <div class="portlet-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Public URL</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($forms as $form): ?>
                        <tr>
                            <td><?php echo $form['id']; ?></td>
                            <td><?php echo htmlspecialchars($form['title']); ?></td>
                            <td>
                                <a href="<?php echo site_url();?>/online_application/public_form/<?php echo $form['slug']; ?>" target="_blank">
                                    <?php echo site_url();?>/online_application/public_form/<?php echo $form['slug']; ?>
                                </a>
                            </td>
                            <td><?php echo $form['status'] == 1 ? 'Active' : 'Inactive'; ?></td>
                            <td><?php echo $form['created_at']; ?></td>
                            <td>
                                <a href="<?php echo site_url();?>/online_application/form_builder/<?php echo $form['id']; ?>" class="btn blue btn-sm">Edit</a>
                                <a href="<?php echo site_url();?>/online_application/delete_dynamic_form/<?php echo $form['id']; ?>" onclick="return confirm('Delete this form?')" class="btn red btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
