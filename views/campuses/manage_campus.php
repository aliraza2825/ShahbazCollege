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
      <span> <?php echo $this->session->userdata('message');?> </span> </div>
    <?php endif;?>
    <?php if(@$this->session->userdata('error')):?>
    <div class="alert alert-danger">
      <button class="close" data-close="alert"></button>
      <span> <?php echo $this->session->userdata('error'); ?> </span> </div>
    <?php endif;

    $count = 0;?>
    <div class="row">
      <div class="col-md-12 "> 
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box green ">
          <div class="portlet-title">
            <div class="caption"> <i class="fa fa-plus"></i> Manage Campus </div>
          </div>
          <div class="portlet-body form">
            <form class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/campuses/insert_partners">
                <div class="form-body">
                    <div class="form-group">
                  <label class="col-md-3 control-label"> Campus <span class="required">*</span></label>
                  <div class="col-md-9">
                    <select name="campus_id" class="form-control input-inline input-large" required>
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
                    <div class="row">
                  <div class="col-md-12">
                    <h2>Partners</h2>
                    <hr />
                  </div>
                  <div class="col-md-12 partner">
                    
                  </div>
                  <div class="col-md-12">
                    <button class="btn green add_partner" type="button"><i class="fa fa-plus"></i> Add Partner</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <button class="btn red remove_partner" type="button"><i class="fa fa-trash"></i> Remove Partner</button>
                  </div>
                </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2>Sharing Expenses</h2>
                            <hr />
                        </div>
                        <div class="col-md-12 shared_campuses">

                        </div>
                        <div class="col-md-12">
                            <button class="btn green add_campus" type="button"><i class="fa fa-plus"></i> Add Campus</button>
                        </div>
                        <div class="col-md-12">
                          <h2>Special Expenses</h2>
                          <p>Special Expense will not include in sharing expense of any campus</p>
                          <hr />
                      </div>
                      <div class="form-group">
                        <label class="col-md-3 control-label"> Special Expenses </label>
                        <div class="col-md-5">
                            <select class="form-control select2" id="select2_sample1" name="special_expense_ids[]" multiple>
                                <?php
                                foreach($expense_categories as $expense_category):
                                ?>
                                    <option value="<?php echo $expense_category['expense_category_id'];?>" <?php if(@$current_campus['special_expense_ids']!=NULL || @$current_campus['special_expense_ids']!='') if(in_array($expense_category['expense_category_id'], json_decode(@$current_campus['special_expense_ids']))){echo 'selected';}?>>
                                        <?php echo $expense_category['name'];?>
                                    </option>
                                <?php
                                endforeach;
                                ?>
                            </select>
                            <!--<span class="help-inline"></span>-->
                        </div>
                      </div>
                    </div>
                </div>
              <div class="form-actions">
                <div class="row">
                  <div class="col-md-offset-3 col-md-9">
                    <button type="submit" class="btn green">Add Partners</button>
                    <button onclick="location.href = '<?php echo site_url()?>'" type="button" class="btn default">Cancel</button>
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
                        <i class="fa fa-list"></i>Campus Partners
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
                             Campus Name
                        </th>
                        <th>
                             Action
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $i = 0;
                        foreach($campus_partners as $campus_partner):
                    ?>
                    <tr class="odd gradeX">
                        <td class="hidden">
                             <?php echo $i;?>
                        </td>
                        <td>
                             <?php echo $campus_partner['campus_partner_id'];?>
                        </td>
                        <td>
                            <?php echo $campus_partner['campus_name'];?>
                        </td>
                        <td>
                            <?php
                                if($this->session->userdata('role')=='Admin'):
                            ?>
                            <a href="<?php echo site_url();?>/campuses/edit_manage_campus_profit/<?php echo $campus_partner['campus_partner_id'];?>" title="Edit" class="btn blue"><i class="fa fa-edit"></i></a>
                            <?php
                                endif;
                            ?>
                            <?php
                                if($this->session->userdata('role')=='Admin'):
                            ?>
                            <a onclick="return confirm('Are you sure you want to delete campus partner?')" href="<?php echo site_url();?>/campuses/delete_partners/<?php echo $campus_partner['campus_partner_id'];?>" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>
                            <?php
                                endif;
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
    <!-- END PAGE CONTENT--> 
  </div>
</div>
<!-- END CONTENT -->
<script>

    var index = 1;

    var vendors = <?php echo json_encode($campuses);?>;

    document.addEventListener( "DOMContentLoaded", function() {

        jQuery('.add_campus').click(function () {


            var str = '<div id="tr' + index + '">' +
                '<select name="campus_share_ids[]" class="form-control input-inline input-large select2" required>';

            str += '<?php
                $html = "";
                $count++;

                foreach ($campuses as $product):

                    $html .= '<option value="' . $product['campus_id'] . '">' . $product['campus_name'] . '</option>';

                endforeach;

                echo $html;
                ?>';
            str += '</select> <input type="number" style="margin-left: 10px;" class="form-control input-inline input-medium" name="no_of_seats[]" placeholder="Enter Number of Seats" value="" required>  <a onclick="removerow(' + index + ')" title="Delete" class="btn red"><i class="fa fa-trash"></i></a>';

            $('.shared_campuses').append(str);
            index++;
        });

    }, false );

    function removerow(id) {
        $('#tr' + id).remove();
        index--;
    }
</script>