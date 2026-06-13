
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
			
			
			<!-- BEGIN PAGE HEADER-->
			<!--<h3 class="page-title">
			Add Class <small>add your desire class</small>
			</h3>-->
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
            <?php
                $count = 0;

            if(@$this->session->userdata('message')):?>
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
                                <i class="fa fa-mobile" aria-hidden="true"></i>
                                Mobile Advertisements
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/mobile_app/insert_image"  enctype="multipart/form-data">
                                <div class="form-body row">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Type <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <select name="course_id" class="form-control input-inline input-medium course_id" required>
                                                <option value="">Select Module Type</option>
                                                <option value="advertisement">Advertisement</option>
                                                <option value="gallery">Gallery</option>
                                                <option value="future_plan">Future Plan</option>
                                                <option value="noticeboard">Noticeboard</option>
                                                <option value="downloads">Downloads</option>
                                                <option value="news">News</option>
                                                <option value="promotion">Promotion</option>
                                                <option value="slider">Slider</option>
                                                <option value="how_to_use">How to Use</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Title <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="text" style="width: 80%;" name="title" class="form-control" value="" required/>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Description <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <textarea name="description" style="width: 80%;" rows="5" class="wysihtml5 form-control"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Expire</label>
                                        <div class="col-md-9">
                                            <input type="checkbox" class="form-control input-inline input-medium expire" name="expire" value="1" />
                                        </div>
                                    </div>
                                    <div class="form-group expire_date" style="display:none;">
                                        <label class="control-label col-md-3">Expire Date</label>
                                        <div class="col-md-3">
                                            <div class="input-group input-medium date date-picker" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy-mm-dd" data-date-viewmode="years">
                                                <input type="text" name="expire_date" class="form-control" value="<?php echo date('Y-m-d');?>" readonly>
                                                <span class="input-group-btn">
                                                <button class="btn default" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Image <span class="required">*</span></label>
                                        <div class="col-md-9">
                                            <input type="file" class="form-control input-inline input-medium" name="picture" required>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button  type="submit" class="btn green">Add Image</button>
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
                <div class="col-md-12">
                    <!-- BEGIN EXAMPLE TABLE PORTLET-->
                    <div class="portlet box grey-cascade">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-list"></i> All Assigned Teachers
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered table-hover" id="sample_2">
                                <thead>
                                <tr>
                                    <th>
                                        Sr.No
                                    </th>
									<th>
                                        Module Type
                                    </th>
                                    <th>
                                        Title
                                    </th>
                                    <th>
                                        Description
                                    </th>
                                    <th>
                                        File
                                    </th>
                                   
                                    <th>
                                        Expire Date
                                    </th>
                                    <th>
                                        Action
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                $i=0;
                                foreach($advertisements as $list):
                                ?>
                                <tr class="odd gradeX">
                                    <td>
                                        <?php echo $i+1;?>
                                    </td>
									<td>
                                        <?php echo strtoupper($list['type']);?>
                                    </td>
                                    <td>
                                        <?php echo $list['title'];?>
                                    </td>
                                    <td>
                                        <?php echo $list['description'];?>
                                    </td>
                                    <td>
									<a target="_blank" class="btn green" href="<?php echo str_replace($bucket_address,$cloudfront_address,$list['file']);?>">File</a>
                                       
                                    </td>
                                    <td>
                                        <?php
                                            if($list['expire_date']!='0000-00-00')
                                            {
                                                echo $list['expire_date'];
                                            }
                                            else
                                            {
                                                echo 'N/A';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo site_url().'/mobile_application/delete_advertisement/'.$list['id'];?>" class="btn red"><i class="fa fa-trash"></i></a>
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
<!-- END CONTENT -->