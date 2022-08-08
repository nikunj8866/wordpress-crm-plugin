<p><?php _e("In order to comply with the GDPR requirements, UpiCRM offers you the ability to control personal information your website stores.", 'upicrm'); ?></p>
<p><?php _e("Our recommendation is to use the Lead aggregation mechanism in order to transmit the information received from site’s forms, and send this information to a 3rd secured / separate server.", 'upicrm'); ?><br />
<a href="https://www.upicrm.com/leads-mangement-web-services-integration-send-leads-remote-server/" target="_blank"><?php _e("please read more about Lead aggregation capability here.", 'upicrm'); ?></a></p>
<p><?php _e("After a successful transmission of the information to a
remote server, you can wipe / delete personal information
from your web site in order to prevent future risk of data
leaks.", 'upicrm'); ?></p>
<p><?php _e("The options are:", 'upicrm'); ?></p>
<form method="post">
    <input type="hidden" name="action" value="save" />
    <div class="">
        <label>
            <input type="checkbox" value="1" name="upicrm_remove_info_from_db" <?php checked(get_option('upicrm_remove_info_from_db'), 1, 1); ?>  />
            <?php _e("Don't save lead information on your site: no information will be stored. An email will be sent
with all the collected data", 'upicrm'); ?>
        </label>
    </div>
    <div class="">
        <label>
            <input type="checkbox" value="1" name="upicrm_remove_after_send_to_master" <?php checked(get_option('upicrm_remove_after_send_to_master'), 1, 1); ?>   />
            <?php _e("Delete lead information from this UpiCRM after it was successfully sent to UpiMaster / 3rd
server.", 'upicrm'); ?>
        </label>
    </div>
    <div class="">
        <label>
            <?php _e("The text we’ll write instead of the deleted information in fields will be:", 'upicrm'); ?>
            &nbsp;&nbsp;
            <input name="upicrm_text_on_lead_clear" type="text" value="<?= get_option('upicrm_text_on_lead_clear'); ?>" />
        </label>
    </div>
    <p><?php _e("Note: we’ll save all lead’s meta data (ID, traffic source, UTM, etc) – but not the personal
information. An email with the complete information will also be sent to you. All the information
will be securely saved on the remote server that you have configured.", 'upicrm'); ?></p>
    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e("Save", 'upicrm'); ?>">
</form>

<br /><br />
<?php _e("History Data deletion: choose date range and delete history data from your server:", 'upicrm'); ?><br />
<form method="post" onsubmit="return confirm('<?php _e("I confirm I have backed up all the data and I know the deletion process is irreversible.", 'upicrm'); ?>');">
    <input type="hidden" name="action" value="do_date" />
    <select name="choose_action">
        <option value="1"><?php _e("Delete personal data only", 'upicrm'); ?></option>
        <option value="2"><?php _e("Delete complete leads records", 'upicrm'); ?></option>
    </select>
    <?php _e("from", 'upicrm'); ?>
    <div class="upicrm_datepicker_desg">
        <input type="text" name="from_date" value="<?= $_POST['from_date'] ?>" class="datepicker" data-dateformat="yy-mm-dd" autocomplete="off" />
        <i class="fa fa-calendar"></i>
    </div>
    <?php _e("to", 'upicrm'); ?>
    <div class="upicrm_datepicker_desg">
        <input type="text" name="to_date" value="<?= $_POST['to_date'] ?>" class="datepicker" data-dateformat="yy-mm-dd" autocomplete="off" />
        <i class="fa fa-calendar"></i>
    </div>
     <p><?php _e("Note: all data will be erased. You have no way of reverting this process. Please make sure you
have backed up / downloaded the complete information (export option) before you delete history
data.", 'upicrm'); ?></p>
    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e("Delete", 'upicrm'); ?>">
</form>