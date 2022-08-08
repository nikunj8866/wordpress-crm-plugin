<form method="post" class="form-inline" action="admin.php?page=upicrm_affiliate">
    <input type="hidden" name="action" value="save_tree" />
    <div class="form-group pad_form">
        <label><?php _e('User', 'upicrm'); ?>:</label>
        <select name="user_child">
            <?php foreach ($UpiCRMAffiliate->get_users() as $user) { ?>
                <option value="<?= $user->ID; ?>"><?= $user->display_name; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group pad_form">
        <label><?php _e('Manager', 'upicrm'); ?>:</label>
        <select name="user_parent">
            <option value="0"></option>
            <?php foreach ($UpiCRMAffiliate->get_users() as $user) { ?>
                <option value="<?= $user->ID; ?>"><?= $user->display_name; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group pad_form">
        <br />
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save', 'upicrm'); ?>">
    </div>

</form>
<br /><br />

<?php
$users = $UpiCRMAffiliate->get_users();
if (count($users)) {
    ?>
    <div class="tree smart-form">
        <ul>
            <?php
            foreach ($users as $user) {
                if (get_user_meta($user->ID, 'affiliate_parent_id', 1) == 0 || get_user_meta($user->ID, 'affiliate_parent_id', 1) == "") {
                    $this->showUserInTree($user, $users);
                }
            }
            ?>
        </ul>
    </div>
<?php } ?>
