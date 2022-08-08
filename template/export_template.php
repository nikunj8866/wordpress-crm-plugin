<?php
$UpiCRMFields = new UpiCRMFields();
$field_on = @get_option('upicrm_export_show_fields');
?>

<?php
if(isset($_POST['filteredExport']) && $_POST['filteredExport'] <> ''){
    if($_POST['fields'] == ''){
        echo '<script type="text/javascript">alert("Please choose at least one field to apply filters on export!");</script>';
    }
    else
        upicrm_filtered_excel_output();
}

global $wpdb;
$table_prefix = $wpdb->prefix;
?>

        <?php /*<h2>Export</h2>
        <form name="export_leads" method="post" action="" >
            <<strong>Select Date range</strong><br>
            <input type="text" name="date_start" class="datepicker" placeholder="Start">
            &nbsp;
            <input type="text" name="date_end" class="datepicker" placeholder="End">
            <br><br>
            <strong>Select Fields</strong><br><br />
            <?php
            //fields from Name to Received From
            $fields = $wpdb->get_results("Select * from ".$table_prefix."upicrm_fields order by field_id ASC");
            ?>
            <input type="checkbox" name="all_fields" id="all_field_checkbox" value="all">&nbsp;Select All
            <br><br />
            <ul id="checkbox_list">
                <?php for($i=0;$i<count($fields);$i++){?>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="<?php echo ($fields[$i]->field_id);?>">&nbsp;<?php echo ($fields[$i]->field_name);?></li>
                <?php }?>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="lead_id">&nbsp;ID</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="time">&nbsp;Timestamp</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="user_referer">&nbsp;Referer</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="source_id">&nbsp;Form Name</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="user_id">&nbsp;Assigned To</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="lead_status_id">&nbsp;Lead Status</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="lead_management_comment">&nbsp;Lead Managment comment</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="utm_source">&nbsp;UTM Source</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="utm_medium">&nbsp;UTM Medium</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="utm_term">&nbsp;UTM Term</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="utm_content">&nbsp;UTM Content</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="utm_campaign">&nbsp;UTM Campaign</li>
                <li style="list-style:none;"><input type="checkbox" name="fields[]" class="" value="integration_domain">&nbsp;Remote server domain</li>
            </ul>
            <br><br>
            <strong>Status</strong>&nbsp;
            <?php
            $statuses = $wpdb->get_results("Select * from ".$table_prefix."upicrm_leads_status order by lead_status_name ASC");
            ?>
            <select name="status">
                <option value="">Please choose</option>
                <?php for($i=0;$i<count($statuses);$i++){?>
                    <option value="<?php echo ($statuses[$i]->lead_status_id);?>"><?php echo ($statuses[$i]->lead_status_name);?></option>
                <?php }?>
            </select>
            <br><br>
            <input type="submit" name="filteredExport" value="Export filtered leads data to Excel">
        </form>
        <br /><br />
         
         */
        ?>
        <br /><br />
        <form id="excel_exp" action="admin.php?page=upicrm_export&action=excel_output" method="post">
            <input type="text" name="date_start2" class="datepicker" value="<?php echo $_POST['date_start2'] ?>" placeholder="<?php _e('Start ','upicrm'); ?>" autocomplete="off">
            &nbsp;
            <input type="text" name="date_end2" class="datepicker" value="<?php echo $_POST['date_end2'] ?>" placeholder="<?php _e('End ','upicrm'); ?>" autocomplete="off">
            &nbsp;
            <a href="javascript:$j('#excel_exp').submit();" class="btn btn-default">
                <i class="glyphicon glyphicon-export"></i> <?php _e('Export leads data to Excel ','upicrm'); ?>
            </a>
        </form>
        <br /><br />
        <a href="admin.php?page=upicrm_export&action=export_csv" class="btn btn-default">
            <i class="glyphicon glyphicon-export"></i> <?php _e('Export all leads data to CSV ','upicrm'); ?>
        </a>
        
        <br /><br />
        
        <a href="admin.php?page=upicrm_export&action=export_last_csv" class="btn btn-default">
            <i class="glyphicon glyphicon-export"></i> <i class="glyphicon glyphicon-hand-up"></i> <?php _e('Export only latest leads to CSV ','upicrm'); ?>
        </a>
        <br /><br /><br />
        <h2><?php _e('Custom Export ','upicrm'); ?></h2>
        
        <?php $link = get_site_url().'?upicrm_custom_export_csv=1'; ?>
        <?php _e('Custom Export Link:','upicrm'); ?> <a href="<?= $link ?>" target="_blank"><?= $link; ?></a><br /><br />
        <?php _e('Parameters:','upicrm'); ?><br />
        <ul class="upicrm_ul_list">
            <li><strong>only_latest</strong> = <?php _e('Show only last leads. Optional values: 0,1 (Default: 0)','upicrm'); ?></li>
            <li><strong>custom_order</strong> = <?php _e('View in custom order. Optional values: 0,1 (Default: 1)','upicrm'); ?></li>
            <li><strong>show_all_fields</strong> = <?php _e('Show all fields or show only the selected. Optional values: 0,1 (Default: 0)','upicrm'); ?></li>
        </ul>
        <br />
        <?php $link = $link."&only_latest=1&custom_order=0&show_all_fields=1"; ?>
        <?php _e('Example:','upicrm'); ?> <a href="<?= $link ?>" target="_blank"><?= $link; ?></a>
        <br /><br /><br />
        
        <form action="admin.php?page=upicrm_export&action=save_custom" method="post">
            <div id="upicrm_fields_order" style="display: none;">
                <?php 
                $i=0;
                foreach ($UpiCRMFields->get_as_array(2) as $key => $value) { ?>
                    <input type="text" name="order[<?= $i; ?>]" value="<?= $key ?>" />
                <?php
                    $i++;
                } ?>
            </div>
            
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save','upicrm'); ?>">
            <br /><br />
            <ul id="upicrm_fields_sortable">
                <?php foreach ($UpiCRMFields->get_as_array(2) as $key => $value) { ?>
                    <li data-id="<?= $key ?>" class="pointer-click">
                        <label>
                            <input type="checkbox" name="show[]" value="<?= $key ?>" <?php checked(isset($field_on[$key])); ?> />&nbsp;&nbsp;
                            <?php echo $value; ?>
                        </label>
                        &nbsp; <i class="fa fa-arrows arr"></i>
                    </li>
                <?php } ?>
            </ul>
            <br />
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save','upicrm'); ?>">
        </form>
<!-- widget grid -->

<script type="text/javascript">
    $j(document).ready(function ($) {
        $j( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
        $j( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );

        $j("#all_field_checkbox").click(function () {
            if ($j("#all_field_checkbox").is(':checked')) {
                $j("#checkbox_list input[type=checkbox]").each(function () {
                    $j(this).prop("checked", true);
                });

            } else {
                $j("#checkbox_list input[type=checkbox]").each(function () {
                    $j(this).prop("checked", false);
                });
            }
        });
        
        $("#upicrm_fields_sortable").sortable({
            update: function() {
                $("#upicrm_fields_order").html("");
                var i=0;
                $('#upicrm_fields_sortable li').each( function(e) {
                    $("#upicrm_fields_order").append('<input type="text" name="order['+i+']" value="'+$(this).data("id")+'" />');
                    i++;
                });
            }
        });
        $("ul, li").disableSelection();
    });
</script>
<style>
    .ui-tabs-vertical { width: 55em; }
    .ui-tabs-vertical .ui-tabs-nav { padding: .2em .1em .2em .2em; float: left; width: 12em; }
    .ui-tabs-vertical .ui-tabs-nav li { clear: left; width: 100%; border-bottom-width: 1px !important; border-right-width: 0 !important; margin: 0 -1px .2em 0; }
    .ui-tabs-vertical .ui-tabs-nav li a { display:block; }
    .ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active { padding-bottom: 0; padding-right: .1em; border-right-width: 1px; }
    .ui-tabs-vertical .ui-tabs-panel { padding: 1em; float: right; width: 40em;}
    .ui-tabs-vertical .ui-tabs-panel{width:85%;}
    .ui-tabs-vertical .ui-tabs-nav{width:15%;}
    .ui-tabs-vertical{width:100%;}
    #tabs{ letter-spacing:0;}
</style>