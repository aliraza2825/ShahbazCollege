
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Teacher <small>You can add teacher here</small>
			</h3>-->
			<!-- END PAGE HEADER-->
            <!-- BEGIN DASHBOARD STATS -->
			<!-- END DASHBOARD STATS -->
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-plus"></i> Add Teacher's Documents
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/teachers/upload/<?php echo $this->uri->segment(3)?>" enctype="multipart/form-data">
								<div class="form-body">
                                    <div class="row">
                                    	<div class="col-md-12">
                                        	<hr />
                                            <h2>Documents</h2>
                                            <hr />
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group"> <label class="col-md-3 control-label">Type <span
                                                            class="required">*</span></label>
                                                <div class="col-md-5">
                                                    <select class="form-control" name="type" id="doc_type" required>
                                                        <option value="">Select Type</option>
                                                        <option value="ID Card">ID Card</option>
                                                        <option value="Photo">Photo</option>
                                                        <option value="Educational Document">Educational Documents</option>
                                                        <option value="Other">other</option>
                                                    </select>
                                                    <!--<span class="help-inline"></span>-->
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-4 control-label">Teacher Documents </label>
                                                <div class="col-md-8">
                                                    <input type="file" name="teacher_document"  value="" required />
                                                    <span class="help-inline"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green">Add Document</button>
											<button onclick="location.href = '<?php echo site_url()?>/teachers/all_teachers'" type="button" class="btn default">Cancel</button>
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
            <div class="row">
            	<div class="col-md-12" style="border:1px solid #CCC; padding:20px;">
                	<?php
                    	foreach($documents as $document):
					?>
                    <div class="col-md-3" style="overflow:hidden; max-height:300px;">
                        <a title="Delete" onclick="return confirm('Are you sure you want to delete this Image?')" href="<?php echo site_url();?>/teachers/delete_documents/<?php echo $this->uri->segment(3)?>/<?php echo $document['id']?>" class="btn red"><i class="fa fa-trash"></i></a>
                        <label><?php echo $document['type']; ?></label>
                    	<a href="<?php echo base_url().'uploads/'.$document['image'];?>" target="_blank">
                        	<img src="<?php echo base_url().'uploads/'.$document['image'];?>" alt="" width="100%" />
                        </a>
                    </div>
                    <?php
                    	endforeach;
					?>
                </div>
            </div>
		</div>
	</div>
	<!-- END CONTENT -->