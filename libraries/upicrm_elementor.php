<?phpif (!class_exists('UpiCRMElementor')) {    class UpiCRMElementor extends WP_Widget {        var $wpdb;        public function __construct() {            global $wpdb;            $this->wpdb = &$wpdb;        }        function get_forms_query() {            $sql_query = "SELECT *  FROM `{$this->wpdb->prefix}postmeta`        WHERE `meta_key` LIKE '_elementor_data'        AND `meta_value` LIKE '%\"widgetType\":\"form\"%'        AND `post_id` IN (            SELECT `id` FROM `{$this->wpdb->prefix}posts`            WHERE `post_status` = 'publish' || `post_status` = 'private' || `post_status` = 'draft'        )";            return $this->wpdb->get_results($sql_query);        }        function save_lead($record) {            //save lead             global $SourceTypeID;            $UpiCRMLeads = new UpiCRMLeads();            $content_arr = [];            foreach ($record->get('fields') as $id => $field) {                $content_arr[$id] = $field['value'];            }            $UpiCRMLeads->add($content_arr, $SourceTypeID['elementor'], $_POST['form_id']);        }        function get_all_form() {            //get all elementor forms as array            $froms = $this->get_forms_query();            $arr = [];            foreach ($froms as $form) {                foreach (json_decode($form->meta_value, true) as $obj) {                    foreach ($obj['elements'] as $obj2) {                        if (isset($obj2['elements'])) {                            foreach ($obj2['elements'] as $obj3) {                                if (isset($obj3['settings']['form_name'])) {                                    $arr[$obj3['id']] = $obj3['settings']['form_name'];                                }                            }                        }                    }                }            }            return $arr;        }        function get_all_form_fields($form_id, $group = true) {            //get all elementor fields by form id            $froms = $this->get_forms_query();            $arr = [];            /*foreach ($froms as $form) {                if ($form[0]['elements'][0]['elements'][0]['id'] == $form_id) {                    foreach ($form[0]['elements'][0]['elements'][0]['settings']['form_fields'] as $fields) {                        $arr[$fields['custom_id']] = $fields['field_label'];                    }                    return $arr;                }            }*/            $arr = [];            foreach ($froms as $form) {                foreach (json_decode($form->meta_value, true) as $obj) {                    foreach ($obj['elements'] as $obj2) {                        if (isset($obj2['elements'])) {                            foreach ($obj2['elements'] as $obj3) {                                if (isset($obj3['settings']['form_name']) && $obj3['id'] == $form_id) {                                    foreach ($obj3['settings']['form_fields'] as $obj4) {                                        $arr[$obj4['custom_id']] = $obj4['field_label'] ? $obj4['field_label'] : $obj4['placeholder'];                                    }                                }                            }                        }                    }                }            }            return $arr;        }        function form_name($source_id) {            //get elementor form name            $arr = $this->get_all_form();            return $arr[$source_id];        }        function is_active() {            //is elementor active            return is_plugin_active('elementor/elementor.php') || is_plugin_active('elementor/elementor-pro.php');        }        /*         function import_all() {          //get all elementor leads and save it to UpiCRM leads        }         */    }    add_action('elementor_pro/forms/new_record', array(new UpiCRMElementor, 'save_lead'), 10, 1);}