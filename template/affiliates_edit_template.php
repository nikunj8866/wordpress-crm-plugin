<?php 
$users = $UpiCRMUsers->get(); 
$users_permission_arr = get_option('upicrm_affiliate_edit_permission_users');
?>

<form method="post" class="form-inline" action="admin.php?page=upicrm_affiliate">
<?php foreach ($users as $user) {
    ?>
    <label class="form-group">
        <input type="checkbox" name="users[]" value="<?= $user->ID; ?>" <?php checked( isset($users_permission_arr[$user->ID]) ); ?> /> &nbsp;<?= $user->display_name; ?>
    </label>
    <br />
<?php } ?>
    <div class="form-group pad_form">
        <br />
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save', 'upicrm'); ?>">
    </div>
    <input type="hidden" name="action" value="save_edit_permission" />
</form>
<br /><br />

