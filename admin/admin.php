<?php
if ( !class_exists('UpiCRMAdmin') ):
function my_custom_admin_head() {
  if (get_option('google_client_id')) {
      echo '<meta name="google-signin-client_id" content="957858017566-cp739cshvn2lj2aeafd4r5v8e0kit5tg.apps.googleusercontent.com" />';
  }
}
add_action( 'admin_head', 'my_custom_admin_head' );
function defer_js_async($tag){
  $scripts = array('https://apis.google.com/js/platform.js');
  $scripts_to_async = array('https://apis.google.com/js/platform.js');
  foreach($scripts as $script){
    if(true == strpos($tag, $script ) )
      return str_replace( '></script>', ' async defer></script>', $tag ); 
  }
  return $tag;
}
add_filter( 'script_loader_tag', 'defer_js_async', 10 );

    class UpiCRMAdmin{
        public function __construct() {   
            add_action( 'admin_menu', array( $this, 'onWpAdminMenu' ) );
            add_action( 'admin_init', array( $this, 'user_has_access_lead' ) );
        } 

        public function user_has_access_lead(){
            global $pagenow;
            if($pagenow == 'admin.php' && $_REQUEST['page'] == 'upicrm_edit_lead')
            {
                $UpiCRMLeads = new UpiCRMLeads();
                $lead_id = (int)$_GET['id'];
                $hasAccess = $UpiCRMLeads->user_has_access_lead($lead_id);
                if(!$hasAccess)
                {
                    wp_die( __( 'Sorry, you are not allowed to access this page.' ), 403 );
                }
            }
        }
        public function onWpAdminMenu() {
            global $pagenow;
            wp_register_style( 'upicrm_css_bootstrap', UPICRM_URL.'resources/css/bootstrap.css', FALSE, '0.1' );
            if (is_rtl()) {
                wp_register_style( 'upicrm_css', UPICRM_URL.'resources/css/smartadmin-production-rtl.css', FALSE, '0.1' );
            } else {
                wp_register_style( 'upicrm_css', UPICRM_URL.'resources/css/smartadmin-production.css', FALSE, '0.1' );
            }
            wp_register_style( 'upicrm_css_smart_admin_skins', UPICRM_URL.'resources/css/smartadmin-skins.css', FALSE, '0.1' );
            wp_register_style( 'upicrm_css_font','https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css', FALSE, '0.1' ); 
            wp_register_style( 'upicrm_css_bootstrap_multiselect', UPICRM_URL.'resources/css/bootstrap-multiselect.css', FALSE, '0.1' ); 
            wp_register_style( 'upicrm_css_bootstrap_tagsinput', UPICRM_URL.'resources/css/bootstrap-tagsinput.css', FALSE, '0.1' );
            wp_register_style( 'upicrm_css_datepicker', UPICRM_URL.'resources/css/datepicker.min.css', FALSE, '0.1' ); 
            wp_register_style( 'upicrm_main_style', UPICRM_URL.'css/style.css', FALSE, '0.7' ); 
            if (is_rtl()) {
               wp_register_style( 'upicrm_main_style_rtl', UPICRM_URL.'css/rtl.css', FALSE, '0.2' ); 
               wp_enqueue_style('upicrm_main_style_rtl');
            }
            //wp_register_style( 'upicrm_data_table_style', 'https://cdn.datatables.net/t/dt/dt-1.10.11/datatables.min.css', FALSE, '0.2' ); 
            //wp_register_script('upicrm_googleapi', 'https://apis.google.com/js/platform.js');
            /*wp_register_script('upicrm_jquery', 'https://code.jquery.com/jquery-1.12.0.min.js', array('jquery'), '2.0');
            wp_register_script('upicrm_jquery_ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js', array('jquery'), '2.0');*/

            wp_register_script('upicrm_js_sparkline',  UPICRM_URL.'resources/js/plugin/sparkline/jquery.sparkline.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_jarvis',  UPICRM_URL.'resources/js/plugin/smartwidgets/jarvis.widget.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_dataTable',  UPICRM_URL.'resources/js/plugin/datatables/jquery.dataTables.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_colVis',  UPICRM_URL.'resources/js/plugin/datatables/dataTables.colVis.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_tableTools',  UPICRM_URL.'resources/js/plugin/datatables/dataTables.tableTools.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_tablebootstrap',  UPICRM_URL.'resources/js/plugin/datatables/dataTables.bootstrap.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_responsive',  UPICRM_URL.'resources/js/plugin/datatable-responsive/datatables.responsive.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_app',  UPICRM_URL.'resources/js/app.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_bootstrap',  UPICRM_URL.'resources/js/bootstrap.min.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_bootstrap_multiselect',  UPICRM_URL.'resources/js/bootstrap-multiselect.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_tagsinput',  UPICRM_URL.'resources/js/bootstrap-tagsinput.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_datepicker',  UPICRM_URL.'resources/js/datepicker.min.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_main',  UPICRM_URL.'js/main.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_admin',  UPICRM_URL.'js/admin.js', array('jquery'), '1.1');

            if($pagenow == 'admin.php' && $_REQUEST['page'] == 'upicrm_edit_lead')
            {
                wp_enqueue_style( 'upicrm_css_bootstrap_tagsinput' );
                wp_enqueue_script('upicrm_js_tagsinput');
            }

            if (isset($_GET['page'])&&(substr($_GET['page'],0,6) == "upicrm")) {
                wp_enqueue_style( 'upicrm_css_bootstrap' );
                wp_enqueue_style( 'upicrm_css' );
                wp_enqueue_style( 'upicrm_css_smart_admin_skins' );
                wp_enqueue_style( 'upicrm_css_font' ); 
                wp_enqueue_style( 'upicrm_css_bootstrap_multiselect' );
                wp_enqueue_style( 'upicrm_main_style' );
                wp_enqueue_style( 'upicrm_css_datepicker' );
                //wp_enqueue_style( 'upicrm_data_table_style' );
                //wp_enqueue_script('upicrm_jquery');
                //wp_enqueue_script('jquery');
                wp_enqueue_script('jquery_ui');
                wp_enqueue_script('jquery-ui-widget');
                wp_enqueue_script('jquery-ui-tabs');
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-ui-draggable');
                wp_enqueue_script('upicrm_js_sparkline'); 
                wp_enqueue_script('upicrm_js_app');
                wp_enqueue_script('upicrm_js_bootstrap');
                wp_enqueue_script('upicrm_js_bootstrap_multiselect');
                wp_enqueue_script('upicrm_js_jarvis');  
                wp_enqueue_script('upicrm_js_dataTable'); 
                wp_enqueue_script('upicrm_js_colVis');
                wp_enqueue_script('upicrm_js_tableTools'); 
                wp_enqueue_script('upicrm_js_tablebootstrap');
                wp_enqueue_script('upicrm_js_dataTable_cdn');
                wp_enqueue_script('upicrm_js_responsive');
                wp_enqueue_script('upicrm_js_datepicker');
                
                wp_enqueue_script('upicrm_googleapi');
                wp_enqueue_script('upicrm_js_main');
                wp_enqueue_script('upicrm_js_admin');
                
                
                //ICL_AdminNotifier::add_script();
                //wp_deregister_script('icl-admin-notifier');

            }

            $UpiCRMUsers = new UpiCRMUsers();
            if ($UpiCRMUsers->get_permission() != 3) {
                if (1 <= $UpiCRMUsers->get_permission()) {
                    add_menu_page('UpiCRM', 'UpiCRM', 'read', 'upicrm_index', array( $this, 'onDisplayDashboard' ), UPICRM_URL . 'resources/images/icon_crm.gif', "27.666");

                    add_submenu_page( 'upicrm_index', __('Lead Management','upicrm'), __('Lead Management','upicrm'), 'read', 'upicrm_allitems', array( $this, 'onDisplayMainMenu' ) );
                    add_submenu_page( 'upicrm_dont_show', '', '', 'read', 'upicrm_edit_lead', array( $this, 'onDisplayAdminEditLead' ) );
                    add_submenu_page( 'upicrm_dont_show', '', '', 'read', 'upicrm_api', array( $this, 'onDisplayAdminAPI' ) );
                    add_submenu_page( 'upicrm_index', __('Trash Lead','upicrm'), __('Trash Lead','upicrm'), 'read', 'upicrm_allitems_trash', array( $this, 'onDisplayMainMenu_Trash' ) );
                }
                if (1 < $UpiCRMUsers->get_permission()) {
                    add_submenu_page( 'upicrm_index', __('Data Types & Fields','upicrm'), __('Data Types & Fields','upicrm'), 'read', 'upicrm_settings', array( $this, 'onDisplayCommonSettings' ) );
                    //add_submenu_page( 'upicrm_index', __('Existing Fields','upicrm'), __('Existing Fields','upicrm'), 'read', 'upicrm_existing_fields', array( $this, 'onDisplayExistingFields' ) );
                    //add_submenu_page( 'upicrm_index', __('Existing Statuses','upicrm'), __('Existing Statuses','upicrm'), 'read', 'upicrm_existing_statuses', array( $this, 'onDisplayExistingStatuses' ) );
                    add_submenu_page( 'upicrm_index', __('Email Notifications','upicrm'), __('Email Notifications','upicrm'), 'read', 'upicrm_email_notifications', array( $this, 'onDisplayEmailNotifications' ) );
                    add_submenu_page( 'upicrm_index', __('Auto Lead Management','upicrm'), __('Auto Lead Management','upicrm'), 'read', 'upicrm_lead_route', array( $this, 'onDisplayLeadRouting' ) );
                    add_submenu_page( 'upicrm_index', __('Lead Aggregation','upicrm'), __('Lead Aggregation','upicrm'), 'read', 'upicrm_integrations', array( $this, 'onDisplayIntegrations' ) );
                    //add_submenu_page( 'upicrm_index', __('Import & Export','upicrm'), __('Import & Export','upicrm'), 'read', 'upicrm_import_export', array( $this, 'onDisplayImport' ) );
                    add_submenu_page( 'upicrm_index', __('Import','upicrm'), __('Import','upicrm'), 'read', 'upicrm_import', array( $this, 'onDisplayImport' ) );
                    add_submenu_page( 'upicrm_index', __('Export','upicrm'), __('Export','upicrm'), 'read', 'upicrm_export', array( $this, 'onDisplayExport' ) );
                    add_submenu_page( 'upicrm_index', __('GDPR','upicrm'), __('GDPR','upicrm'), 'read', 'upicrm_privacy', array( $this, 'onDisplayPrivacy' ) );
                   // add_submenu_page( 'upicrm_index', __('Web Services','upicrm'), __('Web Services','upicrm'), 'read', 'upicrm_webservices', array( $this, 'onDisplayWebServices'));
                    //add_submenu_page( 'upicrm_index', __('Google Auth','upicrm'), __('Google Authentication','upicrm'), 'read', 'upicrm_googleauth', array( $this, 'onDisplayGoogleAuth' ) );
                    add_submenu_page( 'upicrm_index', __('Web Services','upicrm'), __('Web Services','upicrm'), 'read', 'upicrm_webservices', array( $this, 'onDisplayWebServices'));
                    add_submenu_page( 'upicrm_index', __('Users Center','upicrm'), __('Users Center','upicrm'), 'read', 'upicrm_users_center', array( $this, 'onDisplayUser' ) );
                   add_submenu_page( 'upicrm_index', __('Affiliates Center','upicrm'), __('Affiliates Center','upicrm'), 'read', 'upicrm_affiliate', array( $this, 'onDisplayAffiliate' ) );
                    add_submenu_page( 'upicrm_dont_show', '', '', 'read', 'upicrm_wsp', array( $this, 'onDisplayWebServiceParameters' ) );
                     //add_submenu_page( 'upicrm_dont_show', '', '', 'read', 'upicrm_wsp', array( $this, 'UpiCRMAdminFBbanner' ) );
                    if (!is_plugin_active('upicrm-facebook/upicrm-fb.php')) {
                        add_submenu_page('upicrm_index', __('Social Networks Integration', 'upicrm'), __('Social Networks Integration', 'upicrm'), 'read', 'upicrm_fb_banner', array( $this, 'onDisplayFBbanner'));
                    }
                }
            }
        }
        private function beforeAllAdminPages() {
            global $wp_version;
            if ($wp_version >= 6) {
                ?><div class="alert alert-warning fade in">
                    <i class="fa-fw fa fa-warning"></i>
                    <strong>Warning</strong> Your UpiCRM version is not compatible with WordPress 5.X . <a href="http://www.upicrm.com/?utm_source=upicrmvf">Please upgrade your UpiCRM WordPress CRM solution here</a>.
                </div><?php
            }
            
        }
        public function onDisplayDashboard(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Dashboard','upicrm');
            $upi_setting['logo'] = 'home';
            $UpiCRMAdminWarp->header($upi_setting);
            $UpiCRMAdminIndex = new UpiCRMAdminIndex();
            $UpiCRMAdminIndex->Render();
            $UpiCRMAdminWarp->footer();
        }

        public function onDisplayGoogleAuth() {
            
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Dashboard','upicrm');
            $upi_setting['logo'] = 'home';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminGoogle = new UpiCRMAdminGoogleAuth();
            $UpiCRMAdminGoogle->Render();
            
            $UpiCRMAdminWarp->footer();
        }

        public function onDisplayCommonSettings(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Data Types & Fields','upicrm');
            $upi_setting['logo'] = 'cogs';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminSettings = new UpiCRMAdminSettings();
            $UpiCRMAdminSettings->Render(); 
            
            $UpiCRMAdminWarp->footer();
        }
	    public function onDisplayMainMenu() {	
            global $title;           
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Leads Management','upicrm');
            $upi_setting['logo'] = 'table';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminAdminLists = new UpiCRMAdminAdminLists();
            $UpiCRMAdminAdminLists->RenderLists(); 
            
            $UpiCRMAdminWarp->footer();
	    } 
        public function onDisplayMainMenu_Trash() {	
            global $title;           
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Trash Leads','upicrm');
            $upi_setting['logo'] = 'table';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminAdminLists_Trash = new UpiCRMAdminAdminLists_Trash();
            $UpiCRMAdminAdminLists_Trash->RenderLists(); 
            
            $UpiCRMAdminWarp->footer();
	    } 
        
        public function onDisplayExistingFields(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Existing Fields','upicrm');
            $upi_setting['logo'] = 'bolt';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminExistingFields = new UpiCRMAdminExistingFields();
            $UpiCRMAdminExistingFields->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayExistingStatuses(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Existing Statuses','upicrm');
            $upi_setting['logo'] = 'bolt';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminExistingStatuses = new UpiCRMAdminExistingStatuses();
            $UpiCRMAdminExistingStatuses->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayEmailNotifications(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Email Notifications','upicrm');
            $upi_setting['logo'] = 'envelope-o';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminEmailNotifications = new UpiCRMAdminEmailNotifications();
            $UpiCRMAdminEmailNotifications->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayAdminEditLead() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Lead','upicrm');
            $upi_setting['logo'] = 'pencil-square-o';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminEditLead = new UpiCRMAdminEditLead();
            $UpiCRMAdminEditLead->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayAdminAPI() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['logo'] = 'magic';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminAPI = new UpiCRMAdminAPI();
            $UpiCRMAdminAPI->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayLeadRouting() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Auto Lead Management','upicrm');
            $upi_setting['logo'] = 'cogs';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminLeadRoute = new UpiCRMAdminLeadRoute();
            $UpiCRMAdminLeadRoute->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayIntegrations() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Lead Aggregation','upicrm');
            $upi_setting['logo'] = 'exchange';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminIntegrations = new UpiCRMAdminIntegrations();
            $UpiCRMAdminIntegrations->Render();
            
            $UpiCRMAdminWarp->footer();
        }

        public function onDisplayWebServices() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Web Services','upicrm');
            $upi_setting['logo'] = 'exchange';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminIntegrations = new UpiCRMAdminWebServices();
            $UpiCRMAdminIntegrations->Render();
            
            $UpiCRMAdminWarp->footer();
        }

        public function onDisplayUser() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Users Center','upicrm');
            $upi_setting['logo'] = 'user';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminUsers = new UpiCRMAdminUsers();
            $UpiCRMAdminUsers->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayExport() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Export','upicrm');
            $upi_setting['logo'] = 'sign-in';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminImportExport = new UpiCRMAdminImportExport();
            $UpiCRMAdminImportExport->Render('export');
            
            $UpiCRMAdminWarp->footer();
        }

        public function onDisplayImport() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Import','upicrm');
            $upi_setting['logo'] = 'sign-in';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminImportExport = new UpiCRMAdminImportExport();
            $UpiCRMAdminImportExport->Render('import');
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayWebServiceParameters() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Web Service Options','upicrm');
            $upi_setting['logo'] = 'cogs';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminWebServiceParameters = new UpiCRMAdminWebServiceParameters();
            $UpiCRMAdminWebServiceParameters->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayPrivacy() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('GDPR','upicrm');
            $upi_setting['logo'] = 'user-secret';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminPrivacy = new UpiCRMAdminPrivacy();
            $UpiCRMAdminPrivacy->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayFBbanner() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Social Networks Integration','upicrm');
            $upi_setting['logo'] = 'facebook-square';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminFBbanner = new UpiCRMAdminFBbanner();
            $UpiCRMAdminFBbanner->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        
        function onDisplayAffiliate() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Affiliates Center','upicrm');
            $upi_setting['logo'] = 'users';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminAffiliate = new UpiCRMAdminAffiliate();
            $UpiCRMAdminAffiliate->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
    }
endif;