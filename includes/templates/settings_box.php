<?php wp_nonce_field( 'mapsettings', 'mapsettings_nonce' ); ?>

<p>
    <label for="f_submits"><?php _e( "Anonymous Submits", "some-maps" )?></label>
    <input type="checkbox" id="f_submits" name="submits" <?php checked( $mapSubmits ); ?> />
</p>

<p class="form-field">
    <label for="f_width"><?php _e( "Width", "some-maps" )?></label>
    <input type="text" id="f_width" name="width" class="long-text" value="<?php echo $mapWidth; ?>" />
</p>

<p class="form-field">
    <label for="f_height"><?php _e( "Height", "some-maps" )?></label>
    <input type="text" id="f_height" name="height" class="long-text" value="<?php echo $mapHeight; ?>" />
</p>

<p class="form-field">
    <label for="f_lat"><?php _e( "Latitude", "some-maps" )?></label>
    <input type="text" id="f_lat" name="lat" class="long-text" value="<?php echo $mapLat; ?>" />
</p>

<p class="form-field">
    <label for="f_lon"><?php _e( "Longitude", "some-maps" )?></label>
    <input type="text" id="f_lon" name="lon" class="long-text" value="<?php echo $mapLon; ?>" />
</p>

<p class="form-field">
    <label for="f_zoom"><?php _e( "Zoom Level", "some-maps" )?></label>
    <select class="long-text" id="f_zoom" name="zoom">
    <?php for( $i = 1; $i <= 10; $i++ ): ?>
        <option value="<?php echo $i; ?>" <?php selected( $mapZoom, $i ); ?>><?php echo $i; ?></option>
    <?php endfor; ?>
    </select>
</p>

<p style="text-align: right;">
    <?php echo sprintf(__('You can use <a href="%1$s">this tool</a> to find out the above information.','some-maps'),'http://www.getlatlon.com/'); ?>
</p>

<p class="form-field">
    <label for="f_sidebar"><?php _e( "Sidebar ID", "some-maps" )?></label>
    <input type="text" id="f_sidebar" name="sidebar" class="long-text" value="<?php echo $mapSidebar; ?>" />
</p>