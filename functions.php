<?php
if (!function_exists('get_upicrm_template_path')) {

    function get_upicrm_template_path($template) {
        return UPICRM_PATH . "template/{$template}_template.php";
        //load_template( ABSPATH . WPINC . '/theme-compat/header.php');
    }

}

if (!function_exists('upicrm_clean_data')) {

    function upicrm_clean_data($text) {
        return htmlspecialchars($text, ENT_NOQUOTES);
    }

}

if (!function_exists('is_plugin_active')) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}
if (!function_exists('upicrm_get_referer')) {

    function upicrm_get_referer() {
        $ref = '';
        if (!empty($_REQUEST['_wp_http_referer']))
            $ref = $_REQUEST['_wp_http_referer'];
        else if (!empty($_SERVER['HTTP_REFERER']))
            $ref = $_SERVER['HTTP_REFERER'];
        if ($ref !== $_SERVER['REQUEST_URI'])
            return $ref;
        return false;
    }

}

if (!function_exists('upicrm_get_user_lead_id')) {

    function upicrm_get_user_lead_id() {
        return isset($_COOKIE['old_lead_id']) ? $_COOKIE['old_lead_id'] : 0;
    }

}
if (!function_exists('upicrm_set_new_user')) {

    function upicrm_set_new_user($id) {
        @setcookie("old_lead_id", $id);
    }

}
if (!function_exists('upicrm_load')) {

    function upicrm_load($load) {
        switch ($load) {
            case 'excel':
                $path = 'resources/includes/PHPExcel.php';
                break;
            case 'guzzle':
                $path = 'resources/includes/Guzzle/autoload.php';
                break;
        }
        require_once(UPICRM_PATH . $path);
    }

}

if (!function_exists('upicrm_string_cleaner')) {

    function upicrm_string_cleaner($str) {
        $str = strtolower($str);
        $str = trim($str);
        return $str;
    }

}

if (!function_exists('upicrm_parse_url')) {

    function upicrm_parse_url($url) {
        $url_arr = parse_url($url);
        return isset($url_arr['host']) ? preg_replace('#^(http(s)?://)?w{3}\.#', '$1', $url_arr['host']) : false;
    }

}
if (!function_exists('get_user_by')) {

    function get_user_by($field, $value) {
        $userdata = WP_User::get_data_by($field, $value);
        if (!$userdata)
            return false;
        $user = new WP_User;
        $user->init($userdata);
        return $user;
    }

}

if (!function_exists('upicrm_field_name')) {

    function upicrm_field_name($field_id) {
        global $wpdb;
        return $wpdb->get_var("Select field_name from " . $wpdb->prefix . "upicrm_fields where field_id = '$field_id' ");
    }

}
/**
 * csv export func made
 *  for tests since ver 2.1.8.4
 *
 *
 * */
if (!function_exists('upicrm_export_csv')) {

    function upicrm_export_csv($last_lead=false,$return_url=false,$export_fields=false,$fields_order=1) {
        
//    global $wpdb;
        $fileName = '/leads-' . upicrm_random_hash() . '.csv';
        $dirName = WP_CONTENT_DIR . "/uploads/upicrm";
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $file = fopen($dirName . $fileName, 'w');
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMUsers = new UpiCRMUsers();
        $getNamesMap = $UpiCRMFieldsMapping->get();
        $list_option = $UpiCRMUIBuilder->get_list_option($fields_order);
        if (!$last_lead) {
            if ($UpiCRMUsers->get_permission() == 1) {
                $userID = get_current_user_id();
                $getLeads = $UpiCRMLeads->get($userID);
            } elseif ($UpiCRMUsers->get_permission() == 2) {
                $getLeads = $UpiCRMLeads->get();
            }
        } else {
            $from_id = get_option('upicrm_export_csv_last_lead') ? get_option('upicrm_export_csv_last_lead') : 0;
            
            if ($UpiCRMUsers->get_permission() == 1) {
                $userID = get_current_user_id();
                $getLeads = $UpiCRMLeads->get($userID, 0, 0, "DESC", 0, 0, 0, 0,$from_id);
            } elseif ($UpiCRMUsers->get_permission() == 2) {
                $getLeads = $UpiCRMLeads->get(0, 0, 0, "DESC", 0, 0, 0, 0,$from_id);
            }
            elseif ($UpiCRMUsers->get_permission() == 3) {
                $getLeads = $UpiCRMLeads->get(0, 0, 0, "DESC", 0, 0, 0, 0,$from_id);
            }
            

            
            $new_last_id = isset($getLeads[0]->lead_id) ? $getLeads[0]->lead_id : 0;
            
            if ($new_last_id > 0) {
                update_option('upicrm_export_csv_last_lead' ,$new_last_id);
            }
        }
        // $d = 0;
        /**
         *
         * Create csv "header"-first string
         *
         */
        
        $csv_head = '';
        foreach ($list_option as $key => $arr) {
            foreach ($arr as $key2 => $value) {
                if (($export_fields && isset($export_fields[$key][$key2])) || !$export_fields) {
                    $csv_head .= $value . ';';
                }
            }
        }
        $csv_head_arr = explode(';', $csv_head);
        /**
         * Set utf8 BOM-commented , write csv "header"
         */
        // fwrite($file,b"\xEF\xBB\xBF") ;
        fputcsv($file, $csv_head_arr, ';', '"');
        /**
         * Create Content strings
         */
        foreach ($getLeads as $leadObj) {
            $csv_cont = '';
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if ($key2 == 'source_id' && $leadObj->source_id != 0) {//Form Name local
                        $getValue = $UpiCRMLeads->get_source_form_name($leadObj->source_id, $leadObj->source_type);
                    } else if ($key2 == 'source_id' && $leadObj->source_id == 0) {//Form Name remote
                        $lead_content = $leadObj->lead_content;
                        $form_name = json_decode($lead_content, true);
                        $getValue = @$form_name['Form Name'];
                    } else {
                        $getValue = $UpiCRMUIBuilder->lead_routing($leadObj, $key, $key2, $getNamesMap, true);
                    }
                    if ($export_fields) {
                        if (isset($export_fields[$key][$key2])) {
                            $getValue = trim(str_replace(array("\r\n", "\r", "\n", ";"), '', $getValue));
                            $csv_cont .= $getValue . ';';
                        }
                    } else {
                        $getValue = trim(str_replace(array("\r\n", "\r", "\n", ";"), '', $getValue));
                        $csv_cont .= $getValue . ';';
                    }
                }
            }
            $csv_arr = explode(';', $csv_cont);
            fputcsv($file, $csv_arr, ';', '"');
        }

        fclose($file);
        if (!$return_url) {
            echo '<script>window.onload = function (event) { window.location="' . WP_CONTENT_URL . '/uploads/upicrm/' . $fileName . '"; };</script>';
        } else {
            echo WP_CONTENT_URL . '/uploads/upicrm' . $fileName;
        }
    }

}
if (!function_exists('upicrm_random_hash')) {

    function upicrm_random_hash() {
        $strlen = rand(5, 13);
        return substr(md5(openssl_random_pseudo_bytes(20)), -$strlen);
    }

}

if (!function_exists('upicrm_filtered_excel_output')) {

    function upicrm_filtered_excel_output() {

        global $wpdb;
        set_time_limit(0);
        upicrm_load('excel');
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
//        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $objPHPExcel = new PHPExcel();
//        $list_option = $UpiCRMUIBuilder->get_list_option();
        /* Get Leads */
//        $UpiCRMUsers = new UpiCRMUsers();
//        $getNamesMap = $UpiCRMFieldsMapping->get();
        $lead_status = $_POST['status'];

        if (isset($_POST['date_start']) && $_POST['date_start'] <> '') {
            list($d, $m, $y) = explode('.', $_POST['date_start']);
            $from_date = $y . '-' . $m . '-' . $d;
        }
        if (isset($_POST['date_end']) && $_POST['date_end'] <> '') {
            list($d, $m, $y) = explode('.', $_POST['date_end']);
            $to_date = $y . '-' . $m . '-' . $d;
        }

        $query = "SELECT *,  " . $wpdb->prefix . "upicrm_leads.lead_id AS `lead_id`, ";
        $query .= $wpdb->prefix . "upicrm_leads_integration.integration_is_slave AS `is_slave`, ";
        $query .= $wpdb->prefix . "upicrm_leads.user_id AS `user_id` FROM " . $wpdb->prefix . "upicrm_leads";
        $query .= " LEFT JOIN " . $wpdb->prefix . "upicrm_leads_campaign";
        $query .= " ON " . $wpdb->prefix . "upicrm_leads_campaign.lead_id = " . $wpdb->prefix . "upicrm_leads.lead_id";
        $query .= " LEFT JOIN " . $wpdb->prefix . "upicrm_leads_integration";
        $query .= " ON " . $wpdb->prefix . "upicrm_leads_integration.lead_id = " . $wpdb->prefix . "upicrm_leads.lead_id";
        $query .= " LEFT JOIN " . $wpdb->prefix . "upicrm_integrations";
        $query .= " ON " . $wpdb->prefix . "upicrm_integrations.integration_id = " . $wpdb->prefix . "upicrm_leads_integration.integration_id";
        $query .= " LEFT JOIN " . $wpdb->prefix . "upicrm_users";
        $query .= " ON " . $wpdb->prefix . "upicrm_users.user_id = " . $wpdb->prefix . "upicrm_leads.user_id";

        if (isset($from_date) && isset($to_date) && $from_date <> '' && $to_date <> '') {
            $query .= " WHERE " . $wpdb->prefix . "upicrm_leads.time >= '$from_date' AND " . $wpdb->prefix . "upicrm_leads.time <='$to_date' ";
        }
        if ($lead_status <> '') {
            $query .= " AND " . $wpdb->prefix . "upicrm_leads.lead_status_id = '" . $lead_status . "'";
        }
//      $query.= " ORDER BY lead_id {$orderBy}";
        $query .= " ORDER BY " . $wpdb->prefix . "upicrm_leads.lead_id";
//      $getLeads = $UpiCRMLeads->get();
//        echo $query."<br /><br />";
        $getLeads = $wpdb->get_results($query);
//
        ?><!--<pre>--><?php //print_r($getLeads); ?><!--</pre>--><?php
        $getNamesMap = $UpiCRMFieldsMapping->get();
        $fileName = '/leads.xlsx';
        $dirName = WP_CONTENT_DIR . "/uploads/upicrm";
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $t = "A";
        $filtered_fields = $_POST['fields'];
        $list_option = $UpiCRMUIBuilder->get_list_option(); // all fields
        /**
         *   SetCellValue
         *  Create Header
         */
        for ($i = 0; $i < count($filtered_fields); $i++) {
            $objPHPExcel->getActiveSheet()->getStyle($t . '1')->getFont()->setBold(true);
            if (is_numeric($filtered_fields[$i])) {// Numeric fields from "content"
                $objPHPExcel->getActiveSheet()->setCellValue($t . '1', upicrm_field_name($filtered_fields[$i]));
            } else {// Fields refer and utm
                foreach ($list_option as $fields) {
                    foreach ($fields as $field => $value) {
                        if ($filtered_fields[$i] == $field) {
                            $objPHPExcel->getActiveSheet()->setCellValue($t . '1', $value);
                        }
                    }
                }
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension($t)->setWidth(25);
            $t++;
        }

        $loopI = 2;
        $noDataFound = 'TRUE';
        for ($j = 0; $j < count($getLeads); $j++) {
            $t = "A";
            $lead_content = $getLeads[$j]->lead_content;
            $lead_content = json_decode($lead_content, true);
            for ($i = 0; $i < count($filtered_fields); $i++) {
                if (is_numeric($filtered_fields[$i])) {// Numeric fields from "content"
                    $getValue = $UpiCRMUIBuilder->lead_routing($getLeads[$j], 'content', $filtered_fields[$i], $getNamesMap, true);
                } else if ($filtered_fields[$i] == 'user_id') {//Assigned To
                    $getValue = $UpiCRMUIBuilder->lead_routing($getLeads[$j], 'special', $filtered_fields[$i], $getNamesMap, true);
                } else if ($filtered_fields[$i] == 'source_id' && $getLeads[$j]->source_id != 0) {//Form Name local
                    $getValue = $UpiCRMLeads->get_source_form_name($getLeads[$j]->$filtered_fields[$i], $getLeads[$j]->source_type);
                } else if ($filtered_fields[$i] == 'source_id' && $getLeads[$j]->source_id == 0) {//Form Name remote
                    $getValue = $lead_content['Form Name'];
                } else if ($filtered_fields[$i] == 'lead_status_id') {//Lead Status
                    $lead_status_id = $getLeads[$j]->$filtered_fields[$i];
                    $getValue = $UpiCRMLeadsStatus->get_status_name_by_id($lead_status_id);
                } else if ($filtered_fields[$i] == 'lead_id') {//ID
                    $getValue = $getLeads[$j]->$filtered_fields[$i];
                } else if ($filtered_fields[$i] == 'lead_management_comment') {//Lead Managment comment
                    $getValue = $getLeads[$j]->$filtered_fields[$i];
                } else if ($filtered_fields[$i] == 'time') {//Tmestamp
                    $getValue = $getLeads[$j]->$filtered_fields[$i];
                } else if ($filtered_fields[$i] == 'user_referer') {//Refer
                    $getValue = $getLeads[$j]->$filtered_fields[$i];
                } else if ($filtered_fields[$i] == 'utm_source') {
                    if ((isset($lead_content['UTM Source'])) && ($lead_content['UTM Source']) != null) {
                        $getValue = $lead_content['UTM Source'];
                    } else {// Fields refer and utm
                        $getValue = $getLeads[$j]->$filtered_fields[$i];
                    }
                } else if ($filtered_fields[$i] == 'utm_medium') {
                    if ((isset($lead_content['UTM Medium'])) && ($lead_content['UTM Medium']) != null) {
                        $getValue = $lead_content['UTM Medium'];
                    } else {// Fields refer and utm
                        $getValue = $getLeads[$j]->$filtered_fields[$i];
                    }
                } else if ($filtered_fields[$i] == 'utm_term') {
                    if ((isset($lead_content['UTM Term'])) && ($lead_content['UTM Term']) != null) {
                        $getValue = $lead_content['UTM Term'];
                    } else {// Fields refer and utm
                        $getValue = $getLeads[$j]->$filtered_fields[$i];
                    }
                } else if ($filtered_fields[$i] == 'utm_content') {
                    if ((isset($lead_content['UTM Content'])) && ($lead_content['UTM Content']) != null) {
                        $getValue = $lead_content['UTM Content'];
                    } else {// Fields refer and utm
                        $getValue = $getLeads[$j]->$filtered_fields[$i];
                    }
                } else if ($filtered_fields[$i] == 'utm_campaign') {
                    if ((isset($lead_content['UTM Campaign'])) && ($lead_content['UTM Campaign']) != null) {
                        $getValue = $lead_content['UTM Campaign'];
                    } else {// Fields refer and utm
                        $getValue = $getLeads[$j]->$filtered_fields[$i];
                    }
                } else if ($filtered_fields[$i] == 'integration_domain') {
                    if ((isset($lead_content['Remote server domain'])) && ($lead_content['Remote server domain']) != null) {
                        $getValue = $lead_content['Remote server domain'];
                    } else {
                        $getValue = $getLeads[$j]->$filtered_fields[$i];
                    }
                }


                if ($getValue <> '')
                    $noDataFound = 'FALSE';
                $objPHPExcel->getActiveSheet()->setCellValue($t . $loopI, $getValue);
                $t++;
            }
            $loopI++;
        }

        if ($noDataFound == 'TRUE') {
            echo '<script type="text/javascript">alert("No matching lead found!")</script>';
            return false;
        }
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($dirName . $fileName);
        echo '<script>window.onload = function (event) { window.location="' . home_url() . '/wp-content/uploads/upicrm/leads.xlsx"; };</script>';
    }

}

if (!function_exists('upicrm_lead_to_csv_output')) {

    function upicrm_lead_to_csv_output($lead_id) {
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMFields = new UpiCRMFields();
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();

        $listOption = $UpiCRMUIBuilder->get_list_option();
        $getLeads = $UpiCRMLeads->get_by_id($lead_id);
        $getNamesMap = $UpiCRMFieldsMapping->get_all_by($getLeads->source_id, $getLeads->source_type); //get lead fields mapping

        $line = [];
        $line2 = [];
        foreach ($listOption as $key => $list_option) {
            foreach ($list_option as $key2 => $field_name) {
                $value = $UpiCRMUIBuilder->lead_routing($getLeads, $key, $key2, $getNamesMap, true);
                if ($value) {
                    $line[] = $field_name;
                    $line2[] = $value;

                }
            }
        }
        

        $fileName = '/lead-' . $lead_id . '.csv';
        $dirName = WP_CONTENT_DIR . "/uploads/upicrm";
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }

        $list = array(
            $line,
            $line2
        );
        $path = $dirName . $fileName;

        $fp = fopen($path, 'w');
        fputs($fp, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF)); //UTF-8

        foreach ($list as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);

        return $path;
    }

}

if (!function_exists('upicrm_remove_lead_to_csv_file')) {
    function upicrm_remove_lead_to_csv_file($path) {
        @unlink($path);
    }
}


if (!function_exists('upicrm_excel_output')) {

    function upicrm_excel_output() {
        set_time_limit(0);
        upicrm_load('excel');
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $objPHPExcel = new PHPExcel();
        $list_option = $UpiCRMUIBuilder->get_list_option();
        if ($UpiCRMUsers->get_permission() == 1) {
            $userID = get_current_user_id();
            $getLeads = $UpiCRMLeads->get($userID);
        }
        if ($UpiCRMUsers->get_permission() == 2) {
            $getLeads = $UpiCRMLeads->get();
        }
        $getNamesMap = $UpiCRMFieldsMapping->get();
        $fileName = '/leads-' . upicrm_random_hash() . '.xlsx';
        $dirName = WP_CONTENT_DIR . "/uploads/upicrm";
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $t = "A";
        foreach ($list_option as $key => $arr) {
            foreach ($arr as $key2 => $value) {
                $objPHPExcel->getActiveSheet()->getStyle($t . '1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue($t . '1', $value);
                $objPHPExcel->getActiveSheet()->getColumnDimension($t)->setWidth(25);
                $t++;
            }
        }
        $i = 2;
        foreach ($getLeads as $leadObj) {
            $t = "A";
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {

                    if ($key2 == 'source_id' && $leadObj->source_id != 0) {//Form Name local
                        $getValue = $UpiCRMLeads->get_source_form_name($leadObj->source_id, $leadObj->source_type);
                    } else if ($key2 == 'source_id' && $leadObj->source_id == 0) {//Form Name remote
                        $lead_content = $leadObj->lead_content;
                        $form_name = json_decode($lead_content, true);
                        $getValue = $form_name['Form Name'];
                    } else {
                        $getValue = $UpiCRMUIBuilder->lead_routing($leadObj, $key, $key2, $getNamesMap, true);
                    }
                    $objPHPExcel->getActiveSheet()->setCellValue($t . $i, $getValue);
                    $t++;
                }
            }
            $i++;
        }
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($dirName . $fileName);
        echo '<script>window.onload = function (event) { window.location="' . home_url() . '/wp-content/uploads/upicrm/' . $fileName . '"; };</script>';
    }

}

if (!function_exists('get_the_author_meta')) {

    function get_the_author_meta($field = '', $user_id = false) {
        $original_user_id = $user_id;

        if (!$user_id) {
            global $authordata;
            $user_id = isset($authordata->ID) ? $authordata->ID : 0;
        } else {
            $authordata = get_userdata($user_id);
        }
        if (in_array($field, array('login', 'pass', 'nicename', 'email', 'url', 'registered', 'activation_key', 'status')))
            $field = 'user_' . $field;
        $value = isset($authordata->$field) ? $authordata->$field : '';

        /**
         * Filter the value of the requested user metadata.
         *
         * The filter name is dynamic and depends on the $field parameter of the function.
         *
         * @since 2.8.0
         * @since 4.3.0 The `$original_user_id` parameter was added.
         *
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         * @param int|bool $original_user_id The original user ID, as passed to the function.
         */
        return apply_filters('get_the_author_' . $field, $value, $user_id, $original_user_id);
    }

}

if (!function_exists('wp_mail')) :

    function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
        // Compact the input, apply the filters, and extract them back out.

        /**
         * Filters the wp_mail() arguments.
         *
         * @since 2.2.0
         *
         * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
         *                    subject, message, headers, and attachments values.
         */
        $atts = apply_filters('wp_mail', compact('to', 'subject', 'message', 'headers', 'attachments'));

        if (isset($atts['to'])) {
            $to = $atts['to'];
        }

        if (!is_array($to)) {
            $to = explode(',', $to);
        }

        if (isset($atts['subject'])) {
            $subject = $atts['subject'];
        }

        if (isset($atts['message'])) {
            $message = $atts['message'];
        }

        if (isset($atts['headers'])) {
            $headers = $atts['headers'];
        }

        if (isset($atts['attachments'])) {
            $attachments = $atts['attachments'];
        }

        if (!is_array($attachments)) {
            $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
        }
        global $phpmailer;

        // (Re)create it, if it's gone missing.
        if (!( $phpmailer instanceof PHPMailer )) {
            require_once ABSPATH . WPINC . '/class-phpmailer.php';
            require_once ABSPATH . WPINC . '/class-smtp.php';
            $phpmailer = new PHPMailer(true);
        }

        // Headers.
        $cc = array();
        $bcc = array();
        $reply_to = array();

        if (empty($headers)) {
            $headers = array();
        } else {
            if (!is_array($headers)) {
                // Explode the headers out, so this function can take
                // both string headers and an array of headers.
                $tempheaders = explode("\n", str_replace("\r\n", "\n", $headers));
            } else {
                $tempheaders = $headers;
            }
            $headers = array();

            // If it's actually got contents.
            if (!empty($tempheaders)) {
                // Iterate through the raw headers.
                foreach ((array) $tempheaders as $header) {
                    if (strpos($header, ':') === false) {
                        if (false !== stripos($header, 'boundary=')) {
                            $parts = preg_split('/boundary=/i', trim($header));
                            $boundary = trim(str_replace(array("'", '"'), '', $parts[1]));
                        }
                        continue;
                    }
                    // Explode them out.
                    list( $name, $content ) = explode(':', trim($header), 2);

                    // Cleanup crew.
                    $name = trim($name);
                    $content = trim($content);

                    switch (strtolower($name)) {
                        // Mainly for legacy -- process a "From:" header if it's there.
                        case 'from':
                            $bracket_pos = strpos($content, '<');
                            if (false !== $bracket_pos) {
                                // Text before the bracketed email is the "From" name.
                                if ($bracket_pos > 0) {
                                    $from_name = substr($content, 0, $bracket_pos - 1);
                                    $from_name = str_replace('"', '', $from_name);
                                    $from_name = trim($from_name);
                                }

                                $from_email = substr($content, $bracket_pos + 1);
                                $from_email = str_replace('>', '', $from_email);
                                $from_email = trim($from_email);

                                // Avoid setting an empty $from_email.
                            } elseif ('' !== trim($content)) {
                                $from_email = trim($content);
                            }
                            break;
                        case 'content-type':
                            if (strpos($content, ';') !== false) {
                                list( $type, $charset_content ) = explode(';', $content);
                                $content_type = trim($type);
                                if (false !== stripos($charset_content, 'charset=')) {
                                    $charset = trim(str_replace(array('charset=', '"'), '', $charset_content));
                                } elseif (false !== stripos($charset_content, 'boundary=')) {
                                    $boundary = trim(str_replace(array('BOUNDARY=', 'boundary=', '"'), '', $charset_content));
                                    $charset = '';
                                }

                                // Avoid setting an empty $content_type.
                            } elseif ('' !== trim($content)) {
                                $content_type = trim($content);
                            }
                            break;
                        case 'cc':
                            $cc = array_merge((array) $cc, explode(',', $content));
                            break;
                        case 'bcc':
                            $bcc = array_merge((array) $bcc, explode(',', $content));
                            break;
                        case 'reply-to':
                            $reply_to = array_merge((array) $reply_to, explode(',', $content));
                            break;
                        default:
                            // Add it to our grand headers array.
                            $headers[trim($name)] = trim($content);
                            break;
                    }
                }
            }
        }

        // Empty out the values that may be set.
        $phpmailer->clearAllRecipients();
        $phpmailer->clearAttachments();
        $phpmailer->clearCustomHeaders();
        $phpmailer->clearReplyTos();

        // Set "From" name and email.
        // If we don't have a name from the input headers.
        if (!isset($from_name)) {
            $from_name = 'WordPress';
        }

        /*
         * If we don't have an email from the input headers, default to wordpress@$sitename
         * Some hosts will block outgoing mail from this address if it doesn't exist,
         * but there's no easy alternative. Defaulting to admin_email might appear to be
         * another option, but some hosts may refuse to relay mail from an unknown domain.
         * See https://core.trac.wordpress.org/ticket/5007.
         */
        if (!isset($from_email)) {
            // Get the site domain and get rid of www.
            $sitename = strtolower($_SERVER['SERVER_NAME']);
            if (substr($sitename, 0, 4) == 'www.') {
                $sitename = substr($sitename, 4);
            }

            $from_email = 'wordpress@' . $sitename;
        }

        /**
         * Filters the email address to send from.
         *
         * @since 2.2.0
         *
         * @param string $from_email Email address to send from.
         */
        $from_email = apply_filters('wp_mail_from', $from_email);

        /**
         * Filters the name to associate with the "from" email address.
         *
         * @since 2.3.0
         *
         * @param string $from_name Name associated with the "from" email address.
         */
        $from_name = apply_filters('wp_mail_from_name', $from_name);

        try {
            $phpmailer->setFrom($from_email, $from_name, false);
        } catch (phpmailerException $e) {
            $mail_error_data = compact('to', 'subject', 'message', 'headers', 'attachments');
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /** This filter is documented in wp-includes/pluggable.php */
            do_action('wp_mail_failed', new WP_Error('wp_mail_failed', $e->getMessage(), $mail_error_data));

            return false;
        }

        // Set mail's subject and body.
        $phpmailer->Subject = $subject;
        $phpmailer->Body = $message;

        // Set destination addresses, using appropriate methods for handling addresses.
        $address_headers = compact('to', 'cc', 'bcc', 'reply_to');

        foreach ($address_headers as $address_header => $addresses) {
            if (empty($addresses)) {
                continue;
            }

            foreach ((array) $addresses as $address) {
                try {
                    // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
                    $recipient_name = '';

                    if (preg_match('/(.*)<(.+)>/', $address, $matches)) {
                        if (count($matches) == 3) {
                            $recipient_name = $matches[1];
                            $address = $matches[2];
                        }
                    }

                    switch ($address_header) {
                        case 'to':
                            $phpmailer->addAddress($address, $recipient_name);
                            break;
                        case 'cc':
                            $phpmailer->addCc($address, $recipient_name);
                            break;
                        case 'bcc':
                            $phpmailer->addBcc($address, $recipient_name);
                            break;
                        case 'reply_to':
                            $phpmailer->addReplyTo($address, $recipient_name);
                            break;
                    }
                } catch (phpmailerException $e) {
                    continue;
                }
            }
        }

        // Set to use PHP's mail().
        $phpmailer->isMail();

        // Set Content-Type and charset.
        // If we don't have a content-type from the input headers.
        if (!isset($content_type)) {
            $content_type = 'text/plain';
        }

        /**
         * Filters the wp_mail() content type.
         *
         * @since 2.3.0
         *
         * @param string $content_type Default wp_mail() content type.
         */
        $content_type = apply_filters('wp_mail_content_type', $content_type);

        $phpmailer->ContentType = $content_type;

        // Set whether it's plaintext, depending on $content_type.
        if ('text/html' == $content_type) {
            $phpmailer->isHTML(true);
        }

        // If we don't have a charset from the input headers.
        if (!isset($charset)) {
            $charset = get_bloginfo('charset');
        }

        /**
         * Filters the default wp_mail() charset.
         *
         * @since 2.3.0
         *
         * @param string $charset Default email charset.
         */
        $phpmailer->CharSet = apply_filters('wp_mail_charset', $charset);

        // Set custom headers.
        if (!empty($headers)) {
            foreach ((array) $headers as $name => $content) {
                // Only add custom headers not added automatically by PHPMailer.
                if (!in_array($name, array('MIME-Version', 'X-Mailer'))) {
                    $phpmailer->addCustomHeader(sprintf('%1$s: %2$s', $name, $content));
                }
            }

            if (false !== stripos($content_type, 'multipart') && !empty($boundary)) {
                $phpmailer->addCustomHeader(sprintf("Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary));
            }
        }

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                try {
                    $phpmailer->addAttachment($attachment);
                } catch (phpmailerException $e) {
                    continue;
                }
            }
        }

        /**
         * Fires after PHPMailer is initialized.
         *
         * @since 2.2.0
         *
         * @param PHPMailer $phpmailer The PHPMailer instance (passed by reference).
         */
        do_action_ref_array('phpmailer_init', array(&$phpmailer));

        // Send!
        try {
            $return  = $phpmailer->send();
            update_option("last_send_msg_id",$phpmailer->getLastMessageID());
            return $return;
        } catch (phpmailerException $e) {

            $mail_error_data = compact('to', 'subject', 'message', 'headers', 'attachments');
            $mail_error_data['phpmailer_exception_code'] = $e->getCode();

            /**
             * Fires after a phpmailerException is caught.
             *
             * @since 4.4.0
             *
             * @param WP_Error $error A WP_Error object with the phpmailerException message, and an array
             *                        containing the mail recipient, subject, message, headers, and attachments.
             */
            do_action('wp_mail_failed', new WP_Error('wp_mail_failed', $e->getMessage(), $mail_error_data));

            return false;
        }
    }

endif;

if (!function_exists('get_userdata')) :

    /**
     * Retrieve user info by user ID.
     *
     * @since 0.71
     *
     * @param int $user_id User ID
     * @return WP_User|false WP_User object on success, false on failure.
     */
    function get_userdata($user_id) {
        return get_user_by('id', $user_id);
    }

endif;

if (!function_exists('get_user_by')) :

    /**
     * Retrieve user info by a given field
     *
     * @since 2.8.0
     * @since 4.4.0 Added 'ID' as an alias of 'id' for the `$field` parameter.
     *
     * @param string $field The field to retrieve the user with. id | ID | slug | email | login.
     * @param int|string $value A value for $field. A user ID, slug, email address, or login name.
     * @return WP_User|false WP_User object on success, false on failure.
     */
    function get_user_by($field, $value) {
        $userdata = WP_User::get_data_by($field, $value);
        if (!$userdata)
            return false;
        $user = new WP_User;
        $user->init($userdata);
        return $user;
    }

endif;

if (!function_exists('upicrm_get_lang_arr')) {

    function upicrm_get_lang_arr() {
        return  array(
            'af' => array(
                'name' => 'Afrikaans',
                'code' => 'af',
                'wp_locale' => 'af'
            ),
            'ak' => array(
                'name' => 'Akan',
                'code' => 'ak',
                'wp_locale' => 'ak'
            ),
            'sq' => array(
                'name' => 'Albanian',
                'code' => 'sq',
                'wp_locale' => 'sq'
            ),
            'am' => array(
                'name' => 'Amharic',
                'code' => 'am',
                'wp_locale' => 'am'
            ),
            'ar' => array(
                'name' => 'Arabic',
                'code' => 'ar',
                'wp_locale' => 'ar'
            ),
            'hy' => array(
                'name' => 'Armenian',
                'code' => 'hy',
                'wp_locale' => 'hy'
            ),
            'rup_MK' => array(
                'name' => 'Aromanian',
                'code' => 'rup',
                'wp_locale' => 'rup_MK'
            ),
            'as' => array(
                'name' => 'Assamese',
                'code' => 'as',
                'wp_locale' => 'as'
            ),
            'az' => array(
                'name' => 'Azerbaijani',
                'code' => 'az',
                'wp_locale' => 'az'
            ),
            'az_TR' => array(
                'name' => 'Azerbaijani (Turkey)',
                'code' => 'az-tr',
                'wp_locale' => 'az_TR'
            ),
            'ba' => array(
                'name' => 'Bashkir',
                'code' => 'ba',
                'wp_locale' => 'ba'
            ),
            'eu' => array(
                'name' => 'Basque',
                'code' => 'eu',
                'wp_locale' => 'eu'
            ),
            'bel' => array(
                'name' => 'Belarusian',
                'code' => 'bel',
                'wp_locale' => 'bel'
            ),
            'bn_BD' => array(
                'name' => 'Bengali',
                'code' => 'bn',
                'wp_locale' => 'bn_BD'
            ),
            'bs_BA' => array(
                'name' => 'Bosnian',
                'code' => 'bs',
                'wp_locale' => 'bs_BA'
            ),
            'bg_BG' => array(
                'name' => 'Bulgarian',
                'code' => 'bg',
                'wp_locale' => 'bg_BG'
            ),
            'my_MM' => array(
                'name' => 'Burmese',
                'code' => 'mya',
                'wp_locale' => 'my_MM'
            ),
            'ca' => array(
                'name' => 'Catalan',
                'code' => 'ca',
                'wp_locale' => 'ca'
            ),
            'bal' => array(
                'name' => 'Catalan (Balear)',
                'code' => 'bal',
                'wp_locale' => 'bal'
            ),
            'zh_CN' => array(
                'name' => 'Chinese (China)',
                'code' => 'zh-cn',
                'wp_locale' => 'zh_CN'
            ),
            'zh_HK' => array(
                'name' => 'Chinese (Hong Kong)',
                'code' => 'zh-hk',
                'wp_locale' => 'zh_HK'
            ),
            'zh_TW' => array(
                'name' => 'Chinese (Taiwan)',
                'code' => 'zh-tw',
                'wp_locale' => 'zh_TW'
            ),
            'co' => array(
                'name' => 'Corsican',
                'code' => 'co',
                'wp_locale' => 'co'
            ),
            'hr' => array(
                'name' => 'Croatian',
                'code' => 'hr',
                'wp_locale' => 'hr'
            ),
            'cs_CZ' => array(
                'name' => 'Czech',
                'code' => 'cs',
                'wp_locale' => 'cs_CZ'
            ),
            'da_DK' => array(
                'name' => 'Danish',
                'code' => 'da',
                'wp_locale' => 'da_DK'
            ),
            'dv' => array(
                'name' => 'Dhivehi',
                'code' => 'dv',
                'wp_locale' => 'dv'
            ),
            'nl_NL' => array(
                'name' => 'Dutch',
                'code' => 'nl',
                'wp_locale' => 'nl_NL'
            ),
            'nl_BE' => array(
                'name' => 'Dutch (Belgium)',
                'code' => 'nl-be',
                'wp_locale' => 'nl_BE'
            ),
            'en_US' => array(
                'name' => 'English',
                'code' => 'en',
                'wp_locale' => 'en_US'
            ),
            'en_AU' => array(
                'name' => 'English (Australia)',
                'code' => 'en-au',
                'wp_locale' => 'en_AU'
            ),
            'en_CA' => array(
                'name' => 'English (Canada)',
                'code' => 'en-ca',
                'wp_locale' => 'en_CA'
            ),
            'en_GB' => array(
                'name' => 'English (UK)',
                'code' => 'en-gb',
                'wp_locale' => 'en_GB'
            ),
            'eo' => array(
                'name' => 'Esperanto',
                'code' => 'eo',
                'wp_locale' => 'eo'
            ),
            'et' => array(
                'name' => 'Estonian',
                'code' => 'et',
                'wp_locale' => 'et'
            ),
            'fo' => array(
                'name' => 'Faroese',
                'code' => 'fo',
                'wp_locale' => 'fo'
            ),
            'fi' => array(
                'name' => 'Finnish',
                'code' => 'fi',
                'wp_locale' => 'fi'
            ),
            'fr_BE' => array(
                'name' => 'French (Belgium)',
                'code' => 'fr-be',
                'wp_locale' => 'fr_BE'
            ),
            'fr_FR' => array(
                'name' => 'French (France)',
                'code' => 'fr',
                'wp_locale' => 'fr_FR'
            ),
            'fy' => array(
                'name' => 'Frisian',
                'code' => 'fy',
                'wp_locale' => 'fy'
            ),
            'fuc' => array(
                'name' => 'Fulah',
                'code' => 'fuc',
                'wp_locale' => 'fuc'
            ),
            'gl_ES' => array(
                'name' => 'Galician',
                'code' => 'gl',
                'wp_locale' => 'gl_ES'
            ),
            'ka_GE' => array(
                'name' => 'Georgian',
                'code' => 'ka',
                'wp_locale' => 'ka_GE'
            ),
            'de_DE' => array(
                'name' => 'German',
                'code' => 'de',
                'wp_locale' => 'de_DE'
            ),
            'de_CH' => array(
                'name' => 'German (Switzerland)',
                'code' => 'de-ch',
                'wp_locale' => 'de_CH'
            ),
            'el' => array(
                'name' => 'Greek',
                'code' => 'el',
                'wp_locale' => 'el'
            ),
            'gn' => array(
                'name' => 'Guaraní',
                'code' => 'gn',
                'wp_locale' => 'gn'
            ),
            'gu_IN' => array(
                'name' => 'Gujarati',
                'code' => 'gu',
                'wp_locale' => 'gu_IN'
            ),
            'haw_US' => array(
                'name' => 'Hawaiian',
                'code' => 'haw',
                'wp_locale' => 'haw_US'
            ),
            'haz' => array(
                'name' => 'Hazaragi',
                'code' => 'haz',
                'wp_locale' => 'haz'
            ),
            'he_IL' => array(
                'name' => 'Hebrew',
                'code' => 'he',
                'wp_locale' => 'he_IL'
            ),
            'hi_IN' => array(
                'name' => 'Hindi',
                'code' => 'hi',
                'wp_locale' => 'hi_IN'
            ),
            'hu_HU' => array(
                'name' => 'Hungarian',
                'code' => 'hu',
                'wp_locale' => 'hu_HU'
            ),
            'is_IS' => array(
                'name' => 'Icelandic',
                'code' => 'is',
                'wp_locale' => 'is_IS'
            ),
            'ido' => array(
                'name' => 'Ido',
                'code' => 'ido',
                'wp_locale' => 'ido'
            ),
            'id_ID' => array(
                'name' => 'Indonesian',
                'code' => 'id',
                'wp_locale' => 'id_ID'
            ),
            'ga' => array(
                'name' => 'Irish',
                'code' => 'ga',
                'wp_locale' => 'ga'
            ),
            'it_IT' => array(
                'name' => 'Italian',
                'code' => 'it',
                'wp_locale' => 'it_IT'
            ),
            'ja' => array(
                'name' => 'Japanese',
                'code' => 'ja',
                'wp_locale' => 'ja'
            ),
            'jv_ID' => array(
                'name' => 'Javanese',
                'code' => 'jv',
                'wp_locale' => 'jv_ID'
            ),
            'kn' => array(
                'name' => 'Kannada',
                'code' => 'kn',
                'wp_locale' => 'kn'
            ),
            'kk' => array(
                'name' => 'Kazakh',
                'code' => 'kk',
                'wp_locale' => 'kk'
            ),
            'km' => array(
                'name' => 'Khmer',
                'code' => 'km',
                'wp_locale' => 'km'
            ),
            'kin' => array(
                'name' => 'Kinyarwanda',
                'code' => 'kin',
                'wp_locale' => 'kin'
            ),
            'ky_KY' => array(
                'name' => 'Kirghiz',
                'code' => 'ky',
                'wp_locale' => 'ky_KY'
            ),
            'ko_KR' => array(
                'name' => 'Korean',
                'code' => 'ko',
                'wp_locale' => 'ko_KR'
            ),
            'ckb' => array(
                'name' => 'Kurdish (Sorani)',
                'code' => 'ckb',
                'wp_locale' => 'ckb'
            ),
            'lo' => array(
                'name' => 'Lao',
                'code' => 'lo',
                'wp_locale' => 'lo'
            ),
            'lv' => array(
                'name' => 'Latvian',
                'code' => 'lv',
                'wp_locale' => 'lv'
            ),
            'li' => array(
                'name' => 'Limburgish',
                'code' => 'li',
                'wp_locale' => 'li'
            ),
            'lin' => array(
                'name' => 'Lingala',
                'code' => 'lin',
                'wp_locale' => 'lin'
            ),
            'lt_LT' => array(
                'name' => 'Lithuanian',
                'code' => 'lt',
                'wp_locale' => 'lt_LT'
            ),
            'lb_LU' => array(
                'name' => 'Luxembourgish',
                'code' => 'lb',
                'wp_locale' => 'lb_LU'
            ),
            'mk_MK' => array(
                'name' => 'Macedonian',
                'code' => 'mk',
                'wp_locale' => 'mk_MK'
            ),
            'mg_MG' => array(
                'name' => 'Malagasy',
                'code' => 'mg',
                'wp_locale' => 'mg_MG'
            ),
            'ms_MY' => array(
                'name' => 'Malay',
                'code' => 'ms',
                'wp_locale' => 'ms_MY'
            ),
            'ml_IN' => array(
                'name' => 'Malayalam',
                'code' => 'ml',
                'wp_locale' => 'ml_IN'
            ),
            'mr' => array(
                'name' => 'Marathi',
                'code' => 'mr',
                'wp_locale' => 'mr'
            ),
            'xmf' => array(
                'name' => 'Mingrelian',
                'code' => 'xmf',
                'wp_locale' => 'xmf'
            ),
            'mn' => array(
                'name' => 'Mongolian',
                'code' => 'mn',
                'wp_locale' => 'mn'
            ),
            'me_ME' => array(
                'name' => 'Montenegrin',
                'code' => 'me',
                'wp_locale' => 'me_ME'
            ),
            'ne_NP' => array(
                'name' => 'Nepali',
                'code' => 'ne',
                'wp_locale' => 'ne_NP'
            ),
            'nb_NO' => array(
                'name' => 'Norwegian (Bokmål)',
                'code' => 'nb',
                'wp_locale' => 'nb_NO'
            ),
            'nn_NO' => array(
                'name' => 'Norwegian (Nynorsk)',
                'code' => 'nn',
                'wp_locale' => 'nn_NO'
            ),
            'ory' => array(
                'name' => 'Oriya',
                'code' => 'ory',
                'wp_locale' => 'ory'
            ),
            'os' => array(
                'name' => 'Ossetic',
                'code' => 'os',
                'wp_locale' => 'os'
            ),
            'ps' => array(
                'name' => 'Pashto',
                'code' => 'ps',
                'wp_locale' => 'ps'
            ),
            'fa_IR' => array(
                'name' => 'Persian',
                'code' => 'fa',
                'wp_locale' => 'fa_IR'
            ),
            'fa_AF' => array(
                'name' => 'Persian (Afghanistan)',
                'code' => 'fa-af',
                'wp_locale' => 'fa_AF'
            ),
            'pl_PL' => array(
                'name' => 'Polish',
                'code' => 'pl',
                'wp_locale' => 'pl_PL'
            ),
            'pt_BR' => array(
                'name' => 'Portuguese (Brazil)',
                'code' => 'pt-br',
                'wp_locale' => 'pt_BR'
            ),
            'pt_PT' => array(
                'name' => 'Portuguese (Portugal)',
                'code' => 'pt',
                'wp_locale' => 'pt_PT'
            ),
            'pa_IN' => array(
                'name' => 'Punjabi',
                'code' => 'pa',
                'wp_locale' => 'pa_IN'
            ),
            'rhg' => array(
                'name' => 'Rohingya',
                'code' => 'rhg',
                'wp_locale' => 'rhg'
            ),
            'ro_RO' => array(
                'name' => 'Romanian',
                'code' => 'ro',
                'wp_locale' => 'ro_RO'
            ),
            'ru_RU' => array(
                'name' => 'Russian',
                'code' => 'ru',
                'wp_locale' => 'ru_RU'
            ),
            'ru_UA' => array(
                'name' => 'Russian (Ukraine)',
                'code' => 'ru-ua',
                'wp_locale' => 'ru_UA'
            ),
            'rue' => array(
                'name' => 'Rusyn',
                'code' => 'rue',
                'wp_locale' => 'rue'
            ),
            'sah' => array(
                'name' => 'Sakha',
                'code' => 'sah',
                'wp_locale' => 'sah'
            ),
            'sa_IN' => array(
                'name' => 'Sanskrit',
                'code' => 'sa-in',
                'wp_locale' => 'sa_IN'
            ),
            'srd' => array(
                'name' => 'Sardinian',
                'code' => 'srd',
                'wp_locale' => 'srd'
            ),
            'gd' => array(
                'name' => 'Scottish Gaelic',
                'code' => 'gd',
                'wp_locale' => 'gd'
            ),
            'sr_RS' => array(
                'name' => 'Serbian',
                'code' => 'sr',
                'wp_locale' => 'sr_RS'
            ),
            'sd_PK' => array(
                'name' => 'Sindhi',
                'code' => 'sd',
                'wp_locale' => 'sd_PK'
            ),
            'si_LK' => array(
                'name' => 'Sinhala',
                'code' => 'si',
                'wp_locale' => 'si_LK'
            ),
            'sk_SK' => array(
                'name' => 'Slovak',
                'code' => 'sk',
                'wp_locale' => 'sk_SK'
            ),
            'sl_SI' => array(
                'name' => 'Slovenian',
                'code' => 'sl',
                'wp_locale' => 'sl_SI'
            ),
            'so_SO' => array(
                'name' => 'Somali',
                'code' => 'so',
                'wp_locale' => 'so_SO'
            ),
            'azb' => array(
                'name' => 'South Azerbaijani',
                'code' => 'azb',
                'wp_locale' => 'azb'
            ),
            'es_AR' => array(
                'name' => 'Spanish (Argentina)',
                'code' => 'es-ar',
                'wp_locale' => 'es_AR'
            ),
            'es_CL' => array(
                'name' => 'Spanish (Chile)',
                'code' => 'es-cl',
                'wp_locale' => 'es_CL'
            ),
            'es_CO' => array(
                'name' => 'Spanish (Colombia)',
                'code' => 'es-co',
                'wp_locale' => 'es_CO'
            ),
            'es_MX' => array(
                'name' => 'Spanish (Mexico)',
                'code' => 'es-mx',
                'wp_locale' => 'es_MX'
            ),
            'es_PE' => array(
                'name' => 'Spanish (Peru)',
                'code' => 'es-pe',
                'wp_locale' => 'es_PE'
            ),
            'es_PR' => array(
                'name' => 'Spanish (Puerto Rico)',
                'code' => 'es-pr',
                'wp_locale' => 'es_PR'
            ),
            'es_ES' => array(
                'name' => 'Spanish (Spain)',
                'code' => 'es',
                'wp_locale' => 'es_ES'
            ),
            'es_VE' => array(
                'name' => 'Spanish (Venezuela)',
                'code' => 'es-ve',
                'wp_locale' => 'es_VE'
            ),
            'su_ID' => array(
                'name' => 'Sundanese',
                'code' => 'su',
                'wp_locale' => 'su_ID'
            ),
            'sw' => array(
                'name' => 'Swahili',
                'code' => 'sw',
                'wp_locale' => 'sw'
            ),
            'sv_SE' => array(
                'name' => 'Swedish',
                'code' => 'sv',
                'wp_locale' => 'sv_SE'
            ),
            'gsw' => array(
                'name' => 'Swiss German',
                'code' => 'gsw',
                'wp_locale' => 'gsw'
            ),
            'tl' => array(
                'name' => 'Tagalog',
                'code' => 'tl',
                'wp_locale' => 'tl'
            ),
            'tg' => array(
                'name' => 'Tajik',
                'code' => 'tg',
                'wp_locale' => 'tg'
            ),
            'tzm' => array(
                'name' => 'Tamazight (Central Atlas)',
                'code' => 'tzm',
                'wp_locale' => 'tzm'
            ),
            'ta_IN' => array(
                'name' => 'Tamil',
                'code' => 'ta',
                'wp_locale' => 'ta_IN'
            ),
            'ta_LK' => array(
                'name' => 'Tamil (Sri Lanka)',
                'code' => 'ta-lk',
                'wp_locale' => 'ta_LK'
            ),
            'tt_RU' => array(
                'name' => 'Tatar',
                'code' => 'tt',
                'wp_locale' => 'tt_RU'
            ),
            'te' => array(
                'name' => 'Telugu',
                'code' => 'te',
                'wp_locale' => 'te'
            ),
            'th' => array(
                'name' => 'Thai',
                'code' => 'th',
                'wp_locale' => 'th'
            ),
            'bo' => array(
                'name' => 'Tibetan',
                'code' => 'bo',
                'wp_locale' => 'bo'
            ),
            'tir' => array(
                'name' => 'Tigrinya',
                'code' => 'tir',
                'wp_locale' => 'tir'
            ),
            'tr_TR' => array(
                'name' => 'Turkish',
                'code' => 'tr',
                'wp_locale' => 'tr_TR'
            ),
            'tuk' => array(
                'name' => 'Turkmen',
                'code' => 'tuk',
                'wp_locale' => 'tuk'
            ),
            'ug_CN' => array(
                'name' => 'Uighur',
                'code' => 'ug',
                'wp_locale' => 'ug_CN'
            ),
            'uk' => array(
                'name' => 'Ukrainian',
                'code' => 'uk',
                'wp_locale' => 'uk'
            ),
            'ur' => array(
                'name' => 'Urdu',
                'code' => 'ur',
                'wp_locale' => 'ur'
            ),
            'uz_UZ' => array(
                'name' => 'Uzbek',
                'code' => 'uz',
                'wp_locale' => 'uz_UZ'
            ),
            'vi' => array(
                'name' => 'Vietnamese',
                'code' => 'vi',
                'wp_locale' => 'vi'
            ),
            'wa' => array(
                'name' => 'Walloon',
                'code' => 'wa',
                'wp_locale' => 'wa'
            ),
            'cy' => array(
                'name' => 'Welsh',
                'code' => 'cy',
                'wp_locale' => 'cy'
            ),
            'or' => array(
                'name' => 'Yoruba',
                'code' => 'yor',
                'wp_locale' => 'yor'
            )
        );
    }

}