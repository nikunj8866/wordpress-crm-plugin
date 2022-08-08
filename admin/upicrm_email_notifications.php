<?php
if ( !class_exists('UpiCRMAdminEmailNotifications') ):
    class UpiCRMAdminEmailNotifications{
        public function Render() {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            if(isset($_GET['action'])){
            switch ($_GET['action']) {
                case 'save_field':
                    if(isset($_POST)) :
                        $this->saveField();
                        $msg = __( 'changes saved successfully', 'upicrm' );
                    endif;
                break;
                case 'save_settings':
                    if(isset($_POST['default_email'])) :
                        $this->saveSettings();
                        $msg = __( 'changes saved successfully', 'upicrm' );
                    endif;
                break;
                case 'edit_custom_show':
                $edit_mode = true;
                break;
            }
            }
            if(isset($_POST['action'])){
                switch ($_POST['action']) {
                    case 'new_custom':
                        $this->newCustom();
                        $msg = __( 'changes saved successfully', 'upicrm' );
                    break;
                    case 'edit_custom':
                        $this->editCustom();
                        $msg = __( 'changes saved successfully', 'upicrm' );
                    break;
                }
            }

            $UpiCRMMails = new UpiCRMMails();
            $getMails = $UpiCRMMails->get();

            ?>
                <?php
                if (isset($msg)) {
                ?>
                    <div class="updated">
                        <p><?php echo $msg; ?></p>
                    </div>
                <?php
                }
                ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        <h2><?php _e('Lead Management','upicrm'); ?></h2>
                        <form method="post" action="admin.php?page=upicrm_email_notifications&action=save_settings">
                            <?php _e('Send all leads and updates to the following user:','upicrm'); ?>
                                        <select name="default_email">
                                            <?php
                        $default_email = get_option('upicrm_default_email');
                        $get_users = get_users( array( 'role' => '' ) ); //Editor, Administrator
                        foreach ($get_users as $user) {
                            if (get_the_author_meta('upicrm_user_permission', $user->ID) > 0 ) {
                                            ?>
                                            <option value="<?php echo $user->user_email; ?>" <?php selected( $default_email, $user->user_email ); ?>><?php echo $user->display_name; ?></option>
                                            <?php }

                            }?>
                                        </select>
                            <br />
                            <?php _e('Leads are by default assigned to:','upicrm'); ?>
                                        <select name="default_lead">
                                            <?php
                        $default_lead = get_option('upicrm_default_lead');
                        $get_users = get_users( array( 'role' => '' ) ); //Editor, Administrator
                        foreach ($get_users as $user) {
                            if (get_the_author_meta('upicrm_user_permission', $user->ID) > 0 ) {
                                            ?>
                                            <option value="<?php echo $user->ID; ?>" <?php selected( $default_lead, $user->ID ); ?>><?php echo $user->display_name; ?></option>
                                            <?php }
                        }
                                            ?>
                                        </select>
                                        <br />
                            <?php _e('Leads by default status :','upicrm'); ?>
                                        <select name="default_lead_status">
                                        <?php
                                        $default_lead = get_option('upicrm_default_lead_status');
                  
                                        $UpiCRMLeadsStatusARRAY = $UpiCRMLeadsStatus->get_as_array();
                                        if(isset($UpiCRMLeadsStatusARRAY) && $UpiCRMLeadsStatusARRAY <> ''){
                                        foreach ($UpiCRMLeadsStatus->get_as_array() as $key => $value) {
                                            ?>
                                            <option value="<?php echo $key; ?>" <?php selected( $default_lead, $key ); ?>><?php echo $value; ?></option>
                                            <?php }
                                                }
                                            ?>
                                        </select>
                            <br />
                            <?php $email_format =  get_option('upicrm_email_format');?>
                            <?php _e('Email format:','upicrm'); ?>
                            <select name="email_format">
                                <option value="1" <?php selected( $email_format, 1); ?>>HTML</option>
                                <option value="2" <?php selected( $email_format, 2); ?>>Text</option>
                            </select><br />
                            <?php _e('Distribute all leads and updated to additional email address (or multiple addresses separated by comma (,):','upicrm'); ?>
                            <input type="text" name="extra_email" value="<?php echo get_option('upicrm_extra_email'); ?>" /><br />
                                            <?php _e('Change default "from" field for emails sent from','upicrm'); ?> UpiCRM: <input type="text" name="sender_email" value="<?php echo get_option('upicrm_sender_email'); ?>" /><br />
                            <?php _e('Email will be sent in the following format: &lt;name&gt; no-reply@yourdomain.com','upicrm'); ?>
                            <br />
               <div class="checkbox">
                    <label><input type="checkbox" value="1" name="upicrm_cancel_email_alerts" <?php checked(get_option('upicrm_cancel_email_alerts'), 1, 1 ); ?>  />
                     <?php _e('Don\'t send any email alerts','upicrm'); ?></label>
               </div>
               <div class="checkbox">
                    <label><input type="checkbox" value="1" name="upicrm_cancel_email_to_users" <?php checked(get_option('upicrm_cancel_email_to_users'), 1, 1 );; ?>  />
                     <?php _e('Don\'t sent UpiCRM emails alerts to UpiCRM users (email alerts will sent only to UpiAdmins)
','upicrm'); ?></label>
               </div>
               <div class="checkbox">
                    <label><input type="checkbox" value="1" name="upicrm_cancel_email_failsafe" <?php checked(get_option('upicrm_cancel_email_failsafe'), 1, 1 ); ?>  />
                     <?php _e('UpiCRM will send email using only WP-Mail function.','upicrm'); ?></label>
               </div>   
               <div class="checkbox">
                    <label><input type="checkbox" value="1" name="upicrm_cancel_email_from" <?php checked(get_option('upicrm_cancel_email_from'), 1, 1 ); ?>  />
                     <?php _e('Don\'t sent "from" information.','upicrm'); ?></label>
               </div>
               <div class="checkbox">
                    <label><input type="checkbox" value="1" name="upicrm_send_csv_email" <?php checked(get_option('upicrm_send_csv_email'), 1, 1 ); ?>  />
                     <?php _e('Send CSV file to email address:','upicrm'); ?></label>
                    <input type="text" name="upicrm_send_csv_get_mail" value="<?php echo get_option('upicrm_send_csv_get_mail'); ?>" />
               </div>

                         <?php submit_button(); ?>
                        </form>
                    </div>
                </div>

                <form method="post" action="admin.php?page=upicrm_email_notifications&action=save_field">
                    <?php
                    $the_var = '[lead]<br />
                               [url]<br />
                               [assigned-to]<br />
                               [lead-status]<br />
                               [lead-plaintext]<br />
                               [field-*]';
                    
                    foreach ($getMails as $mail) { ?>
                        <div class="row">
                           <h2><?php echo $mail->mail_event_name; ?></h2>
                           <div class="col-xs-12 col-sm-5 col-md-5 col-lg-6">
                               <label><?php _e('Content:','upicrm'); ?> </label><br />
                               <?php if($email_format == 1) : ?>
                               <?php wp_editor($mail->mail_content, $mail->mail_event.'_mail_content', array("textarea_name" => $mail->mail_event.'[mail_content]', "media_buttons" => false, "textarea_rows" => 10, "quicktags" => false,  'tinymce' => array("toolbar1" => "bold,italic,bullist,numlist,hr,alignleft,aligncenter,alignright,link,unlink", "block_formats" => false, "toolbar2" => "formatselect,underline,alignjustify,forecolor,pastetext,removeformat,outdent,indent,undo,redo")) ); ?>
                               <?php else : ?>
                                <textarea name="<?php echo $mail->mail_event; ?>[mail_content]" rows="12" cols="50"><?php echo stripslashes($mail->mail_content); ?></textarea>
                                <?php endif; ?>
                           </div>
                           <div class="col-xs-12 col-sm-5 col-md-5 col-lg-6">
                               <label><?php _e('Subject:','upicrm'); ?> </label><br />
                               <input type="text" name="<?php echo $mail->mail_event; ?>[mail_subject]" value="<?php echo $mail->mail_subject; ?>" />
                               <br /><br />
                               <label><?php _e('CC:','upicrm'); ?> </label><br />
                               <input type="text" name="<?php echo $mail->mail_event; ?>[mail_cc]" value="<?php echo $mail->mail_cc; ?>" />
                               <br /><br />
                               <strong><?php _e('Variables:','upicrm'); ?></strong> <br />
                               <?= $the_var; ?>
                           </div>
                        </div>
                     <?php } ?>
                    <?php submit_button(); ?>
                </form>

                <br /><br /><br />
                <?php require_once get_upicrm_template_path('custom_email'); ?>

        <?php
        }

        function saveField() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMMails->update2($_POST);
        }

        function saveSettings() {
            if(isset($_POST['default_email'])) :
                update_option('upicrm_default_email', $_POST['default_email']);
            endif;
            if(isset($_POST['extra_email'])) :
                update_option('upicrm_extra_email', $_POST['extra_email']);
            endif;
            if(isset($_POST['sender_email'])) :
                update_option('upicrm_sender_email', $_POST['sender_email']);
            endif;
            if(isset($_POST['default_lead'])) :
                update_option('upicrm_default_lead', $_POST['default_lead']);
            endif;
            if(isset($_POST['default_lead_status'])) :
                update_option('upicrm_default_lead_status', $_POST['default_lead_status']);
            endif;
            if(isset($_POST['email_format'])) :
                update_option('upicrm_email_format', $_POST['email_format']);
            endif;
            if(isset($_POST['upicrm_send_csv_get_mail'])) :
                update_option('upicrm_send_csv_get_mail', $_POST['upicrm_send_csv_get_mail']);
            endif;
            if(isset($_POST['upicrm_cancel_email_alerts'])) :
            $upicrm_cancel_email_alerts = $_POST['upicrm_cancel_email_alerts'] ? 1 : 0;
            update_option('upicrm_cancel_email_alerts', $upicrm_cancel_email_alerts);
            endif;
            if(isset($_POST['upicrm_cancel_email_failsafe'])) :
                $upicrm_cancel_email_failsafe = $_POST['upicrm_cancel_email_failsafe'] ? 1 : 0;
                update_option('upicrm_cancel_email_failsafe', $upicrm_cancel_email_failsafe);
            endif;
            if(isset($_POST['upicrm_cancel_email_to_users'])) :
                $upicrm_cancel_email_to_users = $_POST['upicrm_cancel_email_to_users'] ? 1 : 0;
                update_option('upicrm_cancel_email_to_users', $upicrm_cancel_email_to_users); 
            endif;
            if(isset($_POST['upicrm_cancel_email_from'])) :
                $upicrm_cancel_email_from = $_POST['upicrm_cancel_email_from'] ? 1 : 0;
                update_option('upicrm_cancel_email_from', $upicrm_cancel_email_from); 
            endif;
            if(isset($_POST['upicrm_send_csv_email'])) :
                $upicrm_send_csv_email = $_POST['upicrm_send_csv_email'] ? 1 : 0;
                update_option('upicrm_send_csv_email', $upicrm_send_csv_email); 
            endif;
        }
        
        function newCustom() {
            $UpiCRMMails = new UpiCRMMails();
            $arr = array(
                "mail_event"  => 'custom_'.$UpiCRMMails->get_new_id_to_insert(),
                "mail_content"  => $_POST['mail_content'],
                "mail_subject"  => $_POST['mail_subject'],
                "mail_event_name"  => $_POST['mail_event_name']
            );
            $UpiCRMMails->add($arr);
        }
        
        function editCustom() {
            $UpiCRMMails = new UpiCRMMails();
            //$UpiCRMMails->get_new_id_to_insert()
            $arr = array(
                "mail_content"  => $_POST['mail_content'],
                "mail_subject"  => $_POST['mail_subject'],
                "mail_event_name"  => $_POST['mail_event_name']
            );
            $UpiCRMMails->update($arr,$_POST['mail_id']);
        }
        
        function wp_ajax_remove_mail_template_callback() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMMails->remove($_POST['mail_id']);
            echo 1;
            die();
        }
    }
    add_action( 'wp_ajax_remove_mail_template', array(new UpiCRMAdminEmailNotifications,'wp_ajax_remove_mail_template_callback'));
endif;
?>
