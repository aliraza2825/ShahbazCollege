
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
								<i class="fa fa-file"></i> Contractor Documents
							</div>
						</div>
                        <div class="portlet-body">
                        	<h3>Contract Documents</h3>
                            <hr />
                            <?php
								foreach($contractor_documents as $contractor_document):
							?>
							<div class="col-md-3" style="overflow:hidden; max-height:300px; position:relative">
								<a href="<?php echo site_url();?>/contractors/delete_contractor_documents/<?php echo $contractor_document['contractor_id'];?>/<?php echo $contractor_document['contractor_document_id'];?>">
								<div style="position:absolute; right:0; top:0; cursor:pointer; background:#FFF; border-radius:30px; width:30px; height:30px;text-align:center; color:#F00; padding-top:5px;">
									<i class="fa fa-close"></i>
								</div>
								</a>
								<a href="<?php echo base_url().'contract_images/'.$contractor_document['image'];?>" target="_blank">
									<img src="<?php echo base_url().'contract_images/'.$contractor_document['image'];?>" alt="" width="100%" />
								</a>
							</div>
							<?php
								endforeach;
							?>
                            <div class="clearfix"></div>
                            
                        </div>
					</div>
					<!-- END SAMPLE FORM PORTLET-->
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->