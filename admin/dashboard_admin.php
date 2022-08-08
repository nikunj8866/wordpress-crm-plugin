<?php
if ( !class_exists('UpiCRMAdminIndex') ):
    class UpiCRMAdminIndex{
        public function __construct() {
            wp_register_script('upicrm_js_flot',  UPICRM_URL.'resources/js/plugin/flot/jquery.flot.cust.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_vectormap',  UPICRM_URL.'resources/js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_chartjs',  UPICRM_URL.'resources/js/plugin/chartjs/chart.min.js', array('jquery'), '1.0');
            
            wp_enqueue_script('upicrm_js_flot');
            wp_enqueue_script('upicrm_js_vectormap');
            wp_enqueue_script('upicrm_js_chartjs');
        }
        public function Render() {
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'excel_output':
                    upicrm_excel_output();
                    break;
                case 'change_time':
                    //$msg = __('Changes saved successfully','upicrm');
                    $this->change_time();
                    break;
            }
        }
            $UpiCRMStatistics = new UpiCRMStatistics();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            if ($UpiCRMUsers->get_permission() == 1 && $UpiCRMUsers->get_wp_role()=='administrator') {
                $UpiCRMUsers->set_permission(2);
            }
            $user_id = get_current_user_id();
            $userOBJ = $UpiCRMUsers->get_inside_by_user_id($user_id);
            $colorARR = $UpiCRMStatistics->color_array();
            $list_option = $UpiCRMUIBuilder->get_list_option();
            $getNamesMap = $UpiCRMFieldsMapping->get(); 
            $check_date = isset($_COOKIE['upicrm_lead_table_days']) ? $_COOKIE['upicrm_lead_table_days'] : 0;
            $status = 10;
            $statusOpen = 9;
            if ($UpiCRMUsers->get_permission() == 10) {
                $is_admin = false;
                if(isset($_COOKIE['upicrm_lead_table_from_date'])&&isset($_COOKIE['upicrm_lead_table_to_date'])) {
                    $totalLeads = $UpiCRMStatistics->get_total_leads_by_user_id($user_id, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalFormsLeads = $UpiCRMStatistics->get_all_active_forms_lead_count($user_id, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadStatus = $UpiCRMStatistics->get_total_leads_status_by_user_id($user_id, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadUser = $UpiCRMStatistics->get_total_leads_assigned_by_user_id($user_id, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadClosed = $UpiCRMStatistics->get_total_closed_leads_by_user_id($user_id, $check_date, $status, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadOpen = $UpiCRMStatistics->get_total_closed_leads_by_user_id($user_id, $check_date, $statusOpen, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadContry = $UpiCRMStatistics->get_total_leads_group_field_by_user_id($user_id, 17, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadProduct = $UpiCRMStatistics->get_total_leads_group_field_by_user_id($user_id, 13, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadReceivedFrom = $UpiCRMStatistics->get_total_leads_group_field_name_by_user_id($user_id, 'Received From', $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadWebsite = $UpiCRMStatistics->get_total_leads_group_field_by_user_id($user_id, 12, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                } else {

                    $totalLeads = $UpiCRMStatistics->get_total_leads_by_user_id($user_id, $check_date);
                    $totalFormsLeads = $UpiCRMStatistics->get_all_active_forms_lead_count($user_id, $check_date);
                    $totalLeadStatus = $UpiCRMStatistics->get_total_leads_status_by_user_id($user_id, $check_date);
                    $totalLeadUser = $UpiCRMStatistics->get_total_leads_assigned_by_user_id($user_id, $check_date);
                    $totalLeadClosed = $UpiCRMStatistics->get_total_closed_leads_by_user_id($user_id, $check_date, $status);
                    $totalLeadOpen = $UpiCRMStatistics->get_total_closed_leads_by_user_id($user_id, $check_date, $statusOpen);
                    $totalLeadContry = $UpiCRMStatistics->get_total_leads_group_field_by_user_id($user_id, 17, $check_date);
                    $totalLeadProduct = $UpiCRMStatistics->get_total_leads_group_field_by_user_id($user_id, 13, $check_date);
                    $totalLeadReceivedFrom = $UpiCRMStatistics->get_total_leads_group_field_name_by_user_id($user_id, 'Received From', $check_date);
                    $totalLeadWebsite = $UpiCRMStatistics->get_total_leads_group_field_by_user_id($user_id, 12, $check_date);
                    
                }   
                    
                $getLeads = $UpiCRMLeads->get($user_id,1,8);
            }
           // if ($UpiCRMUsers->get_permission() == 2) {
                $is_admin = true;
                $totalLeadsMe = $UpiCRMStatistics->get_total_leads_by_user_id($user_id);
                if(isset($_COOKIE['upicrm_lead_table_from_date'])&&isset($_COOKIE['upicrm_lead_table_to_date'])) {
                    $totalLeads = $UpiCRMStatistics->get_total_leads($check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalFormsLeads = $UpiCRMStatistics->get_all_active_forms_lead_count(0, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadStatus = $UpiCRMStatistics->get_total_leads_status_by_user_id(0, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadUser = $UpiCRMStatistics->get_total_leads_assigned_by_user_id(0, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadClosed = $UpiCRMStatistics->get_total_closed_leads_by_user_id(0, $check_date, $status, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadOpen = $UpiCRMStatistics->get_total_closed_leads_by_user_id(0, $check_date, $statusOpen, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadContry = $UpiCRMStatistics->get_total_leads_group_field_by_user_id(0, 17, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadProduct = $UpiCRMStatistics->get_total_leads_group_field_by_user_id(0, 13, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadReceivedFrom = $UpiCRMStatistics->get_total_leads_group_field_name_by_user_id(0, 'Received From', $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                    $totalLeadWebsite = $UpiCRMStatistics->get_total_leads_group_field_by_user_id(0, 12, $check_date, $_COOKIE['upicrm_lead_table_from_date'], $_COOKIE['upicrm_lead_table_to_date']);
                } else {
                    $totalLeads = $UpiCRMStatistics->get_total_leads($check_date);
                    $totalLeadStatus = $UpiCRMStatistics->get_total_leads_status_by_user_id(0, $check_date);
                    $totalFormsLeads = $UpiCRMStatistics->get_all_active_forms_lead_count(0, $check_date);
                    $totalLeadUser = $UpiCRMStatistics->get_total_leads_assigned_by_user_id(0, $check_date);
                    $totalLeadClosed = $UpiCRMStatistics->get_total_closed_leads_by_user_id(0, $check_date, $status);
                    $totalLeadOpen = $UpiCRMStatistics->get_total_closed_leads_by_user_id(0, $check_date, $statusOpen);
                    $totalLeadContry = $UpiCRMStatistics->get_total_leads_group_field_by_user_id(0, 17, $check_date);
                    $totalLeadProduct = $UpiCRMStatistics->get_total_leads_group_field_by_user_id(0, 13, $check_date);
                    $totalLeadReceivedFrom = $UpiCRMStatistics->get_total_leads_group_field_name_by_user_id(0, 'Received From', $check_date);
                    $totalLeadWebsite = $UpiCRMStatistics->get_total_leads_group_field_by_user_id(0, 12, $check_date);
                    
                    
                }
                
                $getLeads = $UpiCRMLeads->get(0,1,8);
                for ($i=1; $i <= 5; $i++) {
                    $weeksArr[] = $UpiCRMStatistics->get_total_leads_by_weeks($i);
                }
                $weeksArr = array_reverse($weeksArr);
            //}
            
            require_once get_upicrm_template_path('dashboard');

        }
        
    function change_time() {
       if(isset($_GET['days'])) {
           @setcookie("upicrm_lead_table_days", $_GET['days']);
           $_COOKIE['upicrm_lead_table_days'] = upicrm_clean_data($_GET['days']);
       }
        if(isset($_GET['from_date'])) {
            @setcookie("upicrm_lead_table_from_date", $_GET['from_date']);
            $_COOKIE['upicrm_lead_table_from_date'] = upicrm_clean_data($_GET['from_date']);
        }
        if(isset($_GET['to_date'])) {
            @setcookie("upicrm_lead_table_to_date", $_GET['to_date']);
            $_COOKIE['upicrm_lead_table_to_date'] = upicrm_clean_data($_GET['to_date']);
        }
    }
    }
endif;
