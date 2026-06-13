
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
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
			<div class="row">
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Document
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/inventory/insert_document" enctype="multipart/form-data">
								<div class="form-body">
									<div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="campus_id" class="form-control input-inline input-large campus" required>
                                                        <option value="">SELECT CAMPUS</option>
                                                        <?php
                                                            foreach($campuses as $campus):
                                                        ?>
                                                        <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Room <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="room_id" class="form-control input-inline input-large rooms" required>
                                                    
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Sub-Room <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="subroom_id" class="form-control input-inline input-large subrooms" >
                                                    
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Document <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="document_name_id" class="form-control input-inline input-large select2" required>
                                                        <option value="">SELECT DOCUMENT</option>
                                                        <?php
                                                            foreach($document_names as $document_name):
                                                        ?>
                                                        <option value="<?php echo $document_name['document_name_id'];?>"><?php echo $document_name['document_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Picture</label>
                                                <div class="col-md-9">
                                                    <input type="file" name="picture" />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Document Quantity <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <input type="number" class="form-control input-inline input-large" name="document_quantity" placeholder="Enter Product Quantity" value="" required>
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Responsible Person <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="user_id" class="form-control input-inline input-large select2" data-placeholder="Select Responsible Person..." required>
                                                        <option value="">SELECT USER</option>
                                                        <?php
                                                            foreach($users as $user):
                                                        ?>
                                                        <option value="<?php echo $user['user_id'];?>"><?php echo $user['first_name'];?> <?php echo $user['last_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label">Responsibility Person <span class="required">*</span></label>
                                                <div class="col-md-9">
                                                    <select name="reponsilble_user_id" class="form-control input-inline input-large select2" data-placeholder="Select Responsibility Person..." required>
                                                        <option value="">SELECT USER</option>
                                                        <?php
                                                            foreach($users as $user):
                                                        ?>
                                                        <option value="<?php echo $user['user_id'];?>"><?php echo $user['first_name'];?> <?php echo $user['last_name'];?></option>
                                                        <?php
                                                            endforeach;
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<div class="form-group">
                                                <label class="col-md-3 control-label">Remarks</label>
                                                <div class="col-md-9">
                                                    <div class="col-md-6">
                                                        <textarea class="form-control" rows="3" name="remarks"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Document</button>
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