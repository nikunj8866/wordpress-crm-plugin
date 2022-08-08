<?php
if ( !class_exists('UpiCRMAdminEditLead') ):
class UpiCRMAdminEditLead{
    
    public function Render() {
        global $SourceTypeID;
        $lead_id = (int)$_GET['id'];
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMFields = new UpiCRMFields();
        $UpiCRMIntegrations = new UpiCRMIntegrations();
        $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
        $UpiCRMUsers = new UpiCRMUsers(); 
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $UpiCRMMails = new UpiCRMMails();

        $logs = $UpiCRMLeadsChangesLog->get_by_lead_id($lead_id);
        $email_log = $UpiCRMLeadsChangesLog->get_email_by_lead_id($lead_id);
        $all_mail = $UpiCRMLeadsChangesLog->get_all_parent_mail_log($lead_id);
        
        $lead = $UpiCRMLeads->get_by_id($lead_id);
        
        if ($lead->source_type == $SourceTypeID['upi_integration']) {
            $is_integration = true;
            $fields = $UpiCRMFields->get(); 
            foreach ($fields as $field) {
                $list_option[$field->field_name] = $field->field_name;
            }
        }
        else {
            $is_integration = false;
            $getNamesMap = $UpiCRMFieldsMapping->get_all_by($lead->source_id, $lead->source_type);
            foreach ($UpiCRMFields->get() as $field) { 
                foreach ($getNamesMap as $map) {
                    if ($map->field_id == $field->field_id)
                        $list_option[$field->field_id] = $field->field_name;  
                }
            }
        }
                if (isset($msg)) {
                ?><div class="updated">
                        <p><?php echo $msg; ?></p>
                    </div><?php
                }
                ?>
            <div class="tab-panel">
                <ul class="tab-panel-nav">
                    <li class="active"><a href="#lead-data">Lead</a></li>
                    <li><a href="#activity">Activity Log</a></li>
                    <li><a href="#note">Lead Note</a></li>
                    <li><a href="#email">Email</a></li>
                </ul>
                <div class="tab-panel-contents">
                    <div id="lead-data" class="tab-content active">
                        <div class="row">
                            <form method="post" id="edit_lead_form" >
                            <?php 
                                foreach ($list_option as $key => $value) {
                                    ?><div class="col-xs-12 col-sm-5 col-md-5 col-lg-4"><?php
                                        if (!$is_integration) {
                                            $LeadContent = $UpiCRMUIBuilder->return_lead_content_arr($lead,$key,$getNamesMap,$is_integration);
                                            ?><label for="label_<?php echo $LeadContent['fm_name']; ?>"><?php echo $value; ?>:</label><br />
                                            <textarea name="<?php echo $LeadContent['fm_name']; ?>" id="label_<?php echo $LeadContent['fm_name']; ?>" style="width: 100%;  color: #000;"><?php echo $LeadContent['text']; ?></textarea>
                                            <br /><br /><?php } else {
                                            $LeadContent = $UpiCRMIntegrations->get_value_by_lead_and_key($lead->lead_id,$value); 
                                            ?><label><?php echo $value; ?>:</label><br />
                                            <textarea name="<?php echo $value; ?>" style="width: 100%;  color: #000;"><?php echo $LeadContent; ?></textarea>
                                            <input type="hidden" name="is_integration" value="1" />
                                            <br /><br />
                                        <?php } ?></div><?php
                            }  
                            $show_arr = array(
                                "user_ip",
                                "time",
                            );
                            foreach ($lead as $key => $value) {
                                foreach ($show_arr as $arr) {
                                    if ($key == $arr) {
                                    ?><div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
                                            <label><?php echo $key; ?>:</label><br />
                                            <textarea disabled="" style="width: 100%; color: #000;"><?php echo $value; ?></textarea>
                                            <br /><br />
                                        </div><?php
                                    }
                                }
                            }
                            ?><div class="clearfix"></div>
                                <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
                                        <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>" />
                                        <input type="hidden" name="action" value="edit_save_lead" />
                                        <input type="hidden" name="source_id" value="<?php echo $lead->source_id; ?>">
                                        <?php submit_button(); ?>
                                </div>
                            </form>               
                        </div>
                    </div>
                    <div id="activity" class="tab-content">
                        <div class="jarviswidget jarviswidget-color-blueLight" id="wid-id-1" data-widget-editbutton="false">
                            <header>
                                <span class="widget-icon">
                                    <i class="fa fa-table"></i>
                                </span>
                                <h2><?php _e('Activity Table', 'upicrm'); ?></h2>
                            </header>
                            <table class="table table-striped table-bordered" id="data-table-lead">
                                <thead>
                                    <tr>
                                        <th width="3%">#</th>
                                        <th width="80%">Comment</th>
                                        <th width="15%">Time</th>
                                        <th width="2%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($logs as $log) { ?>
                                        <tr>
                                        <td><?php echo $i ?></td>
                                        <td>
                                            <?php 
                                            echo $UpiCRMLeadsChangesLog->get_text_activity($log);
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $text = ''; 
                                            if ($log->lead_change_log_time) {

                                                $text = $log->lead_change_log_time;
                                
                                            } else {
                                
                                                $text = date("Y-m-d H:i:s");
                                
                                            }
                                            echo $text
                                            ?>
                                        </td>
                                        <td>
                                        <span class="glyphicon glyphicon-remove" data-callback="remove-activity" data-activity_id="<?php echo $log->lead_change_log_id; ?>" title="<?php _e('Remove', 'upicrm'); ?>"></span>
                                        </td>
                                        </tr>
                                    <?php $i++; } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="note" class="tab-content">
                        <form method="post" id="edit_lead_comment_form">
                            <div class="row">
                                <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
                                    <label class="textarea textarea-expandable">
                                        <textarea class="custom-scroll" name="lead_remarks" data-callback="change_lead_remarks"><?php echo $lead->lead_management_comment; ?></textarea>
                                    </label>
                                </div>
                                <div class="clearfix"></div>
                                <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
                                        <input type="hidden" name="action" value="save_lead_comment">
                                        <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
                                        <?php submit_button(); ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="email" class="tab-content">
                        <a href="javascript:void(0)" class="button" id="create-email">Create Email</a>
                        <input type="hidden" id="lead_email_id" value="<?php echo $lead->lead_email; ?>">
                        <div class="mail-thread">
                            <?php 
                            foreach($all_mail as $mail) :
                            ?>
                            <div class="mail-item">
                                <span><strong>Email - <?php echo $mail->subject; ?></strong></span> <span>from <?php echo $mail->fromaddress; ?></span>
                                <span class="mail-date"> <?php echo date('M d, Y \a\t h:i a', $mail->timestamp); ?></span>
                                <span class="action">
                                    <a href="javascript:void(0)" class="reply-mail" data-subject="<?php echo str_replace("Re: ", "", $mail->subject); ?>" data-parent="<?php echo $mail->msg_id; ?>" >Reply</a>
                                    <a href="javascript:void(0)" class="remove-mail-log" data-id="<?php echo $mail->email_log_id; ?>">Remove</a>
                                </span>
                                <span class="mail-to">to <?php echo $mail->to; ?></span>
                                <?php if($mail->cc) : ?>
                                <span class="mail-to">cc <?php echo $mail->cc; ?></span>    
                                <?php endif; ?>
                                <div class="mail-content">
                                    <?php echo $mail->mail_content; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                       
                    </div>

                </div>
            </div>
        </div>
        <div id="createemail-box" class="modal fade upicrm" role="dialog">

            <div class="modal-dialog">

                <!-- Modal content-->

                <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                    <h4 class="modal-title"><?php _e('Email', 'upicrm'); ?></h4>

                </div>

                <div class="modal-body">
                <form method="post" id="send_email_lead">
                    <?php 
                    $getMails = $UpiCRMMails->get_custom();
                    ?>
                    <select name="email_template" id="email_template">
                        <option>Template</option>
                        <?php if (count($getMails) > 0 ) { ?>
                            <?php foreach ($getMails as $mail) { ?>
                            <option value="<?php echo $mail->mail_id; ?>" ><?php echo $mail->mail_event_name; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                    <div class="mailBox">
                            <div class="mailRecipients">
                                <div class="mailElem">
                                    <div class="mailElemCnt mailElemCntA">
                                        <span>To</span>
                                    </div>
                                    <div class="mailElemCnt">
                                        <input type="text" id="mailTo" name="mailTo" required data-role="tagsinput" value="<?php echo $lead->lead_email; ?>"/>
                                    </div>
                                    <div class="mailElemCnt mailElemContExpand">
                                        <a id="open-cc">CC</a>
                                    </div>
                                </div>
                                <div id="mailIDCc" class="mailElem hidden">
                                    <div class="mailElemCnt mailElemCntA">
                                        <span>CC</span>
                                    </div>
                                    <div class="mailElemCnt">
                                        <input type="text" id="mailCC" name="mailCC" data-role="tagsinput" />
                                    </div>
                                </div>

                            </div>
                            <div id="mailIDSubject" class="mailElem">
                                    <div class="mailElemCnt">
                                        <input type="text" id="mailSubject" name="mailSubject" placeholder="Subject"/>
                                    </div>
                            </div>
                            <div class="mailBodyBox">
                                <?php wp_editor("", 'mailIDBody', array("textarea_name" => 'mailBody', "editor_class" => "mailBody", "media_buttons" => false, "textarea_rows" => 50, "quicktags" => false,  'tinymce' => array("toolbar1" => "bold,italic,bullist,numlist,hr,alignleft,aligncenter,alignright,link,unlink", "block_formats" => false, "toolbar2" => "formatselect,underline,alignjustify,forecolor,pastetext,removeformat,outdent,indent,undo,redo")) ); ?>
                            </div>
                            <div  class="mailboxBottom mailSend">
                                <input type="hidden" name="action" value="send_email_lead">
                                <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
                                <?php submit_button( "Send" ); ?>
                            </div>
                        </div>

                    </div>
                </form>
                </div>
            </div>

            </div>
            <div id="replyemail-box" class="modal fade upicrm" role="dialog">

                <div class="modal-dialog">

                    <!-- Modal content-->

                    <div class="modal-content">

                    <div class="modal-header">

                        <button type="button" class="close" data-dismiss="modal">&times;</button>

                        <h4 class="modal-title"><?php _e('Reply Email', 'upicrm'); ?></h4>

                    </div>

                    <div class="modal-body">
                    <form method="post" id="send_email_reply_lead">
                        <?php 
                        $getMails = $UpiCRMMails->get_custom();
                        ?>
                        <select name="email_template" id="reply_email_template">
                            <option>Template</option>
                            <?php if (count($getMails) > 0 ) { ?>
                                <?php foreach ($getMails as $mail) { ?>
                                <option value="<?php echo $mail->mail_id; ?>" ><?php echo $mail->mail_event_name; ?></option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                        <div class="mailBox">
                                <div class="mailRecipients">
                                    <div class="mailElem">
                                        <div class="mailElemCnt mailElemCntA">
                                            <span>To</span>
                                        </div>
                                        <div class="mailElemCnt">
                                            <input type="text" id="reply_mailTo" name="reply_mailTo" required data-role="tagsinput" value="<?php echo $lead->lead_email; ?>"/>
                                        </div>
                                        <div class="mailElemCnt mailElemContExpand">
                                            <a id="reply_open-cc">CC</a>
                                        </div>
                                    </div>
                                    <div id="reply_mailIDCc" class="mailElem hidden">
                                        <div class="mailElemCnt mailElemCntA">
                                            <span>CC</span>
                                        </div>
                                        <div class="mailElemCnt">
                                            <input type="text" id="reply_mailCC" name="reply_mailCC" data-role="tagsinput" />
                                        </div>
                                    </div>

                                </div>
                                <div id="mailIDSubject" class="mailElem">
                                        <div class="mailElemCnt">
                                            <input type="text" id="reply_mailSubject" name="reply_mailSubject" placeholder="Subject"/>
                                        </div>
                                </div>
                                <div class="mailBodyBox">
                                    
                                    <?php wp_editor("", 'reply_mailIDBody', array("textarea_name" => 'reply_mailBody', "editor_class" => "mailBody", "media_buttons" => false, "textarea_rows" => 50, "quicktags" => false,  'tinymce' => array("toolbar1" => "bold,italic,bullist,numlist,hr,alignleft,aligncenter,alignright,link,unlink", "block_formats" => false, "toolbar2" => "formatselect,underline,alignjustify,forecolor,pastetext,removeformat,outdent,indent,undo,redo")) ); ?>
                                </div>
                                <div  class="mailboxBottom mailSend">
                                    <input type="hidden" name="action" value="reply_send_email_lead">
                                    <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>">
                                    <input type="hidden" name="parent_email_id" id="parent_email_id" value="">
                                    <?php submit_button( "Send" ); ?>
                                </div>
                            </div>

                        </div>
                    </form>
                    </div>
                </div>

                </div>
        <script>
            var $j = jQuery;
            $j(document).ready(function ($) {
                
                $j(document).on("click", ".tab-panel-nav a", function(e){
                    e.preventDefault();
                    
                    $j('.tab-panel-nav a').removeClass('active');
                    $j('.tab-panel-nav li').removeClass('active');
                    $j(this).addClass('active');
                    $j(this).parent('li').addClass('active');
                    var id = $j(this).attr('href');
                    $j('.tab-panel .tab-panel-contents .tab-content').removeClass('active');
                    $j(id).addClass('active');
                });

                var $databTableLead = $j('#data-table-lead').DataTable({
                    responsive: true,
                    "autoWidth": false,
                    "oLanguage": {
                        "sEmptyTable": "No Log found"
                    }
                });
                $databTableLead.on( 'order.dt search.dt', function () {
                    $databTableLead.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                        cell.innerHTML = i+1;
                    } );
                } ).draw();

                var $databTableLeadEmail = $j('#data-table-lead-email').DataTable({
                    responsive: true,
                    "autoWidth": false,
                    "oLanguage": {
                        "sEmptyTable": "No Email found"
                    }
                });
                $databTableLeadEmail.on( 'order.dt search.dt', function () {
                    $databTableLeadEmail.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                        cell.innerHTML = i+1;
                    } );
                } ).draw();

              
                $j(document).on("click", "*[data-callback='remove-activity']", function () {
                    if (confirm("<?php _e('Are you sure to remove?', 'upicrm'); ?>")) {
                        var GetSelect = $j(this).parents('tr');
                        GetSelect.addClass('upi_processing');
                        var data = {
                            'action': 'remove_activity',
                            'activity_id': $j(this).attr("data-activity_id"),
                        };
                        $j.post(ajaxurl, data, function (response) {
                            $databTableLead
                            .row( GetSelect )
                            .remove()
                            .draw();
                        });
                    }
                });
                $(document).on("click", "#create-email", function(){
                    $("#createemail-box").modal("show")
                });
                $(document).on("click", "#open-cc", function(){
                    $('#mailIDCc').toggle();
                    if(!$('#mailIDCc').is(':visible'))
                    {
                        $('#mailCC').val("");
                        $('#mailCC').tagsinput('removeAll');
                    }
                })

                $(document).on("click", "#reply_open-cc", function(){
                    $('#reply_mailIDCc').toggle();
                    if(!$('#reply_mailIDCc').is(':visible'))
                    {
                        $('#reply_mailCC').val("");
                        $('#reply_mailCC').tagsinput('removeAll');
                    }
                })
              
                $('#mailTo, #reply_mailTo').on('beforeItemAdd', function(event) {
                    var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,5})+$/;
                    if(!event.item.match(mailformat))
                    {
                        event.cancel = true;
                    }
                    return event;
                });
                $('#mailTo, #reply_mailTo').on('beforeItemRemove', function(event) {
                    var $leadEmail = $('#lead_email_id').html();
                    if(!event.item.match == $leadEmail)
                    {
                        event.cancel = true;
                    }
                    return event;
                });
                $('#mailCC, #reply_mailCC').on('beforeItemAdd', function(event) {
                    var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,5})+$/;
                    if(!event.item.match(mailformat))
                    {
                        event.cancel = true;
                    }
                    return event;
                });
                
                $(document).on("change", "#email_template", function(){
                    if($(this).val())
                    {
                        var data = {
                            action: "get_template_data",
                            template_id: $(this).val()
                        };
                        $.ajax({
                        url: ajaxurl,
                        data: data,
                        type: 'POST',
                        datatype: "json",
                        beforeSend: function(){
                            $('#send_email_lead').addClass("upi_processing");
                        },
                        success: function(data){
                            if(data)
                            {
                                $('#mailCC').val(data.mail_cc)
                                $('#mailSubject').val(data.mail_subject)
                                $('#mailIDBody').val(data.mail_content)
                                tinymce.get("mailIDBody").setContent(data.mail_content);

                            }
                            $('#send_email_lead').removeClass("upi_processing");
                        }
                        });
                    }
                });

                $(document).on("change", "#reply_email_template", function(){
                    if($(this).val())
                    {
                        var data = {
                            action: "get_template_data",
                            template_id: $(this).val()
                        };
                        $.ajax({
                        url: ajaxurl,
                        data: data,
                        type: 'POST',
                        datatype: "json",
                        beforeSend: function(){
                            $('#send_email_reply_lead').addClass("upi_processing");
                        },
                        success: function(data){
                            if(data)
                            {
                                $('#reply_mailIDBody').val(data.mail_content)
                                tinymce.get("reply_mailIDBody").setContent(data.mail_content);

                            }
                            $('#send_email_reply_lead').removeClass("upi_processing");
                        }
                        });
                    }
                });

                $(document).on("submit", "#edit_lead_form", function(e){
                    e.preventDefault();
                    var $formdata = new FormData(this); 
                    $.ajax({
                        url: ajaxurl,
                        data: $formdata,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        beforeSend: function(){
                            $('#edit_lead_form').find('input[type="submit"]').addClass("loading");
                        },
                        success: function(data){
                            get_all_activities();
                            $('#edit_lead_form').find('input[type="submit"]').removeClass("loading");
                            $('#lead-data').prepend('<div class="updated"><p>Changes saved successfully</p></div>');
                            setTimeout(function(){
                                $('#lead-data .updated').remove();
                            }, 2000)

                        }
                    });
                })

                $(document).on("submit", "#send_email_lead", function(e){
                    e.preventDefault();
                    var $formdata = new FormData(this); 
                    $.ajax({
                        url: ajaxurl,
                        data: $formdata,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        beforeSend: function(){
                            $('#send_email_lead').find('input[type="submit"]').addClass("loading");
                        },
                        success: function(data){
                            $('.mail-thread').prepend(data);
                            $('#send_email_lead').find('input[type="submit"]').removeClass("loading");
                            $("#createemail-box").modal("hide");
                            get_all_activities();
                            $('#email').prepend('<div class="updated"><p>Send email successfully</p></div>');
                            setTimeout(function(){
                                $('#email .updated').remove();
                            }, 2000)
                        }
                    });
                });

                $(document).on("submit", "#edit_lead_comment_form", function(e){
                    e.preventDefault();
                    var $formdata = new FormData(this); 
                    $.ajax({
                        url: ajaxurl,
                        data: $formdata,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        beforeSend: function(){
                            $('#edit_lead_comment_form').find('input[type="submit"]').addClass("loading");
                        },
                        success: function(data){
                            get_all_activities();
                            $('#edit_lead_comment_form').find('input[type="submit"]').removeClass("loading");
                            $('#note').prepend('<div class="updated"><p>Note saved successfully</p></div>');
                            setTimeout(function(){
                                $('#note .updated').remove();
                            }, 2000)

                        }
                    });
                })

                function get_all_activities()
                {
                    var data = {
                        'lead_id' : <?php echo $_REQUEST['id']; ?>,
                        'action' : "get_all_activities"
                    }
                    $.ajax({
                        url: ajaxurl,
                        data: data,
                        type: 'POST',
                        success: function(data){
                            $('#data-table-lead').dataTable().fnClearTable();
                            $('#data-table-lead').dataTable().fnDestroy();
                            $('#data-table-lead').find('tbody').html(data);
                            $databTableLead = $j('#data-table-lead').DataTable({
                                responsive: true,
                                "autoWidth": false,
                                "oLanguage": {
                                    "sEmptyTable": "No Log found"
                                }
                            });
                        }
                    });
                }
                $(document).on("click", '.mail-item', function(){
                    $(this).toggleClass("open")
                });
                $(document).on("click", '.mail-content > .gmail_quote', function(e){
                    e.stopPropagation()
                    $(this).toggleClass("open")
                });
                $(document).on("click", ".remove-mail-log", function(e){
                    e.stopPropagation();
                    if (confirm("<?php _e('Are you sure to remove?', 'upicrm'); ?>")) {
                        var $id = $(this).data("id");
                        var $element = $(this).parents(".mail-item");
                        $element.addClass("upi_processing")
                        var data = {
                                'action': 'remove_email_log_activity',
                                'activity_id': $id,
                        };
                        $j.post(ajaxurl, data, function (response) {
                            $element.remove();
                        });
                    }
                });
                
                $(document).on("click", '.reply-mail', function(){
                    var $subject = $(this).data("subject");
                    var $parentId = $(this).data("parent");
                    $('#reply_mailSubject').val("Re: "+$subject);
                    $('#parent_email_id').val($parentId);
                    $('#replyemail-box').modal("show");
                });

                $(document).on("submit", "#send_email_reply_lead", function(e){
                    e.preventDefault();
                    var $formdata = new FormData(this); 
                    $.ajax({
                        url: ajaxurl,
                        data: $formdata,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        beforeSend: function(){
                            $('#send_email_reply_lead').find('input[type="submit"]').addClass("loading");
                        },
                        success: function(data){
                            $('.mail-thread').prepend(data);
                            $('#send_email_reply_lead').find('input[type="submit"]').removeClass("loading");
                            $("#replyemail-box").modal("hide");
                            get_all_activities();
                            $('#email').prepend('<div class="updated"><p>Send email successfully</p></div>');
                            setTimeout(function(){
                                $('#email .updated').remove();
                            }, 2000)
                        }
                    });
                });
                $(document).on("keypress", '#send_email_reply_lead, #send_email_lead', function (event) {
                    var keyPressed = event.keyCode || event.which;
                    if (keyPressed === 13) {
                        event.preventDefault();
                        return false;
                    }
                });
            });
        </script>
        <?php
    }
    
   
    function wp_ajax_save_lead_comment_callback() {
            $lead_id = $_REQUEST['lead_id'];
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $leadObj = $UpiCRMLeads->get_by_id($lead_id);
            
            if (!$leadObj->lead_management_comment || ($leadObj->lead_management_comment != $_POST['remarks'])) {
                $updateArr = array();
                $updateArr['lead_management_comment'] = $_POST['lead_remarks'];
                $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                $addLog = array(
                    'lead_id' => $lead_id,
                    'lead_management_comment' => $_POST['lead_remarks']
                );

                $UpiCRMLeadsChangesLog->add($addLog);
            }
            wp_die();
    }
    function wp_ajax_remove_activity_callback()
    {
        $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
        $UpiCRMLeadsChangesLog->remove_activity($_POST['activity_id']);
        die();
    }
    function wp_ajax_send_email_lead_callback()
    {
        $UpiCRMMails = new UpiCRMMails();
        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
        $html = $UpiCRMMails->send_lead_email($_POST);
        echo $html;
        die();
    }
    function wp_ajax_reply_send_email_lead_callback()
    {
        $UpiCRMMails = new UpiCRMMails();
        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
        $html = $UpiCRMMails->send_reply_lead_email($_POST);
        echo $html;
        die();
    }
    function wp_ajax_edit_save_lead_callback()
    {
            $lead_id = $_REQUEST['lead_id'];
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
           
            if ($is_integration == false)
                $lead_content_arr = $_POST;
            else {
                foreach ($_POST as $key => $value) {
                    $lead_content_arr[str_replace("_"," ",$key)] = $value;
                }
            }
            $updateArr['lead_content'] = json_encode($lead_content_arr);
            $emailKey =  $UpiCRMFieldsMapping->get_form_field_id( $_REQUEST['source_id'], 8);
            if($emailKey)
            {
                $updateArr['lead_email'] = $lead_content_arr[$emailKey];
            }
            $UpiCRMLeads->update_by_id($lead_id,$updateArr);
            $log = [
                'lead_id' => $lead_id,
                'lead_change_log_edit_text' => $lead_content_arr,
            ];
            $UpiCRMLeadsChangesLog->add($log);
            
        wp_die();
    }
    function wp_ajax_get_all_activities_callback()
    {
        $lead_id = $_REQUEST['lead_id'];
        $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $logs = $UpiCRMLeadsChangesLog->get_by_lead_id($lead_id);
        $i = 1; foreach ($logs as $log) { ?>
            <tr>
            <td><?php echo $i ?></td>
            <td>
            <?php 
                echo $UpiCRMLeadsChangesLog->get_text_activity($log);
            ?>
            </td>
            <td>
                <?php 
                $text = ''; 
                if ($log->lead_change_log_time) {

                    $text = $log->lead_change_log_time;
    
                } else {
    
                    $text = date("Y-m-d H:i:s");
    
                }
                echo $text
                ?>
            </td>
            <td>
            <span class="glyphicon glyphicon-remove" data-callback="remove-activity" data-activity_id="<?php echo $log->lead_change_log_id; ?>" title="<?php _e('Remove', 'upicrm'); ?>"></span>
            </td>
            </tr>
        <?php $i++; } 
        wp_die();
    }

    function wp_ajax_remove_email_log_activity_callback()
    {
        $id = $_REQUEST['activity_id'];
        $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
        $UpiCRMLeadsChangesLog->remove_email_log_activity($id);
        wp_die();
    }

    function wp_ajax_get_template_data_callback()
    {
        $UpiCRMMails = new UpiCRMMails();
        $template_id = $_POST['template_id'];
        $response = $UpiCRMMails->get_by_id( $template_id );
        $result = array();
        if($response)
        {
            $result['mail_content'] = $response->mail_content;
            $result['mail_subject'] = $response->mail_subject;
            $result['mail_cc'] = $response->mail_cc;
        }
        wp_send_json($result);
    }
    
}
    add_action('wp_ajax_remove_activity', array( new UpiCRMAdminEditLead(), 'wp_ajax_remove_activity_callback'));
    add_action('wp_ajax_send_email_lead', array( new UpiCRMAdminEditLead(), 'wp_ajax_send_email_lead_callback'));
    add_action('wp_ajax_edit_save_lead', array( new UpiCRMAdminEditLead(), 'wp_ajax_edit_save_lead_callback'));
    add_action('wp_ajax_get_all_activities', array( new UpiCRMAdminEditLead(), 'wp_ajax_get_all_activities_callback'));
    add_action('wp_ajax_save_lead_comment', array( new UpiCRMAdminEditLead(), 'wp_ajax_save_lead_comment_callback'));
    add_action('wp_ajax_remove_email_log_activity', array( new UpiCRMAdminEditLead(), 'wp_ajax_remove_email_log_activity_callback'));
    add_action('wp_ajax_reply_send_email_lead', array( new UpiCRMAdminEditLead(), 'wp_ajax_reply_send_email_lead_callback'));
    add_action('wp_ajax_get_template_data', array( new UpiCRMAdminEditLead(), 'wp_ajax_get_template_data_callback'));
    
endif;
?>