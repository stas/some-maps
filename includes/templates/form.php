<div id="map-form" class="form-wrap">
    <?php wp_nonce_field( 'newpoint', 'newpoint_nonce' ); ?>
    <input type="hidden" id="point" name="lm[point]" class="required" />
    
    <div id="map"></div>
    
    <div class="form-field">
        <label for="f_title" class="required"><?php _e( "Title", "some-maps" )?>*</label>
        <input class="inpt" type="text" id="f_title" name="lm[title]" />
    </div>
    
    <div class="form-field">
        <label for="f_subtitle"><?php _e( "Subtitle", "some-maps" )?></label>
        <input class="inpt" type="text" id="f_subtitle" name="lm[subtitle]" />
    </div>
    
    <div class="form-field">
        <label for="f_dsc"><?php _e("Description", "some-maps" )?></label>
        <textarea id="f_dsc" name="lm[dsc]" class="inpt" tabindex="4"></textarea>
    </div>
    
    <div class="form-field">
        <label for="f_email" class="required"><?php _e( "E-mail", "some-maps" )?>*</label>
        <input class="inpt" type="text" id="f_email" name="lm[email]" />
    </div>
    
    <div class="form-field">
        <label for="f_www"><?php _e( "Web Page", "some-maps" )?></label>
        <input class="inpt" type="text" id="f_www" name="lm[www]" value="http://" />
    </div>
    
    <div class="form-field">
        <label for="f_adr" class="adr required" ><?php _e( "Geographical Position", "some-maps" )?>*</label>
        <input id="adr" type="text" id="f_adr" name="lm[adr]" value="<?php _e( "Town, County, Country", "some-maps" )?>" style="width: 85%;" />
        <a href="#map" id="srch" class="button" ><?php _e( "Search", "some-maps" )?></a>
    </div>
    
    <p class="submit" style="float: none;">
        <input type="submit" id="sbmt" class="button-primary" value="<?php _e( "Create", "some-maps" )?>" />
        <?php _e( "Required fields are marked with <em>*</em>.", "some-maps" )?>
    </p>
</div>

<div class="clear"></div>