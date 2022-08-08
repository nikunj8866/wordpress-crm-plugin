<?php
function upicrm_setup_plugin() {
    global $wpdb;
    
    $charset_collate = '';

    if ( ! empty( $wpdb->charset ) ) {
      $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    }

    if ( ! empty( $wpdb->collate ) ) {
      $charset_collate .= " COLLATE {$wpdb->collate}";
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."leads (
            `lead_id` INT NOT NULL AUTO_INCREMENT,
            `source_type` INT NOT NULL,
            `source_id` INT NOT NULL,
            `lead_email` varchar(255),
            `lead_content` TEXT,
            `user_ip` TEXT,
            `user_agent` TEXT,
            `user_referer` TEXT,
            `old_user_lead_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `lead_status_id` INT NOT NULL,
            `lead_management_comment` TEXT,
            `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`lead_id`)
   ) $charset_collate;";
    $wpdb->query($sql);

    $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."lead_users (
        `ID` INT NOT NULL AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `lead_id` INT NOT NULL,
        `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`ID`)
    ) $charset_collate;";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."leads_campaign (
            `lead_id` INT,
            `utm_source` TEXT,
            `utm_medium` TEXT,
            `utm_term` TEXT,
            `utm_content` TEXT,
            `utm_campaign` TEXT
   ) $charset_collate;";
    $wpdb->query($sql);
    
   $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."fields_mapping (
  `fm_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `fm_name` text NOT NULL,
  `source_id` int(11) NOT NULL,
  `source_type` int(11) NOT NULL,
  PRIMARY KEY (`fm_id`)
   ) $charset_collate;";
    $wpdb->query($sql);
    
   $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."fields (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` text NOT NULL,
  PRIMARY KEY (`field_id`)
   ) $charset_collate;";
    $wpdb->query($sql);
    
    $sql = "REPLACE INTO ".upicrm_db()."fields (`field_id`, `field_name`) VALUES
    (1, 'Name'),
    (2, 'Last name'),
    (3, 'Date'),
    (4, 'Message subject'),
    (5, 'Phone number mobile'),
    (6, 'Phone number work'),
    (7, 'Phone number home'),
    (8, 'Email'),
    (9, 'Role'),
    (10, 'Company'),
    (11, 'Industry'),
    (12, 'Website'),
    (13, 'Product'),
    (14, 'Service'),
    (15, 'City'),
    (16, 'Street'),
    (17, 'Country'),
    (18, 'Zip code'),
    (19, 'Address'),
    (20, 'Fax number'),
    (21, 'Future contact allowed'),
    (22, 'Message details/Remarks')
    ;";
    $wpdb->query($sql);
    
   $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."leads_status (
  `lead_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_status_name` varchar(100) NOT NULL,
  PRIMARY KEY (`lead_status_id`),
  UNIQUE (`lead_status_name`)
   ) $charset_collate;";
    $wpdb->query($sql);
    
    $sql = "REPLACE INTO ".upicrm_db()."leads_status (`lead_status_id`, `lead_status_name`) VALUES
    (1, 'Open'),
    (2, 'Close'),
    (3, 'Spam')
    ;";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."mails (
            `mail_id` INT NOT NULL AUTO_INCREMENT,
            `mail_event` TEXT,
            `mail_content` TEXT,
            `mail_subject` TEXT,
            `mail_cc` TEXT,
            `mail_event_name` TEXT,
            PRIMARY KEY (`mail_id`)
   ) $charset_collate;";
    $wpdb->query($sql);

    $sql = "REPLACE INTO ".upicrm_db()."mails (`mail_id`, `mail_event`, `mail_content`, `mail_subject`, `mail_cc`, `mail_event_name`) VALUES
    (1, 'new_lead','[lead]','New Lead','','New Lead'),
    (2, 'change_user','[lead]','Change User','','Change User'),
    (3, 'change_lead_status','[lead]','Change Lead Status','','Change Lead Status'),
    (4, 'request_status','[lead]','Request status update','','Request status update from lead owner')
    ;";
    $wpdb->query($sql);
    
    
    //update all admins permissions to Upi CRM Admin
     $users = get_users( array( 'role' => 'Administrator' ));
     foreach ($users as $user) {
         update_user_meta( $user->ID,'upicrm_user_permission', 2);
     } 

    
    if (!get_option('upicrm_default_email')) {
        $default_email = get_option( 'admin_email' );
        add_option('upicrm_default_email', $default_email);
    } 
    
    $UpiCRMPrivacy = new UpiCRMPrivacy();
    $UpiCRMPrivacy->delete_files();
}

function upicrm_remove_plugin_data() {
    global $wpdb; 
    $sql = "DROP TABLE ".upicrm_db()."leads";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."leads_campaign";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."fields_mapping";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."fields";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."leads_status";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."mails";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."options";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."users";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."leads_route";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."integrations";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."leads_integration";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."leads_status";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."webservice";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."webservice_parameters";
    $wpdb->query($sql);
}

function upicrm_update_db_check() {
    global $upicrm_db_version, $wpdb;
    $charset_collate = '';

    if ( ! empty( $wpdb->charset ) ) {
      $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    }

    if ( ! empty( $wpdb->collate ) ) {
      $charset_collate .= " COLLATE {$wpdb->collate}";
    }
    
    if (get_option("upicrm_db_version") <= 3) {
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_status` ADD UNIQUE( `lead_status_name`);";
        $wpdb->query($sql);
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_status` CHANGE `lead_status_name` `lead_status_name` VARCHAR(100);";
        $wpdb->query($sql);
        
        $sql = "INSERT INTO ".upicrm_db()."leads_status (`lead_status_name`) VALUES
        ('Not relevant')
        ;";
        $wpdb->query($sql);
        
        $sql = "UPDATE ".upicrm_db()."fields SET `field_name` = 'Phone number' WHERE `field_name` = 'Phone number home';";
        $wpdb->query($sql);

    }

    if (get_option("upicrm_db_version") != $upicrm_db_version) {
        $sql = "CREATE TABLE ".upicrm_db()."leads_route (
            `lead_route_id` int(11) NOT NULL AUTO_INCREMENT,
            `field_id` int(11) NOT NULL,
            `lead_route_type` int(11) NOT NULL,
            `lead_route_value` text NOT NULL,
            `user_id` int(11) NOT NULL,
            `lead_status_id` int(11) NOT NULL,
            PRIMARY KEY (`lead_route_id`)
       ) $charset_collate;";
        $wpdb->query($sql);
        
    $sql = "CREATE TABLE ".upicrm_db()."integrations (
            `integration_id` INT(11) NOT NULL AUTO_INCREMENT,
            `integration_domain` TEXT,
            `integration_key` TEXT,
            `integration_status` TEXT,
            `integration_is_slave` INT(1),
            PRIMARY KEY (`integration_id`)
       ) $charset_collate;";
        $wpdb->query($sql);
        
    $sql = "ALTER TABLE `".upicrm_db()."integrations` ADD `integration_clean_domain` TEXT NOT NULL AFTER `integration_domain`";
    $wpdb->query($sql);
    
    
    $sql = "CREATE TABLE ".upicrm_db()."leads_integration (
            `lead_integration_id` INT(11) NOT NULL AUTO_INCREMENT,
            `lead_id` INT NOT NULL,
            `lead_id_external` INT NOT NULL,
            `integration_id` INT NOT NULL,
            `lead_integration_status` TEXT,
            `integration_is_slave` INT(1) NOT NULL,
            `lead_integration_error` INT(1) NOT NULL,
            PRIMARY KEY (`lead_integration_id`)
       ) $charset_collate;";
        $wpdb->query($sql);
         
        $sql = "CREATE TABLE ".upicrm_db()."users (
            `inside_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT,
            `user_parent_id` INT,
            `user_label` TEXT,
            `user_permission` INT,
            PRIMARY KEY (`inside_id`)
       ) $charset_collate;";
        $wpdb->query($sql);
        
        
        $sql = "ALTER TABLE `".upicrm_db()."leads` CHANGE `source_id` `source_id` TEXT NOT NULL";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."fields_mapping` CHANGE `source_id` `source_id` TEXT NOT NULL";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `change_field_id` INT NOT NULL AFTER `lead_status_id`, ADD `change_field_value` TEXT NOT NULL AFTER `change_field_id`";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."leads_route` CHANGE `field_id` `field_id` TEXT NOT NULL;";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."leads_route` CHANGE `field_id` `field_id` TEXT NOT NULL;";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `lead_route_option` VARCHAR(30) NOT NULL DEFAULT 'content' AFTER `lead_route_id`";
        $wpdb->query($sql);


        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `lead_route_and` BOOLEAN NOT NULL AFTER `lead_route_value`, ADD `lead_route_option2` VARCHAR(30) NOT NULL DEFAULT 'content' AFTER `lead_route_and`, ADD `field_id2` TEXT NOT NULL AFTER `lead_route_option2`, ADD `lead_route_type2` INT NOT NULL AFTER `field_id2`, ADD `lead_route_value2` TEXT NOT NULL AFTER `lead_route_type2`";
       $wpdb->query($sql);
       
       
       
        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `lead_route_option3` VARCHAR(30) NOT NULL DEFAULT 'content' AFTER `lead_route_value2`, ADD `field_id3` TEXT NOT NULL AFTER `lead_route_option3`, ADD `lead_route_type3` INT NOT NULL AFTER `field_id3`, ADD `lead_route_value3` TEXT NOT NULL AFTER `lead_route_type3`";
       $wpdb->query($sql);
       

        $sql = "CREATE TABLE IF NOT EXISTS `".upicrm_db()."webservice` (
      `webservice_id` int(11) NOT NULL AUTO_INCREMENT,
      `webservice_method` int(11) NOT NULL,
      `webservice_status` int(11) NOT NULL,
      `webservice_url` TEXT,
      PRIMARY KEY (`webservice_id`)
       ) $charset_collate;";
        $wpdb->query($sql); 
        
        $sql = "CREATE TABLE IF NOT EXISTS `".upicrm_db()."webservice_parameters` (
      `webservice_parameter_id` int(11) NOT NULL AUTO_INCREMENT,
      `webservice_id` int(11) NOT NULL,
      `webservice_parameter_option` VARCHAR(30) NOT NULL DEFAULT 'content',
      `field_id` TEXT,
      `webservice_parameter_value` TEXT,
      PRIMARY KEY (`webservice_parameter_id`)
       ) $charset_collate;";
        $wpdb->query($sql); 
        
        $sql = "ALTER TABLE `".upicrm_db()."webservice` ADD `webservice_header_key1` TEXT NOT NULL AFTER `webservice_log`, ADD `webservice_header_value1` TEXT NOT NULL AFTER `webservice_header_key1`, ADD `webservice_header_key2` TEXT NOT NULL AFTER `webservice_header_value1`, ADD `webservice_header_value2` TEXT NOT NULL AFTER `webservice_header_key2`, ADD `webservice_header_key3` TEXT NOT NULL AFTER `webservice_header_value2`, ADD `webservice_header_value3` TEXT NOT NULL AFTER `webservice_header_key3`;";
        $wpdb->query($sql); 
        
        
        for ($i=4; $i<=10; $i++) {
            $min = $i-1;
            $sql = "ALTER TABLE `".upicrm_db()."webservice` ADD `webservice_header_key{$i}` TEXT NOT NULL AFTER `webservice_header_value{$min}`, ADD `webservice_header_value{$i}` TEXT NOT NULL AFTER `webservice_header_key{$i}`;";
            $wpdb->query($sql); 
        }
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `webservice_id` int(11) NOT NULL";
        $wpdb->query($sql);
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `integration_id` INT NOT NULL DEFAULT '0' AFTER `webservice_id`;";
        $wpdb->query($sql);
        
        $sql = "ALTER TABLE `".upicrm_db()."webservice` ADD `webservice_charset` VARCHAR(100) NOT NULL DEFAULT 'UTF-8'";
        $wpdb->query($sql);
        
        $sql = "ALTER TABLE `".upicrm_db()."webservice` ADD `webservice_log` int(1) NOT NULL";
        $wpdb->query($sql);
        
        $sql = "ALTER TABLE `".upicrm_db()."leads` ADD `lead_webservice_transmission` VARCHAR(300)";
        $wpdb->query($sql);
    
        $sql = "CREATE TABLE IF NOT EXISTS `".upicrm_db()."options` (
		`id` int NOT NULL AUTO_INCREMENT, 
		`name` varchar(255) NOT NULL, 
		`value` varchar(255) NOT NULL, PRIMARY KEY(id)
	);";
        $wpdb->query($sql);
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `leads_route_rr_users` TEXT NOT NULL AFTER `lead_route_value2`, ADD `leads_route_rr_count` INT NOT NULL AFTER `leads_route_rr_users`;";
        $wpdb->query($sql);
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `mail_id` INT NOT NULL DEFAULT '0' AFTER `webservice_id`, ADD `lead_route_email_to` TEXT NOT NULL AFTER `mail_id`";
        $wpdb->query($sql); 
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `lead_route_order` INT NOT NULL DEFAULT '0' AFTER `lead_route_id`;";
        $wpdb->query($sql);

        
        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `lead_route_mail_to_field_id` TEXT NOT NULL  AFTER `lead_route_email_to`;";
        $wpdb->query($sql); 
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `lead_route_mail_no_cc` BOOLEAN NOT NULL DEFAULT FALSE AFTER `lead_route_mail_to_field_id`;";
        $wpdb->query($sql);
         
       $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `affiliate_id` INT NOT NULL DEFAULT '0' AFTER `lead_route_mail_no_cc`;";
        $wpdb->query($sql);
        
       $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `affiliate_type` INT NOT NULL DEFAULT '0' AFTER `affiliate_id`;";
       $wpdb->query($sql);
        
       $sql = "ALTER TABLE `".upicrm_db()."integrations` ADD `integration_type` INT NOT NULL DEFAULT '1' AFTER `integration_is_slave`";
        $wpdb->query($sql);
        
       $sql = "ALTER TABLE `".upicrm_db()."leads_integration` ADD `lead_integration_wait_send` BOOLEAN NOT NULL DEFAULT FALSE AFTER `lead_integration_error`;";
       $wpdb->query($sql);
        
       $sql = "ALTER TABLE `".upicrm_db()."leads` ADD `lead_log_text` TEXT NOT NULL AFTER `lead_webservice_transmission`";
       $wpdb->query($sql);
       
       $sql = "ALTER TABLE `".upicrm_db()."leads` ADD `affiliate_id` INT NOT NULL DEFAULT '0' AFTER `lead_log_text`;";
       $wpdb->query($sql);
       
       $sql = "ALTER TABLE `".upicrm_db()."leads` ADD `affiliate_time` TIMESTAMP NOT NULL AFTER `affiliate_id`;";
       $wpdb->query($sql);
       
       $sql = "ALTER TABLE `".upicrm_db()."leads` ADD `affiliate_type` INT NOT NULL DEFAULT '0' AFTER `affiliate_time`;";
       $wpdb->query($sql);
       
       /*$sql = "ALTER TABLE `".upicrm_db()."users` ADD `user_parent_affiliate_id` INT NOT NULL DEFAULT '0' AFTER `user_parent_id`;";
       $wpdb->query($sql);*/

       $sql = "ALTER TABLE `".upicrm_db()."leads` ADD `is_trash` INT NOT NULL DEFAULT '0' AFTER `affiliate_type`;";
       $wpdb->query($sql);
      
        
        $sql = "CREATE TABLE IF NOT EXISTS `".upicrm_db()."leads_changes_log` (
      `lead_change_log_id` int(11) NOT NULL AUTO_INCREMENT,
      `lead_id` int(11) NOT NULL,
      `lead_change_log_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `lead_status_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `email_log_id` int(11) NOT NULL,
      `lead_management_comment` TEXT,
      `lead_change_log_edit_text` TEXT,
      `log_comments` TEXT,
      `maker_lead_route_id` int(11) NOT NULL,
      `maker_user_id` int(11) NOT NULL,
      PRIMARY KEY (`lead_change_log_id`)
       ) $charset_collate;";
        $wpdb->query($sql); 

        $sql = "CREATE TABLE IF NOT EXISTS `".upicrm_db()."leads_email_log` (
            `email_log_id` int(11) NOT NULL AUTO_INCREMENT,
            `lead_id` int(11) NOT NULL,
            `lead_change_log_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `user` int(11) NOT NULL,
            `to` TEXT,
            `fromaddress` TEXT,    
            `cc` TEXT,
            `subject` TEXT,
            `mail_content` TEXT,
            `timestamp` int(11) NOT NULL,
            `is_sync` ENUM(0,1),
            `uid` int(11) NOT NULL,
            `msg_id` varchar(255),
            `parent_msg_id` varchar(255),
            PRIMARY KEY (`email_log_id`)
             ) $charset_collate;";
              $wpdb->query($sql); 
        
    
        $sql = "ALTER TABLE ".upicrm_db()."fields ADD `field_order` INT NOT NULL DEFAULT '0' AFTER `field_name`";
        $wpdb->query($sql);
    
        $sql = "ALTER TABLE ".upicrm_db()."fields ADD `field_order2` INT NOT NULL DEFAULT '0' AFTER `field_order`";
        $wpdb->query($sql);
        
        if (!get_option('upicrm_fix_stange_wordpress_query_bug')) {
            $sql = "UPDATE `".upicrm_db()."leads_route` SET `leads_route_rr_users`=`user_id`";
            $wpdb->query($sql);
            add_option('upicrm_fix_stange_wordpress_query_bug', 1);
        }
        
        if ($upicrm_db_version >= 80 && $upicrm_db_version < 85) {

            $charset_collate = str_replace("DEFAULT ","",$charset_collate);
            $sql = "ALTER TABLE `".upicrm_db()."leads_changes_log` CHANGE `lead_change_log_edit_text` `lead_change_log_edit_text` TEXT {$charset_collate} NOT NULL;";
            $wpdb->query($sql);

            $sql = "ALTER TABLE `".upicrm_db()."leads_changes_log` CHANGE `lead_management_comment` `lead_management_comment` TEXT {$charset_collate} NOT NULL;";
            $wpdb->query($sql);

        }


        update_option( "upicrm_db_version", $upicrm_db_version );
    }
    
    if (!get_option('upicrm_sender_email')) {
        add_option('upicrm_sender_email', 'no-reply');
    } 
    
    if (!get_option('upicrm_default_lead')) {
       $users = get_users( array( 'role' => 'Administrator' ));
        add_option('upicrm_default_lead', $users[0]->ID);
    } 
    if (!get_option('upicrm_email_format')) {
        add_option('upicrm_email_format', 1);
    } 
    
    if (!get_option('upicrm_enable_audit_log')) {
        add_option('upicrm_enable_audit_log', 1);
    }
    
    if (!get_option('upicrm_user_permission_1')) {
        add_option('upicrm_user_permission_1', 2);
    }
    
    if (!get_option('upicrm_user_permission_2')) {
        add_option('upicrm_user_permission_2', 1);
    }
    
    
    if (!get_option('insert_lead_gen')) {
        add_option('insert_lead_gen', 1);
        $sql = "INSERT INTO ".upicrm_db()."fields (`field_name`) VALUES ('Received From');";
        $wpdb->query($sql); 
    } 
    
    
}
?>
