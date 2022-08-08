<section id="widget-grid" class="">
    <!-- row -->
    <div id="LeadsRouteTable" class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <!-- Widget ID (each widget will need unique ID)-->
            <div class="jarviswidget jarviswidget-color-blueLight" id="wid-id-1" data-widget-editbutton="false">
                <header>
                    <span class="widget-icon">
                        <i class="fa fa-table">
                        </i>
                    </span>
                    <h2>
                        <?php _e('Lead Routing Table', 'upicrm'); ?>
                    </h2>
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
                                    <th data-class="expand">
                                        <?php _e('Order', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Field', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Operator', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Value', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Assign lead to', '', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Change lead status to', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Add field value', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Webservice status', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('UpiCRM master', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Email template', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Email template', 'upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                        <?php _e('Affiliate name', 'upicrm'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody><?php
                                $GetUserArr = $UpiCRMUsers->get_as_array();
                                $FieldsArr = $UpiCRMFields->get_as_array();
                                $LeadsStatusArr = $UpiCRMLeadsStatus->get_as_array();
                                $masterArr = $UpiCRMIntegrations->get_master_array();
                                foreach ($UpiCRMLeadsRoute->get() as $obj) {
                                    ?><tr>
                                        <td class="upicrm_lead_actions"><input type="number" class="route_order" data-lead_route_id="<?php echo $obj->lead_route_id ?>" value="<?php echo $obj->lead_route_order; ?>" style="max-width: 70px;" />
                                        &nbsp;
                                        <span class="glyphicon glyphicon-floppy-save" data-callback="change_order" data-lead_route_id="<?php echo $obj->lead_route_id; ?>" title="<?php _e('Save new order', 'upicrm'); ?>"></span>
                                        </td>
                                        <td data-belongs=""><?php
                                            echo $list_option[$obj->lead_route_option][$obj->field_id];
                                            if ($obj->lead_route_and) {
                                                echo "<br />";
                                                echo $list_option[$obj->lead_route_option2][$obj->field_id2];
                                            }
                                            if ($obj->lead_route_and == 2) {
                                                echo "<br />";
                                                echo $list_option[$obj->lead_route_option3][$obj->field_id3];
                                            }
                                            ?></td>
                                        <td data-belongs=""><?php
                                            echo $OperatorArr[$obj->lead_route_type];
                                            if ($obj->lead_route_and) {
                                                echo "<br />";
                                                echo $OperatorArr[$obj->lead_route_type2];
                                            }
                                             if ($obj->lead_route_and == 2) {
                                                echo "<br />";
                                                echo $OperatorArr[$obj->lead_route_type3];
                                            }
                                            ?></td>
                                        <td data-belongs=""><?php
                                            echo $obj->lead_route_value;
                                            if ($obj->lead_route_and) {
                                                echo "<br />";
                                                echo $obj->lead_route_value2;
                                            }
                                            if ($obj->lead_route_and == 2) {
                                                echo "<br />";
                                                echo $obj->lead_route_value3;
                                            }
                                            ?></td>
                                        <td data-belongs=""><?php
                                            $user_rrs = explode(",", $obj->leads_route_rr_users);
                                            foreach ($user_rrs as $user_rr) {
                                                echo $GetUserArr[$user_rr] . "<br />";
                                            }
                                            ?></td>
                                        <td data-belongs=""><?php echo $LeadsStatusArr[$obj->lead_status_id]; ?></td>
                                        <td data-belongs="">
                                            <?php if ($obj->change_field_id > 0) { ?>
                                                <?php echo $FieldsArr[$obj->change_field_id]; ?>
                                                &gt;&gt;
                                                <?php echo $obj->change_field_value; ?>
    <?php } ?>
                                        </td>
                                        <td data-belongs=""><?php echo $obj->webservice_id > 0 ? "On" : "Off"; ?></td>
                                        <td data-belongs=""><?php echo $obj->integration_id > 0 ? $masterArr[$obj->integration_id] : "Off"; ?></td>
                                        <td data-belongs=""><?php echo $obj->mail_id > 0 && @isset($getMailARR[$obj->mail_id]) ? $getMailARR[$obj->mail_id] : ""; ?></td>
                                        <td data-belongs=""><?= $obj->affiliate_id > 0 && @isset($GetUserArr[$obj->affiliate_id]) ? $GetUserArr[$obj->affiliate_id] : ""; ?></td>
                                        <td data-belongs="" class="upicrm_lead_actions">
                                            <span class="glyphicon glyphicon-edit" data-callback="edit" data-lead_route_id="<?php echo $obj->lead_route_id; ?>" title="<?php _e('Edit', 'upicrm'); ?>"></span>
                                            <span class="glyphicon glyphicon-remove" data-callback="remove" data-lead_route_id="<?php echo $obj->lead_route_id; ?>" title="<?php _e('Remove', 'upicrm'); ?>"></span>
                                        </td>
                                    </tr>
<?php } ?>
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
    <!-- end row -->
    <!-- end row -->
</section>