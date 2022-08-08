<br /><br />
<form method="post" class="form-inline" action="admin.php?page=upicrm_users_center">
    <h2><?php _e(!$GetUserOBJ ? "Add User" : "Update User", 'upicrm'); ?></h2>
    <input type="hidden" name="action" value="<?php echo!$GetUserOBJ ? "save_user" : "update_user"; ?>">
    <input type="hidden" name="inside_id" value="<?php echo $GetUserOBJ->inside_id; ?>">
    <div class="form-group pad_form">
        <label><?php _e('User', 'upicrm'); ?>:</label>
        <?php echo $UpiCRMUsers->select_list_user("user_id", $GetUserOBJ->user_id); ?>
    </div>
    <div class="form-group pad_form">
        <label><?php _e('Reports to', 'upicrm'); ?>:</label>
        <?php echo $UpiCRMUsers->select_list_user("user_parent_id", $GetUserOBJ->user_parent_id); ?>
    </div>
    <div class="form-group pad_form">
        <label><?php _e('Role', 'upicrm'); ?>:</label>
        <input type="text" name="user_label" value="<?php echo $GetUserOBJ->user_label; ?>" />
    </div>
    <div class="form-group pad_form">
        <label><?php _e('Permission', 'upicrm'); ?>:</label>
        <select name="upicrm_user_permission">
            <option value="1" <?php selected(get_the_author_meta('upicrm_user_permission', $GetUserOBJ->user_id), 1); ?>><?php _e('UpiCRM User', 'upicrm'); ?></option>
            <option value="2" <?php selected(get_the_author_meta('upicrm_user_permission', $GetUserOBJ->user_id), 2); ?>><?php _e('UpiCRM Admin', 'upicrm'); ?></option>
        </select>
    </div>
    <div class="clearfix"></div>
    <br />
    <div class="checkbox">
        <label><input type="checkbox" value="1" name="upicrm_user_reassign_manager" <?php checked(get_the_author_meta('upicrm_user_reassign_manager', $GetUserOBJ->user_id), 1, 1); ?>  />
            <?php _e('Can Re-assign leads back to manager', 'upicrm'); ?></label>
    </div>
    <div class="clearfix"></div>
    <div class="checkbox">
        <label><input type="checkbox" value="1" name="upicrm_user_manager_status_change_note" <?php checked(get_the_author_meta('upicrm_user_manager_status_change_note', $GetUserOBJ->user_id), 1, 1); ?>  />
            <?php _e('Update manager on status changes', 'upicrm'); ?></label>
    </div>
    <div class="clearfix"></div>
    <div class="checkbox">
        <label><input type="checkbox" value="1" name="upicrm_user_view_only_associated_leads" <?php checked(get_the_author_meta('upicrm_user_view_only_associated_leads', $GetUserOBJ->user_id), 1, 1); ?>  />
            <?php _e('Restriction for UpiCRM User: View only associated leads', 'upicrm'); ?></label>
    </div>

    <div class="clearfix"></div>
    <br /><br />
    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e(!$GetUserOBJ ? "Add UpiCRM User" : "Update", 'upicrm'); ?>">
</form>
<br /><br />
<div class="tree smart-form">
    <strong><p><?php _e('Global User Hierarchy', 'upicrm'); ?>:</p></strong><?php
    $users = $UpiCRMUsers->get_inside_by_parent_id(0);
    $this->view_user_tree($users);
    ?></div>
<br /><br />
<a href="admin.php?page=upicrm_users_center&action=reset" class="btn btn-default" onclick="return confirm('are you sure?');" onclick="return confirm('<?php _e('Are you sure?', 'upicrm'); ?>');">
    <i class="glyphicon glyphicon-repeat"></i>
    <?php _e('Reset configuration', 'upicrm'); ?>
</a>
<script type="text/javascript">
    $j(document).ready(function ($) {
        $j("*[data-callback='remove']").click(function () {
            if (confirm("<?php _e('Delete this user from UpiCRM?', 'upicrm'); ?>")) {
                GetSelect = $j(this);
                var data = {
                    'action': 'remove_user_hierarchy',
                    'inside_id': $(this).attr("data-inside_id"),
                };
                $j.post(ajaxurl, data, function (response) {
                    GetSelect.closest("li").fadeOut();
                    console.log(response);
                });
            }
        });

        $j("*[data-callback='edit']").click(function () {
            var inside_id = $j(this).attr("data-inside_id");
            window.location = "admin.php?page=upicrm_users_center&id=" + inside_id;
        });

    });
</script>