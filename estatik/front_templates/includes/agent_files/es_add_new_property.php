<?php
global $wpdb;
$es_settings = es_front_settings();
if ( isset( $_POST['prop_id'] ) ) {
    $prop_address		= '';
    $agent_id 			= sanitize_text_field($_POST['agent_id']);
    $prop_title 		= sanitize_text_field($_POST['prop_title']);
    $prop_title 		= stripcslashes($prop_title);
    $prop_type 			= sanitize_text_field($_POST['prop_type']);
    $prop_category 		= sanitize_text_field($_POST['prop_category']);
    $prop_status 		= sanitize_text_field($_POST['prop_status']);
    $prop_featured 		= sanitize_text_field($_POST['prop_featured']);
    $prop_hot 			= sanitize_text_field($_POST['prop_hot']);
    $prop_open_house 	= sanitize_text_field($_POST['prop_open_house']);
    $prop_foreclosure 	= sanitize_text_field($_POST['prop_foreclosure']);
    $prop_price 		= sanitize_text_field($_POST['prop_price']);
    $prop_period 		= sanitize_text_field($_POST['prop_period']);
    $prop_bedrooms 		= sanitize_text_field($_POST['prop_bedrooms']);
    $prop_bathrooms 	= sanitize_text_field($_POST['prop_bathrooms']);
    $prop_floors 		= sanitize_text_field($_POST['prop_floors']);
    $prop_area 			= sanitize_text_field($_POST['prop_area']);
    $prop_lotsize 		= sanitize_text_field($_POST['prop_lotsize']);
    $prop_builtin 		= sanitize_text_field($_POST['prop_builtin']);
    $prop_description 	= $_POST['prop_description'];
    $prop_description 	= stripcslashes($prop_description);
    $country_id 		= sanitize_text_field($_POST['country_id']);
    $state_id 			= sanitize_text_field($_POST['state_id']);
    $city_id 			= sanitize_text_field($_POST['city_id']);
    $prop_zip_postcode 	= $_POST['prop_zip_postcode'];
    $prop_street 		= sanitize_text_field($_POST['prop_street']);
    $prop_meta_keywords 		= sanitize_text_field($_POST['prop_meta_keywords']);
    $prop_meta_description 		= sanitize_text_field($_POST['prop_meta_description']);
    $prop_longitude 	= sanitize_text_field($_POST['prop_longitude']);
    $prop_latitude 		= sanitize_text_field($_POST['prop_latitude']);
    $prop_id			= sanitize_text_field($_POST['prop_id']);
    if ( $country_id != '' || $state_id != '' || $city_id != '' ) {
        $prop_address		= sanitize_text_field( $_POST['prop_address'] );
    }
    $num_properties = get_user_meta( $agent_id, 'es_properties_num', true );
    $featured_num_properties = get_user_meta( $agent_id, 'es_featured_properties_num', true );
    if ( ! isset( $_GET['prop_id'] ) ) {
        if (((int) $num_properties > 0 && es_is_enabled_subscription()) || !es_is_enabled_subscription()) {
            $my_post = array(
                'post_title' => $prop_title,
                'post_status' => es_is_listing_publish_automatic() ? 'publish' : 'draft',
                'post_content' => '[es_single_property]',
                'post_author' => $agent_id,
                'post_type' => 'properties',
            );
            // Insert the post into the database
            $post_id = wp_insert_post($my_post);
            wp_set_object_terms($post_id, (int)$prop_category, 'property_category');
            wp_set_object_terms($post_id, (int)$prop_status, 'property_status');
            wp_set_object_terms($post_id, (int)$prop_type, 'property_type');
            $wpdb->insert(
                $wpdb->prefix . 'estatik_properties', array(
                    'prop_id' => $post_id,
                    'agent_id' => $agent_id,
                    'prop_date_added' => time(),
                    'prop_pub_unpub' => es_is_listing_publish_automatic() ? 1 : 0,
                    'prop_title' => $prop_title,
                    'prop_type' => $prop_type,
                    'prop_category' => $prop_category,
                    'prop_status' => $prop_status,
                    'prop_featured' => $prop_featured,
                    'prop_hot' => $prop_hot,
                    'prop_open_house' => $prop_open_house,
                    'prop_foreclosure' => $prop_foreclosure,
                    'prop_price' => $prop_price,
                    'prop_period' => $prop_period,
                    'prop_bedrooms' => $prop_bedrooms,
                    'prop_bathrooms' => $prop_bathrooms,
                    'prop_floors' => $prop_floors,
                    'prop_area' => $prop_area,
                    'prop_lotsize' => $prop_lotsize,
                    'prop_builtin' => $prop_builtin,
                    'prop_description' => $prop_description,
                    'country_id' => $country_id,
                    'state_id' => $state_id,
                    'city_id' => $city_id,
                    'prop_zip_postcode' => $prop_zip_postcode,
                    'prop_street' => $prop_street,
                    'prop_address' => $prop_address,
                    'prop_longitude' => $prop_longitude,
                    'prop_latitude' => $prop_latitude,
                    'prop_meta_keywords' => $prop_meta_keywords,
                    'prop_meta_description' => $prop_meta_description
                ));
            update_user_meta($agent_id, 'es_properties_num', $num_properties - 1);
            if ((int)$prop_featured == 1) {
                update_user_meta($agent_id, 'es_featured_properties_num', $featured_num_properties - 1);
            }
            $prop_id = $wpdb->insert_id;
            // If listings don't publish automatically then send mail.
            if (!es_is_listing_publish_automatic()) {
                $admin_email = get_option('admin_email');
                $subj = "New property #$post_id  is added.";
                $message = "Property #$post_id $prop_address is added, please check it <a href='" . admin_url() . "admin.php?page=es_my_listings'>here</a> and approve.";
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                mail($admin_email, $subj, $message, $headers);
            }
            // Count of properties by agent ID.
            $prop_count = $wpdb->get_var("SELECT COUNT(agent_id) FROM " . $wpdb->prefix . "estatik_properties WHERE agent_id = " . $agent_id);
            $wpdb->update($wpdb->prefix . 'estatik_agents', array('agent_prop_quantity' => $prop_count,), array('agent_id' => $agent_id));
        } else {
            _e( 'You can add new listing!', 'es-plugin' );
        }
    } else if ( isset( $_GET['prop_id'] ) ) {
        $my_post = array(
            'ID' => $prop_id,
            'post_title' => $prop_title,
            'post_type' => 'properties'
        );
        // Update the post into the database
        $post_id = wp_update_post( $my_post );
        wp_set_object_terms( $post_id, (int)$prop_category, 'property_category');
        wp_set_object_terms( $post_id, (int)$prop_status, 'property_status');
        wp_set_object_terms( $post_id, (int)$prop_type, 'property_type');
        $wpdb->update($wpdb->prefix.'estatik_properties', array(
            'agent_id' 			=> $agent_id,
            'prop_title' 		=> $prop_title,
            'prop_type' 		=> $prop_type,
            'prop_category' 	=> $prop_category,
            'prop_status' 		=> $prop_status,
            'prop_featured' 	=> $prop_featured,
            'prop_hot' 			=> $prop_hot,
            'prop_open_house' 	=> $prop_open_house,
            'prop_foreclosure' 	=> $prop_foreclosure,
            'prop_price' 		=> $prop_price,
            'prop_period' 		=> $prop_period,
            'prop_bedrooms' 	=> $prop_bedrooms,
            'prop_bathrooms' 	=> $prop_bathrooms,
            'prop_floors' 		=> $prop_floors,
            'prop_area' 		=> $prop_area,
            'prop_lotsize' 		=> $prop_lotsize,
            'prop_builtin' 		=> $prop_builtin,
            'prop_description' 	=> $prop_description,
            'country_id' 		=> $country_id,
            'state_id' 			=> $state_id,
            'city_id' 			=> $city_id,
            'prop_zip_postcode' => $prop_zip_postcode,
            'prop_street' 		=> $prop_street,
            'prop_address' 		=> $prop_address,
            'prop_longitude' 	=> $prop_longitude,
            'prop_latitude' 	=> $prop_latitude,
            'prop_meta_keywords' 		=> $prop_meta_keywords,
            'prop_meta_description' 	=> $prop_meta_description
        ), array( 'prop_id' => $prop_id ) );
    }
    if ( ! empty( $_POST['prop_data'] ) ) {
        $prop_meta = $_POST['prop_data'];
        $wpdb->delete( $wpdb->prefix.'estatik_properties_meta', array( 'prop_id' => $prop_id,'prop_meta_key'=>'prop_custom_field') );
        $prop_meta_data = serialize($prop_meta);
        $wpdb->insert($wpdb->prefix.'estatik_properties_meta', array(
                'prop_id' 		=> $prop_id,
                'prop_meta_key' => 'prop_custom_field',
                'prop_meta_value' 	=>$prop_meta_data
            )
        );
    }
    $es_image_del_val = $_POST['es_image_del_val'];
    if(!empty($es_image_del_val)){
        for($i=0; $i<count($es_image_del_val); $i++) {
            $upload_dir = wp_upload_dir();
            @unlink($upload_dir['basedir'].$es_image_del_val[$i]);
            $list_image_name = explode("/",$es_image_del_val[$i]);
            $list_image_name = end($list_image_name);
            $list_image_path = str_replace($list_image_name,"",$es_image_del_val[$i]);
            $list_image = $list_image_path.'list_'.$list_image_name;
            @unlink($upload_dir['basedir'].$list_image);
            $list2_image_name = explode("/",$es_image_del_val[$i]);
            $list2_image_name = end($list2_image_name);
            $list2_image_path = str_replace($list2_image_name,"",$es_image_del_val[$i]);
            $list2_image = $list2_image_path.'2column_'.$list2_image_name;
            @unlink($upload_dir['basedir'].$list2_image);
            $table_image_name = explode("/",$es_image_del_val[$i]);
            $table_image_name = end($table_image_name);
            $table_image_path = str_replace($table_image_name,"",$es_image_del_val[$i]);
            $table_image = $table_image_path.'table_'.$table_image_name;
            @unlink($upload_dir['basedir'].$table_image);
            $single_lr_image_name = explode("/",$es_image_del_val[$i]);
            $single_lr_image_name = end($single_lr_image_name);
            $single_lr_image_path = str_replace($single_lr_image_name,"",$es_image_del_val[$i]);
            $single_lr_image = $single_lr_image_path.'single_lr_'.$single_lr_image_name;
            @unlink($upload_dir['basedir'].$single_lr_image);
            $single_center_image_name = explode("/",$es_image_del_val[$i]);
            $single_center_image_name = end($single_center_image_name);
            $single_center_image_path = str_replace($single_center_image_name,"",$es_image_del_val[$i]);
            $single_center_image = $single_center_image_path.'single_center_'.$single_center_image_name;
            @unlink($upload_dir['basedir'].$single_center_image);
            $single_thumb_image_name = explode("/",$es_image_del_val[$i]);
            $single_thumb_image_name = end($single_thumb_image_name);
            $single_thumb_image_path = str_replace($single_thumb_image_name,"",$es_image_del_val[$i]);
            $single_thumb_image = $single_thumb_image_path.'single_thumb_'.$single_thumb_image_name;
            @unlink($upload_dir['basedir'].$single_thumb_image);
        }
    }
    $prop_media_images = $_POST['uploaded_images'];
    $wpdb->delete( $wpdb->prefix.'estatik_properties_meta', array( 'prop_id' => $prop_id,'prop_meta_key'=>'images') );
    if(!empty($prop_media_images)){
        $prop_meta_data = serialize($prop_media_images);
        $wpdb->insert(
            $wpdb->prefix.'estatik_properties_meta',
            array(
                'prop_id' 		=> $prop_id,
                'prop_meta_key' => 'images',
                'prop_meta_value' 	=>$prop_meta_data
            )
        );
    }
    $es_media_video_embed = $_POST['es_media_video_embed'];
    $wpdb->delete( $wpdb->prefix.'estatik_properties_meta', array( 'prop_id' => $prop_id,'prop_meta_key'=>'video') );
    if(!empty($es_media_video_embed)){
        $wpdb->insert(
            $wpdb->prefix.'estatik_properties_meta',
            array(
                'prop_id' 		=> $prop_id,
                'prop_meta_key' => 'video',
                'prop_meta_value' 	=>$es_media_video_embed
            )
        );
    }
    $es_prop_feature	= $_POST['es_prop_feature'];
    $wpdb->delete( $wpdb->prefix.'estatik_properties_features', array( 'prop_id' => $prop_id ) );
    if(!empty($es_prop_feature)){
        for($i = 0; $i < count($es_prop_feature); $i++){
            $wpdb->insert(
                $wpdb->prefix.'estatik_properties_features',
                array(
                    'feature_id' 	=> $es_prop_feature[$i],
                    'prop_id' 		=> $prop_id,
                )
            );
        }
    }
    $es_prop_appliance	= $_POST['es_prop_appliance'];
    $wpdb->delete( $wpdb->prefix.'estatik_properties_appliances', array( 'prop_id' => $prop_id ) );
    if(!empty($es_prop_appliance)){
        for($i = 0; $i < count($es_prop_appliance); $i++){
            $wpdb->insert(
                $wpdb->prefix.'estatik_properties_appliances',
                array(
                    'appliance_id' 		=> $es_prop_appliance[$i],
                    'prop_id' 		=> $prop_id,
                )
            );
        }
    }
    $es_prop_neigh	= $_POST['es_prop_neigh'];
    $neigh_distance	= $_POST['neigh_distance'];
    $wpdb->delete( $wpdb->prefix.'estatik_properties_neighboarhood', array( 'prop_id' => $prop_id ) );
    if(!empty($es_prop_neigh)){
        foreach($es_prop_neigh as $key=>$value)
        {
            $wpdb->insert(
                $wpdb->prefix.'estatik_properties_neighboarhood',
                array(
                    'neigh_id' 		=> $es_prop_neigh[$key],
                    'neigh_distance'=> $neigh_distance[$key],
                    'prop_id' 		=> $prop_id,
                )
            );
        }
    }
    if(isset($_POST['es_prop_save'])){
        wp_redirect('?add_new_prop&prop_id='.$prop_id.'&status=1',301); exit;
    } else {
        global $post;
        $slug = get_post( $post );
        if(!empty($slug)){
            $slug = get_post( $post )->post_name;
        }
        if (!es_is_enabled_subscription()) {
            $pubUnpub = (isset($_POST['pubUnpub']) && $_POST['pubUnpub'] == "0") ? "?added" : "";
        } else {
            $pubUnpub = '?added';
        }
        wp_redirect(home_url().'/'.$slug.'/'.$pubUnpub); exit;
    }
} ?>
<?php if ((es_is_enabled_subscription() && (int)get_user_meta( get_current_user_id(), 'es_properties_num', true ) > 0) || (!es_is_enabled_subscription()) || !empty($_GET['prop_id']) ) : ?>
    <div class="es_wrapper">
    <?php
    $prop_edit = "";
    $prop_meta = "";
    if(isset($_GET['prop_id'])) {
        $prop_edit = $wpdb->get_row( 'SELECT * FROM '.$wpdb->prefix.'estatik_properties WHERE prop_id = '.$_GET['prop_id']);
        $prop_meta_data = $wpdb->get_row( 'SELECT prop_meta_value FROM '.$wpdb->prefix.'estatik_properties_meta WHERE prop_id = '.$_GET['prop_id']." and prop_meta_key = 'prop_custom_field'");
        if(!empty($prop_meta_data)){
            $prop_meta = $prop_meta_data->prop_meta_value;
        }
    }
    ?>
    <input type="hidden" value="<?php _e( "Please fill your field.", "es-plugin" ); ?>" id="pleasefillfield" />
    <input type="hidden" value="<?php _e( "field has been added.", "es-plugin" ); ?>" id="fieldAdded" />
    <input type="hidden" value="<?php _e( "field has been deleted.", "es-plugin" ); ?>" id="fieldDeleted" />
    <input type="hidden" value="<?php _e( "Please enter numbers only.", "es-plugin" ); ?>" id="pleaseNumbersOnly" />
    <form method="post" id="es_prop_insertion" action="">
    <?php if(isset($_GET['prop_id'])) { ?>
        <h1 class="es_mainHeading"><?php _e( "Edit Property", "es-plugin" ); ?></h1>
        <div class="esHead clearFix">
            <p><?php _e( "Please Edit your property detail and click save to finish.", "es-plugin" ); ?></p>
            <input type="submit" class="save_close" value="Save & Close" name="es_prop_save_close" />
            <input type="submit" value="Save" name="es_prop_save" />
        </div>
    <?php }else { ?>
        <h1 class="es_mainHeading"><?php _e( "Add New Property", "es-plugin" ); ?></h1>
        <div class="esHead clearFix">
            <p class="floatLeft"><?php _e( "Please fill up your property detail and click save to finish.", "es-plugin" ); ?></p>
            <input type="submit"  class="save_close" value="<?php _e( "Save & Close", "es-plugin" ); ?>" name="es_prop_save_close" />
            <input type="submit" value="<?php _e( "Save", "es-plugin" ); ?>" name="es_prop_save" />
        </div>
    <?php } ?>
    <?php if (!empty($prop_edit) && !empty( $_GET['status'] ) ): ?>
        <?php if (es_is_enabled_subscription() ): ?>
            <?php if (es_is_listing_publish_automatic()) : ?>
                <div class="es_success"><?php _e( "Thank you for your submission. Your property has been published.", "es-plugin" ); ?></div>
            <?php else: ?>
                <div class="es_success"><?php _e( "Thank you for your submission. Your property will be published after Admin checks and approves it.", "es-plugin" ); ?></div>
            <?php endif; ?>
        <?php else: ?>
            <?php if ( $prop_edit->prop_pub_unpub == 0 ) : ?>
                <div class="es_success"><?php _e( 'Thank you for your submission. Your property will be published after Admin checks and approves it.', 'es-plugin' ); ?></div>
            <?php else : ?>
                <div class="es_success"><?php _e( 'Thank you for your submission. Your property has been published.', 'es-plugin' ); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif ( isset( $_GET['prop_id'] ) && !empty( $_GET['status'] ) ): ?>
        <div class="es_success"><?php _e( "Property has been updated.", "es-plugin" ); ?></div>
    <?php endif; ?>
    <div class="es_content_in addNewProp">
    <div class="es_tabs clearFix">
        <ul>
            <li><a href="#es_basic_info"><?php _e( "Basic information", "es-plugin" ); ?></a></li>
            <li><a href="#es_address"><?php _e( "Address", "es-plugin" ); ?></a></li>
            <li><a href="#es_prop_features"><?php _e( "Features", "es-plugin" ); ?></a></li>
            <li><a href="#es_neighboarhood"><?php _e( "Neighborhood", "es-plugin" ); ?></a></li>
            <li><a href="#es_media"><?php _e( "Media", "es-plugin" ); ?></a></li>
            <li><a href="#es_meta"><?php _e( "Meta Info", "es-plugin" ); ?></a></li>
        </ul>
    </div>
    <div class="es_tabs_contents clearFix">
    <?php $pubUnpub = (!empty($prop_edit) && $prop_edit->prop_pub_unpub!=0) ?  "1" : "0"; ?>
    <input type="hidden" name="pubUnpub" value="<?php echo $pubUnpub; ?>" />
    <input type="hidden" name="agent_id" value="<?php echo get_current_user_id();?>" />
    <div id="es_basic_info" class="es_tabs_content_in clearFix">
    <div class="new_prop_fields_wrap clearFix">
    <div id="es_basic_info_in">
    <div class="new_prop_field clearFix">
        <span><?php _e( "Property ID", "es-plugin" ); ?>:</span>
        <?php
        $prop_id = "";
        if(isset($_GET['prop_id'])){
            $prop_id = $_GET['prop_id'];
        }
        else
        {
            $lastid_obj = new stdClass;
            $sql = "SELECT `AUTO_INCREMENT` as ID
									FROM  INFORMATION_SCHEMA.TABLES
									WHERE TABLE_SCHEMA = '".DB_NAME."'
									AND TABLE_NAME = '".$wpdb->prefix."posts'";
            $lastid_obj = $wpdb->get_row($sql);
            $prop_id = $lastid_obj->ID;
        }
        ?>
        <input type="text" id="prop_id" name="prop_id" readonly="readonly" value="<?php echo $prop_id?>" />
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Title", "es-plugin" ); ?>:</span>
        <input type="text" name="prop_title" value="<?php echo (!empty($prop_edit))?$prop_edit->prop_title:'' ?>" />
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Type", "es-plugin" ); ?>:</span>
        <select name="prop_type">
            <option value=""><?php _e( "Type", "es-plugin" ); ?></option>
            <?php $es_type_listing = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'estatik_manager_types' );
            if(!empty($es_type_listing)) {
                foreach($es_type_listing as $list) {
                    if(!empty($prop_edit)){
                        $selected = ($prop_edit->prop_type==$list->type_id) ? 'selected="selected"' : "";
                    }
                    echo'<option '.$selected.' value="'.$list->type_id.'">'.$list->type_title.'</option>';
                }
            } ?>
        </select>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Category", "es-plugin" ); ?>:</span>
        <select name="prop_category" id="es_show_period">
            <option value=""><?php _e( "Category", "es-plugin" ); ?></option>
            <?php $es_category_listing = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'estatik_manager_categories' );
            if(!empty($es_category_listing)) {
                foreach($es_category_listing as $list) {
                    if(!empty($prop_edit)){
                        $selected = ($prop_edit->prop_category==$list->cat_id) ? 'selected="selected"' : "";
                    }
                    echo'<option '.$selected.' value="'.$list->cat_id.'">'.$list->cat_title.'</option>';
                }
            } ?>
        </select>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Status", "es-plugin" ); ?>:</span>
        <select name="prop_status">
            <option value=""><?php _e( "Status", "es-plugin" ); ?></option>
            <?php $es_status_listing = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'estatik_manager_status' );
            if(!empty($es_status_listing)) {
                foreach($es_status_listing as $list) {
                    if(!empty($prop_edit)){
                        $selected = ($prop_edit->prop_status==$list->status_id) ? 'selected="selected"' : "";
                    }
                    echo'<option '.$selected.' value="'.$list->status_id.'">'.$list->status_title.'</option>';
                }
            } ?>
        </select>
    </div>
    <?php if ( (int) get_user_meta( $agent_id, 'es_featured_properties_num', true ) > 0 ) : ?>
        <div class="new_prop_field clearFix">
            <span><?php _e( "Featured", "es-plugin" ); ?>:</span>
            <?php
            if(!empty($prop_edit)){
                $yes = ($prop_edit->prop_featured==1) ? 'checked="checked"' : "";
                $no = ($prop_edit->prop_featured==0) ? 'checked="checked"' : "";
                $yesClass = ($prop_edit->prop_featured==1) ? 'active' : "";
                $noClass = ($prop_edit->prop_featured==0) ? 'active' : "";
            }
            ?>
            <label class="<?php echo $yesClass?>"><input <?php echo $yes?> type="radio" name="prop_featured" value="1" /> <?php _e( "Yes", "es-plugin" ); ?></label>
            <label class="<?php echo $noClass?>"><input <?php echo $no?> type="radio" name="prop_featured" value="0" /> <?php _e( "No", "es-plugin" ); ?></label>
        </div>
    <?php endif; ?>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Hot", "es-plugin" ); ?>:</span>
        <?php
        $yes = "";
        $no = "";
        $yesClass = "";
        $noClass = "";
        if(!empty($prop_edit)){
            $yes = ($prop_edit->prop_hot==1) ? 'checked="checked"' : "";
            $no = ($prop_edit->prop_hot==0) ? 'checked="checked"' : "";
            $yesClass = ($prop_edit->prop_hot==1) ? 'active' : "";
            $noClass = ($prop_edit->prop_hot==0) ? 'active' : "";
        }
        ?>
        <label class="<?php echo $yesClass?>"><input <?php echo $yes?> type="radio" name="prop_hot" value="1" /> <?php _e( "Yes", "es-plugin" ); ?></label>
        <label class="<?php echo $noClass?>"><input <?php echo $no?> type="radio" name="prop_hot" value="0" /> <?php _e( "No", "es-plugin" ); ?></label>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Open house", "es-plugin" ); ?>:</span>
        <?php
        $yes = "";
        $no = "";
        $yesClass = "";
        $noClass = "";
        if(!empty($prop_edit)){
            $yes = ($prop_edit->prop_open_house==1) ? 'checked="checked"' : "";
            $no = ($prop_edit->prop_open_house==0) ? 'checked="checked"' : "";
            $yesClass = ($prop_edit->prop_open_house==1) ? 'active' : "";
            $noClass = ($prop_edit->prop_open_house==0) ? 'active' : "";
        }
        ?>
        <label class="<?php echo $yesClass?>"><input <?php echo $yes?> type="radio" name="prop_open_house" value="1" /> <?php _e( "Yes", "es-plugin" ); ?></label>
        <label class="<?php echo $noClass?>"><input <?php echo $no?> type="radio" name="prop_open_house" value="0" /> <?php _e( "No", "es-plugin" ); ?></label>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Foreclosure", "es-plugin" ); ?>:</span>
        <?php
        $yes = "";
        $no = "";
        $yesClass = "";
        $noClass = "";
        if(!empty($prop_edit)){
            $yes = ($prop_edit->prop_foreclosure==1) ? 'checked="checked"' : "";
            $no = ($prop_edit->prop_foreclosure==0) ? 'checked="checked"' : "";
            $yesClass = ($prop_edit->prop_foreclosure==1) ? 'active' : "";
            $noClass = ($prop_edit->prop_foreclosure==0) ? 'active' : "";
        }
        ?>
        <label class="<?php echo $yesClass?>"><input <?php echo $yes?> type="radio" name="prop_foreclosure" value="1" /> <?php _e( "Yes", "es-plugin" ); ?></label>
        <label class="<?php echo $noClass?>"><input <?php echo $no?> type="radio" name="prop_foreclosure" value="0" /> <?php _e( "No", "es-plugin" ); ?></label>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Price", "es-plugin" ); ?>:</span>
        <input type="text" id="prop_price" name="prop_price" value="<?php echo (!empty($prop_edit))?$prop_edit->prop_price:'' ?>" />
        <div class="es_new_prop_error"></div>
    </div>
    <div class="new_prop_field clearFix" id="es_period_for_rent" style="display:none;">
        <span><?php _e( "Period", "es-plugin" ); ?>:</span>
        <select name="prop_period">
            <?php $es_period_listing = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'estatik_manager_rent_period' );
            if(!empty($es_period_listing)) {
                foreach($es_period_listing as $list) {
                    if(!empty($prop_edit)){
                        $selected = ($prop_edit->prop_period==$list->period_id) ? 'selected="selected"' : "";
                    }
                    echo'<option '.$selected.' value="'.$list->period_id.'">'.$list->period_title.'</option>';
                }
            } ?>
        </select>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Bedrooms", "es-plugin" ); ?>:</span>
        <div class="es_spinner">
            <input type="number" id="es_bedrooms" name="prop_bedrooms" value="<?php echo (!empty($prop_edit) && $prop_edit->prop_bedrooms!=0)?$prop_edit->prop_bedrooms:'' ?>" />
        </div>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Bathrooms", "es-plugin" ); ?>:</span>
        <input type="number" name="prop_bathrooms" step="0.1"
               value="<?php echo (!empty($prop_edit) && $prop_edit->prop_bathrooms!=0)?$prop_edit->prop_bathrooms:'' ?>" />
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Floors", "es-plugin" ); ?>:</span>
        <input type="number" name="prop_floors" value="<?php echo (!empty($prop_edit) && $prop_edit->prop_floors!=0)?$prop_edit->prop_floors:'' ?>" />
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Area", "es-plugin" ); ?>:</span>
        <input type="text" name="prop_area" value="<?php echo (!empty($prop_edit) && $prop_edit->prop_area!=0)?$prop_edit->prop_area:'' ?>" />
        <small>
            <?php $es_dimension = $wpdb->get_row( 'SELECT dimension_title FROM '.$wpdb->prefix.'estatik_manager_dimension WHERE dimension_status=1' );
            if(!empty($es_dimension)) { echo $es_dimension->dimension_title; }
            ?>
        </small>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Lot size", "es-plugin" ); ?>:</span>
        <input type="text" name="prop_lotsize" value="<?php echo (!empty($prop_edit) && $prop_edit->prop_lotsize!=0)?$prop_edit->prop_lotsize:'' ?>" />
        <small>
            <?php $es_dimension = $wpdb->get_row( 'SELECT dimension_title FROM '.$wpdb->prefix.'estatik_manager_dimension WHERE dimension_status=1' );
            if(!empty($es_dimension)) { echo $es_dimension->dimension_title; }
            ?>
        </small>
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Built in", "es-plugin" ); ?>:</span>
        <input type="text" name="prop_builtin" value="<?php echo (!empty($prop_edit))?$prop_edit->prop_builtin:'' ?>" />
    </div>
    <div class="new_prop_field clearFix">
        <span><?php _e( "Description", "es-plugin" ); ?>:</span>
        <div class="addNewPropEditor">
            <?php
            $content = (!empty($prop_edit))?$prop_edit->prop_description:'';
            $prop_description = 'prop_description';
            $settings = array( 'media_buttons' => false, 'wpautop' => false );
            wp_editor( $content, $prop_description, $settings );
            ?>
        </div>
    </div>
    <?php
    //echo $prop_meta;
    if($prop_meta != ""){
        $meta_value = unserialize($prop_meta);
        foreach($meta_value as $key=>$val){
            $key_val = str_replace("'","",$key);
            ?>
            <div class="new_prop_field clearFix">
                <span><?php echo $key_val?>:</span>
                <input type="text" name="prop_data['<?php echo $key_val?>']" value="<?php echo $val?>" />
                <a href="javascript:void(0)" onclick="es_field_del(this)" class="field_del"></a>
            </div>
        <?php }
    } ?>
    </div>
    </div>
    </div>
    <div id="es_address" class="es_tabs_content_in clearFix">
        <div class="new_prop_fields_wrap clearFix">
            <div id="es_address">
                <div class="new_prop_field clearFix">
                    <span><?php _e( "Country", "es-plugin" ); ?>:</span>
                    <?php
                    global $wpdb;
                    $es_country_listing = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'estatik_manager_countries' );	 ?>
                    <select onchange="es_prop_country_states(this); myGeocodeFirst()" id="es_country" name="country_id">
                        <option value="0"><?php _e( "Choose Country", "es-plugin" ); ?></option>
                        <?php
                        $selected="";
                        foreach($es_country_listing as $list) {
                            if(!empty($prop_edit)){
                                $selected = ($prop_edit->country_id==$list->country_id) ? 'selected="selected"' : "";
                            }
                            if(!empty($prop_edit) && $prop_edit->country_id==$list->country_id){
                                $es_country_id =$prop_edit->country_id;
                            }
                            ?>
                            <option <?php echo $selected?> value="<?php echo $list->country_id?>"><?php echo $list->country_title?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="new_prop_field clearFix">
                    <span><?php _e( "State/Region", "es-plugin" ); ?>:</span>
                    <select onchange="es_prop_states_cities(this); myGeocodeFirst()" id="es_states" name="state_id">
                        <?php if(isset($_GET['prop_id'])) {
                            include('es_property_temps/es_property_states.php');
                        } else {
                            ?>
                            <option value=""></option>
                        <?php } ?>
                    </select>
                    <div id="es_states_loader" class="new_prop_loader"></div>
                </div>
                <div class="new_prop_field clearFix">
                    <span><?php _e( "City", "es-plugin" ); ?>:</span>
                    <select id="es_cities" onchange="myGeocodeFirst()" name="city_id">
                        <?php if(isset($_GET['prop_id'])) {
                            include('es_property_temps/es_property_cities.php');
                        } else {
                            ?>
                            <option value=""></option>
                        <?php } ?>
                    </select>
                    <div id="es_cities_loader" class="new_prop_loader"></div>
                </div>
                <div class="new_prop_field clearFix">
                    <span><?php _e( "ZIP/Postcode", "es-plugin" ); ?>:</span>
                    <input type="text" name="prop_zip_postcode" value="<?php echo (!empty($prop_edit) && $prop_edit->prop_zip_postcode!=0)?$prop_edit->prop_zip_postcode:'' ?>" />
                </div>
                <div class="new_prop_field clearFix">
                    <span><?php _e( "Street", "es-plugin" ); ?>:</span>
                    <input type="text" id="prop_street" name="prop_street" onblur="myGeocodeFirst()" value="<?php echo (!empty($prop_edit))?$prop_edit->prop_street:'' ?>" />
                    <input type="hidden" value="<?php echo (!empty($prop_edit))?$prop_edit->prop_address:'' ?>" id="prop_address" name="prop_address" />
                </div>
                <hr />
                <div class="new_prop_field clearFix">
                    <span><?php _e( "Longitude", "es-plugin" ); ?>:</span>
                    <input id="prop_longitude" type="text" name="prop_longitude" value="" />
                </div>
                <div class="new_prop_field clearFix">
                    <span><?php _e( "Latitude", "es-plugin" ); ?>:</span>
                    <input type="text" id="prop_latitude" name="prop_latitude" value="" />
                </div>
                <div id="es_address_map">
                </div>
            </div>
        </div>
    </div>
    <div id="es_prop_features" class="es_tabs_content_in clearFix">
        <div class="boxSizing es_manager_lists">
            <h2><?php _e( "Features", "es-plugin" ); ?></h2>
            <div id="es_feature_listing">
                <ul>
                    <?php
                    $prop_feature=1;
                    $prop_id = (isset($_GET['prop_id']))?$_GET['prop_id']:'';
                    include(PATH_DIR.'admin_template/es_manager/es_manager_temps/es_feature.php'); ?>
                </ul>
            </div>
        </div>
        <div class="boxSizing es_manager_lists">
            <h2><?php _e( "Appliances", "es-plugin" ); ?></h2>
            <div id="es_appliance_listing">
                <ul>
                    <?php
                    $prop_appliance=1;
                    $prop_id = (isset($_GET['prop_id']))?$_GET['prop_id']:'';
                    include(PATH_DIR.'admin_template/es_manager/es_manager_temps/es_appliance.php'); ?>
                </ul>
            </div>
        </div>
    </div>
    <div id="es_neighboarhood" class="es_tabs_content_in clearFix">
        <div class="boxSizing es_manager_lists">
            <div id="es_neigh_listing">
                <ul>
                    <?php
                    $prop_neigh=1;
                    $prop_id = (isset($_GET['prop_id']))?$_GET['prop_id']:'';
                    include(PATH_DIR.'admin_template/es_manager/es_manager_temps/es_neigh.php'); ?>
                </ul>
            </div>
        </div>
    </div>
    <div id="es_media" class="es_tabs_content_in clearFix">
        <div class="es_media_tabs clearFix">
            <ul>
                <li><a class="active" href="#es_media_images"><?php _e( "Images", "es-plugin" ); ?></a></li>
                <li><a href="#es_media_video"><?php _e( "Video", "es-plugin" ); ?></a></li>
            </ul>
        </div>
        <div id="es_media_images" style="display:block;" class="es_media_contents clearFix">
            <p><?php _e( "Select files or upload images in a zip-file.", "es-plugin" ); ?></p>
            <div class="es_media_images_field clearFix">
                <input type="file" onchange="es_media_image_upload(this)" multiple="multiple" name="es_media_images[]" />
                <a href="javascript:void(0)"><?php _e( "Upload", "es-plugin" ); ?></a>
                <span id="es_media_images_loader"></span>
            </div>
            <p><?php _e( "The first image will be default image. You can drag and drop images to change their order.", "es-plugin" ); ?></p>
            <div id="es_media_images_listing" class="clearFix">
                <?php
                $prop_images=1;
                $prop_id = (isset($_GET['prop_id']))?$_GET['prop_id']:'';
                include('es_property_temps/es_property_images.php'); ?>
            </div>
            <div id="es_media_image_del"></div>
        </div>
        <div id="es_media_video" class="es_media_contents clearFix">
            <div class="es_media_video_embed clearFix">
                <label><?php _e( "Embed the code", "es-plugin" ); ?>:</label>
                <?php
                $embed_video ="";
                if(isset($_GET['prop_id'])){
                    $prop_id = $_GET['prop_id'];
                    $sql = 'SELECT prop_meta_value FROM '.$wpdb->prefix.'estatik_properties_meta WHERE prop_id = "'.$prop_id.'" AND prop_meta_key = "video"';
                    $embed_video = $wpdb->get_row($sql);
                }
                ?>
                <input type="text" name="es_media_video_embed" value='<?php echo (!empty($embed_video))?stripslashes($embed_video->prop_meta_value):'' ?>' />
            </div>
        </div>
    </div>
    <div id="es_meta" class="es_tabs_content_in clearFix">
        <div class="new_prop_fields_wrap">
            <div class="new_prop_field clearFix">
                <span><?php _e( "Meta keywords", "es-plugin" ); ?>:</span>
                <input type="text" name="prop_meta_keywords" value="<?php echo (!empty($prop_edit))?$prop_edit->prop_meta_keywords:'' ?>" />
            </div>
            <div class="new_prop_field clearFix">
                <span><?php _e( "Meta description", "es-plugin" ); ?>:</span>
                <textarea name="prop_meta_description"><?php echo (!empty($prop_edit))?$prop_edit->prop_meta_description:'' ?></textarea>
            </div>
        </div>
    </div>
    </div>
    </div>
<!--		<script>var __nonce = "<?php echo wp_create_nonce("image-validation"); ?>";</script>	-->
    </form>
    </div>
<?php else : ?>
    <?php _e( 'You can\'t add new listing.', 'es-plugin' ); ?>
<?php endif; ?>
