<?php
if (!class_exists('UpiCRMUIBuilder')) {

    class UpiCRMUIBuilder {

        function get_list_option($order=1) {
            $UpiCRMFields = new UpiCRMFields();
            
            foreach ($UpiCRMFields->get($order) as $field) {
                $arr['content'][$field->field_id] = $field->field_name;
            }
            $arr['leads']['time'] = __('Time', 'upicrm');
            if(!isset($_REQUEST['action']) && $_REQUEST['action'] != 'excel_output' && $_REQUEST['page'] != 'upicrm_index')
            {
                $arr['leads']['lead_id'] = __('ID', 'upicrm');
                $arr['leads']['user_agent'] = __('User Agent', 'upicrm');
                $arr['leads']['user_referer'] = __('Referer', 'upicrm');
                $arr['leads']['user_ip'] = __('IP', 'upicrm');
                $arr['leads']['lead_webservice_transmission'] = __('Web service Transmission Log', 'upicrm');
                $arr['leads']['source_id'] = __('Form Name', 'upicrm'); //removed from special section in 2.1.8.5
                $arr['leads']['lead_log_text'] = __('Audit Log', 'upicrm');

                //$arr['special']['source_id'] = __('Form Name','upicrm');
                $arr['special']['user_id'] = __('Assigned To', 'upicrm');
                $arr['special']['affiliate_id'] = __('Affiliate', 'upicrm');
                $arr['special']['affiliate_type'] = __('Affiliate Type', 'upicrm');
                $arr['special']['affiliate_time'] = __('Affiliate Time', 'upicrm');
                $arr['special']['lead_status_id'] = __('Lead Status', 'upicrm');
                $arr['special']['lead_management_comment'] = __('Lead Note', 'upicrm');

                $arr['leads_campaign']['utm_source'] = "UTM Source";
                $arr['leads_campaign']['utm_medium'] = "UTM Medium";
                $arr['leads_campaign']['utm_term'] = "UTM Term";
                $arr['leads_campaign']['utm_content'] = "UTM Content";
                $arr['leads_campaign']['utm_campaign'] = "UTM Campaign";

                $arr['leads_integration']['lead_id_external'] = __('Lead ID on remote server', 'upicrm');
                $arr['leads_integration']['lead_integration_status'] = __('Transmission Status', 'upicrm');
                $arr['leads_integration']['integration_domain'] = __('Remote server domain', 'upicrm');
                $arr['special']['actions'] = __('Actions', 'upicrm');
                $arr['special']['trash_actions'] = __('Actions', 'upicrm');
            }

            return $arr;
        }

        function get_list_option_minimum() {
            $arr = $this->get_list_option();
            foreach ($arr as $key => $arr2) {
                if ($key == "special") {
                    foreach ($arr2 as $key2 => $value) {
                        if ($key2 != "affiliate_id" && $key2 != "affiliate_type") {
                            unset($arr['special'][$key2]);
                        }
                    }
                }
            }
            //var_dump($arr);
            return $arr;
        }
        
        private function format_text($text) {
            $text = htmlspecialchars($text);
            $text = preg_replace('/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims', '<a href="$1" target="_blank">$1</a> ', $text);
            return $text;
        }
        
        function lead_routing($lead, $route, $value, $map, $noHtml = false, $affTable = false) {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMWebService = new UpiCRMWebService();
            $UpiCRMAffiliate = new UpiCRMAffiliate();
            $webs_OBJ = $UpiCRMWebService->get_by_id(1);
            $affiliate_type_ARR = $UpiCRMAffiliate->get_type_arr();
            switch ($route) {
                case "leads":
                    if (isset($lead->$value)) {
                        if ($value == "source_id") {
                            $text = $UpiCRMLeads->get_source_form_name($lead->source_id, $lead->source_type);
                        } elseif ($value == "lead_log_text" && !$noHtml) {
                            $text = $lead->$value . '<div class="upicrm_lead_actions"><span class="glyphicon glyphicon-th-list" data-lead_id="' . $lead->lead_id . '" title="' . __('Show full log', 'upicrm') . '" data-callback="show_log"></span></div>';
                        } else {
                            $text = $lead->$value;
                        }
                    }

                    break;
                case "leads_campaign":
                    $text = $lead->$value;
                    break;
                case "leads_integration":
                    if ($value == "integration_domain" && !$lead->$value) {
                        $text = get_site_url(); //optimization here
                    } else {
                        $text = $lead->$value;
                    }
                    break;
                case "content":
                        if (!$noHtml) {
                            $text = $this->format_text($this->return_lead_content($lead, $value, $map));
                        } else {
                            $text = $this->return_lead_content($lead, $value, $map);
                        }
                        if ($value == '1' && $_REQUEST['page'] != 'upicrm_index') {
                            $text = "<a href='admin.php?page=upicrm_edit_lead&id=".$lead->lead_id."'>".$text."</a>";
                        }
                    break;
                case "special":
                    switch ($value) {
                        case "source_id":
                            $text = $UpiCRMLeads->get_source_form_name($lead->source_id, $lead->source_type);
                            break;
                        case "user_id":
                            // if (!$noHtml)
                            //    $text = $UpiCRMUsers->select_list($lead,"change_user");
                            //  else
                            if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'excel_output' && $_REQUEST['page'] == 'upicrm_index')
                            {
                                $text = $UpiCRMUsers->select_list($lead,"change_user");
                            }
                            else
                            {
                                $text = '<a href="javascript:void(0)" data-callback="assign-lead" data-lead_id="' . $lead->lead_id . '">'.$UpiCRMUsers->get_by_id_all_lead_user_name($lead->lead_id)."</a>";
                            }
                            break;
                        case "affiliate_id":
                                $text = $lead->$value > 0  ? $UpiCRMUsers->get_by_id($lead->$value) : '';
                            break;
                        case "affiliate_type":
                                $text = "";
                                if ($lead->affiliate_id > 0) {
                                    $text = $affiliate_type_ARR[$lead->$value];
                                }
                            break;
                         case "affiliate_time":
                             if (!$affTable) {
                                $text = $lead->$value != "0000-00-00 00:00:00" ? $lead->$value : '';
                             }
                             else {
                                $showTime_exp = explode(" ",$lead->$value);
                                $showTime = $showTime_exp[0] != "0000-00-00" ? $showTime_exp[0] : '';
                                $text = "<div data-lead_id={$lead->lead_id}><input type='text' class='affiliate_time' value='{$showTime}' /> <button class='aff_click'><span class='dashicons dashicons-saved aff_save_icon'></span><span class='dashicons dashicons-backup loader' style='display: none;'></span></button></div>"; 
                             }
                            break;
                        case "lead_status_id":
                            /**
                             * Select Lead Status in table directly
                             */
                            if (!$noHtml)
                                $text = $this->select_status_list($lead, "change_lead_status");
                            else
                                $text = $UpiCRMLeadsStatus->get_status_name_by_id($lead->$value);
                            break;
                        case "lead_management_comment":
                            if (!$noHtml)
                                $text = $this->remarks_textarea($lead, "change_lead_remarks");
                            else
                                $text = $lead->lead_management_comment;
                            break;
                        case "actions":
                            if (!$noHtml) {
                                $text = '<div class="upicrm_lead_actions">';
                                if ($lead->is_slave == 1) {
                                    $text .= '<span class="glyphicon glyphicon-repeat" data-callback="send_master_again" data-lead_id="' . $lead->lead_id . '" title="' . __('Send lead again to UpiCRM master', 'upicrm') . '"></span>';
                                }
                                if (isset($webs_OBJ->webservice_id) && $webs_OBJ->webservice_id > 0) {
                                    $text .= '<span class="glyphicon glyphicon-share-alt" data-callback="send_webservice" data-lead_id="' . $lead->lead_id . '" title="' . __('Manually transmit lead to a remote web service', 'upicrm') . '"></span>';
                                }
                                //$text .= '<span class="glyphicon glyphicon-question-sign" data-callback="request_status" data-lead_id="' . $lead->lead_id . '" title="' . __('Request status update from lead owner', 'upicrm') . '"></span>';
                                // $text .= '<div class="upicrm_lead_actions"><span class="glyphicon glyphicon-th-list" data-lead_id="' . $lead->lead_id . '" title="' . __('Show full log', 'upicrm') . '" data-callback="show_log"></span></div>';
                                $text .= '<span class="glyphicon glyphicon-floppy-save" data-callback="save" data-lead_id="' . $lead->lead_id . '" title="' . __('Save', 'upicrm') . '"></span>';
                                $text .= '<span class="glyphicon glyphicon-edit" data-callback="edit" data-lead_id="' . $lead->lead_id . '" title="' . __('Edit', 'upicrm') . '"></span>';
                                $text .= '<span class="glyphicon glyphicon-remove" data-callback="trash" data-lead_id="' . $lead->lead_id . '" title="' . __('Trash', 'upicrm') . '"></span>';
                                if (get_option('upicrm_send_csv_email')) {
                                    $text .= '<span class="glyphicon glyphicon-send" data-callback="send_csv_again" data-lead_id="' . $lead->lead_id . '" title="' . __('Send CSV file again', 'upicrm') . '"></span>';
                                }
                                $text .= '</div>';

                            }
                            /*if ($affTable) {
                                $text = '<a href="">'.__('Edit', 'upicrm').'</a>';
                            }*/
                            break;
                            case "trash_actions":
                                if (!$noHtml) {
                                    $text = '<div class="upicrm_lead_actions">';
                                    $text .= '<span class="glyphicon glyphicon-refresh" data-callback="restore_lead" data-lead_id="' . $lead->lead_id . '" title="' . __('Restore', 'upicrm') . '"></span>';
                                    $text .= '<span class="glyphicon glyphicon-remove" data-callback="remove" data-lead_id="' . $lead->lead_id . '" title="' . __('Remove', 'upicrm') . '"></span>';
                                    $text .= '</div>';
    
                                }
                                /*if ($affTable) {
                                    $text = '<a href="">'.__('Edit', 'upicrm').'</a>';
                                }*/
                                break;
                    }
                    break;
            }
            if (isset($text)) {
                return $text;
            } else
                return null;
        }

        function return_lead_content($lead, $value, $map) {
            global $SourceTypeID;
            $content = json_decode($lead->lead_content, true);
            //echo $lead->lead_content;
            if ($lead->source_type != $SourceTypeID['upi_integration']) {
                if (isset($map)) {
                    foreach ($map as $arr) {
                        if ($lead->source_id == $arr->source_id && $lead->source_type == $arr->source_type && $value == $arr->field_id) {
                            if ($content[$arr->fm_name]) {
                                if (!is_array($content[$arr->fm_name])) {
                                    $text = $content[$arr->fm_name];
                                } else {
                                    $text = "";
                                    foreach ($content[$arr->fm_name] as $val) {
                                        $text .= $val . ", ";
                                    }
                                    $text = rtrim($text,", ");
                                }
                                $is_dynamic_field = true;
                                break;
                            } /* else {
                              //static field
                              $text=$this->return_lead_content_static($content,$value);
                              } */
                        }
                    }
                }
                if (!$is_dynamic_field) {
                    //static field
                    $getFields = unserialize(UPICRM_FIELDS_ARR);
                    if (is_array($content)) {
                        foreach ($content as $content_key => $content_value) {
                            if ($getFields[$value] == $content_key) {
                                $text = $this->return_lead_content_static($content, $value);
                                break;
                            }
                        }
                    }
                }
            } else {
                //integration
                return $this->return_lead_content_static($content, $value);
            }
            return $text;
        }

        function return_lead_content_static($content, $value) {
            $UpiCRMFields = new UpiCRMFields();
            $getFields = unserialize(UPICRM_FIELDS_ARR);
            if (!$getFields) {
                $getFields = $UpiCRMFields->get_as_array();
            }
//        echo $content[$getFields[$value]];
//        print_r($getFields);
            return isset($content[$getFields[$value]]) ? $content[$getFields[$value]] : '';
        }

        function return_lead_content_arr($lead, $value, $map) {
            $content = json_decode($lead->lead_content, true);
            foreach ($map as $arr) {
                if ($lead->source_id == $arr->source_id && $lead->source_type == $arr->source_type && $value == $arr->field_id) {
                    if (!is_array($content[$arr->fm_name])) {
                        $text = $content[$arr->fm_name];
                    }
                    $lead_content_arr['text'] = $text;
                    $lead_content_arr['fm_name'] = $arr->fm_name;
                    $lead_content_arr['field_id'] = $arr->field_id;
                    $lead_content_arr['source_id'] = $arr->source_id;
                    $lead_content_arr['source_type'] = $arr->source_type;
                    break;
                }
            }
            return $lead_content_arr;
        }

        function select_status_list($lead, $callback) {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $get_status = $UpiCRMLeadsStatus->get();
            $text = '<select name="lead_status_id" data-lead_id="' . $lead->lead_id . '" data-callback="' . $callback . '">';
            foreach ($get_status as $status) {
                $selected = selected($status->lead_status_id, $lead->lead_status_id, false);
                $text .= '<option value="' . $status->lead_status_id . '" ' . $selected . '>' . $status->lead_status_name . '</option>';
            }
            $text .= '</select>';
            return $text;
        }

        function remarks_textarea($lead, $callback) {
            $text = '<label class="textarea textarea-expandable">';
            $text .= '<textarea class="custom-scroll" name="lead_remarks" data-lead_id="' . $lead->lead_id . '" data-callback="' . $callback . '">';
            $text .= $lead->lead_management_comment;
            $text .= '</textarea>';
            $text .= '</label>';
            return $text;
        }

        function show_dropdown($name, $arr, $selected = "") {
            ?><select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
            <?php foreach ($arr as $key => $value) { ?>
                    <option value="<?php echo $key; ?>" <?php selected($key, $selected); ?> ><?php echo $value; ?></option>
            <?php } ?>
            </select><?php
        }

        function show_table($id, $title, $table_arr, $class = "col-xs-12 col-sm-12 col-md-12 col-lg-12") {
            ?>
            <div class="row">
                <article class="<?php echo $class ?>">
                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget jarviswidget-color-blueLight" id="wid-id-1" data-widget-editbutton="false">
                        <header>
                            <span class="widget-icon">
                                <i class="fa fa-table">
                                </i>
                            </span>
                            <h2><?php echo $title; ?></h2>
                        </header>
                        <!-- widget div-->
                        <div>
                            <!-- widget edit box -->
                            <div class="jarviswidget-editbox">
                                <!-- This area used as dropdown edit box -->
                            </div>
                            <!-- end widget edit box -->
                            <!-- widget content -->
                            <div class="widget-body no-padding">
                                <table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
                                    <thead>
                                        <tr>
            <?php
            foreach ($table_arr as $arr) {
                foreach ($arr as $val) {
                    ?>
                                                    <th data-class="expand">
                    <?php echo $val; ?>
                                                    </th>
                    <?php
                }
                break;
            }
            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            <?php
                                            $count = 0;
                                            foreach ($table_arr as $arr) {
                                                if ($count > 0) {
                                                    ?>
                                                <tr>
                                                <?php foreach ($arr as $val) { ?>
                                                        <td data-belongs="">
                                                    <?php echo $val; ?>
                                                        </td>
                                                    <?php
                                                }
                                                ?>
                                                </tr>
                                                    <?php
                                                    }
                                                    $count++;
                                                }
                                                ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- end widget content -->
                        </div>
                        <!-- end widget div -->
                    </div>
                    <!-- end widget -->
                </article>
            </div>
            <?php
        }
        
        function lead_variables($text,$lead) {
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            
            $getNamesMap = $UpiCRMFieldsMapping->get_all_by($lead->source_id, $lead->source_type);
            $list_option = $this->get_list_option();
            
            $text = str_replace("[url]", get_site_url(), $text);
            $text = str_replace("[assigned-to]", $UpiCRMUsers->get_by_id($lead->user_id), $text);
            $text = str_replace("[lead-status]", $UpiCRMLeadsStatus->get_status_name_by_id($lead->lead_status_id), $text);
            $text = str_replace("[affiliate-name]", $UpiCRMUsers->get_by_id($lead->affiliate_id), $text);
            
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    $getValue = $this->lead_routing($lead, $key, $key2, $getNamesMap, true);
                    if ($getValue != "") {
                        $fields[$value] = $getValue;
                    }
                }
            }
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if (isset($fields)) {
                        $text = str_replace("[field-$value]", $fields[$value], $text);
                        //echo $value."<br/ >";
                    }
                }
            }

            return $text;
        }

    }
}
?>