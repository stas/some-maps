<?php
/**
 * SomeMaps class
 */
class SomeMaps {
    // Our post type
    public static $post_type = 'map';
    
    // Post type meta keys
    public static $meta_keys = array(
        'map_settings',
        'map_submits',
        'map_sidebar'
    );
    
    // Post type comments meta
    public static $comment_meta = array(
        'point_subtitle',
        'point_pos'
    );
    
    /**
     * init()
     * 
     * Sets the hooks and other initialization stuff
     */
    function init() {
        add_action( 'init', array( __CLASS__, 'post_type' ) );
        add_action( 'init', array( __CLASS__, 'enqueues' ) );
        add_action( 'init', array( __CLASS__, 'localization' ) );
        add_action( 'init', array( __CLASS__, 'ajax' ) );
        add_action( 'init', array( __CLASS__, 'reverse_geocode' ) );
        add_action( 'wp', array( __CLASS__, 'save_point' ) );
        add_action( 'save_post', array( __CLASS__, 'save_settings' ) );
        add_action( 'save_post', array( __CLASS__, 'save_point' ) );
        add_filter( 'the_comments', array( __CLASS__, 'filter_entries' ) );
        add_shortcode( 'map', array( __CLASS__, 'shortcode' ) );
    }
    
    /**
     * localization()
     * 
     * i18n
     */
    function localization() {
        load_plugin_textdomain( 'some-maps', false, basename( dirname( __FILE__ ) ) . '../languages' );
    }
    
    /**
     * post_type()
     * 
     * Register our post type
     */
    function post_type() {
        register_post_type( self::$post_type, array(
            'labels' => array(
                'name' => __('Maps', 'some-maps' ),
                'singular_name' => __('Map', 'some-maps' ),
                'add_new_item' => __('New Map', 'some-maps' ),
                'edit_item' => __('Edit Map', 'some-maps' ),
            ),
            'public' => true,
            'map_meta_cap' => true,
            'rewrite' => array( 'slug' => self::$post_type ),
            'supports' => array( 'title', 'comments' ),
            'register_meta_box_cb' => array( __CLASS__, 'meta_boxes' ),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_ui' => true
        ) );
    }
    
    /**
     * meta_boxes( $post )
     * 
     * Activate the meta boxes
     * @param Object $post, the post/page object
     */
    function meta_boxes( $post ) {
        remove_meta_box( 'commentstatusdiv', self::$post_type, 'normal' );
        // Change comments to list entries
        remove_meta_box( 'commentsdiv', self::$post_type, 'normal' );
        // Add shortcode box
        add_meta_box( 'map_shortcode', __( 'Shortcode', 'some-maps' ), array(__CLASS__, 'shortcode_box'), self::$post_type, 'side' );
        // Add default centering
        add_meta_box( 'map_settings', __( 'Settings', 'some-maps' ), array(__CLASS__, 'settings_box'), self::$post_type, 'side' );
        // Meta box to add a new entry
        if( 'publish' == $post->post_status ) {
            add_meta_box('commentsdiv', __('Entries', 'some-maps'), 'post_comment_meta_box', self::$post_type, 'normal' );
            add_meta_box( 'add_new_point', __( 'Add Points', 'some-maps' ), array(__CLASS__, 'new_point_box'), self::$post_type, 'normal' );
        }
    }
    
    /**
     * save_settings( $post_id )
     * 
     * Save sent settings for current map
     * @param Int $post_id, the ID of the map
     * @return Int $post_id, the ID of the map
     */
    function save_settings( $post_id ) {
        $width = null;
        $height = null;
        $lat = null;
        $lon = null;
        $zoom = null;
        $sidebar = null;
        
        if ( isset( $_POST['mapsettings_nonce'] ) && !wp_verify_nonce( $_POST['mapsettings_nonce'], 'mapsettings' ))
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
        
        if( isset( $_POST['sidebar'] ) && !empty( $_POST['sidebar'] ) )
            $sidebar = sanitize_text_field( $_POST['sidebar'] );
        
        update_post_meta( $post_id, self::$meta_keys[1], $submits );
        update_post_meta( $post_id, self::$meta_keys[2], $sidebar );
        
        if( $width && $height && $lat && $lon && $zoom ) {
            $key_value = implode( ',', array( $width, $height, $lat, $lon, $zoom ) );
            update_post_meta( $post_id, self::$meta_keys[0], $key_value );
        }
        
        return $post_id;
    }
    
    /**
     * get_map_settings( $post_id )
     * 
     * Fetch the settings for given map ID
     * @param Int $post_id, the ID of the map
     * @return Mixed $settings, the fetched settings array
     */
    function get_map_settings( $post_id = null ) {
        $settings = get_post_meta( $post_id, self::$meta_keys[0], true ) . ',' . $post_id;
        
        if( count( explode( ',', $settings ) ) == 6 )
            $settings = array_combine(
                array( 'mapWidth', 'mapHeight', 'mapLat', 'mapLon', 'mapZoom', 'mapID' ), explode( ',', $settings )
            );
        else
            $settings = array();
        
        $settings['mapSubmits'] = get_post_meta( $post_id, self::$meta_keys[1], true );
        $settings['mapSidebar'] = get_post_meta( $post_id, self::$meta_keys[2], true );
        $settings['failMessage'] = __( 'Please fill all the required fields!', 'some-maps' );
        $settings['loadMsg'] = __( 'Loading...', 'some-maps' );
        
        return $settings;
    }
    
    /**
     * settings_box( $post )
     * 
     * Render the map settings meta box
     * @param Object $post, the post/page object
     */
    function settings_box( $post ) {
        self::template_render( 'settings_box', self::get_map_settings( $post->ID ) );
    }
    
    /**
     * shortcode_box( $post )
     * 
     * Render the map shortcode meta box
     * @param Object $post, the post/page object
     */
    function shortcode_box( $post ) {
        if( 'publish' != $post->post_status ) 
            echo '<p>' . __( 'Publish this map to get it\'s shortcode', 'some-maps' ) . '</p>';
        else {
            echo '<input type="text" class="long-text" value="[' . self::$post_type . ' ' . $post->ID . ']" />';
        }
    }
    
    /**
     * shortcode( $args )
     * 
     * Handler for our shortcode
     * @param Mixed $args, the shortcode args
     * @return String, the rendered template
     */
    function shortcode( $args ) {
        $map_id = reset($args);
        $map = get_post( $map_id );
        $settings = self::get_map_settings( $map_id );
        
        if( $map && $map->post_type == self::$post_type ) {
            // This will load map settings
            wp_localize_script( 'some-maps', 'mapSettings', $settings );
            
            return self::template_render( 'single', $settings, false );
        }
    }
    
    /**
     * save_point()
     *
     * Methods hooks into `save_post` and saves the new points if those are posted
     */
    function save_point() {
        global $post;
        
        $verified_submit = 1;
        
        if ( isset( $_POST['newpoint_nonce'] ) && !wp_verify_nonce( $_POST['newpoint_nonce'], 'newpoint' ))
            if ( isset( $_POST['newpoint_nonce'] ) && !wp_verify_nonce( $_POST['newpoint_nonce'], 'newpoint_anon' ))
                return;
            else
                $verified_submit = 0;
        
        if( !isset( $_POST['lm'] ) || empty( $_POST['lm'] ) )
            return;
        
        $map_id = $post->ID;
        $title = sanitize_text_field( $_POST['lm']['title'] );
        $subtitle = sanitize_text_field( $_POST['lm']['subtitle'] );
        $email = sanitize_email( $_POST['lm']['email'] );
        $www = sanitize_url( $_POST['lm']['www'] );
        $desc = esc_html( $_POST['lm']['dsc'] );
        $point = sanitize_text_field($_POST['lm']['point']);
        if( isset( $_POST['lm']['map_id'] ) )
            $map_id = (int) $_POST['lm']['map_id'];
        
        if( $title && $email && $point ){
            $point_data = array(
                "comment_post_ID" => $map_id,
                "comment_author" => $title,
                "comment_author_email" => $email,
                "comment_author_url" => $www,
                "comment_content" => $desc
            );
            
            if( !$verified_submit )
                $point_data['comment_approved'] = wp_allow_comment( $point_data );
            else
                $point_data['comment_approved'] = 1;
            
            $point_id = wp_insert_comment( $point_data );
            
            if( $point_id ) {
                add_comment_meta( $point_id, self::$comment_meta[0], $subtitle, true  );
                add_comment_meta( $point_id, self::$comment_meta[1], $point, true  );
            }
        }
    }
    
    /**
     * new_point_box( $post )
     * 
     * Render the new pointer form meta box
     * @param Object $post, the post/page object
     */
    function new_point_box( $post ) {
        // This will load map settings
        wp_localize_script( 'some-maps', 'mapSettings',  self::get_map_settings( $post->ID ) );
        
        self::template_render( 'form' );
    }
    
    /**
     * enqueues()
     * 
     * Loads the CSS and JS assides
     */
    function enqueues() {
        wp_register_script( 'googlemaps-v3', 'http://maps.google.com/maps/api/js?sensor=false', null, SOME_MAPS );
        wp_register_script( 'bmap', plugins_url( '/js/jQuery.bMap.1.3.min.js', SOME_MAPS_PATH ), array( 'jquery', 'googlemaps-v3' ), '1.3' );
        wp_enqueue_script( 'some-maps', plugins_url( '/js/some-maps.js', SOME_MAPS_PATH ), array( 'bmap' ), SOME_MAPS, true );
        if( !defined( 'WP_ADMIN' ) )
            wp_enqueue_style( 'some-maps', plugins_url( '/css/some-maps.css', SOME_MAPS_PATH ), null, SOME_MAPS );
    }
    
    /**
     * get_map_data( $post_id )
     * 
     * Fetch the data/pointers for given map ID
     * @param Int $post_id, the ID of the map
     * @return Mixed $points, the fetched data array, has a filter `get_map_data`
     */
    function get_map_data( $post_id ) {
        if( !intval( $post_id ) && $post_id == 0 )
            return;
        
        $entries = get_approved_comments( $post_id );
        if( empty( $entries ) )
            return;
        
        $points = array();
        foreach( $entries as $e ) {
            $pos = get_comment_meta( $e->comment_ID, self::$comment_meta[1], true );
            $pos = explode( ',', $pos );
            $subtitle = get_comment_meta( $e->comment_ID, self::$comment_meta[0], true );
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
    
    /**
     * ajax()
     *
     * Ajax request handler for fetching map data
     */
    function ajax() {
        if( isset( $_GET['ajax_map'] ) && isset( $_GET['action'] ) ) {
            define( 'MAP_AJAX', true );
            $response['name'] = "Map_" . intval( $_GET['action'] );
            $response['type'] = "marker";
            $response['data'] = self::get_map_data( intval( $_GET['action'] ) );
            die( json_encode( $response ) );
        }
    }
    
    /**
     * reverse_geocode()
     *
     * Ajax reverse geocoder for points localization
     */
    function reverse_geocode() {
        if( !isset( $_SERVER['HTTP_REFERER'] ) )
            return;
        
        $referer = parse_url( $_SERVER['HTTP_REFERER'] );
        
        if( gethostbyname( $referer['host'] ) != $_SERVER['SERVER_ADDR'] )
            return;
        
        if( !isset( $_GET['ajax_reverse_geocode'] ) )
            return;
        
        $url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=";
        $address = sanitize_title( $_GET['address'] );
        
        $request = new WP_Http;
        $response = $request->request( $url . $address );
        die( $response['body'] );
    }
    
    /**
     * filter_entries( $comments )
     * 
     * Filters the comments that belong to a map from other public queries
     * @param Mixed $comments, the fetched comments
     * @return Mixed $comments, the filtered comments
     */
    function filter_entries( $comments ) {
        // Do no filtering in `wp-admin`
        if( defined( 'WP_ADMIN' ) && WP_ADMIN )
            return $comments;
        
        // Do no filtering on ajax
        if( defined( 'MAP_AJAX' ) && MAP_AJAX )
            return $comments;
        
        for( $i = 0; $i <= count( $comments ); $i++ )
            if( get_post_type( $comments[$i]->comment_post_ID ) == self::$post_type )
                unset( $comments[$i] );
        
        return $comments;
    }
    
    /**
     * template_render( $name, $vars = null, $echo = true )
     *
     * Helper to load and render templates easily
     * @param String $name, the name of the template
     * @param Mixed $vars, some variables you want to pass to the template
     * @param Boolean $echo, to echo the results or return as data
     * @return String $data, the resulted data if $echo is `false`
     */
    function template_render( $name, $vars = null, $echo = true ) {
        ob_start();
        if( !empty( $vars ) )
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