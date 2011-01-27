<form action="<?php do_action( 'lugmap_form_action' ); ?>" method="post">
    <?php wp_nonce_field( 'newlugmap_settings_box', 'newlugmap_nonce' ); ?>
    
    <p>
        <label for="f_submits" class="required"><?php _e( "Anonymous Submits", "newlugmap" )?></label>
        <input type="checkbox" id="f_submits" name="submits" <?php checked( $mapSubmits ); ?> />
    </p>
    
    <p class="form-field">
        <label for="f_width" class="required"><?php _e( "Width", "newlugmap" )?></label>
        <input type="text" id="f_width" name="width" class="long-text" value="<?php echo $mapWidth; ?>" />
    </p>
    
    <p class="form-field">
        <label for="f_height" class="required"><?php _e( "Height", "newlugmap" )?></label>
        <input type="text" id="f_height" name="height" class="long-text" value="<?php echo $mapHeight; ?>" />
    </p>

    <p class="form-field">
        <label for="f_lat" class="required"><?php _e( "Latitude", "newlugmap" )?></label>
        <input type="text" id="f_lat" name="lat" class="long-text" value="<?php echo $mapLat; ?>" />
    </p>
    
    <p class="form-field">
        <label for="f_lon" class="required"><?php _e( "Longitude", "newlugmap" )?></label>
        <input type="text" id="f_lon" name="lon" class="long-text" value="<?php echo $mapLon; ?>" />
    </p>
    
    <p class="form-field">
        <label for="f_zoom" class="required"><?php _e( "Zoom Level", "newlugmap" )?></label>
        <select class="long-text" id="f_zoom" name="zoom">
        <?php for( $i = 1; $i <= 10; $i++ ): ?>
            <option value="<?php echo $i; ?>" <?php selected( $mapZoom, $i ); ?>><?php echo $i; ?></option>
        <?php endfor; ?>
        </select>
    </p>
    
    <p style="text-align: right;">
        <?php echo sprintf(__('You can use <a href="%1$s">this tool</a> to find out the above information.','newlugmap'),'http://www.getlatlon.com/'); ?>
    </p>
</form>