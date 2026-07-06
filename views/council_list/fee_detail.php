
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
				<div class="col-md-12 ">
					<!-- BEGIN SAMPLE FORM PORTLET-->
					<div class="portlet box green ">
						<div class="portlet-title">
							<div class="caption">
								<i class="fa fa-list"></i> Council List With Fee
							</div>
						</div>
						<div class="portlet-body form">
                            <form id="council-fee-export-form" class="form-horizontal" role="form" method="post" action="<?php echo site_url();?>/council_list/start_council_fee_export">
								<div class="form-body">
									<div class="form-group">
                                        <label class="col-md-3 control-label">Campus <span class="required">*</span></label>
                                        <div class="col-md-5">
                                            <select class="form-control campus" name="campus_id">
                                                <option value="">ALL CAMPUS</option>
												<?php
													foreach($campuses as $campus):
												?>
                                                <option value="<?php echo $campus['campus_id'];?>"><?php echo $campus['campus_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                            <!--<span class="help-inline"></span>-->
                                        </div>
                                    </div>
									<div class="form-group">
                                        <label class="col-md-3 control-label">Course</label>
                                        <div class="col-md-5">
                                            <select class="form-control course_id" name="course_id">
                                                <option value="">ALL COURSE</option>
												<?php
													foreach($courses as $course):
												?>
                                                <option value="<?php echo $course['course_id'];?>"><?php echo $course['course_name'];?></option>
                                                <?php
                                                	endforeach;
												?>
                                            </select>
                                        </div>
                                    </div>
							
									<div class="class">
										<div class="form-group">
											<label class="col-md-3 control-label">Class</label>
											<div class="col-md-5">
												<select class="form-control classes" name="class_id">
                                                    <option value="">ALL CLASSES</option>
												</select>
												<!--<span class="help-inline"></span>-->
											</div>
										</div>
								</div>
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-offset-3 col-md-9">
											<button type="submit" class="btn green" id="start-export-btn">Create List</button>
											<button onclick="location.href = '<?php echo site_url();?>'" type="button" class="btn default">Cancel</button>
                                            <div id="council-export-progress-box" style="display:none; margin-top: 12px;">
                                                <div class="alert alert-info" style="margin-bottom: 8px;">
                                                    Export in progress... <span id="council-export-progress-text">0 / 0</span>
                                                </div>
                                                <div class="progress" style="height: 18px; margin-bottom: 8px;">
                                                    <div id="council-export-progress-bar" class="progress-bar progress-bar-success" role="progressbar" style="width:0%;">0%</div>
                                                </div>
                                                <a id="council-export-download-link" href="#" class="btn blue" style="display:none;">Download CSV</a>
                                            </div>
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
        (function () {
            var processing = false;
            var retryCount = 0;
            var maxRetries = 3;

            var form = document.getElementById('council-fee-export-form');
            var startBtn = document.getElementById('start-export-btn');
            var progressBox = document.getElementById('council-export-progress-box');
            var progressText = document.getElementById('council-export-progress-text');
            var progressBar = document.getElementById('council-export-progress-bar');
            var statusAlert = progressBox ? progressBox.querySelector('.alert') : null;
            var downloadLink = document.getElementById('council-export-download-link');

            if (!form || !startBtn || !progressBox || !progressText || !progressBar || !statusAlert || !downloadLink) {
                return;
            }

            function postForm(url, body, onSuccess, onError) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState !== 4) {
                        return;
                    }
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            onSuccess(JSON.parse(xhr.responseText || '{}'));
                        } catch (e) {
                            onError();
                        }
                        return;
                    }
                    onError();
                };
                xhr.send(body);
            }

            function serializeForm(formEl) {
                var pairs = [];
                var elements = formEl.querySelectorAll('input, select, textarea');
                for (var i = 0; i < elements.length; i++) {
                    var element = elements[i];
                    if (!element.name || element.disabled) {
                        continue;
                    }
                    pairs.push(encodeURIComponent(element.name) + '=' + encodeURIComponent(element.value));
                }
                return pairs.join('&');
            }

            function updateProgress(processed, total) {
                var safeTotal = total > 0 ? total : 1;
                var percent = Math.round((processed / safeTotal) * 100);
                if (percent > 100) {
                    percent = 100;
                }
                progressText.textContent = processed + ' / ' + total;
                progressBar.style.width = percent + '%';
                progressBar.textContent = percent + '%';
            }

            function setAlert(typeClass, message) {
                statusAlert.className = 'alert ' + typeClass;
                statusAlert.textContent = message;
            }

            function setExportError(message) {
                setAlert('alert-danger', message);
                startBtn.disabled = false;
                processing = false;
            }

            function processChunk(token) {
                postForm(
                    '<?php echo site_url(); ?>/council_list/process_council_fee_export',
                    'token=' + encodeURIComponent(token),
                    function (resp) {
                        retryCount = 0;
                        if (!resp || !resp.success) {
                            setExportError(resp && resp.message ? resp.message : 'Export failed. Please try again.');
                            return;
                        }

                        updateProgress(resp.processed, resp.total);
                        if (resp.completed) {
                            setAlert('alert-success', 'Export completed successfully.');
                            downloadLink.href = resp.download_url;
                            downloadLink.style.display = 'inline-block';
                            startBtn.disabled = false;
                            processing = false;
                            return;
                        }

                        setTimeout(function () { processChunk(token); }, 50);
                    },
                    function () {
                        retryCount++;
                        if (retryCount > maxRetries) {
                            setExportError('Network issue during export. Please retry.');
                            return;
                        }
                        setTimeout(function () { processChunk(token); }, 800);
                    }
                );
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (processing) {
                    return;
                }

                processing = true;
                retryCount = 0;
                startBtn.disabled = true;
                progressBox.style.display = 'block';
                downloadLink.style.display = 'none';
                setAlert('alert-info', 'Starting export...');
                updateProgress(0, 0);

                postForm(
                    '<?php echo site_url(); ?>/council_list/start_council_fee_export',
                    serializeForm(form),
                    function (resp) {
                        if (!resp || !resp.success) {
                            setExportError(resp && resp.message ? resp.message : 'Could not start export.');
                            return;
                        }
                        updateProgress(resp.processed, resp.total);
                        processChunk(resp.token);
                    },
                    function () {
                        setExportError('Could not start export. Please try again.');
                    }
                );
            });
        })();
    </script>