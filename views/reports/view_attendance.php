<?php
	$myAccess = checkUserAccess();
?>	
    <!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
            <?php if(@$this->session->userdata('message')):?>
                <div class="alert alert-success">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('message');?> </span>
                </div>
            <?php endif;?>
			<?php if(@$this->session->userdata('error')):?>
                <div class="alert alert-danger">
                    <button class="close" data-close="alert"></button>
                    <span>
                    <?php echo $this->session->userdata('error');?> </span>
                </div>
            <?php endif;?>
            <div class="row">
                <div class="col-md-12">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i>Employee Attendance
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th>
                                        Campus
                                    </th>
                                    <th>
                                        Name
                                    </th>
                                    <th>
                                        IN TIME
                                    </th>
                                    <th>
                                        Status
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $i=0;
                                foreach($students as $student):?>
                                        <tr class="odd gradeX">
                                            <td>
                                                <?php echo $student['campus_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $student['first_name'].' '.$student['last_name'];?>
                                            </td>
                                            <td>
                                                <?php echo $student['in_time'];?>
                                            </td>
                                            <td>
                                                <?php
                                                    if ($student['in_time'] != '')
                                                        echo 'Present';
                                                    else
                                                        echo 'Absent';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php

                                    $i++;
                                endforeach;
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- END EXAMPLE TABLE PORTLET-->
                </div>
			</div>
        </div>
    </div>