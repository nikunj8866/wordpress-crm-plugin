<?php
$field_on = @get_option('upicrm_affiliate_show_fields');
$list_option = $UpiCRMUIBuilder->get_list_option();
?>
<form method="post" >
    <h2><?php _e('Choose fields that will be visible for the affiliate:', 'upicrm'); ?></h2>

<article class="">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget jarviswidget-color-blueLight" id="wid-id-1" data-widget-editbutton="false">
        <header>
            <span class="widget-icon">
                <i class="fa fa-table">
                </i>
            </span>
            <h2>
                <?php _e('Affiliate Fields Table', 'upicrm'); ?>                    
            </h2>
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
                <table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
                    <thead>
                        <tr>
                            <th><?php _e('Status', 'upicrm'); ?></th>
                            <th><?php _e('Field', 'upicrm'); ?></th>
                            <th><?php _e('ID', 'upicrm'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        
                            <?php
                            foreach ($list_option as $key => $arr) {
                                foreach ($arr as $key2 => $value) {
                                    //if ($key == "content" || $key == "special" && $key2 == "affiliate_time") {
                                    ?>
                            <tr>
                                <td>
                                    <input type="checkbox" <?php checked(isset($field_on[$key][$key2])); ?> name="field[<?= $key; ?>][<?= $key2; ?>]" value="<?= $key2; ?>" /></td> 
                                <td><?= $value; ?> </td>
                                <td><?php if ($key == "content") { ?>
                                    <?= $key2; ?>
                                <?php } ?></td>
                            </tr>
                            <?php
                            //}
                        }
                    }
                    ?>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    <!-- end widget -->
</article>

    <br />
    <input type="hidden" name="action" value="save_field" />
    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save', 'upicrm'); ?>">
</form>
