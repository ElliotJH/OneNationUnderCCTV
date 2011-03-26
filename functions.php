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

function geocode_cameras()
{
	global $db;
	
	$sql = 'SELECT * FROM cameras';
	$result = $db->sql_query($sql);

	$postcodes = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if (!$row['camera_postcode'])
		{
			continue;
		}
	
		if(isset($postcodes[$row['camera_postcode']]))
		{
			$geocode = $postcodes[$row['camera_postcode']];
		}
		else
		{
			$geocode = geocode_address($row['camera_postcode']);
			$postcodes[$row['camera_postcode']] = $geocode;
		}
	
		if ($geocode)
		{
			$sql = 'UPDATE cameras
				SET camera_lat = ' . $geocode[0] . ', camera_lng = ' . $geocode[1] . '
				WHERE camera_id = ' . $row['camera_id'];
			$db->sql_query($sql);
		}
		echo $row['camera_id'] . ' - ' . (($geocode) ? 'Success' : 'Fail') . '<br />';
	}
}

function camera_addr($data, $br = false)
{
	$separator = ($br) ? '<br />' : ', ';
	$addr = array($data['camera_addr_ln1'], $data['camera_addr_ln2'], $data['camera_town_city'], $data['camera_county'], $data['camera_postcode']);
	$addr = array_filter($addr);
	return implode($separator, $addr);
}

function operator_addr($data, $br = false)
{
	$separator = ($br) ? '<br />' : ', ';
	$addr = array($data['operator_name'], $data['operator_addr_ln1'], $data['operator_addr_ln2'], $data['operator_town_city'], $data['operator_county'], $data['operator_postcode']);
	$addr = array_filter($addr);
	return implode($separator, $addr);
}

function subject_addr($data, $br = false)
{
	$separator = ($br) ? '<br />' : ', ';
	$addr = array($data['addr_ln1'], $data['addr_ln2'], $data['town_city'], $data['county'], $data['postcode']);
	$addr = array_filter($addr);
	return implode($separator, $addr);
}

function format_phone($phone)
{
	return substr($phone, 0, 5) . ' ' . substr($phone, 5);
}

function generate_sar($data)
{
	return subject_addr($data) . "\n\nDear Sir/Madam,
	
In exercise of the right granted to me under the terms of the Data Protection Act 1998, I request that you provide me with a copy of the Personal Data about the Data Subject which you process for the purposes I have indicated below. I confirm that this is all of the Personal Data to which I am requesting access. I also confirm that I am the Data Subject for this request.

Location: {$data['location']}
Date/time: {$data['date_time']}

At the time of the incident I was wearing {$data['clothing']}.

Kind regards,
{$data['firstname']} {$data['lastname']}";
}

function gen_heatmap($lat1, $lng1, $lat2, $lng2)
{
	system("python crimeDateMidlands.py $lat1 $lng1 $lat2 $lng2 foobar", $output);
}

?>
