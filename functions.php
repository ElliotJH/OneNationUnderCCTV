<?php

function geocode_address($address, $min_accuracy = 5)
{
	global $config;
	
	if(is_array($address))
	{
		$address = implode(' ', array_filter($address));
	}
	
	$csv = file_get_contents('http://maps.google.com/maps/geo?q=' . urlencode($address) . '&key=' . $config['maps_api_key'] . '&sensor=false&output=csv&oe=utf8');
	
	if ($csv === false)
	{
		return false;
	}
	
	list($status, $accuracy, $lat, $lng) = split(',', $csv);
	
	if (strcmp($status, "200") == 0 && $accuracy >= $min_accuracy)
	{
		return array($lat, $lng);
	}
	else
	{
		return false;
	}
}

?>
