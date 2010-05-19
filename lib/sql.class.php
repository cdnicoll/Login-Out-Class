<?php
/*
@author:    cNicoll
@name:	    SQL Database class
@date:      02/11/09

RELEASE NOTES:
==========================================================================================
12-22-09_12|35 version 1.3
	- added a new method to allow for a custom query
			customQuery($q)
	- fixed a bug when returning a result set, multiple arrays may have 
	been sent. Only one should be returned now.

11-25-09_11|47 version 1.2
	- cleaned up some allignment of the code
	
07-21-09_14|53 version 1.1
    - updated error messages

HEADER:
==========================================================================================
public:
    Database($db_host, $db_user, $db_pass, $db_name)
    connect()
    disconnect()
    customQuery($q)
    select($table, $rows='*', $where = null, $order = null, $limit = null)
    insert($table,$values,$rows = null)
    delete($table, $where = null)
    update($table,$rows,$where)
    getResult()
private:
    $db_host
    $db_user
    $db_pass
    $result
    $con
    $debug
    tableExists($table)

*/



class Database {
    // Set up instance variables
    private $db_host = '';
    private $db_user = '';
    private $db_pass = '';
    private $db_name = '';
    private $result = array();
    private $con = false;     // Checks to see if the connection is active
    private $debug = false;	 // Set to true for debug messages.
	
	/**
    * constructor
    * @param host name
    * @param db user
    * @param db password
    * @param db name to connect to
    */
    public function Database($db_host, $db_user, $db_pass, $db_name) {
		$this->db_host = $db_host;
		$this->db_user = $db_user;
		$this->db_pass = $db_pass;
		$this->db_name = $db_name;
    }
	
	/**
	* Connect to the Database. First checks if the user is connected - if they are
	* select the database. If they are not return false
	*
	* @return boolean - return true if connected and database is found.
	*/
    public function connect() {
		// Check to see if the user is already connected. If its not...
		if (!($this->con)) {
		    $myconn = @mysql_connect($this->db_host, $this->db_user, $this->db_pass);	 // Connect to the database
		}
		// If the connection was already made...
		if ($myconn) {
		    $seldb = @mysql_select_db($this->db_name, $myconn);	 // Select a database to use
		    // If a database was connected...
		    if ($seldb) {
			$this->con = true;	// con has a connection
			return true;
		    }
		    else {
			return false;	 // Could not select a database
		    }
		}
		else {
		    return false;	 // Could not make a connection
		}
    }
	
	/**
	* Disconnect from the Database. Checks the connection variable to see if its true.
	* If it is, it means there is a connection to the database.
	*
	* @return boolean - return true if connection true if there is no connection to the database
	*/
    public function disconnect() {
		if ($this->con) {
		    if (@mysql_close()) {
			$this->con = false;
			return true;	 // Connection closed
		    }
		    else {
			return false;	 // Connection still open
		    }
		}
    }
    
    /*
	* @param custom query to be entered
	* @return bool if query ran or not. 
	*/
    public function customQuery($q)
    {
    	$query = @mysql_query($q);
    	
    	if ($query) {
				$this->numResults = mysql_num_rows($query);
				/*
				* The columns and data that are requested from the database. 
				* It then assigns it to the result variable. However, to make it easier 
				* for the end user, instead of auto-incrementing numeric keys, the names of the columns are used. 
				* In case more than one result is provided each row that is returned is stored with a 
				* two dimensional array, with the first key being numerical and auto-incrementing, 
				* and the second key being the name of the column. If only one result is returned, then a 
				* one dimensional array is created with the keys being the columns. If no results are turned then 
				* the result variable is set to null.
				*/
				for($i=0; $i < $this->numResults; $i++) {
				    $r = mysql_fetch_array($query);	 // put the query into an array
				    $key = array_keys($r);	 // get the keys for the array
				    //
				    for($x = 0; $x < count($key); $x++) {
						// check if the key has an int value. If it does...
						if (!(is_int($key[$x]))) {
						    // check if the query has more then one row, if so...
						    if (mysql_num_rows($query) > 1) {
								$this->result[$i][$key[$x]] = $r[$key[$x]];	 // 
						   	}
						    // if the result has less then one...
						    else if(mysql_num_rows($query) < 1) {
								$this->result = null;	 // Nothing in table
						    }
						    // Only one result found
						    else {
								$this->result[$key[$x]] = $r[$key[$x]];
						    }
						}
				    }
				}
				if ($this->debug == true) {
		    		echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $q . '</code>';	 //enable for debugging.
				}
				
				return true;	 // If query ran
	    	}
	    	else {
				if ($this->debug == true) {
		    		echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $q . '</code>';	 //enable for debugging.
				}
				return false;	 // If query failed
	    	}
    }
	
	/**
	* Checks to see if a particular tables exists in the database.
	*
	* @param table - table name
	*/
    private function tableExists($table) {
		$q = 'SHOW TABLES FROM '.$this->db_name.' LIKE "'.$table.'"';
		$tablesInDb = @mysql_query($q);	 // search the database for the table name
		// if a table was found...
		if($tablesInDb) {
		    // ensure there are not more then one row in the query
		    if (mysql_num_rows($tablesInDb)==1) {
			return true;	 // Table was found
		    }
		    else {
			if ($this->debug == true) {
			    echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $q . '</code>';	 //enable for debugging.
			}
			return false;	 // No table found
		    }
		} 
	}

	/**
	* Querey the Database. Create a variable called $results that will hold the 
	* query result. Checks tje database to see if the required table already exists. 
	* 
	* @param $table - table name in use
	* @param $rows - default *(all)
	* @param $where - default null
	* @param $order - default null
	* @return true if table exists
	*/
    public function select($table, $rows='*', $where = null, $order = null, $limit = null) {
		$q = 'SELECT ' .$rows.' FROM '.$table;	 // Create start of query
		// if where does not equal null
		if ($where != null) {
		    $q .= ' WHERE '.$where;	 // Add conditions if they have been defined
		}
		// if order does not equal null
		if ($order != null) {
		    $q .= ' ORDER BY '.$order;	 // Add orderby if its been defined
		}
		// if limit does not equal null
		if ($limit != null) {
		    $q .= ' LIMIT '.$limit;	 // Add orderby if its been defined
		}
		// check if the table exists, if it does...
		if ($this->tableExists($table)) {
		    $query = @mysql_query($q);	 // create a variable to hold the query for whenever its called
		    // If there is a query...
		    if ($query) {
				$this->numResults = mysql_num_rows($query);
				/*
				* The columns and data that are requested from the database. 
				* It then assigns it to the result variable. However, to make it easier 
				* for the end user, instead of auto-incrementing numeric keys, the names of the columns are used. 
				* In case more than one result is provided each row that is returned is stored with a 
				* two dimensional array, with the first key being numerical and auto-incrementing, 
				* and the second key being the name of the column. If only one result is returned, then a 
				* one dimensional array is created with the keys being the columns. If no results are turned then 
				* the result variable is set to null.
				*/
				for($i=0; $i < $this->numResults; $i++) {
				    $r = mysql_fetch_array($query);	 // put the query into an array
				    $key = array_keys($r);	 // get the keys for the array
				    //
				    for($x = 0; $x < count($key); $x++) {
						// check if the key has an int value. If it does...
						if (!(is_int($key[$x]))) {
						    // check if the query has more then one row, if so...
						    if (mysql_num_rows($query) > 1) {
								$this->result[$i][$key[$x]] = $r[$key[$x]];	 // 
						   	}
						    // if the result has less then one...
						    else if(mysql_num_rows($query) < 1) {
								$this->result = null;	 // Nothing in table
						    }
						    // Only one result found
						    else {
								$this->result[$key[$x]] = $r[$key[$x]];
						    }
						}
				    }
				}
				if ($this->debug == true) {
		    		echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $q . '</code>';	 //enable for debugging.
				}
				
				return true;	 // If query ran
	    	}
	    	else {
				if ($this->debug == true) {
		    		echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $q . '</code>';	 //enable for debugging.
				}
				return false;	 // If query failed
	    	}
		}
		else {
	    	return false;	 // No table found
		}
    }
    
	/**
	* Insert content into the Database
	*
	* @param $table - get the table name
	* @param $values
	* @param $rows - Default to null value
	* @return boolean - true if the insert took place
	*/
    public function insert($table,$values,$rows = null)
    {
        if($this->tableExists($table))
        {
            $insert = 'INSERT INTO '.$table;
            if($rows != null)
            {
                $insert .= ' ('.$rows.')';
            }

            for($i = 0; $i < count($values); $i++)
            {
                if(is_string($values[$i]))
                    $values[$i] = '"'.$values[$i].'"';
            }
            $values = implode(',',$values);
            $insert .= ' VALUES ('.$values.')';

            $ins = @mysql_query($insert);

            if($ins)
            {
                return true;
            }
            else
            {
                if ($this->debug == true) {
		    		echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $ins . '</code>';	 //enable for debugging.
				}
                return false;
            }
        }
    }
	
	/**
	* Delete from the Database. Delete either a table or row from the database.
	*
	* @param $table - get the table name
	* @param $where - from where. Default null
	* @return boolean - 
	*/
    public function delete($table, $where = null) {
		// check if table exisits
		if ($this->tableExists($table)) {
		    // Delete table if there is no where clause
		    if ($where == null) {
				$delete = 'DELETE '.$table;	 // Variable to delete table
		    }
		    else {
				$delete = 'DELETE FROM '.$table.' WHERE '.$where;	// Variable to delete with where clause
		    }
		    $del = @mysql_query($delete);	 // Run the query
		    // check if delete query was a success
		    if ($del) {
				return true;	 // Delete successful
		    }
		    else {
				if ($this->debug == true) {
			    	echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $delete . '</code>';	 //enable for debugging.
				}
				return false;	 // Deleted Failed
		    }
		}
		else {
		    return false;	 // No table
		}
    }
    
	/*
	 * Updates the database with the values sent
	 * Required: table (the name of the table to be updated
	 *           rows (the rows/values in a key/value array
	 *           where (the row/condition in an array (row,condition) )
	 */
    public function update($table,$rows,$where)
    {
        if($this->tableExists($table))
        {
            // Parse the where values
            // even values (including 0) contain the where rows
            // odd values contain the clauses for the row
            for($i = 0; $i < count($where); $i++)
            {
                if($i%2 != 0)
                {
                    if(is_string($where[$i]))
                    {
                        if(($i+1) != null)
                            //$where[$i] = '"'.$where[$i].'" AND ';
                            $where[$i] = '"'.$where[$i].'"';
                        else
                            $where[$i] = '"'.$where[$i].'"';
                    }
                }
            }
            $where = implode('=',$where);
            
            
            $update = 'UPDATE '.$table.' SET ';
            $keys = array_keys($rows); 
            for($i = 0; $i < count($rows); $i++)
           {
                if(is_string($rows[$keys[$i]]))
                {
                    $update .= $keys[$i].'="'.$rows[$keys[$i]].'"';
                }
                else
                {
                    $update .= $keys[$i].'='.$rows[$keys[$i]];
                }
                
                // Parse to add commas
                if($i != count($rows)-1)
                {
                    $update .= ','; 
                }
            }
            $update .= ' WHERE '.$where;
            $query = @mysql_query($update);
            if($query)
            {
            	if ($this->debug == true) {
		    		echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $update . '</code>';	 //enable for debugging.
				}
                return true; 
            }
            else
            {
            	if ($this->debug == true) {
		    		echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $update . '</code>';	 //enable for debugging.
				}
                return false; 
            }
        }
        else
        {
        	if ($this->debug == true) {
		    	echo '<code class="debug">' . mysql_error() . '<br /><br />Query: ' . $update . '</code>';	 //enable for debugging.
			}
            return false; 
        }
    }
    
	/**
    * Returns the result set
    * @return restult set
    */
    public function getResult() 
    {
		$res = $this->result;
		unset($this->result);	
		return $res;
    }
}
?>