<script type="text/javascript">
    $j(document).ready(function () {
        pageSetUp();
    })
</script><?php
            if (isset($msg)) {
    ?><div class="updated">
        <p><?php echo $msg; ?></p>
    </div><?php
            }
    ?><div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <h2><?php _e('Add New lead routing rule','upicrm'); ?></h2>
            <?php _e('Here you can define rules and conditions to automatically assign leads to designated UpiCRM users or change leads status fields. 
For example you can define that If field "country" equals "India" , then assign this lead to the regional sales manager of India. 
Another example: if subject contains "buy free", then change lead status to "Spam".','upicrm'); ?>
            <br /><br />
            <strong><?php _e('Important notes:','upicrm'); ?></strong>
            <ul class="num_list">
                <li><?php _e("You can add as many rules as you'd like, UPICRM will process them in the order they're sorted.
The last rule always overrides the previous rules – so make sure you sort the rules according to your required logic.",'upicrm'); ?></li>
                <li><?php _e('You can add multiple values separated by commas (,). 
Example: if subject contains buy, free, shop, cheap, new, deals – then change lead status to "Spam"','upicrm'); ?></li>
                <li><?php _e('Value are by definition NOT case sensitive, meaning – if you write "india", as content, the rule will be processed also in cases where field contains value "INDIA".','upicrm'); ?></li>
                <li><?php _e('You can always delete or edit rules in the future… so start slow and add more rules as you go.','upicrm'); ?></li>
                <li><?php _e('Checkboxes on forms: if you\'re using check box fields on you forms, we recommend using "contains" operator only.','upicrm'); ?></li>
            </ul>
            <br /><br />
            <form method="post" action="admin.php?page=upicrm_lead_route">
                <?php if(isset($id)&&($id > 0)) { ?>
                    <input type="hidden" name="action" value="update_route" />
                    <input type="hidden" name="upicrm_lead_id" value="<?php echo $id; ?>" />
                <?php } else { ?>
                    <input type="hidden" name="action" value="save_route" />
                <?php } ?>
                <strong><?php _e('IF','upicrm'); ?></strong>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <select name="field_id"><?php
                                $i = 1;
                                foreach ($list_option as $key => $arr) {
                                    foreach ($arr as $key2 => $value) {
                                        $selected = "";
                                        if (isset($id)&&($id > 0))
                                            $selected = selected($GetLeadsRouteOBJ->lead_route_option.'||exp||'.$GetLeadsRouteOBJ->field_id,$key.'||exp||'.$key2, false);
                                        ?><option value="<?php echo $key.'||exp||'.$key2; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option><?php
                                    }
                                }
                                ?></select>
                <select name="lead_route_type"><?php
                    $OperatorArr = $UpiCRMLeadsRoute->get_type_options();
                    foreach ($OperatorArr as $key => $value) {
                        $selected = "";
                        if (isset($id)&&($id > 0))
                            $selected = selected( $GetLeadsRouteOBJ->lead_route_type, $key, false);
                        ?><option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                   <?php } ?></select>
                : <input type="text" name="lead_route_value" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->lead_route_value : ""?>" style="height: 28px; position: relative; top: 2px;" />
                <input type="checkbox" value="1" name="lead_route_and" id="lead_route_and" style="position: relative; top: -4px; margin-left: 15px;" />
                <label for="lead_route_and"><?php _e('Add and option','upicrm'); ?></label>
                <div id="add_more" style="display: none;">
                <strong><?php _e('AND','upicrm'); ?></strong>
                               &nbsp;
                               <select name="field_id2"><?php
                                               $i = 1;
                                               foreach ($list_option as $key => $arr) {
                                                   foreach ($arr as $key2 => $value) {
                                                       $selected = "";
                                                       if (isset($id)&&($id > 0))
                                                           $selected = selected($GetLeadsRouteOBJ->lead_route_option2.'||exp||'.$GetLeadsRouteOBJ->field_id2,$key.'||exp||'.$key2, false);
                                                       ?>
                                                       <option value="<?php echo $key.'||exp||'.$key2; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                                                       <?php
                                                   }
                                               }
                                               ?></select>
                               <select name="lead_route_type2">
                                   <?php
                                   $OperatorArr = $UpiCRMLeadsRoute->get_type_options();
                                   foreach ($OperatorArr as $key => $value) {
                                       $selected = "";
                                       if (isset($id)&&($id > 0))
                                           $selected = selected( $GetLeadsRouteOBJ->lead_route_type2, $key, false);
                                       ?><option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                                  <?php } ?>
                               </select>
                               : <input type="text" name="lead_route_value2" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->lead_route_value2 : ""?>" style="height: 28px; position: relative; top: 2px;" />
                <input type="checkbox" value="1" name="lead_route_and2" id="lead_route_and2" style="position: relative; top: -4px; margin-left: 15px;" />
                <label for="lead_route_and2"><?php _e('Add another and option','upicrm'); ?></label>
                </div>
                
                <div id="add_more2" style="display: none;">
                <strong><?php _e('AND','upicrm'); ?></strong>
                               &nbsp;
                               <select name="field_id3"><?php
                                               $i = 1;
                                               foreach ($list_option as $key => $arr) {
                                                   foreach ($arr as $key2 => $value) {
                                                       $selected = "";
                                                       if (isset($id)&&($id > 0))
                                                           $selected = selected($GetLeadsRouteOBJ->lead_route_option3.'||exp||'.$GetLeadsRouteOBJ->field_id3,$key.'||exp||'.$key2, false);
                                                       ?>
                                                       <option value="<?php echo $key.'||exp||'.$key2; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                                                       <?php
                                                   }
                                               }
                                               ?></select>
                               <select name="lead_route_type3">
                                   <?php
                                   $OperatorArr = $UpiCRMLeadsRoute->get_type_options();
                                   foreach ($OperatorArr as $key => $value) {
                                       $selected = "";
                                       if (isset($id)&&($id > 0))
                                           $selected = selected( $GetLeadsRouteOBJ->lead_route_type3, $key, false);
                                       ?><option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                                  <?php } ?>
                               </select>
                               : <input type="text" name="lead_route_value3" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->lead_route_value3 : ""?>" style="height: 28px; position: relative; top: 2px;" />
                </div>
                
                <br /><br />
                <strong><?php _e('THEN','upicrm'); ?></strong>
                &nbsp;&nbsp;
                <ul style="list-style-type: decimal; margin: 0 35px;">
                    <li>
                        <?php _e('Assign lead to','upicrm'); ?>
                        <select name="leads_route_rr_users[]" id="leads_route_rr_users"  multiple="multiple" >
                            <?php
                            foreach ($UpiCRMUsers->get() as $user) {
                                $user_rr = explode(",", $GetLeadsRouteOBJ->leads_route_rr_users);
                                $selected = selected(true, in_array($user->ID,$user_rr), false);
                                ?>
                                <option value="<?php echo $user->ID; ?>" <?php echo $selected; ?> ><?php echo $user->display_name; ?></option>
                           <?php } ?>
                        </select><br />
                        <?php _e('Note: if you choose more than one person, every lead will be distributed to each assignee in turn, one after the other, in a “Round-robin” que. 
A lead will NOT be distributed to more than one person simultaneously!','upicrm'); ?>
                    </li>
                    <li>
                        <?php _e('Change lead status to','upicrm'); ?>
                        <select name="lead_status_id">
                            <option value="0"></option><?php
                            foreach ($UpiCRMLeadsStatus->get() as $status) {
                                if (isset($id)&&($id > 0))
                                    $selected = selected( $GetLeadsRouteOBJ->lead_status_id, $status->lead_status_id, false);
                                ?><option value="<?php echo $status->lead_status_id; ?>" <?php echo $selected; ?> ><?php echo $status->lead_status_name; ?></option>
                           <?php } ?>
                        </select>
                    </li>
                    <li>
                        <?php _e('Change','upicrm'); ?>
                        <select name="change_field_id">
                            <option value="0"></option><?php
                            foreach ($UpiCRMFields->get() as $field) {
                                $selected = "";
                                if (isset($id)&&($id > 0))
                                    $selected = selected( $GetLeadsRouteOBJ->change_field_id, $field->field_id, false);
                                ?><option value="<?php echo $field->field_id; ?>" <?php echo $selected; ?> ><?php echo $field->field_name; ?></option>
                           <?php } ?>
                        </select>
                        <?php _e('value to:','upicrm'); ?>
                        <input type="text" name="change_field_value" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->change_field_value : ""?>" style="height: 28px; position: relative; top: 2px;"><br />
                        <?php _e('Note: You can use variables:','upicrm'); ?> [field-*], [url], [assigned-to], [lead-status], [affiliate-name]


                    </li>
                    <li>
                        <select name="webservice_id">
                            <option value="0" <?php selected( $GetLeadsRouteOBJ->webservice_id, 0); ?> ><?php _e('Webservice off','upicrm'); ?></option>
                            <option value="1" <?php selected( $GetLeadsRouteOBJ->webservice_id, 1) ?> ><?php _e('Webservice on','upicrm'); ?></option>
                        </select>
                    </li>
                    <li>
                        <?php _e('Send lead to UpiCRM master','upicrm'); ?>
                        <select name="integration_id">
                             <option value="0" <?php selected( $GetLeadsRouteOBJ->webservice_id, 0); ?> ><?php _e('Off','upicrm'); ?></option>
                            <?php
                            $masters = $UpiCRMIntegrations->get_master();
                            if (isset($masters)) {
                                foreach ($masters as $master) {
                            ?>
                                <option value="<?php echo $master->integration_id ?>" <?php selected( $GetLeadsRouteOBJ->integration_id, $master->integration_id) ?> ><?php echo $master->integration_domain ?></option>
                            <?php 
                                }
                            } ?>
                        </select>
                    </li>
                    <li>
                        <?php _e('Send email template','upicrm'); ?>
                        <select name="mail_id">
                            <option value="0"></option>
                            <?php
                            $getMails = $UpiCRMMails->get_custom();
                            foreach ($getMails as $mail) {
                                    $getMailARR[$mail->mail_id] = $mail->mail_event_name;
                                    $selected = selected( $GetLeadsRouteOBJ->mail_id, $mail->mail_id, false);
                                ?>
                                <option value="<?= $mail->mail_id; ?>"  <?php echo $selected; ?>><?= $mail->mail_event_name; ?></option>
                            <?php } ?>
                        </select>
                        <?php _e('to: ','upicrm'); ?>
                        <input type="text" name="lead_route_email_to" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->lead_route_email_to : ""?>" style="height: 28px; position: relative; top: 2px;" />
                        <?php _e('and/or to:','upicrm'); ?>
                        <select name="lead_route_mail_to_field_id">
                            <option value=""></option>
                            <?php
                                        $i = 1;
                                        foreach ($list_option as $key => $arr) {
                                            foreach ($arr as $key2 => $value) {
                                                $selected = "";
                                                if (isset($id) && ($id > 0)) {
                                                    $selected = selected($GetLeadsRouteOBJ->lead_route_mail_to_field_id, $key2, false);
                                                }
                                                if ( $key == "content") {
                                                ?><option value="<?= $key2; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option><?php 
                                                }
                                            }
                                        }
                                        ?>
                        </select>
                        <input type="checkbox" value="1" name="lead_route_mail_no_cc" id="lead_route_mail_no_cc" style="position: relative; top: -4px; margin-left: 15px;" <?php checked( $GetLeadsRouteOBJ->lead_route_mail_no_cc, 1); ?>  />
                <label for="lead_route_mail_no_cc"><?php _e('Don\'t send cc email','upicrm'); ?></label>
                    </li>
                    <li>
                        <?php _e('Assign lead to affiliate','upicrm'); ?>
                        <select name="affiliate_id">
                            <option value="0"></option>
                            <?php foreach ($UpiCRMAffiliate->get_users() as $user) {
                                $selected = "";
                                if (isset($user->data->ID) && ($user->data->ID > 0)) {
                                    $selected = selected($GetLeadsRouteOBJ->affiliate_id, $user->data->ID, false);
                                }
                                ?>
                                <option value="<?= $user->data->ID; ?>" <?= $selected; ?>><?= $user->data->display_name; ?></option>
                            <?php } ?>
                        </select>
                    </li>
                </ul>
                <br />
                &nbsp;&nbsp; <?php _e('Order','upicrm'); ?> <input type="number" step="1" name="lead_route_order" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->lead_route_order : "0"?>" style="height: 28px; position: relative; top: 2px;" />
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e($id > 0 ? 'Update Rule' : 'Save Rule','upicrm'); ?>"></p>            
            
            </form>
        </div>
    </div>
<script type="text/javascript">
    $j(document).ready(function($) {
        $j("*[data-callback='remove']").click(function() {
                if (confirm("<?php _e('Remove this lead?','upicrm'); ?>")) {
                    GetSelect = $j(this);
                    var data = {
                        'action': 'remove_lead_route',
                        'lead_route_id': $j(this).attr("data-lead_route_id"),
                    };
                    $j.post(ajaxurl, data , function(response) {
                        GetSelect.closest("tr").fadeOut();
                        console.log(response);
                    });
                }
            });
        
        $j("*[data-callback='edit']").click(function() {
            var lead_route_id = $j(this).attr("data-lead_route_id");
            window.location = "admin.php?page=upicrm_lead_route&id="+lead_route_id;
        });
        
        $j("*[data-callback='change_order']").click(function() {
            var lead_route_id = $j(this).attr("data-lead_route_id");
            window.location = "admin.php?page=upicrm_lead_route&action=change_order&id="+lead_route_id+"&order="+$j(".route_order[data-lead_route_id='"+lead_route_id+"']").val();
            
        });
        
        $("#lead_route_and").click(function() {
            if ($(this).prop("checked")) {
                $("#add_more").fadeIn();
            }
            else {
                $("#add_more").fadeOut();
                $("#add_more2").fadeOut();
                $("#lead_route_and2").prop("checked",false);
            }
        });
        
        $("#lead_route_and2").click(function() {
            if ($(this).prop("checked")) {
                $("#add_more2").fadeIn();
            }
            else {
                $("#add_more2").fadeOut();
            }
        });
        
        $('#leads_route_rr_users').multiselect({});
        
        <?php if($GetLeadsRouteOBJ->lead_route_and > 0) { ?>
            $("#lead_route_and").prop("checked",true);
            $("#add_more").show();
        <?php } ?>
            
        <?php if($GetLeadsRouteOBJ->lead_route_and > 1) { ?>
            $("#lead_route_and2").prop("checked",true);
            $("#add_more2").show();
        <?php } ?>
    });
</script>