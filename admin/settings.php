<?php
if ( !class_exists('UpiCRMAdminSettings') ):
    class UpiCRMAdminSettings{
        public function Render() {
            global $SourceTypeID;
            $UpiCRMgform = new UpiCRMgform();
            $UpiCRMwpcf7 = new UpiCRMwpcf7();
            $UpiCRMninja = new UpiCRMninja();
            $UpiCRMcaldera = new UpiCRMcaldera();
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMwpforms = new UpiCRMwpforms();
            $UpiCRMElementor = new UpiCRMElementor();
            if(isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'save_field':
                        $this->saveField();
                        $msg = __('changes saved successfully', 'upicrm');
                        break;
                    case 'save_status':
                        $this->saveStatus();
                        $msg = __('changes saved successfully', 'upicrm');
                        break;
                    case 'delete_upi_settings':
                        if (isset($_POST['delete_upi']) && $_POST['delete_upi'] == 1) {
                            upicrm_remove_plugin_data();
                            update_option("upicrm_db_version", 1);
                        }
                        break;
                }
            }
            if(isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'reset_form':
                        $msg = __('Form reset successfully', 'upicrm');
                    break;
                }
            }
            $tabs_html = '';
            $content_html = '';
            if($UpiCRMgform->is_active()) {

                foreach ($UpiCRMgform->get_all_form() as $key => $value) {
                    $tabs_html .= '<li><a href="#g'.strval($key).'">'.$value.'</a></li>';
                    $content_html .= '<div id="g'.$key.'"><div class="table-responsive"><table class="table"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMgform->get_all_form_fields($key,true) as $inputName => $inputValue) {
                        $arr = array();
                        $arr["name"] = $inputName;
                        $arr["value"] = $inputValue;
                        $arr["source_id"] = $key;
                        $arr["item_id"] = 'g'.$key;
                        $arr["source_type"] = $SourceTypeID['gform'];
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= $this->endOfContent($SourceTypeID['gform'],$key);
                }
            }
            if ($UpiCRMwpcf7->is_active()) {
                foreach ($UpiCRMwpcf7->get_all_form() as $key => $value) {
                    $tabs_html .= '<li><a href="#f7'.strval($key).'">'.$value.'</a></li>';
                    $content_html .= '<div id="f7'.$key.'"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMwpcf7->get_all_form_fields($key)  as $inputValue => $inputName) {
                        $arr = array();
                        $arr["name"] = $inputName;
                        $arr["value"] = $inputName;
                        $arr["source_id"] = $key;
                        $arr["source_type"] = $SourceTypeID['wpcf7'];
                        $arr["item_id"] = 'f7'.$key;
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= $this->endOfContent($SourceTypeID['wpcf7'],$key);
                }
            }
            if ($UpiCRMninja->is_active()) {
                foreach ($UpiCRMninja->get_all_form() as $key => $value) {
                    $tabs_html .= '<li><a href="#ninja'.strval($key).'">'.$value.'</a></li>';
                    $content_html .= '<div id="ninja'.$key.'"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMninja->get_all_form_fields($key)  as $inputValue => $inputName) {
                        $arr = array();
                        $arr["name"] = $inputValue;
                        $arr["value"] = $inputName;
                        $arr["source_id"] = $key;
                        $arr["source_type"] = $SourceTypeID['ninja'];
                        $arr["item_id"] = 'ninja'.$key;
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= $this->endOfContent($SourceTypeID['ninja'],$key);
                }
            }

            if ($UpiCRMcaldera->is_active()) {
                  foreach ($UpiCRMcaldera->get_all_form() as $key => $value) {
                    $tabs_html .= '<li><a href="#caldera'.strval($key).'">'.$value.'</a></li>';
                    $content_html .= '<div id="caldera'.$key.'"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMcaldera->get_all_form_fields($key)  as $inputValue => $inputName) {
                        $arr = array();
                        $arr["name"] = $inputValue;
                        $arr["value"] = $inputName;
                        $arr["source_id"] = $key;
                        $arr["source_type"] = $SourceTypeID['caldera'];
                        $arr["item_id"] = 'caldera'.$key;
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= $this->endOfContent($SourceTypeID['caldera'],$key);
                }
            }
            
            if ($UpiCRMwpforms->is_active()) {
                foreach ($UpiCRMwpforms->get_all_form() as $key => $value) {
                    $tabs_html .= '<li><a href="#wpforms'.strval($key).'">'.$value.'</a></li>';
                    $content_html .= '<div id="wpforms'.$key.'"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMwpforms->get_all_form_fields($key)  as $inputValue => $inputName) {
                        $arr = array();
                        $arr["name"] = $inputValue;
                        $arr["value"] = $inputName;
                        $arr["source_id"] = $key;
                        $arr["source_type"] = $SourceTypeID['wpforms'];
                        $arr["item_id"] = 'caldera'.$key;
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= $this->endOfContent($SourceTypeID['wpforms'],$key);
                }
            }
            
            if ($UpiCRMElementor->is_active()) {
                foreach ($UpiCRMElementor->get_all_form() as $key => $value) {
                    $tabs_html .= '<li><a href="#elementor'.strval($key).'">'.$value.'</a></li>';
                    $content_html .= '<div id="elementor'.$key.'"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMElementor->get_all_form_fields($key)  as $inputValue => $inputName) {
                        $arr = array();
                        $arr["name"] = $inputValue;
                        $arr["value"] = $inputName;
                        $arr["source_id"] = $key;
                        $arr["source_type"] = $SourceTypeID['elementor'];
                        $arr["item_id"] = 'elementor'.$key;
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= $this->endOfContent($SourceTypeID['elementor'],$key);
                }

            }



?>
    <div class="row">
        <div>
            <?php
                if (isset($msg)) {
            ?>
            <div class="updated">
                <p><?php echo $msg; ?></p>
            </div>
            <?php
                }
            ?>
        </div>
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <?php _e('In this screen you can perform the following actions:','upicrm'); ?> <br />
        <?php _e('1. Map all your site\'s forms and fields into a single, central and manageable data structure. Please use the forms fields mapping wizard below in order to map all you site\'s forms into UpiCRM leads table.','upicrm'); ?> <br />
        <?php _e('2. Add / Edit additional data fields for UpiCRM database: UpiCRM default set of fields can be easily extended to support your unique and specific needs. Just add ANY fields you may need – and map your forms into the new field/s you have defined. ','upicrm'); ?> <br />
        <?php _e('3. Add / edit additional status fields : UpiCRM default set of status fields can be easily extended. Just add ANY status  you may need – and it will become available for you to manage.','upicrm'); ?>

    </div>
        <div class="clearfix"></div>
        <br /><br />
<div class="checkbox">
                    <label><input type="checkbox" value="1" name="upicrm_enable_audit_log" id="upicrm_enable_audit_log" <?php checked(get_option('upicrm_enable_audit_log'), 1, 1 ); ?>>
                     <?php _e('Enable Audit Log for lead management','upicrm'); ?></label>
                    <div class="ajax_load" id="upicrm_enable_audit_log_load" style="display: none; margin-left: 10px;"></div>
               </div>
        <br />
        <a href="admin.php?page=upicrm_allitems&action=reset" class="btn button-primary" onclick="return confirm('<?php _e('are you sure?','upicrm'); ?>');">
            <i class="glyphicon glyphicon-repeat"></i>
            <?php _e('Reset configuration','upicrm'); ?>
        </a>
        <?php _e('this option will reset all configuration, but will not delete data from UpiCRM.','upicrm'); ?>
        <br /><br />
        <a href="admin.php?page=upicrm_allitems&action=delete_all" class="btn button-primary" onclick="return confirm('<?php _e('are you sure?','upicrm'); ?>');">
            <i class="glyphicon glyphicon-trash"></i>
            <?php _e('Delete All data','upicrm'); ?>
        </a>
        <?php _e('this option will delete all data from UpiCRM, but will not delete the configuration.','upicrm'); ?>
        <br /><br />
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h2><?php _e('Map existing forms fields to UpiCRM structured database field:','upicrm'); ?></h2>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <div class="table-responsive">
                <div id="tabs">
                    <table id="table-tabs" class="table table-bordered">
                        <thead style="display: none;">
                            <tr>
                                <th><?php _e('Form Name','upicrm'); ?></th>
                                <th><?php _e('Fields','upicrm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="width: 25%;">
                                    <ul>
                                        <?php
            echo $tabs_html;
            do_action('upicrm_add_map_existing_forms_tab');
                                        ?>
                                    </ul>
                                </td>
                                <td class="fields-container"><?php
            echo $content_html;
            /*$arr = array();
            $arr = apply_filters('alter_loop',$arr);
            if (count($arr)) {
                //Custom Integration
                $content_html .= '<div id="upicrmgs_3"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                $content_html .= $this->TabContentTemplate($arr);
                $content_html .= $this->endOfContent($SourceTypeID['google_sheets'],3);
                echo $content_html;
  
            }*/
                                                             ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<div class="clearfix"></div>
<br /><br />
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h2><?php _e('Existing Fields','upicrm'); ?></h2>
            <form method="post" action="admin.php?page=upicrm_settings">
                <?php _e('Add additional fields and datatypes to UpiCRM:','upicrm'); ?>
                <input type="hidden" name="action" value="save_field" />
                <input type="text" name="field_name" value="" /><br />
                <?php submit_button(__('Add New Field','upicrm')); ?>
            </form>
            <br />
            <div id="load_fields" class="ajax_load2" style="display: none;"></div>
            <ul id="upicrm_fields_sortable">
            <?php
            foreach ($UpiCRMFields->get_as_array() as $key => $value) { ?>
                <li data-id="<?= $key ?>" class="pointer-click">
                    <?php echo $value." (ID - ".$key.")"; ?>
                    &nbsp; <i class="fa fa-arrows arr"></i>
                </li>
            <?php } ?>
            </ul>
            
        </div>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h2><?php _e('Existing Statuses','upicrm'); ?></h2>
            <form method="post" action="admin.php?page=upicrm_settings">
                <?php _e('Add additional status to UpiCRM:','upicrm'); ?>
                <input type="text" name="status_name" value="" /><br />
                <input type="hidden" name="action" value="save_status" />
                <?php submit_button(__('Add New Status','upicrm')); ?>
            </form>
            <br />
            <?php
			$UpiCRMLeadsStatusARRAY = $UpiCRMLeadsStatus->get_as_array();
            if(isset($UpiCRMLeadsStatusARRAY) && $UpiCRMLeadsStatusARRAY <> ''){
			foreach ($UpiCRMLeadsStatus->get_as_array() as $key => $value) { ?>
                <div class="status_edit" style="display: none;" data-status_id="<?php echo $key; ?>">
                    <input type="text" value="<?php echo $value; ?>" data-callback="edit_input" data-status_id="<?php echo $key; ?>" />
                    <span class="glyphicon glyphicon-floppy-save pointer-click" data-callback="save" data-status_id="<?php echo $key; ?>" title="save"></span>
                    <span class="glyphicon glyphicon-floppy-remove pointer-click" data-callback="cancel" data-status_id="<?php echo $key; ?>" title="cancel"></span>
                </div>
                <div class="status_show" data-status_id="<?php echo $key; ?>">
                    <span class="text"><?php echo $value; ?></span>
                    <span class="glyphicon glyphicon-edit pointer-click" data-callback="edit" data-status_id="<?php echo $key; ?>" title="edit"></span>
                    <span class="glyphicon glyphicon-remove pointer-click" data-callback="remove" data-status_id="<?php echo $key; ?>" title="remove"></span>
                </div>
            <?php }//LOOP END
			}//IF ISSET?>
        </div>
    </div>
    <div style="margin-top:20px; display: none;" class="col-md-6"><h2><?php _e('Remove all UpiCRM settings');?></h2>
<p><?php _e('choosing this option will <strong>permanently delete all settings</strong> stored in the database by UpiCRM.Unless you have backed up your database, this action is irreversible and once executed,  can not be undone.'); ?> </p> <form id="upi_settings_deletion" method="post" action="admin.php?page=upicrm_settings"> <input type="hidden" name="action" value="delete_upi_settings" /><input style="margin:0;" type="checkbox" id="delete_upi" name="delete_upi" value="1" /> <label style="margin:0;">I Understand I am about to delete all settings of UpiCRM</label> <div style="margin-top:5px; display:block;"><input id="delete_upi_settings" type="submit" value="<?php _e('Delete all my UPISettings');?>" /></div></form>
    </div>
    <script type="text/javascript">
        $j(document).ready(function ($) {
                $("#upicrm_fields_sortable").sortable({
                    update: function() {
                        $("#load_fields").show();
                        var order = [];
                        var i=0;
                        $('#upicrm_fields_sortable li').each( function(e) {
                            order[i] = $(this).data("id");
                            i++;
                        });
                        var data = {
                            'action': 'change_upicrm_fields_order',
                            'order': order,
                        };
                        $.post(ajaxurl, data , function(response) {
                            if (response != 1) {
                                alert("Oh no! Error!");
                                console.log(response);
                            }
                            $("#load_fields").hide();
                        });
                    }
                });
                $("ul, li").disableSelection();  
            
            
                $j("#upicrm_enable_audit_log").click(function() {
                    var data = {
                        'action': 'change_upicrm_enable_audit_log',
                        'upicrm_enable_audit_log': $(this).prop("checked") ? 1 : 0,
                    };
                    $j("#upicrm_enable_audit_log_load").css("display","inline-block");
                    $j.post(ajaxurl, data , function(response) {
                        if (response == 1) {
                            $j("#upicrm_enable_audit_log_load").css("display","none");
                        }
                        else {
                            alert("Oh no! Error!");
                            console.log(response);
                        }
                    });
                });
            
            $j("select[data-callback='save_field']").change(function () {
                var _this = $j(this);
                if (_this.val() != 0) {
                    var data = {
                        'action': 'save_field_mapping_ajax',
                        'fm_name': $j(this).attr("data-name"),
                        'source_id': $j(this).attr("data-source_id"),
                        'source_type': $j(this).attr("data-source_type"),
                        'field_id': $j(this).val(),
                        'fm_id': $j(this).attr("data-fm_id"),

                    };
                    $j.post(ajaxurl, data, function (response) {
                        if (response != 0) {
                            _this.attr("data-fm_id", response);
                            $j.bigBox({
                                title: "Field has been mapped succesfully!",
                                content: "Your new mapping pair is:<br/><h4 style='font-size: 1.3em;'>" + _this.attr("data-value") + " >> " + _this[0].options[_this[0].selectedIndex].text + "</h4>",
                                color: "#739E73",
                                timeout: 5000,
                                icon: "fa fa-check-square-o",
                                number: ""
                            }, function () {
                                closedthis();
                            });
                        }
                        else {
                            _this.val(0);
                            $j.bigBox({
                                title: "Field mapping attempt failed!",
                                content: "Please, select another UPiCRM field.",
                                color: "#C46A69",
                                icon: "fa fa-warning shake animated",
                                number: "",
                                timeout: 4500
                            });
                        }
                    });
                }
            });

            $j("*[data-callback='edit']").click(function() {
                var id = $j(this).attr("data-status_id");
                $j(".status_show[data-status_id="+id+"]").hide();
                $j(".status_edit[data-status_id="+id+"]").show();
            });

            $j("*[data-callback='save']").click(function() {
                var id = $j(this).attr("data-status_id");
                var val = $j("input[data-callback='edit_input'][data-status_id="+id+"]").val();
                        var data = {
                            'action': 'change_status_name',
                            'lead_status_id': id,
                            'lead_status_name': val,
                        };
                        $j.post(ajaxurl, data , function(response) {
                            if (response == 1) {
                                $j(".status_edit[data-status_id="+id+"]").hide();
                                $j(".status_show[data-status_id="+id+"]").show();
                                $j(".status_show[data-status_id="+id+"] .text").text(val);
                            }
                            else {
                                alert("Oh no! Error!");
                                console.log(response);
                            }
                        });
            });
            
            $("*[data-callback='reset_form']").click(function() {
                if (confirm('<?php _e('are you sure?','upicrm'); ?>')) {
                    var id = $(this).attr("data-id");
                    var SourceTypeID = $(this).attr("data-source-type-id");
                    var elem = $(this);
                    var data = {
                                'action': 'reset_form',
                                'source_type': SourceTypeID,
                                'source_id': id,
                    };
                    $.post(ajaxurl, data , function(response) {
                        if (response == 1) {
                            //alert(elem.closest('.fields-container').html());
                            //elem.closest('.table-responsive').find('select option[value=0]').attr("selected","selected");
                            /*elem.closest('.table-responsive').find('select').each(function() {
                                alert($(this).val());
                            });*/
                            location = 'admin.php?page=upicrm_settings&action=reset_form';
                        }
                        else {
                            alert("Oh no! Error!");
                            console.log(response);
                       }
                  });
            }
            });
            
            $("*[data-callback='disable_form']").click(function() {
                $(this).closest('.actions').find(".loady").css("display","inline-block");
                var id = $(this).attr("data-id");
                var SourceTypeID = $(this).attr("data-source-type-id");
                var elem = $(this);
                var data = {
                    'action': 'disable_form',
                    'source_type': SourceTypeID,
                    'source_id': id,
                };
                $.post(ajaxurl, data , function(response) {
                    if (response == 1) {
                        elem.hide();
                        elem.closest('.actions').find("*[data-callback='enable_form']").show();
                        elem.closest('.actions').find(".loady").hide();
                    }
                    else {
                        alert("Oh no! Error!");
                        console.log(response);
                    }
                });
            });

            $("*[data-callback='enable_form']").click(function() {
                $(this).closest('.actions').find(".loady").css("display","inline-block");
                var id = $(this).attr("data-id");
                var SourceTypeID = $(this).attr("data-source-type-id");
                var elem = $(this);
                var data = {
                    'action': 'enable_form',
                    'source_type': SourceTypeID,
                    'source_id': id,
                };
                $.post(ajaxurl, data , function(response) {
                    if (response == 1) {
                        elem.hide();
                        elem.closest('.actions').find("*[data-callback='disable_form']").show();
                        elem.closest('.actions').find(".loady").hide();
                    }
                    else {
                        alert("Oh no! Error!");
                        console.log(response);
                   }
               });
            });

            $j("*[data-callback='cancel']").click(function() {
                var id = $j(this).attr("data-status_id");
                $j(".status_edit[data-status_id="+id+"]").hide();
                $j(".status_show[data-status_id="+id+"]").show();
            });

            
            

			$j("*[data-callback='remove']").click(function() {
				var is_del = confirm("\nAre you sure?\nDeleting a status might cause a serious problem.\nIf you have leads with this status, the leads will revert to another status, depending on the alphabetic order of your current status list.\nPlease backup your database and export your current leads before you make this change.\nDo you still want to delete this status ?\n");
				if(is_del === true){
					var id = $j(this).data('status_id');
					var data = {
						'action': 'delete_status',
						'lead_status_id': id,
					};
					$j.post(ajaxurl, data , function(response) {
						if (response == 1) {
							$j(".status_show[data-status_id="+id+"]").hide('slow');
						}
						else {
							alert("Oh no! Error!");
							console.log(response);
						}
					});
				}
			});
        });
    </script>
    <?php
        }
        function TabContentTemplate($arr) {
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $fm_obj = $UpiCRMFieldsMapping->get_by($arr["name"],  $arr["source_id"], $arr["source_type"]);
            $content_html = '<tr><td><label class="control-label">'.$arr["value"].'</label></td><td>';
            $content_html .= '<fieldset><section><select data-callback="save_field" data-value="'.$arr["value"].'" data-name="';
            $content_html .= $arr["name"].'" data-source_id="'.$arr["source_id"].'" ';
            //if(isset($fm_obj->fm_id)){
                $content_html .= 'data-source_type="'.$arr["source_type"].'" data-fm_id="'.@$fm_obj->fm_id.'"><option value="0"></option>';
            //}
            foreach ($UpiCRMFields->get() as $field) {
                //if(isset($field->field_id)&& isset($fm_obj->field_id) &&(isset($field->field_name))) {
                   $content_html .= '<option value="' . @$field->field_id . '" ' . @selected( $field->field_id, $fm_obj->field_id, false ) . '>' . @$field->field_name . '</option>';
             //  }
            }
            $content_html .= '</select></section></fieldset></td></tr>';
            //$content_html .= print_r($fm_obj,true);
            return $content_html;
        }

        function InputsTemplate($arr) {
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $fm_obj = $UpiCRMFieldsMapping->get_by($arr["name"],  $arr["source_id"], $arr["source_type"]);
            echo $arr["value"];
    ?>
            <select data-callback="save_field" data-name="<?php echo $arr["name"];?>" data-source_id="<?php echo $arr["source_id"];?>" data-source_type="<?php echo $arr["source_type"];?>"  data-fm_id="<?php echo $fm_obj->fm_id;?>">
                <option value="0"></option>
                <?php
            foreach ($UpiCRMFields->get() as $field) {
                ?>
                <option value="<?php echo $field->field_id; ?>" <?php selected( $field->field_id, $fm_obj->field_id ); ?>><?php echo $field->field_name; ?></option>
                <?php } ?>
            </select>
    <br />
<?php
        }
        function endOfContent($SourceTypeID,$key) {
            $upicrm_cancel_lead_form = get_option('upicrm_cancel_lead_form');
            $text ="";
            $text.= '</tbody></table></div><div class="actions">';
            $text.= '<a href="javascript:void(0);" data-callback="reset_form" data-id="'.$key.'" data-source-type-id="'.$SourceTypeID.'" class="btn btn-default">
            <i class="glyphicon glyphicon-repeat"></i>
            '.__('Reset form configuration', 'upicrm').'</a>&nbsp;&nbsp;&nbsp;';
            $display[0] = "none";
            if (isset($upicrm_cancel_lead_form[$SourceTypeID][$key])) {
                $display[0] = "none";
            } else {
                $display[1] = "none";
            }
            $text.= '<a href="javascript:void(0);" data-callback="disable_form" data-id="'.$key.'" data-source-type-id="'.$SourceTypeID.'" class="btn btn-default" style="display: '.$display[0].';">
            <i class="glyphicon glyphicon-ban-circle"></i>
            '.__('Disable form integration with UpiCRM', 'upicrm').' <i class="glyphicon glyphicon-refresh upicrm-gly-spin loady"></i></a>';
            $text.= '<a href="javascript:void(0);" data-callback="enable_form" data-id="'.$key.'" data-source-type-id="'.$SourceTypeID.'" class="btn btn-default" style="display: '.$display[1].';">
            <i class="glyphicon glyphicon-ok-circle"></i>
            '.__('Enable form integration with UpiCRM', 'upicrm').'<i class="glyphicon glyphicon-refresh upicrm-gly-spin loady"></i></a>';
            $text.= '</div></div>';
            return $text;
        }
        function wp_ajax_save_field_mapping_ajax_callback() {
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            if (!$UpiCRMFieldsMapping->is_exists($_POST['field_id'], $_POST['source_id'], $_POST['source_type'])) {
                echo $UpiCRMFieldsMapping->add_or_update($_POST['fm_id'],$_POST['field_id'], $_POST['fm_name'], $_POST['source_id'], $_POST['source_type']);
            }
            else {
                echo 0;
            }
            die();
        }

        function saveField() {
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMFields->add_unique($_POST['field_name']);
        }

        function saveStatus() {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMLeadsStatus->add_unique($_POST['status_name']);
        }

        function wp_ajax_change_status_name_callback() {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $updateArr = array("lead_status_name" => $_POST['lead_status_name']);
            $UpiCRMLeadsStatus->update($updateArr,$_POST['lead_status_id']);
            echo 1;
            die();
        }

	function wp_ajax_delete_status_callback() {
			global $wpdb;
            $status_table = $wpdb->prefix.'upicrm_leads_status';
			$lead_status_id = $_POST['lead_status_id'];
			$del_query = "Delete from $status_table where lead_status_id = '$lead_status_id'";
            $wpdb->query($del_query);

			echo 1;
            die();
        }
        
        function wp_ajax_reset_form_callback() {
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $UpiCRMFieldsMapping->empty_form_by_id($_POST['source_type'],$_POST['source_id']);
            echo 1;
            die();
        }
        
        function wp_ajax_disable_form_callback() {
            $upicrm_cancel_lead_form = get_option('upicrm_cancel_lead_form');
            $upicrm_cancel_lead_form[$_POST['source_type']][$_POST['source_id']] = 1;
            update_option('upicrm_cancel_lead_form', $upicrm_cancel_lead_form);
            echo 1;
            die();
        }
        
        function wp_ajax_enable_form_callback() {
            $upicrm_cancel_lead_form = get_option('upicrm_cancel_lead_form');
            unset($upicrm_cancel_lead_form[$_POST['source_type']][$_POST['source_id']]);
            update_option('upicrm_cancel_lead_form', $upicrm_cancel_lead_form);
            echo 1;
            die();
        }
        
        function change_upicrm_enable_audit_log_callback() {
            update_option('upicrm_enable_audit_log', $_POST['upicrm_enable_audit_log']);
            echo 1;
            die();
        }
        
        function change_upicrm_fields_order() {
            if (isset($_POST['order']) && is_array($_POST['order'])) {
                foreach ($_POST['order'] as $key => $value) {
                    $value = (int)$value;
                    $key = (int)$key;
                    $UpiCRMFields = new UpiCRMFields();
                    $UpiCRMFields->update(['field_order' => $key], $value);
                }
                echo 1;
            } else {
                echo 0;
            }
            die();
        }
    }

endif;
add_action( 'wp_ajax_save_field_mapping_ajax', array(new UpiCRMAdminSettings,'wp_ajax_save_field_mapping_ajax_callback'));
add_action( 'wp_ajax_change_status_name', array(new UpiCRMAdminSettings,'wp_ajax_change_status_name_callback'));
add_action( 'wp_ajax_delete_status', array(new UpiCRMAdminSettings,'wp_ajax_delete_status_callback'));
add_action( 'wp_ajax_reset_form', array(new UpiCRMAdminSettings,'wp_ajax_reset_form_callback'));
add_action( 'wp_ajax_disable_form', array(new UpiCRMAdminSettings,'wp_ajax_disable_form_callback'));
add_action( 'wp_ajax_enable_form', array(new UpiCRMAdminSettings,'wp_ajax_enable_form_callback'));
add_action( 'wp_ajax_change_upicrm_enable_audit_log', array(new UpiCRMAdminSettings,'change_upicrm_enable_audit_log_callback'));
add_action( 'wp_ajax_change_upicrm_fields_order', array(new UpiCRMAdminSettings,'change_upicrm_fields_order'));


