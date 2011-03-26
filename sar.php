<?php
include('common.php');

$template->set(array(
	'PAGE_TITLE'	=> 'Subject Access Request',
));

$sql = 'SELECT *
	FROM cameras c
	JOIN operators o
		ON o.operator_id = c.operator_id
	WHERE camera_id = ' . request_var('camera_id', 0);
$result = $db->sql_query($sql);

if (!($row = $db->sql_fetchrow($result)))
{
	error_msg('Camera not found');
}

$submit = (isset($_POST['submit'])) ? true : false;
$data = array(
	'camera_id'			=> request_var('camera_id', 0),
	'firstname'			=> request_var('firstname', ''),
	'lastname'			=> request_var('lastname', ''),
	'addr_ln1'			=> request_var('addr_ln1', ''),
	'addr_ln2'			=> request_var('addr_ln2', ''),
	'town_city'			=> request_var('town_city', ''),
	'county'			=> request_var('county', ''),
	'postcode'			=> request_var('postcode', ''),
	'phone_dial_code'	=> request_var('phone_dial_code', ''),
	'phone_number'		=> request_var('phone_number', ''),
	
	'location'		=> request_var('location', camera_addr($row)),
	'date_time'		=> request_var('date_time', ''),
	'clothing'		=> request_var('clothing', ''),
);

if ($submit)
{
	$sar = generate_sar(array_merge($data, $row));
	echo '<h3>Subject Access Request</h3>';
	echo nl2br($sar);
	exit;
}

?>
<h3>Data controller</h3>
<fieldset>
	<p><strong><?php echo $row['operator_name']; ?></strong></p>
	<dl>
		<dt>Address</dt>
		<dd><?php echo operator_addr($row, true); ?></dd>
	</dl>
	<dl>
		<dt>Email</dt>
		<dd><a href="<?php echo $row['operator_email']; ?>"><?php echo $row['operator_email']; ?></a></dd>
	</dl>
	<dl>
		<dt>Phone</dt>
		<dd><a href="tel:<?php echo $row['operator_phone']; ?>"><?php echo format_phone($row['operator_phone']); ?></a></dd>
	</dl>
</fieldset>

<h3>Subject access request tool</h3>
<p>Fill in your contact details below and details of the incident you which to make a request for, and we will generate a Subject Access Request for you and email it to the data controller.</p>
<form method="post" action="">
	<fieldset>
	
	<dl>
		<dt><label for="firstname">Firstname:*</label></dt>
		<dd><input type="text" tabindex="4" name="firstname" id="firstname" size="18" maxlength="50" value="" class="inputbox autowidth" title="Firstname" /></dd>
	</dl>
	<dl>
		<dt><label for="lastname">Lastname:*</label></dt>
		<dd><input type="text" tabindex="5" name="lastname" id="lastname" size="18" maxlength="50" value="" class="inputbox autowidth" title="Lastname" /></dd>
	</dl>
	
	<hr />
	
	<dl>
		<dt><label for="addr_ln1">Address line 1:*</label></dt>
		<dd><input type="text" tabindex="6" name="addr_ln1" id="addr_ln1" size="25" maxlength="100" value="" class="inputbox autowidth" title="Address line 1" /></dd>
	</dl>
	<dl>
		<dt><label for="addr_ln2">Address line 2:</label></dt>
		<dd><input type="text" tabindex="7" name="addr_ln2" id="addr_ln2" size="25" maxlength="100" value="" class="inputbox autowidth" title="Address line 2" /></dd>
	</dl>
	<dl>
		<dt><label for="town_city">Town/city:</label></dt>
		<dd><input type="text" tabindex="8" name="town_city" id="town_city" size="18" maxlength="50" value="" class="inputbox autowidth" title="Town/city" /></dd>
	</dl>
	<dl>
		<dt><label for="county">County:*</label></dt>
		<dd><input type="text" tabindex="9" name="county" id="county" size="18" maxlength="50" value="" class="inputbox autowidth" title="County" /></dd>
	</dl>
	<dl>
		<dt><label for="postcode">Postcode:*</label></dt>
		<dd><input type="text" tabindex="10" name="postcode" id="postcode" size="10" maxlength="20" value="" class="inputbox autowidth" title="Postcode" onkeyup="this.value = this.value.toUpperCase();" /></dd>
	</dl>
    <dl>
        <dt><label for="phone_dial_code">Phone:</dt>
        <dd><input type="text" tabindex="12" name="phone_dial_code" id="phone_dial_code" size="5" maxlength="5" value="" class="inputbox autowidth" onkeyup="autotab(this, document.getElementById('phone_number'));" />&nbsp;<input type="text" tabindex="13" name="phone_number" id="phone_number" size="6" maxlength="6" class="inputbox autowidth" onkeyup="autotab(this, document.getElementById('mobile'));" /></dd>
    </dl>
    
    <hr />
    
    <dl>
		<dt><label for="location">Location of incident</label></dt>
		<dd><input type="text" tabindex="6" name="location" id="location" size="25" maxlength="100" value="" class="inputbox autowidth" title="Location" /></dd>
	</dl>
    <dl>
		<dt><label for="date_time">Date/time of incident</label></dt>
		<dd><input type="text" tabindex="6" name="date_time" id="date_time" size="25" maxlength="100" value="" class="inputbox autowidth" title="Date/time" /></dd>
	</dl>
	<dl>
		<dt><label for="clothing">Description of clothing</label></dt>
		<dd><input type="text" tabindex="6" name="clothing" id="clothing" size="25" maxlength="100" value="" class="inputbox autowidth" title="Clothing" /></dd>
	</dl>
	
	<input type="submit" name="submit" value="Submit" class="button1" />
	</fieldset>
</form>
