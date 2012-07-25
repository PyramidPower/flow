	
	var map; //map object;
	
	var poly; //polygon object, which is used to create new polygon or modify polygon;
	
	var base; //marker's location before drag event happened;
	
	var markersArray = []; //array of polygon vertex's lat&lon represented as markers in google map;
	
	var suburbsArray = []; //array of suburb's lat&lon represented as markers in google map;
	
	var suburbsIdArray = []; //array of poetcode_id for each suburb;
	
	var infoWindow; //pop up window, show the suburb, postcode information;
	
	var existPolyArray = []; //store the existing polygons;
	
	var polyToSubPoly; //if swtich from create polygon to assigned area, set to true, vise versa;
	
	var latLngControl; //LatLng control obj, transform LatLng coordinates into pixel coordinates;
	
	/**
	 * Initial map, create objects;
	 * 
	 * @param double lat: latitude of center location;
	 * @param double long: longtitude of center location;
	 * @param int zoom: zoom level of the map;
	 * @param string renderTo: name of the tag contains the map;
	 **/
	function initialize(lat, lng, zoom, renderTo) 
	{
		var latLng = new google.maps.LatLng(lat, lng);
	  
		var mapOptions = {zoom: zoom, center: latLng, mapTypeId: google.maps.MapTypeId.ROADMAP};
	  
		map =  new google.maps.Map(document.getElementById(renderTo), mapOptions);
	
		infowindow = new google.maps.InfoWindow();
	  
		latLngControl = new LatLngControl(map);
	}
	
	/**
	 * Load the actual polygon data;
	 * 
	 * @param string resonse: string representation of lat&lng coordinate;
	 * @param int owner_id: the user_id/agency_id of the polygon's owner;
	 * @param boolean is_agency: if the polygon is for agency then true, vise versa, if it's other agency's area, set to null;
	 **/
	function loadPolygonData(response, owner_id, is_agency)
	{	
		var polygon = new google.maps.Polygon({strokeColor: '#000000', strokeOpacity: 1.0, strokeWeight: 1});
			
		polygon.setMap(map);

		var path = polygon.getPath();

		for(var i=0; i<response.length; i++)
		{
			var latAndLon = response[i].split(" ",2);

			var latLng = new google.maps.LatLng(latAndLon[0], latAndLon[1]);
		   	   
			path.push(latLng);
		}		
		
		if(is_agency == true)
			existPolyArray["agency"] = polygon;
		else if(is_agency == false)
			existPolyArray["member_" + owner_id] = polygon;
		else
			existPolyArray["other_" + owner_id] = polygon;
		
		return polygon;
	}
	
	/**
	 * Initial poly object before using it to draw a new polygon;
	 * 
	 * @param int owner_id: user_id/agency_id of the polygon's owner;
	 * @param boolean: is_agency: the owner of the polygon is agency or not;
	 **/
	function initialBeforeDrawn(owner_id, is_agency)
	{
		var options = {strokeColor: '#000000', strokeOpacity: 1.0, strokeWeight: 1};

		if(!poly)
		{
		  	poly = new google.maps.Polygon(options);
			  
		  	poly.setMap(map);	
		}	 

		if(!existPolyArray['agency'])
		{
			google.maps.event.addListener(map, 'click', function(event){createPolygon(poly, event.latLng);}); 
		}
		else
		{
			google.maps.event.clearInstanceListeners(map);
		}
	}
	
	/**
	 * Draw a new polygon;
	 * 
	 * @param Polygon polygon: the polygon object used to create area;
	 * @param LatLng location: the marker's lat&lng coordinate;
	 **/
	function createPolygon(polygon, location)
	{
		var path = polygon.getPath();

		path.push(location);

//		if(isValidPosition(path) == false)
//			path.pop();
//		else
			addMarker(location, path);
	}

	/**
	 * Highlight the select polygon, and add marker events;
	 * 
	 * @param Polygon polygon: the polygon object which will be edit by user;
	 **/
	function editPolygon(polygon)
	{
		var path = polygon.getPath();

		for(var i=0; i<path.getLength(); i++)
		{
			addMarker(path.getAt(i), path);
		}

		if(polygon.getPath() == existPolyArray['agency'].getPath())
		{
			google.maps.event.addListener(map, 'click', function(event){createPolygon(poly, event.latLng);}); 
		}
		else
		{
			google.maps.event.clearInstanceListeners(map);
		}
	}

	/**
	 * Add marker for polygon vertex, and marker event;
	 * 
	 * @param LatLng location: the marker's lat&lng coordinate;
	 * @param MVCArray path: the vertex array of the polygon;
	 **/
	function addMarker(location, path)
	{
		var marker = new google.maps.Marker({position: location, title: location.toString(), draggable: true, map: map});

		google.maps.event.addListener(marker, 'click', function(){markerClick(location, path);});

		google.maps.event.addListener(marker, 'dragstart', function(event){dragstart(event.latLng, path);});

		google.maps.event.addListener(marker, 'dragend', function(event){dragend(event.latLng, path, marker);});

		markersArray.push(marker);
	}

	/**
	 * Remove the marker when it is clicked;
	 * 
	 * @param LatLng location: the marker's lat&lng coordinate;
	 * @param MVCArray path: the vertex array of the polygon;
	 **/
	function markerClick(location, path)
	{
		var marker = path.pop();

		for(var i=0; i<markersArray.length; i++)
		{
			if(markersArray[i].getPosition().equals(marker) == true)
			{
				markersArray[i].setMap(null);
			}
		}
	}

	/**
	 * Drag event start;
	 **/
	function dragstart(location, path)
	{
		for (i=0; i<path.getLength(); i++)
		{
			if (location.equals(path.getAt(i)))
			{
				base = i;

				break;
			}
		}
	}

	/**
	 * Drag event end;
	 * 
	 * @param LatLng location: the new lat&lng coordinate of the marker;
	 * @param MVCArray path: the vertex array of the polygon;
	 * @param Marker marker: the marker object;
	 **/
	function dragend(location, path, marker)
	{	
		if(existPolyArray['agency'])	
		{
			/**
			 * A valid position means:
			 * Each marker of polygon has to reside within agency's polygon,
			 * and outside other sales rep's polygon; In addition, polygon of
			 * sales rep will not overlap each other; 
			 **/
			var validPosition = true; 

			if(path == existPolyArray['agency'].getPath())
				marker.setPosition(path.getAt(base));
			else
			{
				var baseValue = path.getAt(base);

				path.setAt(base, location);
				
//				if(isValidPosition(path) == false)
//					validPosition = false;

				path.setAt(base, baseValue);

				if(existPolyArray['agency'].containsLatLng(location) == true && validPosition == true)
					path.setAt(base, location);
				else
					marker.setPosition(path.getAt(base));
			}
		}
		else
		{
			if(sizeofArray(existPolyArray) > 0)
			{
				var baseValue = path.getAt(base);

				path.setAt(base, location);
				
//				if(isValidPosition(path) == false)
//				{
//					path.setAt(base, baseValue);
//					
//					marker.setPosition(path.getAt(base));
//				}
			}
			else
				path.setAt(base, location);
		}
	}
	
	/**
	 * Check if polygons are overlapping each other;
	 * 
	 * @param MVCArray path: the vertex array of the polygon needs to be checked;
	 **/
	function isValidPosition(path)
	{
		if(path.getLength() > 1)
		{
			for(var i in existPolyArray)
			{
				var existPath = existPolyArray[i].getPath();
				
				//if overlap with other polygon
				if(path != existPath && existPath.getLength() != 0)
				{
					if(arePolygonsOverlapped(path, existPath) == true)
						return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Convert address details to latitude and longtitude in Google Map;
	 * 
	 * @param string address: address details for convertion;
	 * @param function callback(location, error): callback function to handle webservice callback; if location exist, then error set to null, vise versa;
	 **/
	function addressToLatLng(address, callback)
	{
		  var geocoder = new google.maps.Geocoder();
		  
		  geocoder.geocode({'address': address}, function(results, status) 
				  {
				      if (status == google.maps.GeocoderStatus.OK) 
				    	  callback(results[0].geometry.location, null);
				      else 
				    	  callback(null, status);
				  });
	}

	/**
	 * Show the suburb details within a polygon;
	 * 
	 * @param JSON responseText: JSON array contains lat&lng, postcode_id;
	 * @param Polygon poly: the area on which the right click event happen;
	 **/
	function getPolygonDetails(responseText, poly)
	{	
//		//empty suburb array before fill it in with new suburb markers
//		for (i in suburbsArray) 
//		{
//			suburbsArray[i].setMap(null);
//		}
//		suburbsArray.length = 0;
//
//		//empty postcode_id array
//		suburbsIdArray = [];

		//according to australian_postcodes table to query suburb details;
		for(i=0; i<responseText.length; i++)
		{
			var coordinate = new google.maps.LatLng(responseText[i]['Lat'], responseText[i]['Long']);

			if(poly.containsLatLng(coordinate) == true)
			{
				var marker = new google.maps.Marker({position: coordinate, title: coordinate.toString(), map: map});
				
				suburbsArray.push(marker);

				suburbsIdArray.push(responseText[i]['id']);
				
				showCoordinates(coordinate, marker, responseText[i]);

				deleteSuburb(marker);
			}
		}		
	}

	/**
	 * Pop up an dialog box to show the details of a marker;
	 **/
	function showCoordinates(coordinate, marker, details)
	{
		google.maps.event.addListener(marker, 'mouseover', function(){

			var content = "<p><strong>" + details['Suburb'] + ", " + details['State'] + " " + details['Pcode'] + "</strong></p>";
					
			content += "<p><strong>Australia</strong></p>";
          
            infowindow.setContent(content);
		            
            infowindow.open(map, marker);
		});

		google.maps.event.addListener(marker, 'mouseout', function(){

            infowindow.close();
		});
	}

	/**
	 * Remove unnecessary marker within a polygon;
	 **/
	function deleteSuburb(marker)
	{
		google.maps.event.addListener(marker, 'click', function(){

			marker.setMap(null);

			for(i in suburbsArray)
			{
				if (suburbsArray[i].getPosition().equals(marker.getPosition()))
				{
					suburbsArray.splice(i,1);
					
					suburbsIdArray.splice(i,1);
				}
			}
		    infowindow.close();
		});
	}

	/**
	 * Clear marker of poly object; 
	 **/
	function clearAgencyPolygonMarker()
	{
		if (markersArray) 
		{
			for (i in markersArray) 
			{
				markersArray[i].setMap(null);
			}
		    markersArray.length = 0;
		}
	}

	/**
	 * Clear poly object; 
	 **/
	function clearAgencyPolygon()
	{
		if(poly)
		{
		    var path = poly.getPath();

		    while (path.getLength() != 0)
		    {
			    path.pop();
		    }
		}
	}

	/**
	 * Clear suburb array; 
	 **/
	function clearAllSuburbs()
	{
		if(suburbsArray)
		{
			for (i in suburbsArray) 
			{
				suburbsArray[i].setMap(null);
			}
			suburbsArray.length = 0;
		}
	}

	/**
	 * Clear postcode_id array; 
	 **/
	function clearAllSuburbId()
	{
		if(suburbsIdArray)
		{
			suburbsIdArray = [];
		}
	}

	/**
	 * Clear existing polygon array; 
	 **/
	function clearExistPoly()
	{
		for(var i in existPolyArray)
		{
			var path = existPolyArray[i].getPath();
			
		    while (path.getLength() != 0)
		    {
			    path.pop();
		    }
		    existPolyArray[i] = null;
		}
		existPolyArray = [];
	}

	/**
	 * If switch from polygon to sub-polygon, clear MVCArray if it exists;
	 * if switch from sub-polygon to polygon, just reset the reference;
	 * if switch from sub-polygon to sub-polygon, nothing happens; 
	 **/
	function cleanUpForSwitch()
	{
		if(poly)
		{
			if(polyToSubPoly == true)
			{
				var path = poly.getPath();

				while(path.getLength() != 0)
				{
					path.pop();
				}	
			}
			poly = null;	
		}
	}

	/**
	 * Clear polygon and markers so user can start over again; 
	 **/
	function reset()
	{
		clearAgencyPolygonMarker();

		clearAgencyPolygon();

		clearAllSuburbs();

		clearAllSuburbId();
	}
	
	/**
	 * Perform clean up actions to remove markers, polygons and postcode_ids;
	 **/
	function resetAll() 
	{
		clearAgencyPolygonMarker();

		clearAgencyPolygon();

		poly = null;
		
		clearAllSuburbs();

		clearAllSuburbId();

		clearExistPoly();
	}
	
	/**
	 * Return the size of an associative array; 
	 **/
	function sizeofArray(array)
	{
		var counter = 0;
		
		for(var i in array)
		{
			counter++;
		}
		return counter;
	}
	
	/**
	 * The below section of functions detect if two polygons overlap each other;
	 * 
	 * This link provide the principle of the algorithm and a simple implementation: http://mathforum.org/library/drmath/view/63171.html;
	 * 
	 * Quoted from the above link:
	 * 
	 * The polygons overlap if a side of one polygon intersects a side of the other polygon.
	 * 
	 * Let's say that line L1 contains vertices V11 and V12 from one polygon, and line L2 contains vertices V21 and V22 from the other polygon.
	 * 
	 * We can write vector equations for L1 and L2 like this.
	 * 
	 * L1 = V11 + a(V12 - V11)
	 * 
	 * L2 = V21 + b(V22 - V21)
	 * 
	 * Where L1 and L2 intersect we must have
	 * 
	 * V11 + a(V12 - V11) = V21 + b(V22 - V21)
	 * 
	 * a(V12 - V11) = (V21  - V11) + b(V22 - V21)
	 * 
	 * Now define vector VP to be perpendicular to (V22 - V21) and take the dot product of both sides with VP.
	 * 
	 * a[(V12 - V11).VP] = (V21 - V11).VP
	 * 
	 * Note that the b term has dropped out. If [(V12 - V11).VP] = 0 then L1 and L2 are parallel; otherwise we can solve for a. In a similar
	 * way we can also make a drop out and solve for b.
	 * 
	 * Now we need to work through this procedure for every combination of sides from the two polygons and make a list of all of the (a,b) pairs.
	 * 
	 * Here are the options.
	 * 
	 * 1. If a and b are both in [0,1] for some (a,b), then L1 intersects L2 at a point on both polygon edges.
	 * 
	 * 2. If some a is in [0,1] and no b is in [0,1], then the polygon with the a values contains the polygon with the b values.
	 * 
	 * 3. If no a is in [0,1] and some b is in [0,1], then the polygon with the b values contains the polygon with the a values.
	 * 
	 * 4. If no a is in [0,1] and no b is in [0,1], then the polygons have no overlap.
	 **/
	
	/**
	 * Test if two polygons overlap;
	 * 
	 *  @param MVCArray poly1: vertex array of the first polygon;
	 *  @param MVCArray poly2: vertex array of the second polygon;
	 **/
	function arePolygonsOverlapped(poly1, poly2)
	{         
		for(var i = 0; i < poly1.getLength(); i++)        
		{      
			for(var k = 0; k < poly2.getLength(); k++)      
			{
				var poly1_v1 = latLngControl.updatePosition(poly1.getAt(i));

				var poly2_v1 = latLngControl.updatePosition(poly2.getAt(k));
				
				//if reach the last element of the array, then return the first element to make it a closed polygon;
				if(!poly1.getAt(i+1))
					var poly1_v2 = latLngControl.updatePosition(poly1.getAt(0));
				else
					var poly1_v2 = latLngControl.updatePosition(poly1.getAt(i+1));

				if(!poly2.getAt(k+1))
					var poly2_v2 = latLngControl.updatePosition(poly2.getAt(0));
				else
					var poly2_v2 = latLngControl.updatePosition(poly2.getAt(k+1));

				if(lineIntersection(poly1_v1, poly1_v2, poly2_v1, poly2_v2) == true)
					return true;
			}    
		}
	    return false; 
	}

	/**
	 * According to the above principle, we can have the following equation:
	 * 
	 * a = (V21 - V11).VP / (V12 - V11).VP;
	 * 
	 * b = (V11 - V21).VP / (V22 - V21).VP;
	 * 
	 * "." means dot product of two vectors; In the following function, each parameter is an array of two elements,
	 * 
	 * vertex.x is the x coordinate of the vertex and vertex.y is the y coordinate of the vertex;
	 **/
	
	/**
	 * Test if two line segmant intersect;
	 * 
	 *  @param float vertex11: first vertex of first line segmant;
	 *  @param float vertex12: second vertex of first line segmant;
	 *  @param float vertex21: first vertex of second line segmant;
	 *  @param float vertex22: second vertex of second line segmant;
	 **/
	function lineIntersection(vertex11, vertex12, vertex21, vertex22)
	{
		var Ax = vertex12.x - vertex11.x;
		var Ay = vertex12.y - vertex11.y;

		var Bx = vertex22.x - vertex21.x;
		var By = vertex22.y - vertex21.y;

		var Sx = vertex21.x - vertex11.x;
		var Sy = vertex21.y - vertex11.y;

		var a = (Bx*Sy - By*Sx) / (Bx*Ay - By*Ax);
		var b = (Sx*Ay - Sy*Ax) / (Ax*By - Ay*Bx);

		if(a >= 0 && a <= 1)
		{
			if(b >= 0 && b <= 1)
				return true;
			else
				return false;
		}
		return false;
	}