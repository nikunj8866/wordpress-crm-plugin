<script>
    jQuery(document).ready(function ($) {
        var data = {
            'action': 'upicrm_on_load',
        };
        $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', data, function (response) {
            //console.log(response);
        });
    });
</script>