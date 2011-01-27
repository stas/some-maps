jQuery(document).ready(function(){
    if ( typeof mapSettings === "undefined" ) return;
    // Load settings
    jQuery("#map").width( parseInt( mapSettings.mapWidth ) );
    jQuery("#map").height( parseInt( mapSettings.mapHeight ) );
    
    // Render the map
    jQuery("#map").bMap({
        mapZoom: parseInt( mapSettings.mapZoom ),
        mapCenter: [ parseFloat( mapSettings.mapLat ), parseFloat( mapSettings.mapLon ) ]
    });
    
    // Load the markers
    jQuery("#map").data('bMap').AJAXMarkers({	
        serviceURL: '/?ajax_map',
        action: mapSettings.mapID,			
        vars: []
    });
    
    // Enable reverse geocoding
    jQuery("#srch").click( function() {
        jQuery.getJSON(
            "/?ajax_reverse_geocode",
            { address : jQuery("#adr").val() },
            function( data ) {
                if( data.status == "OK" && data.results[0] ) {
                    jQuery("#map").data('bMap').insertMarkers({
                        name : "Result",
                        data : [{
                            lat: data.results[0].geometry.location.lat,
                            lng: data.results[0].geometry.location.lng
                        }]
                    });
                    jQuery("input#point").val( String( data.results[0].geometry.location.lat ) + ',' + String( data.results[0].geometry.location.lng ));
                }
            }
        );
    });
});