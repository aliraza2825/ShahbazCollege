
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Edit Profile <small>You can edit your profile here</small>
			</h3>-->
			
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
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
								<i class="fa fa-list"></i> Set Default Rooms & Subrooms of Campuses
							</div>
						</div>
						<div class="portlet-body form">
                            <form action="<?php echo site_url();?>/rules/update_inventory_default_campus_rooms" method="post">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th class="hidden">
                                            hidden
                                        </th>
                                        <th>
                                            Campus Name
                                        </th>
                                        <th>
                                            Room Name
                                        </th>
                                        <th>
                                            SubRoom
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $i = 1;
                                            foreach($campuses as $campus):
                                        ?>
                                        <tr class="odd gradeX">
                                            <td class="hidden">
                                                <?php echo $i;?>
                                            </td>
                                            <td>
                                                <?php echo $campus['campus_name']?>
                                                <input type="hidden" name="campus_id[]" value="<?php echo $campus['campus_id'];?>" />
                                            </td>
                                            <td>
                                                <?php 
                                                    $rooms = $this->db->get_where('rooms',array('campus_id'=>$campus['campus_id']))->result_array();
                                                    $default_room_id = @$this->db->get_where('default_room_rules',array('campus_id'=>$campus['campus_id']))->row()->room_id;
                                                ?>
                                                <select name="room_id[]" class="form-control input-inline input-large select2 rooms" data-row-number="<?php echo $i;?>">
                                                    <option value="">SELECT ROOM</option>
                                                    <?php
                                                        foreach($rooms as $room):
                                                    ?>
                                                    <option value="<?php echo $room['room_id'];?>" <?php if(@$room['room_id']==$default_room_id){echo 'selected';}?>><?php echo $room['room_name'];?></option>
                                                    <?php
                                                        endforeach;
                                                    ?>
                                                </select>
                                            </td>
                                            <td>
                                                <?php 
                                                    $subrooms=array();
                                                    if($default_room_id!='' && $default_room_id!=0)
                                                    {
                                                        $subrooms = $this->db->get_where('subrooms',array('room_id'=>$default_room_id))->result_array();
                                                        $default_subroom_id = @$this->db->get_where('default_room_rules',array('campus_id'=>$campus['campus_id']))->row()->subroom_id;
                                                    }
                                                ?>
                                                <select name="subroom_id[]" class="form-control input-inline input-large select2 subrooms_<?php echo $i;?>" data-row-number="<?php echo $i;?>">
                                                    <option value="">Select Subroom</option>
                                                    <?php
                                                        foreach($subrooms as $subroom):
                                                    ?>
                                                    <option value="<?php echo $subroom['subroom_id'];?>" <?php if(@$subroom['subroom_id']==$default_subroom_id){echo 'selected';}?>><?php echo $subroom['subroom_name'];?></option>
                                                    <?php
                                                        endforeach;
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <?php
                                            $i++;
                                            endforeach;
                                        ?>
                                    </tbody>
                                </table>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Set Default Rooms</button>
										</div>
									</div>
								</div>
                            </form>
						</div>
					</div>
					<!-- END EXAMPLE TABLE PORTLET-->
				</div>
			</div>


            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Default Inventory Expense Category
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/rules/update_inventory_expense_rule" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="exp_details">
                                        <div class="exp_cats">
                                            <div class="form-group" id="div-0">
                                                <label class="col-md-3 control-label">Expense Category <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control select2 exps" data-count="0" name="expense_category_id[]" id="category_id" required>
                                                        <option value="">Select expense category</option>
                                                        <?php
                                                            foreach($exp_categories as $category):
                                                        ?>
                                                        <option value="<?php echo $category['expense_category_id'];?>"><?php echo $category['name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
                                <?php
                                    if(count($default_expense_category_inventory)>0):
                                ?>
                                <div class="alert alert-success">
                                    <?php
                                        $expense_category_id = $default_expense_category_inventory[0]['expense_category_id'];
                                        $child_expense_category = $this->db->get_where('expense_category',array('expense_category_id'=>$expense_category_id))->result_array();
                                        if($child_expense_category[0]['sub_of']!=0)
                                        {
                                            $main_expense_category = $this->db->get_where('expense_category',array('expense_category_id'=>$child_expense_category[0]['sub_of']))->result_array();
                                            echo '<h2>Default Expense Category is '.$main_expense_category[0]['name'].' => '.$child_expense_category[0]['name'].'</h2>';
                                        }
                                        else
                                        {
                                            echo '<h2>Default Expense Category is '.$child_expense_category[0]['name'].'</h2>';
                                        }
                                    ?>
                                </div>
                                <?php
                                    endif;
                                ?>
                                <div class="alert alert-info">
                                    Kindly Choose the category which will effect when someone add product through purchase order.
                                </div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Default Expense Category</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>

            <div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Default Return Product Expense Category
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/rules/update_inventory_return_rule" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="exp_details">
                                        <div class="exp_cats">
                                            <div class="form-group" id="div-0">
                                                <label class="col-md-3 control-label">Expense Category <span class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control select2 exps" data-count="0" name="expense_category_id[]" id="category_id" required>
                                                        <option value="">Select expense category</option>
                                                        <?php
                                                            foreach($exp_categories as $category):
                                                        ?>
                                                        <option value="<?php echo $category['expense_category_id'];?>"><?php echo $category['name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
                                <?php
                                    if(count($default_return_category_inventory)>0):
                                ?>
                                <div class="alert alert-success">
                                    <?php
                                        $expense_category_id = $default_return_category_inventory[0]['expense_category_id'];
                                        $child_expense_category = $this->db->get_where('expense_category',array('expense_category_id'=>$expense_category_id))->result_array();
                                        if($child_expense_category[0]['sub_of']!=0)
                                        {
                                            $main_expense_category = $this->db->get_where('expense_category',array('expense_category_id'=>$child_expense_category[0]['sub_of']))->result_array();
                                            echo '<h2>Default Return Product Category is '.$main_expense_category[0]['name'].' => '.$child_expense_category[0]['name'].'</h2>';
                                        }
                                        else
                                        {
                                            echo '<h2>Default Return Product Category is '.$child_expense_category[0]['name'].'</h2>';
                                        }
                                    ?>
                                </div>
                                <?php
                                    endif;
                                ?>
                                <div class="alert alert-info">
                                    Kindly Choose the category which will effect when someone return purchased product from POS.
                                </div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Update Default Expense Category</button>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->

    <script>
		document.addEventListener('DOMContentLoaded', () => {
            $('.select2').select2();
		    var count = 0;
            $(document).on('change', '.exps', function (e) {
                var exp_id = this.value;
                var con = $(this).data('count');
                jQuery.ajax({
                    type: "post",
                    async: false,
                    url: '<?php echo site_url()?>/expenses/getSubExpenses',
                    data: {
                        campus_id : exp_id,
                        expense_id : exp_id,
                        count : con,
                    },
                    success: function(data) {
                        if (data !="") {
                            con++;
                            for (let n=con;n<=count;n++){
                                console.log($('#div-'+n));
                                $('#div-'+n).remove();
                            }
                            jQuery('.exp_cats').append(data);
                            count = con;
                            $('#category_id'+(con--)).select2();
                        }else {
                            con++;
                            for (let n=con;n<=count;n++){
                                jQuery('#div-'+n).remove();
                            }
                            count = con;
                        }
                    }
                });
            });
        });
    </script>