<?php
if (!class_exists('UpiCRMAdminAdminLists_Trash')):

    class UpiCRMAdminAdminLists_Trash {

        /**
         * Page Lead Management table is collected here
         *
         */
        public function RenderLists() {
            /**
             * Array
             */
            global $SourceTypeName;

            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
    
                    case 'change_time':
                        //$msg = __('Changes saved successfully','upicrm');
                        $this->change_time();
                        break;
                }
            }
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $getNamesMap = $UpiCRMFieldsMapping->get();
            
            $check_date = isset($_COOKIE['upicrm_trash_lead_table_days']) ? $_COOKIE['upicrm_trash_lead_table_days'] : 0;
            if ($UpiCRMUsers->get_permission() == 1) {
                $userID = get_current_user_id();
                $choose_view = get_user_meta($userID, 'upicrm_user_view_only_associated_leads');
                if ((isset($_COOKIE['upicrm_trash_lead_table_from_date'])) && isset($_COOKIE['upicrm_trash_lead_table_to_date'])) {
                    if ($choose_view[0] == 1) {
                        $getLeads = $UpiCRMLeads->get_trash($userID, 0, 0, 'DESC', $check_date, $_COOKIE['upicrm_trash_lead_table_from_date'], $_COOKIE['upicrm_trash_lead_table_to_date']);
                    } else {
                        $getLeads = $UpiCRMLeads->get_trash(0, 0, 0, 'DESC', $check_date, $_COOKIE['upicrm_trash_lead_table_from_date'], $_COOKIE['upicrm_trash_lead_table_to_date']);
                    }
                } else {
                    if ($choose_view[0] == 1) {
                        $getLeads = $UpiCRMLeads->get_trash($userID, 0, 0, 'DESC', $check_date);
                    } else {
                        $getLeads = $UpiCRMLeads->get_trash(0, 0, 0, 'DESC', $check_date);
                    }
                }
            }
            if ($UpiCRMUsers->get_permission() == 2) {
             
                if ((isset($_COOKIE['upicrm_trash_lead_table_from_date'])) && isset($_COOKIE['upicrm_trash_lead_table_to_date'])) {
                    $getLeads = $UpiCRMLeads->get_trash(0, 0, 0, 'DESC', $check_date, $_COOKIE['upicrm_trash_lead_table_from_date'], $_COOKIE['upicrm_trash_lead_table_to_date']);
                } else {
                    $getLeads = $UpiCRMLeads->get_trash(0, 0, 0, 'DESC', $check_date);
                }
            } 
            ?>
            <?php
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
                        <a href="admin.php?page=upicrm_allitems_trash&action=change_time&days=1" data-id="1"
                           class="btn btn-default"><?php _e('1 Day', 'upicrm'); ?></a>
                        <a href="admin.php?page=upicrm_allitems_trash&action=change_time&days=7" data-id="7"
                           class="btn btn-default"><?php _e('7 Days', 'upicrm'); ?></a>
                        <a href="admin.php?page=upicrm_allitems_trash&action=change_time&days=30" data-id="30"
                           class="btn btn-default"><?php _e('1 Month', 'upicrm'); ?></a>
                        <a href="admin.php?page=upicrm_allitems_trash&action=change_time&days=90" data-id="90"
                           class="btn btn-default"><?php _e('3 Months', 'upicrm'); ?></a>
                        <a href="admin.php?page=upicrm_allitems_trash&action=change_time&days=0" data-id="0"
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
                           if (isset($_COOKIE['upicrm_trash_lead_table_from_date'])) {
                               echo $_COOKIE['upicrm_trash_lead_table_from_date'];
                           }
                           ?>" class="form-control datepicker" data-dateformat="yy-mm-dd">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <div class="input-group">
                                <label><?php _e('to:', 'upicrm'); ?></label>
                                <input type="text" name="to_date" value="<?php
                           if (isset($_COOKIE['upicrm_trash_lead_table_to_date'])) {
                               echo $_COOKIE['upicrm_trash_lead_table_to_date'];
                           }
                           ?>" class="form-control datepicker" data-dateformat="yy-mm-dd">
                                <i class="fa fa-calendar"></i>
                            </div>
                            <input type="hidden" name="page" value="upicrm_allitems_trash">
                            <input type="hidden" name="action" value="change_time">
                            <input type="hidden" name="days" value="custom">
                            <input type="submit" class="btn btn-primary" value="<?php _e('Apply', 'upicrm'); ?> "
                                   style="margin-left: -29px;">
                        </form>
                    </div>
                    <span style="display:none;"><br/><br/><br/>
                                 <?php _e('Choose Fields to display:', 'upicrm'); ?><select id="ChooseInputs" multiple="multiple"><?php
                                 $i = 1;
                                 foreach ($list_option as $key => $arr) {
                                     foreach ($arr as $key2 => $value) {
                                         ?>
                                <option  data-count="<?php echo $i; ?>"
                                         value="<?php echo $key; ?>[<?php echo $key2; ?>]"><?php echo $value; ?></option><?php
                                         $i++;
                                     }
                                 }
                                 ?></select>
                    </span>
                    <br/><br/>
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
                        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12 nopad">
                            <form method="post" action="admin.php?page=upicrm_allitems_trash&action=save_leads">
                                <br/>
                                <a href="javascript:void(0);" class="btn btn-default delete_all">
                                    <i class="glyphicon glyphicon-remove"></i>
                                    <?php _e('Remove Selected leads', 'upicrm'); ?>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-default restore_all">
                                    <i class="glyphicon glyphicon-refresh"></i>
                                    <?php _e('Restore Selected leads', 'upicrm'); ?>
                                </a>
                                <br/><br/>
            
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
                                                        <tr>
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
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    
                    //show default options
                    var count_lead = <?php echo $count_id + 1; ?>;
            <?php if (isset($_COOKIE['upicrm_trash_lead_table_days'])) { ?>
                        var cda = $("#ChooseDate a[data-id='<?php echo $_COOKIE['upicrm_trash_lead_table_days']; ?>']");
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
            <?php if (isset($_COOKIE['upicrm_trash_lead_table_days']) && ($_COOKIE['upicrm_trash_lead_table_days'] == "custom")) { ?>
                        CustomDateOpen();
            <?php } ?>
                    var otable = $('#upicrm_datatable').DataTable({
                        order: [[count_lead, "desc"]],
                        columnDefs: [
                            {orderable: false, targets: 0}
                        ],
                        responsive: true,
                        "oLanguage": {
                            "sEmptyTable": "No trash lead available"
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
                      
                        } else
                            otable.column($(this).parent().index() + ':visible').search(this.value).draw();
                    });

                    var column = otable.columns();
                    column.visible(false);
                    var column = otable.column(0);
                    column.visible(true);
            <?php
            $trash_lead_table_fields = get_option('trash_lead_table_fields');
            if ($trash_lead_table_fields == false) {
                add_option('trash_lead_table_fields', '["content[1]", "content[7]", "content[7]", "leads[time]", "special[trash_actions]"]');
                echo "console.log( 'Empty Value');";
                $trash_lead_table_fields = get_option('trash_lead_table_fields');
            }
            echo "console.log( 'Start: " . $trash_lead_table_fields . "');";
            echo "var show_option = JSON.parse('" . $trash_lead_table_fields . "');";
            ?>show_option.forEach(function (entry) {
                                            $j("#ChooseInputs option[value='" + entry + "']").prop('selected', true);
                                            var i = $("#ChooseInputs option[value='" + entry + "']").index() + 1;
         
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
                                               
                                                var remember_me = new Array();
                                                $j(brands).each(function (index, brand) {
                                                    val = $j(this).val();
                                                    var column = otable.column($(this).attr("data-count"));
                                                    column.visible(true);
                                                   
                                                    remember_me[index] = val;
                                                });
                                                
                                                upicrm_set_cookie('upicrm_trash_lead_table_fields', JSON.stringify(remember_me), 30);
                                                console.log('Ajax Started');
                                                var data = {
                                                    'action': 'upicrm_trash_lead_table_fields',
                                                    'setting': upicrm_get_cookie('upicrm_trash_lead_table_fields')
                                                };
                                                console.log(upicrm_get_cookie('upicrm_trash_lead_table_fields'));
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
                                        $j(document).on("click", "*[data-callback='remove']", function () {
                                            if (confirm("<?php _e('remove this lead?', 'upicrm'); ?>")) {
                                                var GetSelect = $j(this).parents('tr');
                                                GetSelect.addClass('upi_processing');
                                                var data = {
                                                    'action': 'remove_lead',
                                                    'lead_id': $j(this).attr("data-lead_id"),
                                                };
                                                $j.post(ajaxurl, data, function (response) {
                                                    otable
                                                    .row( GetSelect )
                                                    .remove()
                                                    .draw();
                                                });
                                            }
                                        });
                                        $j(document).on("click", "*[data-callback='restore_lead']", function () {
                                            if (confirm("<?php _e('Are you sure to restore this lead?', 'upicrm'); ?>")) {
                                                var GetSelect = $j(this).parents('tr');
                                                GetSelect.addClass('upi_processing');
                                                var data = {
                                                    'action': 'restore_lead',
                                                    'lead_id': $j(this).attr("data-lead_id"),
                                                };
                                                $j.post(ajaxurl, data, function (response) {
                                                    otable
                                                    .row( GetSelect )
                                                    .remove()
                                                    .draw();
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
                                       
                                        $(".delete_all").click(function () {
                                            if (confirm("<?php _e('Remove all selected leads?', 'upicrm'); ?>")) {
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
                                                    'action': 'remove_lead_arr',
                                                    'lead_id': ids,
                                                };
                                                $j.post(ajaxurl, data, function (response) {
                                                    $('#upicrm_datatable').removeClass("upi_processing")
                                                    // GetSelect.closest("tr").fadeOut();
                                                });
                                            }
                                          
                                        });

                                        $(".restore_all").click(function () {
                                            if (confirm("<?php _e('Restore all selected leads?', 'upicrm'); ?>")) {
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
                                                    'action': 'restore_lead_arr',
                                                    'lead_id': ids,
                                                };
                                                $j.post(ajaxurl, data, function (response) {
                                                    $('#upicrm_datatable').removeClass("upi_processing")
                                                    // GetSelect.closest("tr").fadeOut();
                                                });
                                            }
                                          
                                        });
                                        

                                        $(".ajax_load2").hide();
                                        $("#finish_load").fadeIn();
                                    });
                                 
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
                @setcookie("upicrm_trash_lead_table_days", $_GET['days']);
                $_COOKIE['upicrm_trash_lead_table_days'] = $_GET['days'];
            }
            if (isset($_GET['from_date'])) {
                @setcookie("upicrm_trash_lead_table_from_date", $_GET['from_date']);
                $_COOKIE['upicrm_trash_lead_table_from_date'] = $_GET['from_date'];
            }
            if (isset($_GET['to_date'])) {
                @setcookie("upicrm_trash_lead_table_to_date", $_GET['to_date']);
                $_COOKIE['upicrm_trash_lead_table_to_date'] = $_GET['to_date'];
            }
        }

        function wp_ajax_remove_lead_callback() {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeads->remove_lead($_POST['lead_id']);
            die();
        }

        function wp_ajax_remove_lead_arr_callback() {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeads->remove_leads($_POST['lead_id']);
            die();
        }     
        function wp_ajax_restore_lead_callback()
        {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeads->restore_lead($_POST['lead_id']);
            die();
        }
        function wp_ajax_restore_lead_arr_callback()
        {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeads->restore_lead($_POST['lead_id']);
            die();
        }
           
    }


    add_action('wp_ajax_remove_lead', array(new UpiCRMAdminAdminLists_Trash, 'wp_ajax_remove_lead_callback'));
    add_action('wp_ajax_remove_lead_arr', array(new UpiCRMAdminAdminLists_Trash, 'wp_ajax_remove_lead_arr_callback'));
    add_action('wp_ajax_restore_lead', array(new UpiCRMAdminAdminLists_Trash, 'wp_ajax_restore_lead_callback'));
    add_action('wp_ajax_restore_lead_arr', array(new UpiCRMAdminAdminLists_Trash, 'wp_ajax_restore_lead_arr_callback'));

    
    add_action('wp_ajax_upicrm_trash_lead_table_fields', function () {
        $string = $_POST['setting'];
        update_option('trash_lead_table_fields', $string);
        echo 'Update: ' . $string;
        die();
    });
endif;