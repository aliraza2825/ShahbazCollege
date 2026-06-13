
	<!-- BEGIN CONTENT -->
	<div class="page-content-wrapper">
		<div class="page-content">
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
								<i class="fa fa-plus"></i> Result Rules
							</div>
						</div>
						<div class="portlet-body form">
							<form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/councils/insert_result_rules">
							    <input type="hidden" name="council_result_rule_id" id="rule_id">
                               <div class="form-body">
                                  <div class="form-group">
                                     <label class="col-md-3 control-label">Select Courses <span class="required">*</span></label>
                                     <div class="col-md-6">
                                        <select class="form-control input-inline input-large" name="course_id" required>
                                           <?php foreach($courses as $course): ?>
                                           <option value="<?php echo $course['course_id']; ?>">
                                              <?php echo $course['course_name']; ?>
                                           </option>
                                           <?php endforeach; ?>
                                        </select>
                                     </div>
                                  </div>
                                  <div class="form-group">
                                     <label class="col-md-3 control-label">Total Chances from Council<span class="required">*</span></label>
                                     <div class="col-md-6">
                                        <input type="number"
                                           class="form-control input-inline input-large"
                                           name="total_chances"
                                           placeholder="Enter total chances"
                                           required>
                                           <br>
                                           <span class="help-inline">Write the No of Chances here. If there is no Limit then write 0.</span>
                                     </div>
                                  </div>
                                  <div class="form-group">
                                     <label class="col-md-3 control-label">After Chances<span class="required">*</span>
                                     </label>
                                     <div class="col-md-6">
                                         <select class="form-control input-inline input-large" name="after_chances" required>
                                           <option value="">Select Option</option>
                                           <option value="same_semmester">Same Semmester/Year</option>
                                           <option value="start_again">Start again</option>
                                           <option value="not_eligibl">Not Eligible</option>
                                        </select>
                                     </div>
                                  </div>
                                  <div class="form-group">
                                     <label class="col-md-3 control-label">Chances Scope<span class="required">*</span>
                                     </label>
                                     <div class="col-md-6">
                                         <select class="form-control input-inline input-large" name="attempt_scope" required>
                                           <option value="">Select Option</option>
                                           <option value="per_semmester">Each Year/Semmester</option>
                                           <option value="overall_course">In Whole Course</option>
                                        </select>
                                     </div>
                                  </div>
                                  <div class="form-group">
                                     <label class="col-md-3 control-label">
                                     Annual Students Can Appear In <span class="required">*</span>
                                     </label>
                                     <div class="col-md-6">
                                        <select class="form-control input-inline input-large" name="annual_students_can_appear_in" required>
                                           <option value="">Select Option</option>
                                           <option value="annual">Annual Only</option>
                                           <option value="supplementary">Supplementary Only</option>
                                           <option value="both">Annual + Supplementary</option>
                                        </select>
                                     </div>
                                  </div>
                                  <div class="form-group">
                                     <label class="col-md-3 control-label">
                                     Supplementary Students Can Appear In <span class="required">*</span>
                                     </label>
                                     <div class="col-md-6">
                                         <select class="form-control input-inline input-large" name="supplementary_students_can_appear_in" required>
                                           <option value="">Select Option</option>
                                           <option value="annual">Annual Only</option>
                                           <option value="supplementary">Supplementary Only</option>
                                           <option value="both">Annual + Supplementary</option>
                                        </select>
                                     </div>
                                  </div>
                                  <div class="form-group">
                                     <label class="col-md-3 control-label">
                                     Promote On Supplementary
                                     </label>
                                     <div class="col-md-6">
                                        <select class="form-control input-inline input-large" name="promote_on_supplementary">
                                           <option value="0">No</option>
                                           <option value="1">Yes</option>
                                        </select>
                                     </div>
                                  </div>
                               </div>
                               <div class="form-actions">
                                  <div class="row">
                                     <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" id="submitBtn" class="btn green">
                                        Add Result Rule
                                        </button>
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
								<i class="fa fa-list"></i> All Result Rules
							</div>
						</div>
						<div class="portlet-body">
							<table class="table table-striped table-bordered table-hover" id="sample_2">
                               <thead>
                                  <tr>
                                     <th class="hidden">
                                        hidden
                                     </th>
                                     <th>
                                        ID
                                     </th>
                                     <th>
                                        Course Name
                                     </th>
                                     <th>
                                        Total Chances
                                     </th>
                                     <th>
                                        What Happen After Chances
                                     </th>
                                     <th>
                                        Annual Students Can Appear In
                                     </th>
                                     <th>
                                        Supplementary Students Can Appear In
                                     </th>
                                     <th>
                                        Promote On Supplementary
                                     </th>
                                     <th>
                                        Action
                                     </th>
                                  </tr>
                               </thead>
                               <tbody>
                                  <?php
                                     $i = 0;
                                     foreach($rules as $rule):
                                     ?>
                                  <tr class="odd gradeX">
                                     <td class="hidden">
                                        <?php echo $i;?>
                                     </td>
                                     <td>
                                        <?php echo $rule['council_result_rule_id']?>
                                     </td>
                                     <td>
                                        <?php echo $rule['course_name']?>
                                     </td>
                                     <td>
                                        <?php echo $rule['total_chances']?>
                                     </td>
                                     <td>
                                        <?php echo $rule['after_chances']?>
                                     </td>
                                     <td>
                                        <?php echo ucfirst($rule['annual_students_can_appear_in']); ?>
                                     </td>
                                     <td>
                                        <?php echo ucfirst($rule['supplementary_students_can_appear_in']); ?>
                                     </td>
                                     <td>
                                        <?php echo $rule['promote_on_supplementary'] == 1 ? 'Yes' : 'No'; ?>
                                     </td>
                                     <td>
                                        <a href="javascript:void(0)"
                                           class="btn blue edit-rule"
                                           data-id="<?php echo $rule['council_result_rule_id'];?>"
                                           data-course="<?php echo $rule['course_id'];?>"
                                           data-chances="<?php echo $rule['total_chances'];?>"
                                           data-annual="<?php echo $rule['annual_students_can_appear_in'];?>"
                                           data-supplementary="<?php echo $rule['supplementary_students_can_appear_in'];?>"
                                           data-promote="<?php echo $rule['promote_on_supplementary'];?>"
                                           data-after_chances="<?php echo $rule['after_chances'];?>"
                                           data-attempt_scope="<?php echo $rule['attempt_scope'];?>"
                                           title="Edit">
                                        <i class="fa fa-edit"></i>
                                        </a>
                                        <a onclick="return confirm('Are you sure you want to delete this Council Rule?')" 
                                           href="<?php echo site_url().'/councils/delete_council_result_rule/'.$rule['council_result_rule_id'];?>" 
                                           title="Delete" 
                                           class="btn red">
                                        <i class="fa fa-trash"></i>
                                        </a>
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
			<!-- END PAGE CONTENT-->
		</div>
		<script>

            document.addEventListener("click", function(e){
            
                if(!e.target.closest(".edit-rule")) return;
                
                let btn = e.target.closest(".edit-rule");
                
                document.getElementById("rule_id").value = btn.dataset.id;
                
                document.querySelector("[name='course_id']").value = btn.dataset.course;
                
                document.querySelector("[name='total_chances']").value = btn.dataset.chances;
                
                document.querySelector("[name='annual_students_can_appear_in']").value = btn.dataset.annual;
                
                document.querySelector("[name='supplementary_students_can_appear_in']").value = btn.dataset.supplementary;
                
                document.querySelector("[name='promote_on_supplementary']").value = btn.dataset.promote;
                
                document.querySelector("[name='after_chances']").value = btn.dataset.after_chances;
                
                document.querySelector("[name='attempt_scope']").value = btn.dataset.attempt_scope;
                
                /* button text change */
                
                document.getElementById("submitBtn").innerText = "Update Result Rule";
                
                /* scroll to form */
            
                window.scrollTo({
                    top:0,
                behavior:"smooth"
                });
            
            });

    </script>
	</div>
	<!-- END CONTENT -->