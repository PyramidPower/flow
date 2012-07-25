
	function LatLngControl(map) 
	{
		// Bind this OverlayView to the map so we can access MapCanvasProjection
        // to convert LatLng to Point coordinates.
        this.setMap(map);
	}
	
	// Extend OverlayView so we can access MapCanvasProjection.
	LatLngControl.prototype = new google.maps.OverlayView();
	
	LatLngControl.prototype.draw = function() {};
	
	LatLngControl.prototype.updatePosition = function(latLng) 
	{
        var projection = this.getProjection();
        
        var point = projection.fromLatLngToContainerPixel(latLng);
        
        return point;
	};
