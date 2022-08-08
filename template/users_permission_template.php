<br /><br />
<form method="post" class="form-inline" action="admin.php?page=upicrm_users_center">
    <h2><?php _e("Set new WordPress users default Upi Role", 'upicrm'); ?></h2>
    <input type="hidden" name="action" value="change_permission">
    <div class="clearfix"></div>
    <?php 
    _e("A New WordPress Admin will become a:", 'upicrm');
    $UpiCRMUsers->show_permissions_options(1);
    ?>
    
    <br />
    <?php 
    _e("Any other New WordPress User will become a:", 'upicrm');
    $UpiCRMUsers->show_permissions_options(2);
    ?>
    <br /><br />
    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e("Save", 'upicrm'); ?>">
</form>

<script type="text/javascript">
    $j(document).ready(function ($) {


    });
</script>