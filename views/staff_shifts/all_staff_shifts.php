<?php
$myAccess = checkUserAccess();
?>
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span><?php echo $this->session->userdata('message');?></span>
                </div>
            <?php endif;?>
			<div class="row">
				<div class="col-md-12">
					<div class="portlet box grey-cascade">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-users"></i> All Staff Shifts
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
							<thead>
							<tr>
								<th class="hidden">hidden</th>
                                <th>Shift ID</th>
                                <th>Shift Name</th>
                                <th>Study Type</th>
                                <th>Combo</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Timing</th>
								<th>Action</th>
							</tr>
							</thead>
							<tbody>
							<?php $i = 0; foreach($staff_shifts as $staff_shift): ?>
                            <tr class="odd gradeX">
								<td class="hidden"><?php echo $i;?></td>
                                <td><?php echo $staff_shift['staff_shift_id'];?></td>
								<td><?php echo $staff_shift['shift_name'];?></td>
                                <td><?php echo $staff_shift['study_type_name'];?></td>
                                <td><?php echo staff_shift_label($staff_shift); ?></td>
                                <td><?php echo $staff_shift['description'];?></td>
                                <td><?php echo ((int) $staff_shift['status'] === 1) ? 'Active' : 'Inactive'; ?></td>
                                <td>
                                    <?php if(isset($timing_map[$staff_shift['staff_shift_id']]) && $timing_map[$staff_shift['staff_shift_id']] > 0): ?>
                                        <span class="label label-success">Configured</span>
                                    <?php else: ?>
                                        <span class="label label-warning">Not Set</span>
                                    <?php endif; ?>
                                </td>
								<td>
                                    <a href="<?php echo site_url().'/staff_shifts/staff_timing/'.$staff_shift['staff_shift_id'];?>" title="Timing" class="btn yellow"><i class="fa fa-clock-o"></i></a>
                                    <a href="<?php echo site_url().'/staff_shifts/edit_staff_shift/'.$staff_shift['staff_shift_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                                    <a onclick="return confirm('Are you sure you want to delete this Staff Shift?')" href="<?php echo site_url().'/staff_shifts/delete/'.$staff_shift['staff_shift_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
                            <?php $i++; endforeach; ?>
							</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
