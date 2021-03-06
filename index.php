<?php
include('common.php');

$template->set(array(
	//'PAGE_TITLE'	=> 'Map',
));

//gen_heatmap(52.4, -3, 52.5, -2);

?>
<p style="font-size: large;">OneNationUnderCCTV is a project to determine the cost-effectiveness of government CCTV systems and their ability to reduce crime-rates. We plot data on the location of CCTV in an area, obtained via Freedom of Information Requests, with frequency of crime from public datasets. We also make it easy for individuals to identify CCTV operators and make Subject Access Requests to data controllers.</p>
<div id="map_canvas" style="width: 100%; height: 400px"></div>
<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
<script type="text/javascript">
// <![CDATA[
	function initialize()
	{
		var map = new google.maps.Map(document.getElementById('map_canvas'), {
			zoom		: 6,
			center		: new google.maps.LatLng(54.5, -3.436),
			mapTypeId	: google.maps.MapTypeId.ROADMAP
		});
		
		// Close any open infowindows when you click the map
		google.maps.event.addListener(map, 'click', close_infowindow);
		
		
		var cur_infowindow = false;
		var marker_images = Array();
		var coords = Array();
		
		function add_marker(point, title, html)
		{
			var hash = point.lat() + point.lng();
			//hash = hash.replace(".","").replace(",", "").replace("-","");
			
			// check to see if we've seen this hash before
			if(coords[hash] == null)
			{
				coords[hash] = 1;
			}
			else
			{
				var lat = parseFloat(point.lat()) + (Math.random() -.5) / 1500;
				var lng = parseFloat(point.lng()) + (Math.random() -.5) / 1500;
				point = new google.maps.LatLng(lat.toFixed(6), lng.toFixed(6));
			}
			
			var infowindow = new google.maps.InfoWindow({content: html});
			
			var marker = new google.maps.Marker({
				position	: point, 
				map			: map, 
				title		: title,
			});
			
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map, marker);
				
				infowindow.open(map, marker);
				
				close_infowindow();
				
				// Remember that this infowindow is open
				cur_infowindow = infowindow;
			});
		}
		
		function close_infowindow()
		{
			// Close the open infowindow
			if(cur_infowindow !== false)
			{
				cur_infowindow.close();
			}
		}

		<?php
		$sql = 'SELECT *
			FROM cameras c
			JOIN operators o
				ON o.operator_id = c.operator_id
			WHERE camera_postcode <> ""';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$installed = ($row['camera_installed']) ? '<dl>Installed:</dl><dd>' . $row['camera_installed'] . '</dd>': '';
			$address = camera_addr($row, true);
			echo 'point = new google.maps.LatLng(' . $row['camera_lat'] . ', ' . $row['camera_lng'] . ');' . "\n";
			echo "add_marker(point, '{$row['camera_postcode']}','<h2>Camera at {$row['camera_postcode']}</h2><p>Operated by <a href=\"sar.php?camera_id={$row['camera_id']}\">{$row['operator_name']}</a>.</p>{$installed}<dl>Camera address</dl><dd>{$address}</dd><p><strong><a href=\"sar.php?camera_id={$row['camera_id']}\">Subject Access Requests</a></strong></p>');" . "\n";
		}
		?>
		
		var imageBounds = new google.maps.LatLngBounds(
			new google.maps.LatLng(52.2, -2.5),
			new google.maps.LatLng(53.4, -1)
		);
		var crime = new google.maps.GroundOverlay(
			"foobar.png",
		imageBounds);
		crime.setMap(map);
	}

	onload_functions.push('initialize()');
// ]]>
</script>
