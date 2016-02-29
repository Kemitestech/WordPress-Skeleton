<?php

//This is an array with all the information from the db for the specific loaction in the loop
global $location_details;

$full_address = "{$location_details['_epl_location_address']} {$location_details['_epl_location_city']} {$location_details['_epl_location_state']} {$location_details['_epl_location_zip']}";

$_c = stripslashes_deep( $location_details['post_content'] );
$_t = stripslashes_deep( $location_details['post_title'] );

$lat = epl_get_element( '_epl_location_lat', $location_details );
$long = epl_get_element( '_epl_location_long', $location_details );

if ( $lat == '' || $long == '' ) {
    $r = epl_get_gmap_geocode( $full_address );
    extract( $r );
}
?>


<script type="text/javascript">
    var geocoder;
    var map;

    function codeAddress() {

        var myLatlng = new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $long; ?>);

        var myOptions = { zoom: 15, center: myLatlng, mapTypeId: google.maps.MapTypeId.ROADMAP }

        var map = new google.maps.Map( document.getElementById("event_location_map"), myOptions );

        contentString = '<?php echo $full_address; ?>';

        var infowindow = new google.maps.InfoWindow({ content: contentString });
        var marker = new google.maps.Marker({ position: myLatlng, map: map, title: "<?php echo $_t; ?>" });
        google.maps.event.addListener(marker, 'click', function() { infowindow.open(map,marker); });



    };
</script>
<script>
    jQuery(document).ready(function(){
        codeAddress();
    });



</script>


<?php echo $full_address; ?>
<div id="event_location_map" style="width:100%; height:400px;"></div>
<div>
    <a target="_blank" href="http://maps.google.com/maps?q=<?php echo urlencode( $full_address ); ?>"><?php epl_e( 'Map and Driving Direction' ); ?> </a> 

</div>
