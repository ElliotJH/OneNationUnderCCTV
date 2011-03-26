<?php
class mysql_db
{
	var $sql;
	function sql_connect($sqlserver, $sqluser, $sqlpassword, $database)
	{
		$this->connect_id = mysql_connect($sqlserver, $sqluser, $sqlpassword);
		
		if($this->connect_id)
		{
			if (mysql_select_db($database))
			{
				return $this->connect_id;
			}
			else
			{
				return $this->sql_error();
			}
		}
		else
		{
			return $this->sql_error();
		}
	}
	
	function sql_error()
	{
		if(mysql_error() != '')
		{
			die('MySQL Error: ' . mysql_error() . 'SQL: ' . $this->sql);
		}
	}
	
	function sql_query($query)
	{
		$this->sql = $query;
		$this->query_result = mysql_query($query, $this->connect_id);
		
		if(!$this->query_result)
		{
			return $this->sql_error();
		}
		else
		{
			return $this->query_result;
		}
	}
	
	function sql_numrows($query_id = '')
	{
		if($query_id == NULL)
		{
			$return = mysql_num_rows($this->query_result);
		}
		else
		{
			$return = mysql_num_rows($query_id);
		}
		
		if(!$return)
		{
			$this->sql_error();
		}
		else
		{
			return $return;
		}
	}
	
	function sql_fetchrow($query_id = '')
	{
		if($query_id == NULL)
		{
			$return = mysql_fetch_array($this->query_result);
		}
		else
		{
			$return = mysql_fetch_array($query_id);
		}
		
		if(!$return)
		{
			$this->sql_error();
		}
		else
		{
			return $return;
		}
	}   
	
	function get_affected_rows($query_id = '')
	{
		if($query_id == NULL)
		{
			$return = mysql_affected_rows();
		}
		else
		{
			$return = mysql_affected_rows($query_id);
		}
		
		if(!$return)
		{
			$this->sql_error();
		}
		else
		{
			return $return;
		}
	}
	
	function insert_id()
	{
		$return = mysql_insert_id($this->connect_id);
		
		if(!$return)
		{
			$this->sql_error();
		}
		else
		{
			return $return;
		}
	}   
	
	function get_enum_values($table, $field)
	{
		$enum_array = array();
		$sql = 'SHOW COLUMNS FROM `' . $table . '` LIKE "' . $field . '"';
		$result = $this->query($sql);
		$row = $this->fetch_row($result);
		preg_match_all('/\'(.*?)\'/', $row[1], $enum_array);
		if(!empty($enum_array[1]))
		{
			// Shift array keys to match original enumerated index in MySQL (allows for use of index values instead of strings)
			foreach($enum_array[1] as $mkey => $mval)
			{
				$enum_fields[$mkey+1] = $mval;
			}
			return $enum_fields;
		}
		else
		{
			return array();
		}
	}
	
	function sql_close()
	{
		if($this->connect_id)
		{
			return mysql_close($this->connect_id);
		}
	}
	
	/**
	* Build IN or NOT IN sql comparison string, uses <> or = on single element
	* arrays to improve comparison speed
	*
	* @access public
	* @param	string	$field				name of the sql column that shall be compared
	* @param	array	$array				array of values that are allowed (IN) or not allowed (NOT IN)
	* @param	bool	$negate				true for NOT IN (), false for IN () (default)
	* @param	bool	$allow_empty_set	If true, allow $array to be empty, this function will return 1=1 or 1=0 then. Default to false.
	*/
	function sql_in_set($field, $array, $negate = false, $allow_empty_set = false)
	{
		if (!sizeof($array))
		{
			if (!$allow_empty_set)
			{
				// Print the backtrace to help identifying the location of the problematic code
				$this->sql_error('No values specified for SQL IN comparison');
			}
			else
			{
				// NOT IN () actually means everything so use a tautology
				if ($negate)
				{
					return '1=1';
				}
				// IN () actually means nothing so use a contradiction
				else
				{
					return '1=0';
				}
			}
		}

		if (!is_array($array))
		{
			$array = array($array);
		}

		if (sizeof($array) == 1)
		{
			@reset($array);
			$var = current($array);

			return $field . ($negate ? ' <> ' : ' = ') . $this->_sql_validate_value($var);
		}
		else
		{
			return $field . ($negate ? ' NOT IN ' : ' IN ') . '(' . implode(', ', array_map(array($this, '_sql_validate_value'), $array)) . ')';
		}
	}
	
	/**
	* Run more than one insert statement.
	*
	* @param string $table table name to run the statements on
	* @param array &$sql_ary multi-dimensional array holding the statement data.
	*
	* @return bool false if no statements were executed.
	* @access public
	*/
	function sql_multi_insert($table, &$sql_ary)
	{
		if (!sizeof($sql_ary))
		{
			return false;
		}
		
		$ary = array();
		foreach ($sql_ary as $id => $_sql_ary)
		{
			// If by accident the sql array is only one-dimensional we build a normal insert statement
			if (!is_array($_sql_ary))
			{
				return $this->sql_query('INSERT INTO ' . $table . ' ' . $this->sql_build_array('INSERT', $sql_ary));
			}

			$values = array();
			foreach ($_sql_ary as $key => $var)
			{
				$values[] = $this->_sql_validate_value($var);
			}
			$ary[] = '(' . implode(', ', $values) . ')';
		}

		return $this->sql_query('INSERT INTO ' . $table . ' ' . ' (' . implode(', ', array_keys($sql_ary[0])) . ') VALUES ' . implode(', ', $ary));
	}
	
	/**
	* Function for validating values
	* @access private
	*/
	function _sql_validate_value($var)
	{
		if (is_null($var))
		{
			return 'NULL';
		}
		else if (is_string($var))
		{
			return "'" . $this->sql_escape($var) . "'";
		}
		else
		{
			return (is_bool($var)) ? intval($var) : $var;
		}
	}
	
	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		if (!$this->connect_id)
		{
			return @mysql_real_escape_string($msg);
		}

		return @mysql_real_escape_string($msg, $this->connect_id);
	}
}
?>

