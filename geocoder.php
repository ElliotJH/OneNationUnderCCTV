<?php
define('CLI', true);
include('common.php');

// Read user prompt
function r($prompt = '')
{
	// Trim to get rid of newlines
	if ($prompt)
	{
		w($prompt, false);
	}
	
	return trim(fgets(STDIN));	
}

// Output to screen/log
function w($output = '', $nl = true)
{
	global $log_fh;
	
	$output .= ( ($nl) ? "\n" : ' ');
	
	if ($log_fh)
	{
		fwrite($log_fh, $output);
	}
	
	return fwrite(STDOUT, $output);
}

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
	w($row['camera_id'] . ' - ' . (($geocode) ? 'Success' : 'Fail'));
}

?>
