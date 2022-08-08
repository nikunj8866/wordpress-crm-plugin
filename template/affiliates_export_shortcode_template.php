<div id="upicrm_export_affiliate_choose">
<?php
foreach ($list_option as $key => $arr) {
    foreach ($arr as $key2 => $value) {
                 ?>
<label><input type="checkbox" name="<?php echo $key; ?>||exp||<?php echo $key2; ?>"><?php echo $value; ?> </label>
                  <?php
             }
         }
?>
</div>
<style>
#upicrm_export_affiliate_choose label {
    display: block;
}
</style>
<a href="javascript:void(0);" id="upicrm_export_affiliate_btn">
    <?php _e('Export latest leads to CSV ', 'upicrm'); ?>
</a>
<div id="upicrm_export_affiliate_loader" style="display: none;">
    <?php _e('Loading...', 'upicrm'); ?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $("body").on("click", "#upicrm_export_affiliate_btn", function () {
            /*var exportFields = $("input.upicrm_export_field:checkbox:checked").map(function(){
               return $(this).val();
            }).get();*/
        
            var exportFields = [];
            $('#upicrm_export_affiliate_choose input[type="checkbox"]:checked').each(function() {
                exportFields.push(this.name);
            });

            $("#upicrm_export_affiliate_loader").show();
            var data = {
                'action': 'upicrm_export_do',
                'export_fields': exportFields,
            };
            $.post('<?= admin_url('admin-ajax.php') ?>', data, function (response) {
                $("#upicrm_export_affiliate_loader").hide();
                window.location = response;
               //console.log(response);
            });
        });
    });
</script>