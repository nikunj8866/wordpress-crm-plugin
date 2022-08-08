<?php
if (!class_exists('UpiCRMUsers')) {

    class UpiCRMUsers {

        var $wpdb;

        public function __construct() {
            global $wpdb;
            $this->wpdb = &$wpdb;
        }

        function action_add_meta_user_profile() {
            $UpiCRMAffiliate = new UpiCRMAffiliate();
            $show = false;
            $user = new WP_User(get_current_user_id());
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role)
                    if ($role == "administrator") {
                        $show = true;
                    }
            }

            if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
                $user_id = get_current_user_id();
                // If is another user's profile page
            } elseif (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
                $user_id = $_GET['user_id'];
                // Otherwise something is wrong.
            }
            $user = new WP_User($user_id);

            if ($show) {
                ?><h3><?php _e('UpiCRM options', 'upicrm'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><label for="upicrm_user_permission"><?php _e('Permission', 'upicrm'); ?></label></th>
                        <td>
                            <select id="upicrm_user_permission" name="upicrm_user_permission">
                                <option
                                    value="0" <?php selected(get_the_author_meta('upicrm_user_permission', $user->ID), 0); ?>><?php _e('Not a UpiCRM User', 'upicrm'); ?></option>
                                <option
                                    value="1" <?php selected(get_the_author_meta('upicrm_user_permission', $user->ID), 1); ?>><?php _e('UpiCRM User', 'upicrm'); ?></option>
                                <option
                                    value="2" <?php selected(get_the_author_meta('upicrm_user_permission', $user->ID), 2); ?>><?php _e('UpiCRM Admin', 'upicrm'); ?></option>
                                 <option value="3" <?php selected(get_the_author_meta('upicrm_user_permission', $user->ID), 3); ?>><?php _e('UpiCRM Affiliate', 'upicrm'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="upicrm_user_affiliate_type"><?php _e('Affiliate Type', 'upicrm'); ?></label></th>
                        <td>
                            <select id="upicrm_user_affiliate_type" name="upicrm_user_affiliate_type">
                                <?php foreach ($UpiCRMAffiliate->get_type_arr() as $key => $value) { ?>
                                <option
                                    value="<?= $key; ?>" <?php selected(get_the_author_meta('upicrm_user_affiliate_type', $user->ID), $key); ?>><?= $value; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                </table><?php
            }
        }

        function action_save_meta_user_profile($user_id) {
            update_user_meta($user_id, 'upicrm_user_permission', sanitize_text_field($_POST['upicrm_user_permission']));
            update_user_meta($user_id, 'upicrm_user_affiliate_type', sanitize_text_field($_POST['upicrm_user_affiliate_type']));

        }

        function action_add_new_user($user_id) {
            $user = new WP_User($user_id);
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role) {
                    if ($role == "administrator") {
                        update_user_meta($user_id, 'upicrm_user_permission', get_option('upicrm_user_permission_1'));
                    } else {
                        update_user_meta($user_id, 'upicrm_user_permission', get_option('upicrm_user_permission_2'));
                    }
                }
            }
        }

        function get_permission() {   global $current_user;
            include_once(ABSPATH . 'wp-includes/pluggable.php');
            if (get_current_user_id() > 0) {
                $get = get_the_author_meta('upicrm_user_permission', get_current_user_id());
                return $get ? $get : 1;
            } else {
                return 0;
            }
        }

        function set_permission($upicrm_user_permission) {
            update_user_meta(get_current_user_id(), 'upicrm_user_permission', sanitize_text_field($upicrm_user_permission));
        }

        function select_list($lead, $callback) {
            $text = '<select name="user_id" data-lead_id="' . $lead->lead_id . '" data-callback="' . $callback . '">';
            $myID = get_current_user_id();
            $user_arr = $this->get_as_array();
            if (get_user_meta($myID, 'upicrm_user_permission', 1) < 2) {
                $save_arr[$myID] = 1;

                $child_users = $this->get_childrens_by_parent_id($myID);
                foreach ($child_users as $child_user) {
                    $save_arr[$child_user->user_id] = 1;
                }

                if (get_user_meta($myID, 'upicrm_user_reassign_manager', 1)) {
                    $parent = $this->get_inside_by_user_id($myID);
                    $save_arr[$parent->user_parent_id] = 1;
                }
                foreach (array_diff_key($user_arr, $save_arr) as $key => $value) {
                    unset($user_arr[$key]);
                }
            }
            foreach ($user_arr as $user_id => $user_name) {
                $selected = selected($lead->user_id, $user_id, false);
                $text .= '<option value="' . $user_id . '" ' . $selected . '>' . $user_name . '</option>';
            }

            $text .= '</select>';
            return $text;
        }

        function select_list_no_lead($callback = "", $name = "user_id") {
            $text = '<select name="' . $name . '" data-callback="' . $callback . '">';
            $myID = get_current_user_id();
            $user_arr = $this->get_as_array();
            if (get_user_meta($myID, 'upicrm_user_permission', 1) < 2) {
                $save_arr[$myID] = 1;

                $child_users = $this->get_childrens_by_parent_id($myID);
                foreach ($child_users as $child_user) {
                    $save_arr[$child_user->user_id] = 1;
                }

                if (get_user_meta($myID, 'upicrm_user_reassign_manager', 1)) {
                    $parent = $this->get_inside_by_user_id($myID);
                    $save_arr[$parent->user_parent_id] = 1;
                }
                foreach (array_diff_key($user_arr, $save_arr) as $key => $value) {
                    unset($user_arr[$key]);
                }
            }
            $text .= '<option value="0"></option>';
            foreach ($user_arr as $user_id => $user_name) {
                //$selected = selected($lead->user_id, $user_id, false);
                $selected = "";
                $text .= '<option value="' . $user_id . '" ' . $selected . '>' . $user_name . '</option>';
            }
            $text .= '</select>';
            return $text;
        }

        function select_list_no_lead_multiple($callback = "", $name = "user_id", $lead_id) {
            $UpiCRMLeads = new UpiCRMLeads();
            $usersAr = array();
            $users = $UpiCRMLeads->get_users_of_lead($lead_id);
            foreach($users as $u)
            {
                array_push($usersAr, $u->user_id);
            }
            $text = '<select name="' . $name . '" data-callback="' . $callback . '" multiple>';
            $myID = get_current_user_id();
            $user_arr = $this->get_as_array();
            if (get_user_meta($myID, 'upicrm_user_permission', 1) < 2) {
                $save_arr[$myID] = 1;

                $child_users = $this->get_childrens_by_parent_id($myID);
                foreach ($child_users as $child_user) {
                    $save_arr[$child_user->user_id] = 1;
                }

                if (get_user_meta($myID, 'upicrm_user_reassign_manager', 1)) {
                    $parent = $this->get_inside_by_user_id($myID);
                    $save_arr[$parent->user_parent_id] = 1;
                }
                foreach (array_diff_key($user_arr, $save_arr) as $key => $value) {
                    unset($user_arr[$key]);
                }
            }

            foreach ($user_arr as $user_id => $user_name) {
                //$selected = selected($lead->user_id, $user_id, false);
                $selected = "";
                if(in_array( $user_id, $usersAr) )
                {
                    $selected = "selected";
                }
                $userID = get_current_user_id();
                $choose_view = get_user_meta($userID, 'upicrm_user_view_only_associated_leads');

                if($user_id == $userID)
                {
                    if($this->get_permission() == 2 && $choose_view[0] != 1)
                    {
                        $text .= '<option value="' . $user_id . '" ' . $selected . '>' . $user_name . '</option>';
                    }
                }
                else
                {
                    $text .= '<option value="' . $user_id . '" ' . $selected . '>' . $user_name . '</option>';
                }
            }
            $text .= '</select>';
            return $text;
        }
        function get_by_id_all_lead_user_name_bkp($lead_id = 0) {
            $displayName = array();
            if( $lead_id != 0)
            {
                $UpiCRMLeads = new UpiCRMLeads();
                $users = $UpiCRMLeads->get_users_of_lead($lead_id);
                $totalUser = count( $users );
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                foreach($users as $id)
                {
                    $user = get_user_by('id', $id->user_id);
                    if (isset($user->display_name)) {
                        if(get_current_user_id() != $id->user_id )
                        {
                            if($leadObj->user_id != $id->user_id)
                            {
                                $displayName[] = $user->display_name;
                            }
                        }
                        else
                        {
                            if($leadObj->user_id != $id->user_id)
                            {
                                $displayName[] = __('You', 'upicrm');
                            }
                            else
                            {
                                if($leadObj->user_id == get_current_user_id() && $totalUser <= 1)
                                {
                                    $displayName[] = __('Unassigned', 'upicrm');
                                }
                            }
                        }
                        
                    }
                }
            }
            
            return implode(", ", $displayName);
        }
        function get_by_id_all_lead_user_name($lead_id = 0) {
            $displayName = array();
            if( $lead_id != 0)
            {
                $UpiCRMLeads = new UpiCRMLeads();
                $users = $UpiCRMLeads->get_users_of_lead($lead_id);
                $totalUser = count( $users );
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                foreach($users as $id)
                {
                    $user = get_user_by('id', $id->user_id);
                    if (isset($user->display_name)) {
                        if(get_current_user_id() != $id->user_id )
                        {
                            if($leadObj->user_id != $id->user_id)
                            {
                                $displayName[] = $user->display_name;
                            }
                            else{
                                if( $this->get_permission() == 2 && empty($displayName))
                                {
                                    $displayName[] = __('Unassigned', 'upicrm');
                                }
                            }
                        }
                        else
                        {
                            if($leadObj->user_id != $id->user_id )
                            {
                                $displayName[] = __('You', 'upicrm');
                            }
                            else
                            {
                                if($leadObj->user_id == get_current_user_id() && $totalUser <= 1)
                                {
                                    $displayName[] = __('Unassigned', 'upicrm');
                                }
                            }
                        }
                        
                    }
                }
            }
            
            return implode(", ", $displayName);
        }

        function select_list_user($name, $selected_option = false, $callback = false) {
            $text = '<select name="' . $name . '" data-callback="' . $callback . '">';
            $text .= '<option value="0"></option>';
            foreach ($this->get_as_array() as $user_id => $user_name) {
                $selected = selected($selected_option, $user_id, false);
                $text .= '<option value="' . $user_id . '" ' . $selected . '>' . $user_name . '</option>';
            }
            $text .= '</select>';
            return $text;
        }

        function select_list_user_table($name, $selected_option = false, $callback = false) {
            $text = '<select name="' . $name . '" data-callback="' . $callback . '" class="form-control">';
            $text .= '<option value="">Show All</option>';
            foreach ($this->get_as_array() as $user_id => $user_name) {
                $selected = selected($selected_option, $user_id, false);
                $text .= '<option value="' . $user_name . '" ' . $selected . '>' . $user_name . '</option>';
            }
            $text .= '</select>';
            return $text;
        }

        function show_permissions_options($type) {

            $arr = [
                0 => __('Not a UpiCRM User', 'upicrm'),
                1 => __('UpiCRM User', 'upicrm'),
                2 => __('UpiCRM Admin', 'upicrm'),
                2 => __('UpiCRM Affiliate', 'upicrm'),
            ];
            ?>
            <select id="user_permission_<?= $type; ?>" name="user_permission_<?= $type; ?>">
                <?php
                foreach ($arr as $key => $value) {
                    $selected = selected(get_option('upicrm_user_permission_' . $type), $key, false);
                    ?>
                    <option value="<?= $key; ?>" <?= $selected; ?>><?= $value; ?></option>
                    <?php
                }
                ?>
            </select>
            <?php
        }

        function get_by_id($id = 0) {
            $displayName = '';
            if ($id != 0) {
                $user = get_user_by('id', $id);
                if (isset($user->display_name)) {
                    $displayName = $user->display_name;
                }
            } else {
                $user = get_users(array('role' => '')); //Editor, Administrator
                $displayName = $user[0]->display_name;
            }
            return $displayName;
        }

        function is_have_permission_to_lead($user_id, $lead_id) {
            //fix this
            return true;
            $UpiCRMLeads = new UpiCRMLeads();
            $permission = false;
            if ($this->get_permission() == 1) {
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                if ($leadObj->user_id == $user_id) {
                    $permission = true;
                }
            }
            if ($this->get_permission() == 2) {
                $permission = true;
            }

            return $permission;
        }

        function get_as_array() {
            $get_users = get_users(array('role' => '')); //Editor, Administrator
            foreach ($get_users as $user) {
                if (get_the_author_meta('upicrm_user_permission', $user->ID) > 0) {
                    $arr[$user->ID] = $user->display_name;
                }
            }
            return $arr;
        }

        function get() {
            $get_users = get_users(array('role' => '')); //Editor, Administrator
            foreach ($get_users as $user) {
                if (get_the_author_meta('upicrm_user_permission', $user->ID) > 0) {
                    $arr[] = $user;
                }
            }
            return $arr;
        }

        function add_inside($insertArr) {
            $this->wpdb->insert(upicrm_db() . "users", $insertArr);
        }

        function update_inside($updateArr, $inside_id) {
            //update integration
            $this->wpdb->update(upicrm_db() . "users", $updateArr, array("inside_id" => $inside_id));
        }

        function get_inside() {
            $rows = $this->wpdb->get_results("SELECT * FROM " . upicrm_db() . "user");
            return $rows;
        }

        function get_inside_by_id($inside_id) {
            $rows = $this->wpdb->get_results("SELECT * FROM " . upicrm_db() . "users WHERE `inside_id`={$inside_id}");
            return $rows[0];
        }

        function get_inside_by_user_id($user_id) {
            $rows = $this->wpdb->get_results("SELECT * FROM " . upicrm_db() . "users WHERE `user_id`={$user_id}");
            if (!empty($rows)) {
                return $rows[0];
            } else {
                return null;
            }
        }

        function get_inside_by_parent_id($user_parent_id) {
            $rows = $this->wpdb->get_results("SELECT * FROM " . upicrm_db() . "users WHERE `user_parent_id`={$user_parent_id}");
            return $rows ? $rows : array();
        }

        function remove_inside($inside_id) {
            //delete integration
            $this->wpdb->delete(upicrm_db() . "users", array("inside_id" => $inside_id));
        }

        function get_childrens_by_parent_id($user_id) {
            $users = $this->get_inside_by_parent_id($user_id);
            $arr = $users;
            if ($users) {
                foreach ($users as $user) {
                    $users2 = $this->get_childrens_by_parent_id($user->user_id);
                    if ($users2) {
                        foreach ($users2 as $user2) {
                            $arr[] = $user2;
                        }
                    }
                }
            }
            return $arr ? $arr : array();
        }

        function get_wp_role() {
            $user = new WP_User(get_current_user_id());
            if (!empty($user->roles) && is_array($user->roles)) {
                foreach ($user->roles as $role)
                    return $role;
            }
        }

        function empty_all() {
            $this->wpdb->query("TRUNCATE TABLE " . upicrm_db() . "users");
            $get_users = get_users(array('role' => '')); //Editor, Administrator
            foreach ($get_users as $user) {
                if (get_the_author_meta('upicrm_user_permission', $user->ID) == 2) {
                    $insertArr['user_id'] = $user->ID;
                    $insertArr['user_parent_id'] = 0;
                    $insertArr['user_label'] = __('Admin', 'upicrm');
                    $this->add_inside($insertArr);
                }
            }
        }

    }
    
    add_action('show_user_profile', array(new UpiCRMUsers, 'action_add_meta_user_profile'));
    add_action('edit_user_profile', array(new UpiCRMUsers, 'action_add_meta_user_profile'));
    add_action('user_register', array(new UpiCRMUsers, 'action_add_new_user'), 10, 1);
    /* function myplugin_registration_save( $user_id ) {
      if ( isset( $_POST['first_name'] ) )
      update_user_meta($user_id, 'first_name', $_POST['first_name']);
      } */

    add_action('personal_options_update', array(new UpiCRMUsers, 'action_save_meta_user_profile'));
    add_action('edit_user_profile_update', array(new UpiCRMUsers, 'action_save_meta_user_profile'));
}