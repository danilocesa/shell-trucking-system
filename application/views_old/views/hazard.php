
<!DOCTYPE html>
<html>
<head>
  <title>Snap to Road - Google Maps API v3</title>
  <style type="text/css">
    html, body {margin: 0; width:100%; height: 100%; }
    #map_canvas { position:absolute; top:20px;bottom:0;left:0;right:0;}
  </style>
  <script type="text/javascript" src="http://maps.google.com/maps/api/js?libraries=geometry&sensor=false"></script>
  <script type="text/javascript" src="http://www.geocodezip.com/scripts/v3_epoly.js"></script>
  <script type="text/javascript">
    var map, path = new google.maps.MVCArray(), service = new google.maps.DirectionsService(), shiftPressed = false, poly;

    google.maps.event.addDomListener(document, "keydown", function(e) { shiftPressed = e.shiftKey; });
    google.maps.event.addDomListener(document, "keyup", function(e) { shiftPressed = e.shiftKey; });
		
    function Init() {
      var myOptions = {
        zoom: 17,
        center: new google.maps.LatLng(14.608797,120.978978),
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControlOptions: {
          mapTypeIds: [google.maps.MapTypeId.ROADMAP, google.maps.MapTypeId.HYBRID, google.maps.MapTypeId.SATELLITE]
        },
        disableDoubleClickZoom: true,
        scrollwheel: true,
        draggableCursor: "crosshair"
      }
       map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);


      var flightPlanCoordinates = [
        new google.maps.LatLng(37.772323, -122.214897),
        new google.maps.LatLng(21.291982, -157.821856),
        new google.maps.LatLng(-18.142599, 178.431),
        new google.maps.LatLng(-27.46758, 153.027892)
      ];
      var poly = new google.maps.Polyline({
        path: flightPlanCoordinates,
        strokeColor: '#FF0000',
        strokeOpacity: 1.0,
        strokeWeight: 2
      });

      // poly.setMap(map);
      // google.maps.event.addListener(map, "click", function(evt) {

      //     console.log(poly.getPath().getArray().toString());
      //     path.push(evt.latLng);
		    //   if(path.getLength() === 1) {
			   //     poly.setPath(path);
		    //   }
     
      // });

      var i=1;
      var length = google.maps.geometry.spherical.computeLength(poly.getPath());
      var remainingDist = length;
      while (remainingDist > 0)
      {
         createMarker(map, poly.GetPointAtDistance(1000*i),i+" km");
         remainingDist -= 1000;
         i++;
      }
        // put markers at the ends
        createMarker(map,poly.getPath().getAt(0),length/1000+" km");
        createMarker(map,poly.getPath().getAt(poly.getPath().getLength()-1),(length/1000).toFixed(2)+" km");
        poly.setMap(map);
      }

      function createMarker(map, latlng, title){
          var marker = new google.maps.Marker({
                position:latlng,
                map:map,
                title: title
                });
      }
	  
	  
	  
	  //note: kailangan paganahin si DirectionsRenderer para draggable ung path
	  //directions_changed 
    // }
  </script>
</head>
<body onload="Init()">
  <div id="map_canvas"></div>
</body>
</html>