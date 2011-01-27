<?php
class LugMap {
    function init() {
        add_action( 'init', array( __CLASS__, 'ajax' ) );
        add_action( 'init', array( __CLASS__, 'post_type' ) );
        add_action( 'init', array( __CLASS__, 'enqueues' ) );
        add_action( 'init', array( __CLASS__, 'reverse_geocode' ) );
        add_action( 'wp_head', array( __CLASS__, 'add_new_entry' ) );
        add_action( 'save_post', array( __CLASS__, 'settings_box_process' ) );
        add_action( 'admin_head', array( __CLASS__, 'add_new_entry' ) );
        add_filter( 'the_comments', array( __CLASS__, 'filter_entries' ) );
        add_shortcode( 'lugmap', array( __CLASS__, 'shortcode' ) );
    }
    
    function post_type() {
        register_post_type( 'lugmap', array(
            'labels' => array(
                'name' => __('Lug Map', 'newlugmap' ),
                'singular_name' => __('Lug Map', 'newlugmap' ),
                'add_new_item' => __('New Point', 'newlugmap' ),
                'edit_item' => __('Edit Point', 'newlugmap' ),
            ),
            'public' => true,
            'map_meta_cap' => true,
            'rewrite' => array('slug' => 'lugmap'),
            'supports' => array( 'title', 'comments' ),
            'register_meta_box_cb' => array( __CLASS__, 'meta_boxes' ),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true
        ) );
    }
    
    function meta_boxes( $post ) {
        remove_meta_box( 'commentstatusdiv', 'lugmap', 'normal' );
        // Change comments to list entries
        remove_meta_box( 'commentsdiv', 'lugmap', 'normal' );
        // Add shortcode box
        add_meta_box( 'lugmap_shortcode', __( 'Shortcode', 'newlugmap' ), array(__CLASS__, 'shortcode_box'), 'lugmap', 'side' );
        // Add default centering
        add_meta_box( 'lugmap_settings', __( 'Settings', 'newlugmap' ), array(__CLASS__, 'settings_box'), 'lugmap', 'side' );
        // Meta box to add a new entry
        if( 'publish' == $post->post_status ) {
            add_meta_box('commentsdiv', __('Entries', 'newlugmap'), 'post_comment_meta_box', 'lugmap', 'normal' );
            add_meta_box( 'lugmap_new_entry', __( 'Create New Entry', 'newlugmap' ), array(__CLASS__, 'new_entry_box'), 'lugmap', 'normal' );
        }
    }
    
    function settings_box_process( $post_id ) {
        $meta_key = 'map_settings';
        $meta_key_submits = 'map_submits';
        $width = null;
        $height = null;
        $lat = null;
        $lon = null;
        $zoom = null;
        
        if ( !wp_verify_nonce( $_POST['newlugmap_nonce'], 'newlugmap_settings_box' ))
            return $post_id;
        
        if ( !current_user_can( 'edit_post', $post_id ) )
            return $post_id;
        
        if( isset( $_POST['submits'] ) )
            $submits = ( sanitize_key( $_POST['submits'] ) == 'on' ) ? 1 : 0;
        
        if( isset( $_POST['width'] ) && !empty( $_POST['width'] ) )
            $width = sanitize_key( $_POST['width'] );
        
        if( isset( $_POST['height'] ) && !empty( $_POST['height'] ) )
            $height = sanitize_key( $_POST['height'] );
        
        if( isset( $_POST['lat'] ) && !empty( $_POST['lat'] ) )
            $lat = sanitize_text_field( $_POST['lat'] );
        
        if( isset( $_POST['lon'] ) && !empty( $_POST['lon'] ) )
            $lon = sanitize_text_field( $_POST['lon'] );
        
        if( isset( $_POST['zoom'] ) && !empty( $_POST['zoom'] ) )
            $zoom = sanitize_key( $_POST['zoom'] );
        
        update_post_meta( $post_id, $meta_key_submits, $submits );
        
        if( $width && $height && $lat && $lon && $zoom ) {
            $key_value = implode( ',', array( $width, $height, $lat, $lon, $zoom ) );
            update_post_meta( $post_id, $meta_key, $key_value );
        }
        
        return $post_id;
    }
    
    function get_map_settings( $post_id = null ) {
        $meta_key = 'map_settings';
        $meta_key_submits = 'map_submits';
        
        $settings = get_post_meta( $post_id, $meta_key, true ) . ',' . $post_id;
        $setting_submits = get_post_meta( $post_id, $meta_key_submits, true );
        
        $settings = array_combine( array( 'mapWidth', 'mapHeight', 'mapLat', 'mapLon', 'mapZoom', 'mapID' ), explode( ',', $settings ) );
        $settings['mapSubmits'] = $setting_submits;
        
        return $settings;
    }
    
    function settings_box( $post ) {
        if( 'publish' != $post->post_status ) 
            echo '<p>' . __( 'Publish this map to access it\'s settings', 'newlugmap' ) . '</p>';
        else 
            self::template_render( 'settings_box', self::get_map_settings( $post->ID ) );
    }
    
    function shortcode_box( $post ) {
        if( 'publish' != $post->post_status ) 
            echo '<p>' . __( 'Publish this map to get it\'s shortcode', 'newlugmap' ) . '</p>';
        else {
            echo '<input type="text" class="long-text" value="[lugmap ' . $post->ID . ']" />';
        }
    }
    
    function shortcode( $args ) {
        $map_id = reset($args);
        $map = get_post( $map_id );
        $settings = self::get_map_settings( $map_id );
        
        if( $map && $map->post_type == 'lugmap' ) {
            // This will load map settings
            wp_localize_script( 'new-lug-map', 'mapSettings', $settings );
            if( $settings['mapSubmits'] )
                return self::template_render( 'form', null, false );
            else
                return self::template_render( 'single', null, false );
        }
    }
    
    function add_new_entry() {
        global $post;
        
        if ( !wp_verify_nonce( $_POST['newlugmap_nonce'], 'newlugmap_entry' ))
            return;
        
        if( !isset( $_POST['lm'] ) || empty( $_POST['lm'] ) )
            return;
        
        $title = sanitize_text_field( $_POST['lm']['title'] );
        $subtitle = sanitize_text_field( $_POST['lm']['subtitle'] );
        $email = sanitize_email( $_POST['lm']['email'] );
        $www = sanitize_url( $_POST['lm']['www'] );
        $desc = esc_html( $_POST['lm']['dsc'] );
        $point = sanitize_text_field($_POST['lm']['point']);
        
        if( $title && $email && $point ){ 
            $entry_id = wp_insert_comment(
                array(
                    "comment_post_ID" => $post->ID,
                    "comment_author" => $title,
                    "comment_author_email" => $email,
                    "comment_author_url" => $www,
                    "comment_content" => $desc,
                    "comment_approved" => '1'
                )
            );
            if( $entry_id ) {
                add_comment_meta( $entry_id, 'point_subtitle', $subtitle, true  );
                add_comment_meta( $entry_id, 'point_pos', $point, true  );
            }
        }
    }
    
    function new_entry_box( $post ) {
        // This will load map settings
        wp_localize_script( 'new-lug-map', 'mapSettings',  self::get_map_settings( $post->ID ) );
        
        self::template_render( 'form' );
    }
    
    function enqueues() {
        wp_register_script( 'googlemaps-v3', 'http://maps.google.com/maps/api/js?sensor=false', null, NEW_LUG_MAP );
        wp_register_script( 'bmap', '/wp-content/plugins/new-lug-map/js/jQuery.bMap.1.3.min.js', array( 'jquery', 'googlemaps-v3' ), '1.3' );
        //wp_register_script( 'bmap', plugins_url( '/js/jQuery.bMap.1.3.min.js', __FILE__ ), array( 'jquery', 'googlemaps-v3' ), '1.3' );
        wp_enqueue_script( 'new-lug-map', '/wp-content/plugins/new-lug-map/js/new-lug-map.js', array( 'bmap' ), '1.3', true );
        //wp_enqueue_script( 'new-lug-map', plugins_url( '/js/new-lug-map.js', __FILE__ ), array( 'bmap' ), '1.3', true );
    }
    
    function get_map_data( $post_id ) {
        if( !intval( $post_id ) && $post_id == 0 )
            return;
        
        $entries = get_comments( array( 'post_id' => $post_id ) );
        if( empty( $entries ) )
            return;
        
        $points = array();
        foreach( $entries as $e ) {
            $pos = get_comment_meta( $e->comment_ID, 'point_pos', true );
            $pos = explode( ',', $pos );
            $subtitle = get_comment_meta( $e->comment_ID, 'point_subtitle', true );
            $subtitle = $subtitle ? "($subtitle)" : '';
            $points[] = array(
                "lat" => $pos[0],
                "lng" => $pos[1],
                "title" => "<a href=\"{$e->comment_author_url}\">{$e->comment_author}</a> $subtitle",
                "body" => $e->comment_content
            );
        }
        return apply_filters( 'get_map_data', $points );
    }
    
    function ajax() {
        if( isset( $_GET['ajax_map'] ) && isset( $_GET['action'] ) ) {
            define( 'NEW_LUG_MAP_AJAX', true );
            $response['name'] = "Map_" . intval( $_GET['action'] );
            $response['type'] = "marker";
            $response['data'] = self::get_map_data( intval( $_GET['action'] ) );
            die( json_encode( $response ) );
        }
    }
    
    function reverse_geocode() {
        if( $_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'] )
            return;
        
        if( !isset( $_GET['ajax_reverse_geocode'] ) )
            return;
        
        $url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=";
        $address = sanitize_title( $_GET['address'] );
        
        $request = new WP_Http;
        $response = $request->request( $url . $address );
        die( $response['body'] );
    }
    
    function filter_entries( $comments ) {
        // Do no filtering in `wp-admin`
        if( defined( 'WP_ADMIN' ) && WP_ADMIN )
            return $comments;
        
        // Do no filtering on ajax
        if( defined( 'NEW_LUG_MAP_AJAX' ) && NEW_LUG_MAP_AJAX )
            return $comments;
        
        for( $i = 0; $i <= count( $comments ); $i++ )
            if( get_post_type( $comments[$i]->comment_post_ID ) == 'lugmap' )
                unset( $comments[$i] );
        
        return $comments;
    }
    
    function template_render( $name, $vars = null, $echo = true ) {
        ob_start();
        extract( $vars );
        include dirname( __FILE__ ) . '/templates/' . $name . '.php';
        $data = ob_get_clean();
        
        if( $echo )
            echo $data;
        else
            return $data;
    }
}
?>