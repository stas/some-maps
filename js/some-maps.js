jQuery(document).ready(function(){
    if ( typeof mapSettings === "undefined" || !mapSettings.mapLat || !mapSettings.mapLon ) {
        jQuery("#map").css('height', 300);
        jQuery("#map").bMap();
        return;
    }
    
    // Load settings
    jQuery("#map").width( parseInt( mapSettings.mapWidth ) );
    jQuery("#map").height( parseInt( mapSettings.mapHeight ) );
    
    // Add class to sidebar
    if( mapSettings.mapSidebar )
        jQuery( '#' + mapSettings.mapSidebar ).addClass('mapSidebar');
    
    // Render the map
    jQuery("#map").bMap({
        loadMsg: mapSettings.loadMsg,
        mapZoom: parseInt( mapSettings.mapZoom ),
        mapCenter: [ parseFloat( mapSettings.mapLat ), parseFloat( mapSettings.mapLon ) ],
        mapSidebar: mapSettings.mapSidebar
    });
    
    // Load the markers
    jQuery("#map").data('bMap').AJAXMarkers({	
        serviceURL: '/?ajax_map',
        action: mapSettings.mapID,			
        vars: []
    });
    
    // Some basic form validation
    jQuery("#sbmt").click(function(event) {
        var err = [];
        jQuery("#map-form label.required").css('color','inherit');
        jQuery("#map-form label.required").each(function() {
                if(jQuery('input[name='+this.htmlFor+']').val() == '')
                    err[err.length] = this.htmlFor;
        });
        if(jQuery('#point.required').val() == '')
            err[err.length] = this.name;
        if (err.length > 0) {
            alert(mapSettings.failMessage);
            jQuery('#map-form label.required').css('color','red');
        }
        return (err.length <= 0);
    });
    
    // Enable reverse geocoding
    jQuery("#srch").click( function() {
        jQuery.getJSON(
            "/?ajax_reverse_geocode",
            { address : jQuery("#adr").val() },
            function( data ) {
                if( data.status == "OK" && data.results[0] ) {
                    var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(
                            data.results[0].geometry.location.lat,
                            data.results[0].geometry.location.lng
                        ), 
                        map:jQuery("#map").data('bMap').map,
                        title: jQuery("#adr").val(),
                        clickable: false,
                        icon: 'http://maps.google.com/mapfiles/marker_green.png'
                    });
                    jQuery("input#point").val( String( data.results[0].geometry.location.lat ) + ',' + String( data.results[0].geometry.location.lng ));
                }
            }
        );
    });
});