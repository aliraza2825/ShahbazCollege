<!--<link rel="stylesheet" href="https://unpkg.com/bootstrap@4/dist/css/bootstrap.min.css" crossorigin="anonymous">-->
<!-- BEGIN CONTENT -->
<style>
.label {
    cursor: pointer;
}

.progress {
    display: none;
    margin-bottom: 1rem;
}

.alert {
    display: none;
}

.img-container img {
    max-width: 100%;
}
</style>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE HEADER-->
        <!--<h3 class="page-title">        Add Teacher <small>You can add teacher here</small>        </h3>-->
        <!-- END PAGE HEADER-->
        <!-- BEGIN DASHBOARD STATS -->
        <!-- END DASHBOARD STATS -->
        <!-- BEGIN PAGE CONTENT--> <?php if(@$this->session->userdata('message')):?> <div class="alert alert-success">
            <button class="close" data-close="alert"></button> <span> <?php echo $this->session->userdata('message');?>
            </span> </div> <?php endif;?> <?php if(@$this->session->userdata('error')):?> <div
            class="alert alert-danger"> <button class="close" data-close="alert"></button> <span>
                <?php echo $this->session->userdata('error');?> </span> </div> <?php endif;?> <div class="row">
            <div class="col-md-12 ">
                <!-- BEGIN SAMPLE FORM PORTLET-->
                <div class="portlet box green ">
                    <div class="portlet-title">
                        <div class="caption"> <i class="fa fa-plus"></i> Add Students's
                            (<?php echo $student[0]['first_name'].' '.$student[0]['last_name'];?>) Documents </div>
                    </div>
                    <div class="portlet-body form">
                        <form class="form-horizontal" role="form" method="post"
                            action="<?php echo site_url();?>/students/upload/<?php echo $this->uri->segment(3)?>"
                            enctype="multipart/form-data">
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
                                            <div class="col-md-5"> <select class="form-control" name="type"
                                                    id="doc_type" required>
                                                    <option value="">Select Type</option>
                                                    <option value="ID Card">ID Card</option>
                                                    <option value="B - FORM">B - FORM</option>
                                                    <option value="Photo">Photo</option>
                                                    <option value="Result Card">Result Card</option>
                                                    <option value="Student Signature">Student Signature</option>
                                                    <option value="Student thumb">Student Thumb</option>
                                                    <option value="College Form">College Form</option>
                                                    <option value="Rules and Regulation Form">Rules and Regulation Form
                                                    </option>
                                                    <option value="Fee Strcuture Form">Fee Strcuture Form</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                                <!--<span class="help-inline"></span>-->
                                            </div>
                                        </div> <label class="col-md-3 control-label">Image <span
                                                class="required">*</span></label> <input type="file" name="clock_image"
                                            required>
                                    </div>
                                    <div style="width:2px; background-colo:black; height:100%"></div>
                                    <div class="col-md-6">
                                        <div class=""> <label class="label" data-toggle="tooltip"
                                                title="Change your avatar"> <img class="rounded" id="avatar"
                                                    src="https://www.drupal.org/files/styles/grid-3-2x/public/project-images/logo_CROP.png"
                                                    alt="avatar" width="150px"> <input type="file" class="sr-only"
                                                    id="input" name="image" accept="image/*"> </label>
                                            <h3>Uplod Image (Only Use For Crop Image)</h3>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                    role="progressbar" aria-valuenow="0" aria-valuemin="0"
                                                    aria-valuemax="100">0%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9"> <button type="submit" class="btn green">Add
                                            Document</button> <button
                                            onclick="location.href = '<?php echo site_url()?>/students/all_students'"
                                            type="button" class="btn default">Cancel</button> </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div> <!-- END SAMPLE FORM PORTLET-->
            </div>
        </div> <!-- END PAGE CONTENT-->
        <div class="row">
            <div class="col-md-12">
                <!-- BEGIN EXAMPLE TABLE PORTLET-->
                <div class="portlet box grey-cascade">
                    <div class="portlet-title">
                        <div class="caption"> <i class="fa fa-list"></i>All Event Images </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-6"> </div>
                            </div>
                        </div>
                        <table class="table table-striped table-bordered table-hover" id="sample_2">
                            <thead>
                                <tr>
                                    <th class="hidden"> Hidden </th>
                                    <th> Image # </th>
                                    <th> Title </th>
                                    <th> Image </th>
                                    <th> Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php                            $i=1;                            foreach($documents as $image):                                ?>
                                <tr class="odd gradeX">
                                    <td class="hidden"> <?php echo $i;?> </td>
                                    <td> <?php echo $i;?> </td>
                                    <td> <?php echo $image['type'];?> </td>
                                    <td> 
                                        <?php
                                            if($image['online_image']==''):
                                        ?>
                                        <a href="<?php echo base_url().'uploads/'.$image['image']?>" target="_blank">
                                            <img src="<?php echo base_url().'uploads/'.$image['image']?>" alt="" width="100" /> 
                                        </a> 
                                        <?php
                                        else:
                                        ?>
                                        <a href="<?php echo str_replace($bucket_address, $cloudfront_address, $image['online_image']);?>" target="_blank">
                                            <img src="<?php echo str_replace($bucket_address, $cloudfront_address, $image['online_image']);?>" alt="" width="100" /> 
                                        </a>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td> <a title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this Image?')"
                                            href="<?php echo site_url();?>/students/delete_documents/<?php echo $this->uri->segment(3).'/'.$image['id'];?>"
                                            class="btn red"><i class="fa fa-trash"></i></a> </td>
                                </tr>
                                <?php                                $i++;                            endforeach;                            ?>
                            </tbody>
                        </table>
                    </div>
                </div> <!-- END EXAMPLE TABLE PORTLET-->
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false"
    data-width="600" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Crop the image</h5> <button type="button" class="close"
                    data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <div class="modal-body"> <button type="button" id="left" class="btn btn-secondary">Rotate Left</button>
                <button type="button" id="right" class="btn btn-secondary">Rotate Right</button>
                <div class="img-container"> <img id="image" src="https://avatars0.githubusercontent.com/u/3456749">
                </div>
            </div>
            <div class="modal-footer"> <button type="button" class="btn btn-secondary"
                    data-dismiss="modal">Cancel</button> <button type="button" class="btn btn-primary" id="crop">Crop &
                    Save</button> </div>
        </div>
    </div>
</div><!-- END CONTENT -->
<script>
window.addEventListener('DOMContentLoaded', function() {
    var avatar = document.getElementById('avatar');
    var image = document.getElementById('image');
    var input = document.getElementById('input');
    var left = document.getElementById('left');
    var right = document.getElementById('right');
    var $progress = $('.progress');
    var $progressBar = $('.progress-bar');
    var $alert = $('.alert');
    var $modal = $('#modal');
    var cropper;
    $('[data-toggle="tooltip"]').tooltip();
    left.addEventListener('click', function() {
        cropper.rotate(-1)
    });
    right.addEventListener('click', function() {
        cropper.rotate(1)
    });
    input.addEventListener('change', function(e) {
        var files = e.target.files;
        var done = function(url) {
            input.value = '';
            image.src = url;
            $alert.hide();
            $modal.modal('show');
        };
        var reader;
        var file;
        var url;
        if (files && files.length > 0) {
            file = files[0];
            if (URL) {
                done(URL.createObjectURL(file));
            } else if (FileReader) {
                reader = new FileReader();
                reader.onload = function(e) {
                    done(reader.result);
                };
                reader.readAsDataURL(file);
            }
        }
    });
    $modal.on('shown.bs.modal', function() {
        cropper = new Cropper(image, {
            aspectRatio: 0,
            viewMode: 0,
            dragMode: 'move',
        });
    }).on('hidden.bs.modal', function() {
        cropper.destroy();
        cropper = null;
    });
    document.getElementById('crop').addEventListener('click', function() {
        var initialAvatarURL;
        var canvas;
        $modal.modal('hide');
        if (cropper) {
            canvas = cropper.getCroppedCanvas();
            var type = $('#doc_type').val();
            var minNumber = 1;
            var maxNumber = 100;
            var randomnumber = Math.floor(Math.random() * (maxNumber + 1255) + minNumber);
            initialAvatarURL = avatar.src;
            avatar.src = canvas.toDataURL();
            $progress.show();
            $alert.removeClass('alert-success alert-warning');
            canvas.toBlob(function(blob) {
                var formData = new FormData();
                formData.append('clock_image', blob, randomnumber + type +
                    <?php echo $this->uri->segment(3)?> + '.jpg');
                formData.append('type', type);
                $.ajax('<?php echo site_url();?>/students/upload/<?php echo $this->uri->segment(3)?>', {
                    method: 'POST',
                    type: type,
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new XMLHttpRequest();
                        xhr.upload.onprogress = function(e) {
                            var percent = '0';
                            var percentage = '0%';
                            if (e.lengthComputable) {
                                percent = Math.round((e.loaded / e.total) *
                                100);
                                percentage = percent + '%';
                                $progressBar.width(percentage).attr(
                                    'aria-valuenow', percent).text(
                                    percentage);
                            }
                        };
                        return xhr;
                    },
                    success: function() {
                        $alert.show().addClass('alert-success').text(
                            'Upload success');
                        location.reload();
                    },
                    error: function() {
                        avatar.src = initialAvatarURL;
                        $alert.show().addClass('alert-warning').text(
                        'Upload error');
                    },
                    complete: function() {
                        $progress.hide();
                    },
                });
            }, 'image/jpeg', 1);
        }
    });
});
</script>