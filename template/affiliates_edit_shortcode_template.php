<?php
$users_permission_arr = get_option('upicrm_affiliate_edit_permission_users');
if (isset($users_permission_arr[get_current_user_id()])) {
    ?>
    <form method="post" id="upicrm_affiliate_edit" action="">
        <?php
        foreach ($list_option as $key => $arr) {
            foreach ($arr as $key2 => $value) {

                if (isset($field_on[$key][$key2])) {
                    $getVal = $UpiCRMUIBuilder->lead_routing($leadObj, $key, $key2, $getNamesMap);
                    if ($key == "content") {
                        ?>
                        <div>
                            <label><?= $value; ?>:</label>
                            <input type="text" name="content[<?= str_replace(" ", "_", $value); ?>]" value="<?= $getVal; ?>" />

                        </div>
                    <?php
                    }
                    if ($key == "special" && ($key2 == "lead_status_id" || $key2 == "lead_management_comment")) {
                        ?>
                        <div>
                            <label><?= $value; ?>:</label>
                            <?= $getVal ?>

                        </div>
                        <?php
                    }
                }
            }
        }
        ?>
        <br />
        <input type="hidden" name="lead_id" value="<?= (int) $_GET['lead_id'] ?>" />
        <input type="hidden" name="action" value="edit_aff" />
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save', 'upicrm'); ?>">
    </form>

    <?php
}
?>