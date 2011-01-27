<div id="lugmap" class="form-wrap">
<form action="<?php do_action( 'lugmap_form_action' ); ?>" method="post">
    <?php wp_nonce_field( 'newlugmap_entry', 'newlugmap_nonce' ); ?>
    <input type="hidden" id="point" name="lm[point]" class="required" />

    <div class="form-field">
        <div id="map"></div>
    </div>
    
    <div class="form-field">
        <label for="f_title" class="required"><?php _e( "Title", "newlugmap" )?>*</label>
        <input class="inpt" type="text" id="f_title" name="lm[title]" />
    </div>
    
    <div class="form-field">
        <label for="f_subtitle"><?php _e( "Subtitle", "newlugmap" )?></label>
        <input class="inpt" type="text" id="f_subtitle" name="lm[subtitle]" />
    </div>
    
    <div class="form-field">
        <label for="f_dsc"><?php _e("Description", "newlugmap" )?></label>
        <textarea id="f_dsc" name="lm[dsc]" class="inpt" tabindex="4"></textarea>
    </div>
    
    <div class="form-field">
        <label for="f_email" class="required"><?php _e( "E-mail", "newlugmap" )?>*</label>
        <input class="inpt" type="text" id="f_email" name="lm[email]" />
    </div>
    
    <div class="form-field">
        <label for="f_www"><?php _e( "Web Page", "newlugmap" )?></label>
        <input class="inpt" type="text" id="f_www" name="lm[www]" value="http://" />
    </div>
    
    <div class="form-field">
        <label for="f_adr" class="adr required" ><?php _e( "Geographical Position", "newlugmap" )?>*</label>
        <input id="adr" type="text" id="f_adr" name="lm[adr]" value="<?php _e( "Town, County, Country", "newlugmap" )?>" style="width: 85%;" />
        <a href="#" id="srch" class="button" ><?php _e( "Search", "newlugmap" )?></a>
    </div>
    
    <p class="submit" style="float: none;">
        <input type="submit" id="sbmt" class="button-primary" value="<?php _e( "Create", "newlugmap" )?>" />
        <?php _e( "Required fields are marked with <em>*</em>.", "newlugmap" )?>
    </p>
</form>
</div>

<div class="clear"></div>