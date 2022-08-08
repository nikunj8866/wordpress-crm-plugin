<?php
if ( !class_exists('UpiCRMAdminPrivacy') ):
    class UpiCRMAdminPrivacy{
        public function Render() {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'save':
                        $this->save();
                    break;
                    case 'do_date':
                        $this->do_date();
                    break;
                }
            }
            
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'delete_files':
                        $this->delete_files();
                    break;
                    case 'show_msg':
                        $this->show_msg((int)$_GET['msg']);
                    break;
                }
            }
            
            if (get_option('upicrm_text_on_lead_clear') === false) {
                 update_option('upicrm_text_on_lead_clear','GDPR');
            }
            
            require_once @get_upicrm_template_path('privacy');
        }
        
        private function save() {
            if (isset($_POST['upicrm_remove_info_from_db']) && $_POST['upicrm_remove_info_from_db'] == 1) {
                update_option('upicrm_remove_info_from_db',1);
            }
            else {
               update_option('upicrm_remove_info_from_db',0); 
            }
            if (isset($_POST['upicrm_remove_after_send_to_master']) && $_POST['upicrm_remove_after_send_to_master'] == 1) {
                update_option('upicrm_remove_after_send_to_master',1);
            }
            else {
               update_option('upicrm_remove_after_send_to_master',0); 
            }
            
            if (isset($_POST['upicrm_text_on_lead_clear'])) {
                update_option('upicrm_text_on_lead_clear',$_POST['upicrm_text_on_lead_clear']);
            }
        }
        
        private function do_date() {
            $UpiCRMLeads = new UpiCRMLeads();
            $getLeads = $UpiCRMLeads->get(0, 0, 0, 'DESC', "custom", $_POST['from_date'], $_POST['to_date']);
            foreach ($getLeads as $lead) {
                if ($_POST['choose_action'] == 1) {
                    $UpiCRMLeads->clear_by_id($lead->lead_id);
                }
                if ($_POST['choose_action'] == 2) {
                    $UpiCRMLeads->remove_lead($lead->lead_id);
                }
            }

        }
        
        private function delete_files() {
            $UpiCRMPrivacy = new UpiCRMPrivacy();
            $UpiCRMPrivacy->delete_files();
            echo "<script>window.location = 'admin.php?page=upicrm_privacy&action=show_msg&msg=1';</script>";
           
        }
        
        private function show_msg($msg) {
            switch ($msg) {
                case 1:
                    $get_msg = __('All data deleted!', 'upicrm');
                break;
            }
            
            ?>
            <div class="updated">
                <p><?php echo $get_msg; ?></p>
            </div>
            <br />
            <?php
        }
    }
endif;
