<?php
if (!class_exists('UpiCRMAdminAdminLists')):

    class UpiCRMAdminAdminLists {

        /**
         * Page Lead Management table is collected here
         *
         */
        public function RenderLists() {
            /**
             * Array
             */
            global $SourceTypeName;
//    $pageNum = 1;
//
//   if (isset($_GET['page_num'])) {
//
//       $pageNum = (int)$_GET['page_num'];}
//       if ($pageNum == 0){
//
//           }
// if ($perPage == NULL)
//    $perPage = 30;
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'reset':
                        $msg = __('Reset all settings successfully', 'upicrm');
                        $UpiCRMFieldsMapping->empty_all();
                        $UpiCRMUsers->empty_all();
                        break;
                    case 'delete_all':
                        if(isset($_REQUEST['lead_id']) && !empty($_REQUEST['lead_id']))
                        {
                            $msg = __('Delete all leads successfully', 'upicrm');
                            $UpiCRMLeads->empty_all();
                        }
                        break;
                    case 'save_leads':
                        $msg = __('Changes saved successfully', 'upicrm');
                        $this->save_leads_arr();
                        break;
                    case 'assign_lead_users':
                        if(isset($_REQUEST['user_id_all']) && !empty($_REQUEST['user_id_all']))
                        {
                            $msg = __('Assigned lead successfully', 'upicrm');
                            $this->save_assign_lead_users();
                        }
                        break;
                    case 'change_time':
                        //$msg = __('Changes saved successfully','upicrm');
                        $this->change_time();
                        break;
                }
            }
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $getNamesMap = $UpiCRMFieldsMapping->get();
            $check_date = isset($_COOKIE['upicrm_lead_table_days']) ? $_COOKIE['upicrm_lead_table_days'] : 0;
            if ($UpiCRMUsers->get_permission() == 1) {
                $userID = get_current_user_id();
                $choose_view = get_user_meta($userID, 'upicrm_user_view_only_associated_leads');
                if ((isset($_COOKIE['upicrm_lead_table_from_date'])) && isset($_COOKIE['upicrm_lead_table_to_date'])) {
                    if ($choose_view[0] == 1) {
                        $getLeads = $UpiCRMLeads->get($userID, 0, 0, 'DESC', $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    } else {
                        $getLeads = $UpiCRMLeads->get(0, 0, 0, 'DESC', $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    }
                } else {
                    if ($choose_view[0] == 1) {
                        $getLeads = $UpiCRMLeads->get($userID, 0, 0, 'DESC', $check_date);
                    } else {
                        $getLeads = $UpiCRMLeads->get(0, 0, 0, 'DESC', $check_date);
                    }
                }
            }
            if ($UpiCRMUsers->get_permission() == 2) {
                // $upicrm_is_admin = true;
                // $userID = get_current_user_id();
                if ((isset($_COOKIE['upicrm_lead_table_from_date'])) && isset($_COOKIE['upicrm_lead_table_to_date'])) {
                    $getLeads = $UpiCRMLeads->get(0, 0, 0, 'DESC', $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                } else {
                    $getLeads = $UpiCRMLeads->get(0, 0, 0, 'DESC', $check_date);
                }
            }
//    
            ?><!--<pre>--><?php //print_r($getLeads); ?><!--</pre>--><?php
            if (isset($msg)) {
                ?>
                <div class="updated">
                    <p><?php echo $msg; ?></p>
                </div>
                <div class="clearfix"></div><?php
            }
            ?>
            <div class="g-signin2" data-onsuccess="onSignIn"></div>
            <div class="ajax_load2"></div>
            <div id="finish_load" style="display: none;">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 nopad">
                    <div id="ChooseDate">
            <?php _e('Default date range:', 'upicrm'); ?> &nbsp;&nbsp;
                        <a href="admin.php?page=upicrm_allitems&action=change_time&days=1" data-id="1"
                           class="btn btn-default"><?php _e('1 Day', 'upicrm'); ?></a>
                        <a href="admin.php?page=upicrm_allitems&action=change_time&days=7" data-id="7"
                           class="btn btn-default"><?php _e('7 Days', 'upicrm'); ?></a>
                        <a href="admin.php?page=upicrm_allitems&action=change_time&days=30" data-id="30"
                           class="btn btn-default"><?php _e('1 Month', 'upicrm'); ?></a>
                        <a href="admin.php?page=upicrm_allitems&action=change_time&days=90" data-id="90"
                           class="btn btn-default"><?php _e('3 Months', 'upicrm'); ?></a>
                        <a href="admin.php?page=upicrm_allitems&action=change_time&days=0" data-id="0"
                           class="btn btn-default"><?php _e('All Time', 'upicrm'); ?></a>
                        <a href="javascript:void(0);" id="custom_date" data-id="custom"
                           class="btn btn-default"><?php _e('Custom', 'upicrm'); ?></a>
                    </div>
                    <div id="upicrm_date_range">
                        <form action="" method="get">
                            <div class="input-group">
                                <label><?php _e('from:', 'upicrm'); ?></label>
                                <input type="text" name="from_date"
                                       value="<?php
                           if (isset($_COOKIE['upicrm_lead_table_from_date'])) {
                               echo $_COOKIE['upicrm_lead_table_from_date'];
                           }
                           ?>" class="form-control datepicker" data-dateformat="yy-mm-dd">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <div class="input-group">
                                <label><?php _e('to:', 'upicrm'); ?></label>
                                <input type="text" name="to_date" value="<?php
                           if (isset($_COOKIE['upicrm_lead_table_to_date'])) {
                               echo $_COOKIE['upicrm_lead_table_to_date'];
                           }
                           ?>" class="form-control datepicker" data-dateformat="yy-mm-dd">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input type="hidden" name="page" value="upicrm_allitems">
                            <input type="hidden" name="action" value="change_time">
                            <input type="hidden" name="days" value="custom">
                            <input type="submit" class="btn btn-primary" value="<?php _e('Apply', 'upicrm'); ?> "
                                   style="margin-left: -29px;">
                        </form>
                    </div>
                    <br/><br/><br/>
                                <span style="display: none;"> <?php _e('Choose Fields to display:', 'upicrm'); ?><select id="ChooseInputs" multiple="multiple"><?php
                                 $i = 1;
                                 foreach ($list_option as $key => $arr) {
                                     foreach ($arr as $key2 => $value) {
                                         ?>
                                <option  data-count="<?php echo $i; ?>"
                                         value="<?php echo $key; ?>[<?php echo $key2; ?>]"><?php echo $value; ?></option><?php
                                         $i++;
                                     }
                                 }
                                 ?></select><br/><br/> </span>
                    <div class="input-group">
                        <label><?php _e('Set table length (Row count - Default value 25) :', 'upicrm'); ?></label>
                        <input type="text" name="gantulgas" id="gantulgas"
                               value="<?php if (isset($_COOKIE['gantulga'])) echo $_COOKIE['gantulga']; ?>" class="form-control">
                    </div>
                </div>
                <!-- widget grid -->
                <section id="widget-grid" class="">
                    <!-- row -->
                    <div id="LeadTable" class="">
                        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12 nopad"></br>
                                <?php _e('Edit Selected leads', 'upicrm'); ?>:
                            <form method="post" action="admin.php?page=upicrm_allitems&action=save_leads">
                                <br/>
                                <a href="javascript:void(0);" class="btn btn-default trash_all">
                                    <i class="glyphicon glyphicon-remove"></i>
            <?php _e('Trash Selected leads', 'upicrm'); ?>
                                </a>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <!-- <?php _e('Assign Selected leads to', 'upicrm'); ?>
                                :<?php echo $UpiCRMUsers->select_list_no_lead('assigned_to_all', 'user_id_all'); ?>
                                &nbsp;&nbsp;&nbsp; -->
            <?php _e('Change Selected leads status to', 'upicrm'); ?>
                                :<?php echo $UpiCRMLeadsStatus->select_status_list_no_lead('status_to_all', 'lead_status_id_all'); ?>
                                <input type="submit" class="btn btn-primary" value="<?php _e('Apply', 'upicrm'); ?>"/><br/><br/>
                                <!-- Widget ID (each widget will need unique ID)-->
                                <div class="jarviswidget jarviswidget-color-blueLight" id="wid-id-1" data-widget-editbutton="false">
                                    <header>
                                        <span class="widget-icon">
                                            <i class="fa fa-table"></i>
                                        </span>
                                        <h2><?php _e('Leads Table', 'upicrm'); ?></h2>
                                    </header>
                                    <!-- widget div-->
                                    <div>
                                        <!-- widget edit box -->
                                        <div class="jarviswidget-editbox">
                                            <!-- This area used as dropdown edit box -->
                                        </div>
                                        <!-- end widget edit box -->
                                        <!-- widget content -->
                                        <div class="widget-body no-padding">
                                            <table id="upicrm_datatable" class="table table-striped table-bordered" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th class="checklead hasinput" data-class="expand"></th><?php
                                                                foreach ($list_option as $key => $arr) {
                                                                    foreach ($arr as $key2 => $value) {
                                                                        ?>
                                                                <th class="hasinput"
                                                                    data-belongs="<?php echo $key; ?>[<?php echo $key2; ?>]"><?php
                                                                        /**
                                                                         * Add fields to table with search filter, for column User & Lead Status add Dropdown Menu
                                                                         */
                                                                        if ($key2 == "user_id") {
                                                                            echo $UpiCRMUsers->select_list_user_table("user_id");
                                                                        } elseif ($key2 == "lead_status_id") {
                                                                            echo $UpiCRMLeadsStatus->select_list_status_table("lead_status_id");
                                                                        } elseif ($key2 == "actions") {
                                                                            
                                                                        } else {
                                                                            ?><input type="text" class="form-control" data-toggle="tooltip"
                                                                               data-placement="top"
                                                                               title="Input something for filter this column"
                                                                               placeholder="<?php _e('Filter', 'upicrm');
                                                                            echo $value;
                                                                            ?>"  /><?php } ?></th><?php
                                                            }
                                                        }
                                                        ?></tr>
                                                    <tr>
                                                        <th class="checklead" data-class="expand"><input type="checkbox"
                                                                                                         name="checklead"
                                                                                                         class="checklead_checkall"/><?php _e('Select all', 'upicrm'); ?>
                                                        </th><?php
                                                            $i = 0;
                                                            foreach ($list_option as $key => $arr) {
                                                                foreach ($arr as $key2 => $value) {
                                                                    ?>
                                                                <th data-class="expand"
                                                                    data-belongs="<?php echo $key; ?>[<?php echo $key2; ?>]"><?php
                                                            echo $value;
                                                            if ($key == "leads" && $key2 == "lead_id") {
                                                                $count_id = $i;
                                                            }
                                                            $i++;
                                                            ?></th><?php
                                                        }
                                                    }
                                                    ?></thead>
                                                <tbody><?php
                                                        /**
                                                         * View as forming content also in classes/upicrm_ui_builder.php
                                                         */
                                                        foreach ($getLeads as $leadObj) {
                                                            ?>
                                                        <tr data-id="<?php echo $leadObj->lead_id; ?>">
                                                            <td class="checklead"><input type="checkbox"
                                                                                         value="<?php echo $leadObj->lead_id; ?>"
                                                                                         name="checklead_check[]" class="checklead_check"
                                                                                         data-lead_id="<?php echo $leadObj->lead_id; ?>"
                                                                                         id="checklead_check<?php echo $leadObj->lead_id; ?>"/>
                                                            </td><?php
                                                                foreach ($list_option as $key => $arr) {
                                                                    foreach ($arr as $key2 => $value) {
                                                                        ?>
                                                                    <td data-belongs="<?php echo $key; ?>[<?php echo $key2; ?>]"><?php
                                        if (!($key2 == 'source_id' && $leadObj->source_id == '0')) {
                                            echo $UpiCRMUIBuilder->lead_routing($leadObj, $key, $key2, $getNamesMap);
                                        } else {
                                            $begin_remote_form_name = strripos($leadObj->lead_content, 'Form Name') + 12;
                                            $end_remote_form_name = strpos($leadObj->lead_content, ',', $begin_remote_form_name) - 1;
                                            if ($begin_remote_form_name <> 12)
                                                echo('Remote form: ' . (substr($leadObj->lead_content, $begin_remote_form_name, $end_remote_form_name - $begin_remote_form_name)));
                                        }
                                        ?></td><?php
                                    }
                                }
                                ?></tr><?php }
                            ?></tbody>
                                            </table>
                                        </div>
                                        <!-- end widget content -->
                                    </div>
                                    <!-- end widget div -->
                                </div>
                                <!-- end widget -->
                            </form>
                        </article>
                    </div>
                    <!-- end row -->
                    <!-- end row -->
                </section>
            </div>
            <?php require_once get_upicrm_template_path('list_log_modal'); ?>
            <?php require_once get_upicrm_template_path('user_assign_modal'); ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    // alert(jQuery.fn.jquery);
                    //show default options
                    var count_lead = <?php echo $count_id + 1; ?>;
            <?php if (isset($_COOKIE['upicrm_lead_table_days'])) { ?>
                        var cda = $("#ChooseDate a[data-id='<?php echo $_COOKIE['upicrm_lead_table_days']; ?>']");
            <?php } else { ?>
                        var cda = $("#ChooseDate a[data-id='0']");
            <?php } ?>
                    cda.removeClass('btn-default');
                    cda.addClass('btn-primary');
                    function CustomDateOpen() {
                        $("#upicrm_date_range").css("display", "inline-block");
                    }
                    $("#custom_date").click(function () {
                        CustomDateOpen();
                        $("#ChooseDate a").removeClass('btn-primary');
                        $("#ChooseDate a").addClass('btn-default');
                        $("#custom_date").addClass('btn-primary');
                    });
            <?php if (isset($_COOKIE['upicrm_lead_table_days']) && ($_COOKIE['upicrm_lead_table_days'] == "custom")) { ?>
                        CustomDateOpen();
            <?php } ?>
                    var otable = $('#upicrm_datatable').DataTable({
                        order: [[count_lead, "desc"]],
                        columnDefs: [
                            {orderable: false, targets: 0}
                        ],
                        responsive: true,
                        "oLanguage": {
                            "sEmptyTable": "No available lead"
                        }
                    });
                    otable.on('length.dt', function (e, settings, len) {
                        setCookie("gantulga", len, 365);
                        $("#gantulgas").val(len);
                    });
                    var gantulga = getCookie("gantulga");
                    if (gantulga == "" || gantulga == null || isNaN(gantulga)) {
                        gantulga = 25;
                    }
                    otable.page.len(parseInt(gantulga)).draw();
                    $("#gantulgas").on('keyup change', function () {
                        if (this.value == "" || this.value == null || isNaN(this.value) || !isInt(this.value)) {
                            otable.page.len(parseInt(25)).draw();
                        } else
                            otable.page.len(parseInt(this.value)).draw();
                    });

                    $("#upicrm_datatable thead th input[type='text'],#upicrm_datatable thead th select").on('keyup change', function () {
                        if (this.name == 'lead_status_id') {
                            var filt = this.value;
                            var sel = [];
                            $("#upicrm_datatable tbody td option:selected").each(function (index, value) {
                                sel[index] = $(this).text();
            //                        console.log('sel ' + index + ':' + sel[index]);
                                console.log('Value : ' + this.text)

                                if (sel[index] == filt) {
                                    $(this).parent().parent().parent().show();
                                } else if (sel[index] != filt) {
                                    $(this).parent().parent().parent().hide();
                                }
                                if (filt == '') {
                                    $("#upicrm_datatable tbody tr").removeAttr('style');
                                }
                            });
            //                    otable.column($(this).parent().index() + ':visible').search((this.value).find("option:selected")).draw();
                        } else
                            otable.column($(this).parent().index() + ':visible').search(this.value).draw();
                    });

                    var column = otable.columns();
                    column.visible(false);
                    var column = otable.column(0);
                    column.visible(true);
            <?php
            $lead_table_fields = get_option('lead_table_fields');
            if ($lead_table_fields == false) {
                add_option('lead_table_fields', '["content[1]", "content[2]"]');
                echo "console.log( 'Empty Value');";
                $lead_table_fields = get_option('lead_table_fields');
            }
            echo "console.log( 'Start: " . $lead_table_fields . "');";
            echo "var show_option = JSON.parse('" . $lead_table_fields . "');";
            ?>show_option.forEach(function (entry) {
                                            $j("#ChooseInputs option[value='" + entry + "']").prop('selected', true);
                                            var i = $("#ChooseInputs option[value='" + entry + "']").index() + 1;
            //                 $j("#LeadTable *[data-belongs='"+entry+"']").show();
                                            var column = otable.column(i);
                                            column.visible(true);
                                        });
                                        $j('#ChooseInputs').multiselect({
                                            onChange: function (options) {
                                                console.log('This is the exect place to add');
                                                var column = otable.columns();
                                                column.visible(false);
                                                var column = otable.column(0);
                                                column.visible(true);
                                                var brands = $j('#ChooseInputs option:selected');
                                                var selected = [];
                                                //$j("#LeadTable td, #LeadTable th").hide();
                                                var remember_me = new Array();
                                                $j(brands).each(function (index, brand) {
                                                    val = $j(this).val();
                                                    var column = otable.column($(this).attr("data-count"));
                                                    column.visible(true);
                                                    //$j("#LeadTable *[data-belongs='"+val+"']").show();
                                                    remember_me[index] = val;
                                                });
                                                //$j("#LeadTable .checklead").show();
                                                upicrm_set_cookie('upicrm_lead_table_fields', JSON.stringify(remember_me), 30);
                                                console.log('Ajax Started');
                                                var data = {
                                                    'action': 'upicrm_lead_table_fields',
                                                    'setting': upicrm_get_cookie('upicrm_lead_table_fields')
                                                };
                                                console.log(upicrm_get_cookie('upicrm_lead_table_fields'));
                                                jQuery.post(ajaxurl, data, function (response) {
                                                    console.log('Got this from the server: ' + response);
                                                });
                                            }
                                        });
                                        $(".checklead_checkall").click(function () {
                                            if ($(this).prop("checked")) {
                                                $('.checklead_check').prop("checked", true);
                                                $('.checklead_check').closest("tr").find("td").css("background", "#ECF3F8");
                                            } else {
                                                $('.checklead_check').prop("checked", false);
                                                $('.checklead_check').closest("tr").find("td").css("background", "");
                                            }
                                        });
                                        $j(document).on("click", "*[data-callback='trash']", function () {
                                            if (confirm("<?php _e('Trash this lead?', 'upicrm'); ?>")) {
                                                var GetSelect = $j(this).parents('tr');
                                                GetSelect.addClass('upi_processing');
                                                var data = {
                                                    'action': 'trash_lead',
                                                    'lead_id': $j(this).attr("data-lead_id"),
                                                };
                                                $j.post(ajaxurl, data, function (response) {
                                                    otable
                                                    .row( GetSelect )
                                                    .remove()
                                                    .draw();
                                                    //console.log(response);
                                                });
                                            }
                                        });
                                        $(".checklead_check").click(function () {
                                            if ($(this).prop("checked")) {
                                                $(this).closest("tr").find("td").css("background", "#ECF3F8");
                                            } else {
                                                $(this).closest("tr").find("td").css("background", "");
                                            }
                                        });
                                        $j(document).on("click", "*[data-callback='save']", function () {
                                            lead_id = $j(this).attr("data-lead_id");
                                            user_id = $j("select[name='user_id'][data-lead_id='" + lead_id + "']").val();
                                            lead_status_id = $j("select[name='lead_status_id'][data-lead_id='" + lead_id + "']").val();
                                            remarks = $j("textarea[name='lead_remarks'][data-lead_id='" + lead_id + "']").val();
                                            var $tr = $j(this).parents('tr');
                                            $tr.addClass('upi_processing');
                                            var data = {
                                                'action': 'save_lead',
                                                'lead_id': lead_id,
                                                'user_id': user_id,
                                                'lead_status_id': lead_status_id,
                                                'remarks': remarks,
                                            };
                                            $j.post(ajaxurl, data, function (response) {
                                                console.log(response);
                                                $tr.removeClass('upi_processing');
                                                if (response == 1)
                                                    alert("<?php _e('Saved successfully!', 'upicrm'); ?>");
                                                else
                                                    alert("Oh no! Error!");
                                            });
                                        });
                                        $j(document).on("click", "*[data-callback='edit']", function () {
                                            window.location = "admin.php?page=upicrm_edit_lead&id=" + $j(this).attr("data-lead_id");
                                        });
                                        $j(document).on("click", "*[data-callback='request_status']", function () {
                                            lead_id = $j(this).attr("data-lead_id");
                                            var data = {
                                                'action': 'request_status',
                                                'lead_id': lead_id,
                                            };
                                            $j.post(ajaxurl, data, function (response) {
                                                console.log(response);
                                                if (response == 1)
                                                    alert("<?php _e('Status update request was sent successfully!', 'upicrm'); ?>");
                                                else
                                                    alert("Oh no! Error!");
                                            });
                                        });
                                        $j(document).on("click", "*[data-callback='send_master_again']", function () {
                                            lead_id = $j(this).attr("data-lead_id");
                                            var data = {
                                                'action': 'send_lead_again_to_master',
                                                'lead_id': lead_id,
                                            };
                                            $j.post(ajaxurl, data, function (response) {
                                                if (response == 1)
                                                    alert("<?php _e('Lead retransmission success!', 'upicrm'); ?>");
                                                else
                                                    console.log(response);
                                            });
                                        });
                                        /*$j(document).on('keyup change', "*[data-callback='change_lead_status']", function () {
                                            lead_id = $j(this).attr("data-lead_id");
                                            lead_status_id = $j("select[name='lead_status_id'][data-lead_id='" + lead_id + "']").val();
                                            var data = {
                                                'action': 'save_lead',
                                                'lead_id': lead_id,
                                                'lead_status_id': lead_status_id,
                                            };
                                            $j.post(ajaxurl, data, function (response) {
                                                console.log(response);
                                                if (response == 1)
                                                    alert("<?php _e('Saved successfully!', 'upicrm'); ?>");
                                                else
                                                    alert("Oh no! Error!");
                                            });
                                        });*/

                                        $j(document).on("click", "*[data-callback='send_webservice']", function () {
                                            lead_id = $j(this).attr("data-lead_id");
                                            var data = {
                                                'action': 'send_webservice',
                                                'lead_id': lead_id,
                                            };
                                            $j.post(ajaxurl, data, function (response) {
                                                if (response == 1)
                                                    alert("<?php _e('Webservice send success!', 'upicrm'); ?>");
                                                else
                                                    console.log(response);
                                            });
                                        });
                                        
                                       $j(document).on("click", "*[data-callback='send_csv_again']", function () {
                                            lead_id = $j(this).attr("data-lead_id");
                                            var data = {
                                                'action': 'send_csv_again',
                                                'lead_id': lead_id,
                                            };
                                            $j.post(ajaxurl, data, function (response) {
                                                if (response == 1)
                                                    alert("<?php _e('CSV file sent!', 'upicrm'); ?>");
                                                else
                                                    console.log(response);
                                            });
                                        });
                                        
                                        $(".trash_all").click(function () {
                                            if (confirm("<?php _e('Trash all selected leads?', 'upicrm'); ?>")) {
                                                var ids = new Array();
                                                $('#upicrm_datatable').addClass("upi_processing");
                                                $(".checklead_check:checked").each(function (index) {
                                                    ids[index] = $(this).attr("data-lead_id");
                                                    var GetSelect = $(this).closest("tr");
                                                    otable.
                                                    row( GetSelect )
                                                    .remove()
                                                    .draw();
                                                });
                                                var data = {
                                                    'action': 'trash_lead_arr',
                                                    'lead_id': ids,
                                                };
                                                $j.post(ajaxurl, data, function (response) {
                                                    $('#upicrm_datatable').removeClass("upi_processing")
                                                    
                                                });
                                            }
                                            console.log(ids);
                                        });

                                        $(".ajax_load2").hide();
                                        $("#finish_load").fadeIn();
                                    });
                                    
                                    $j(document).on("click", "*[data-callback='show_log']", function () {
                                        $ = $j;
                                        lead_id = $(this).data("lead_id");
                                        $("#log_modal").modal("show");
                                        $("#log_modal #log_loader").show();
                                        $("#log_modal #inside_log").hide();
                                        var data = {
                                            'action': 'get_lead_log',
                                            'lead_id': lead_id,
                                        };
                                        $.post(ajaxurl, data, function (response) {
                                            $("#log_modal #inside_log").html(response);
                                            $("#log_modal #log_loader").hide();
                                            $("#log_modal #inside_log").show();
                                        });
                                    });
                                  
                                    $j(document).on("click", "*[data-callback='assign-lead']", function () {
                                        $ = $j;
                                        lead_id = $(this).data("lead_id");
                                        $("#user_assign_modal").modal("show");
                                        $("#user_assign_modal #user_assign_loader").show();
                                        $("#user_assign_modal #inside_user_assign").hide();
                                        var data = {
                                            'action': 'get_assign_lead',
                                            'lead_id': lead_id,
                                        };
                                        $.post(ajaxurl, data, function (response) {
                                            $("#user_assign_modal #inside_user_assign").html(response);
                                            $('#user_assign_modal select').multiselect({
                                                nonSelectedText: 'Select Team Member'
                                            });
                                            $("#user_assign_modal #user_assign_loader").hide();
                                            $("#user_assign_modal #inside_user_assign").show();
                                        });
                                    });

                                    $j(document).on("submit", "#assign_lead_users", function(e){
                                        e.preventDefault();
                                        var $formdata = new FormData(this); 
                                        var $lead_id = $('#assign_lead_users').find('input[name="lead_id"]').val();
                                        $.ajax({
                                            url: ajaxurl,
                                            data: $formdata,
                                            processData: false,
                                            contentType: false,
                                            type: 'POST',
                                            beforeSend: function(){
                                                $('#assign_lead_users input[type="submit"]').addClass("loading");
                                            },
                                            success: function(data){
                                                var $html = '<div class="updated"><p>Assigned lead successfully</p></div>';
                                                $('#assign_lead_users').before($html);
                                                $('#upicrm_datatable').find('tr[data-id="'+$lead_id+'"] td[data-belongs="special[user_id]"]').html(data)
                                                $("#user_assign_modal").modal("hide")
                                            }
                                        });
                                    })
                                    function getCookie(cname) {
                                        var name = cname + "=";
                                        var ca = document.cookie.split(';');
                                        for (var i = 0; i < ca.length; i++) {
                                            var c = ca[i];
                                            while (c.charAt(0) == ' ') {
                                                c = c.substring(1);
                                            }
                                            if (c.indexOf(name) == 0) {
                                                return c.substring(name.length, c.length);
                                            }
                                        }
                                        return "";
                                    }
                                    function setCookie(cname, cvalue, exdays) {
                                        var d = new Date();
                                        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                                        var expires = "expires=" + d.toUTCString();
                                        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
                                    }
                                    function isInt(value) {
                                        return !isNaN(value) &&
                                                parseInt(Number(value)) == value && !isNaN(parseInt(value, 10));
                                    }
            </script><?php
        }


        function change_time() {
            if (isset($_GET['days'])) {
                @setcookie("upicrm_lead_table_days", $_GET['days']);
                $_COOKIE['upicrm_lead_table_days'] = $_GET['days'];
            }
            if (isset($_GET['from_date'])) {
                @setcookie("upicrm_lead_table_from_date", $_GET['from_date']);
                $_COOKIE['upicrm_lead_table_from_date'] = $_GET['from_date'];
            }
            if (isset($_GET['to_date'])) {
                @setcookie("upicrm_lead_table_to_date", $_GET['to_date']);
                $_COOKIE['upicrm_lead_table_to_date'] = $_GET['to_date'];
            }
        }


        function wp_ajax_trash_lead_callback() {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeads->trash_lead($_POST['lead_id']);
            die();
        }

        function wp_ajax_trash_lead_arr_callback() {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeads->trash_leads($_POST['lead_id']);
            die();
        }

        function wp_ajax_save_lead_callback() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $leadObj = $UpiCRMLeads->get_by_id($_POST['lead_id']);
            $updateArr = array();

            $updateArr['lead_management_comment'] = $_POST['remarks'];

            if (isset($_POST['user_id'])) {
                if ($leadObj->user_id != $_POST['user_id']) {
                    $updateArr['user_id'] = $_POST['user_id'];
                }
            }
            if (isset($_POST['lead_status_id'])) {
                if ($leadObj->lead_status_id != $_POST['lead_status_id']) {
                    $updateArr['lead_status_id'] = $_POST['lead_status_id'];
                }
            }

            $UpiCRMLeads->update_by_id($_POST['lead_id'], $updateArr);
            $user = get_user_by('id', $_POST['user_id']);

            if (isset($_POST['user_id']) && ($leadObj->user_id != $_POST['user_id'])) {
                $UpiCRMMails->send($_POST['lead_id'], "change_user", $user->user_email);
                $UpiCRMLeadsChangesLog->add($_POST);
            }

            if ((isset($_POST['lead_status_id'])) && $leadObj->lead_status_id != $_POST['lead_status_id']) {
                $UpiCRMMails->send($_POST['lead_id'], "change_lead_status", $user->user_email);
                $UpiCRMLeadsChangesLog->add($_POST);
                //@todo need test to send email
            }
            
            if ($leadObj->lead_management_comment != $_POST['remarks']) {
                $log->lead_management_comment = $_POST['remarks'];
                $log->lead_id = $_POST['lead_id'];
                $UpiCRMLeadsChangesLog->add($log);
            }
            
            
            echo 1;
            die();
        }

        function wp_ajax_save_lead_user_arr_callback() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMLeads = new UpiCRMLeads();
            $user_id = $_POST['user_id'];

            foreach ($_POST['lead_id'] as $lead_id) {
                $updateArr = array();
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                if ($leadObj->user_id != $user_id) {
                    $updateArr['user_id'] = $user_id;
                    $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                    $user = get_user_by('id', $user_id);
                    $UpiCRMMails->send($lead_id, "change_user", $user->user_email);
                }
            }
            echo 1;
            die();
        }

        function wp_ajax_save_lead_status_arr_callback() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMLeads = new UpiCRMLeads();
            $lead_status_id = $_POST['lead_status_id'];
            foreach ($_POST['lead_id'] as $lead_id) {
                $updateArr = array();
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                
                if ($leadObj->lead_status_id != $lead_status_id) {
                    $updateArr['lead_status_id'] = $lead_status_id;
                    $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                    $user = get_user_by('id', $leadObj->user_id);
                    $UpiCRMMails->send($lead_id, "change_lead_status", $user->user_email);
                }
            }
            echo 1;
            die();
        }

        function save_leads_arr() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $lead_status_id = $_POST['lead_status_id_all'];
            $user_id = $_POST['user_id_all'];

            if ($lead_status_id > 0) {
                foreach ($_POST['checklead_check'] as $lead_id) {
                    $updateArr = array();
                    $leadObj = $UpiCRMLeads->get_by_id($lead_id);
//                print_r($leadObj);
                    if ($leadObj->lead_status_id != $lead_status_id) {
                        $updateArr['lead_status_id'] = $lead_status_id;
                        $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                        $user = get_user_by('id', $leadObj->user_id);
                        $UpiCRMMails->send($lead_id, "change_lead_status", $user->user_email);
                        $addLog = [
                            'lead_status_id' => $lead_status_id,
                            'lead_id' => $lead_id,
                        ];
                        $UpiCRMLeadsChangesLog->add($addLog);
                    }
                }
            }

            if ($user_id > 0) {
                foreach ($_POST['checklead_check'] as $lead_id) {
                    $updateArr = array();
                    $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                    if ($leadObj->user_id != $user_id) {
                        $updateArr['user_id'] = $user_id;
                        $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                        $user = get_user_by('id', $user_id);
                        $UpiCRMMails->send($lead_id, "change_user", $user->user_email);
                        $addLog = [
                            'user_id' => $user_id,
                            'lead_id' => $lead_id,
                        ];
                        $UpiCRMLeadsChangesLog->add($addLog);
                    }
                }
            }
           /* if (isset($_POST['checklead_check'])) {
                foreach ($_POST['checklead_check'] as $lead_id) {
                    $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                    $UpiCRMLeadsChangesLog->add($leadObj);
                }
            }*/
            
        }
        function wp_ajax_assign_lead_users_callback()
        {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $user_id_all = $_POST['user_id_all'];
            
            $lead_id = $_POST['lead_id'];
            $updateArr = array();
            if ($user_id_all) {
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                    $existing_users = $UpiCRMLeads->get_all_lead_users($lead_id, $leadObj->user_id);
                    $remove_users= array_diff($existing_users,$user_id_all);

                    foreach($remove_users as $u)
                    {
                        if($u == get_current_user_id() )
                        {
                            $user = get_user_by('id', $leadObj->user_id);
                            $UpiCRMMails->send($lead_id, "change_user", $user->user_email);
                            $addLog = [
                                'user_id' => $leadObj->user_id,
                                'lead_id' => $lead_id
                            ];
                            $UpiCRMLeadsChangesLog->add($addLog);
                        }
                        $UpiCRMLeads->remove_existing_lead_users( $lead_id, $u);
                    }
                    $skip_users = array_diff($user_id_all,$existing_users);
                                       
                    foreach($skip_users as $user_id)
                    {
                        if($leadObj->user_id != $user_id)
                        {
                            $updateArr['user_id'] = $user_id;
                            $updateArr['lead_id'] = $lead_id;
                            $UpiCRMLeads->assign_lead_to_users($updateArr);
                            $user = get_user_by('id', $user_id);
                            $UpiCRMMails->send($lead_id, "change_user", $user->user_email);
                            $addLog = [
                                'user_id' => $user_id,
                                'lead_id' => $lead_id,
                            ];
                            $UpiCRMLeadsChangesLog->add($addLog);
                        }
                    }
            }
            $text = '<a href="javascript:void(0)" data-callback="assign-lead" data-lead_id="' . $lead_id . '">'.$UpiCRMUsers->get_by_id_all_lead_user_name($lead_id)."</a>";
            echo $text;
            wp_die();
          
        }
        function wp_ajax_request_status_callback() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMLeads = new UpiCRMLeads();
            $leadObj = $UpiCRMLeads->get_by_id($_POST['lead_id']);
            $user = get_user_by('id', $leadObj->user_id);
            $UpiCRMMails->send($_POST['lead_id'], "request_status", $user->user_email);
            echo 1;
            die();
        }

        function wp_ajax_send_lead_again_to_master_callback() {
            $UpiCRMIntegrationsLib = new UpiCRMIntegrationsLib();
            $UpiCRMIntegrationsLib->send_slave($_POST['lead_id']);
            echo 1;
            die();
        }

        function wp_ajax_send_webservice_callback() {
            $UpiCRMWebServiceLib = new UpiCRMWebServiceLib();
            $UpiCRMWebServiceLib->send($_POST['lead_id'], 0);
            $UpiCRMWebServiceLib->send($_POST['lead_id'], 1);
            $UpiCRMWebServiceLib->send($_POST['lead_id'], 2);
            echo 1;
            die();
        }
        
        function wp_ajax_get_lead_log_callback() {
            $UpiCRMLeadsChangesLog = new UpiCRMLeadsChangesLog();
            $logs = $UpiCRMLeadsChangesLog->get_by_lead_id($_POST['lead_id']);
            if ($logs) {
                foreach ($logs as $log) {
                    echo $UpiCRMLeadsChangesLog->get_text($log)."<br />";
                }
            } else {
                echo __('No log found', 'upicrm');
            }
            die();
        }
        function wp_ajax_get_assign_lead_callback()
        {
            $UpiCRMUsers = new UpiCRMUsers();
            $lead_ID = $_POST['lead_id'];
            $html = '<form method="post" id="assign_lead_users">';
            $html .= '<label>'.__('Assign Selected leads to', 'upicrm').'</label>';
            $html .= $UpiCRMUsers->select_list_no_lead_multiple('assigned_to_all', 'user_id_all[]', $lead_ID);
            $html .= '<input type="hidden" name="lead_id" value="'. $lead_ID. '">';
            $html .= '<input type="hidden" name="action" value="assign_lead_users">';
            $html .='<input type="submit" class="btn btn-primary" value="'.__('Assign', 'upicrm').'"/>';
            $html .='</form';
            echo $html;
            die(); 
        }
        function wp_ajax_send_csv_again_callback() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMMails->send_csv($_POST['lead_id']);
            echo 1;
            die();
        }

    }

// add_action( 'wp_ajax_excel_output', array(new UpiCRMAdminAdminLists,'wp_ajax_excel_output_callback'));

    add_action('wp_ajax_trash_lead', array(new UpiCRMAdminAdminLists, 'wp_ajax_trash_lead_callback'));
    add_action('wp_ajax_trash_lead_arr', array(new UpiCRMAdminAdminLists, 'wp_ajax_trash_lead_arr_callback'));
    add_action('wp_ajax_save_lead', array(new UpiCRMAdminAdminLists, 'wp_ajax_save_lead_callback'));
    add_action('wp_ajax_save_lead_user_arr', array(new UpiCRMAdminAdminLists, 'wp_ajax_save_lead_user_arr_callback'));
    add_action('wp_ajax_save_lead_status_arr', array(new UpiCRMAdminAdminLists, 'wp_ajax_save_lead_status_arr_callback'));
    add_action('wp_ajax_request_status', array(new UpiCRMAdminAdminLists, 'wp_ajax_request_status_callback'));
    add_action('wp_ajax_send_lead_again_to_master', array(new UpiCRMAdminAdminLists, 'wp_ajax_send_lead_again_to_master_callback'));
    add_action('wp_ajax_send_webservice', array(new UpiCRMAdminAdminLists, 'wp_ajax_send_webservice_callback'));
    add_action('wp_ajax_get_lead_log', array(new UpiCRMAdminAdminLists, 'wp_ajax_get_lead_log_callback'));
    add_action('wp_ajax_send_csv_again', array(new UpiCRMAdminAdminLists, 'wp_ajax_send_csv_again_callback'));
    add_action('wp_ajax_get_assign_lead', array(new UpiCRMAdminAdminLists, 'wp_ajax_get_assign_lead_callback'));
    add_action('wp_ajax_assign_lead_users', array(new UpiCRMAdminAdminLists, 'wp_ajax_assign_lead_users_callback'));

    
    add_action('wp_ajax_upicrm_lead_table_fields', function () {
        $string = $_POST['setting'];
        update_option('lead_table_fields', $string);
        echo 'Update: ' . $string;
        die();
    });
endif;