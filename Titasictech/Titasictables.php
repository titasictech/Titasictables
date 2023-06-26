<?php namespace Titasictech; 
  /**
  * Titasictables
  *
  * This is a wrapper class/library inspired and based on Ignited Datatables
  * found at https://github.com/IgnitedDatatables/Ignited-Datatables for CodeIgniter 3.x
  *
  * @package    CodeIgniter 4.x
  * @subpackage libraries
  * @category   library
  * @version    1.1 <beta> First Release
  * @author     Ruhaendi <titasictech@gmail.com / https://titasictech.com>
  * @create     04 Jan 2021
  * @update	    28 Jan 2021
  
  =======================ORIGINAL=======================
  * Ignited Datatables
  *
  * This is a wrapper class/library based on the native Datatables server-side implementation by Allan Jardine
  * found at http://datatables.net/examples/data_sources/server_side.html for CodeIgniter
  *
  * @package    CodeIgniter
  * @subpackage libraries
  * @category   library
  * @version    2.0 <beta>
  * @author     Vincent Bambico <metal.conspiracy@gmail.com>
  *             Yusuf Ozdemir <yusuf@ozdemir.be>
  * @link       http://ellislab.com/forums/viewthread/160896/
  =======================ORIGINAL=======================
  **/
  
use \Config\Database;
use CodeIgniter\Config\Services;

class Titasictables
{
	/**
	* Global container variables for chained argument results
	*
	*/
	private $db;
	private $builder;
	private $table;
	private $distinct;
	private $group_by = [];
	private $select = [];
	private $joins = [];
	private $columns = [];
	private $columnstr; 
	private $where = [];
	private $or_where = [];
	private $where_in = [];
	private $like = [];
	private $or_like = [];
	private $filter = [];
	private $add_columns = [];
	private $edit_columns = [];
	private $unset_columns = [];
	private $request;
	private $cek;
	private $is_csrf = false;
	public $security;
	/**
	* Copies an instance of CI
	*/
	public function __construct()
	{
		$this->db = Database::connect();
		$this->request = Services::request();
		$this->security = Services::security();
	}

	/**
	* If you establish multiple databases in config/database.php this will allow you to
	* set the database (other than $active_group) - more info: http://ellislab.com/forums/viewthread/145901/#712942
	*/
	public function setDatabase($db_name)
	{
		$this->db->setDatabase($db_name);
	}

	# jika true berarti csrf token diaktifkan di Config/Filters.php
	public function setCSRF($bool)
	{
		$this->is_csrf = $bool;
	}

	/**
	* Generates the SELECT portion of the query
	*
	* @param string $columns
	* @param bool $backtick_protect
	* @return mixed
	*/
	public function select(String $columns, $backtick_protect = TRUE)
	{
		foreach($this->explode(',', $columns) as $val)
		{
			$column = trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$2', $val));
			$column = preg_replace('/.*\.(.*)/i', '$1', $column); // get name after `.`
			$this->columns[] =  $column;
			$this->select[$column] =  trim(preg_replace('/(.*)\s+as\s+(\w*)/i', '$1', $val));
		}
		$this->columnstr = $columns;
		return $this;
	}

	/**
	* Generates the DISTINCT portion of the query
	*
	* @param string $column
	* @return mixed
	*/
	public function distinct($column)
	{
		$this->distinct = $column;
		$this->builder->distinct($column);
		return $this;
	}

	/**
	* Generates a custom GROUP BY portion of the query
	*
	* @param string $val
	* @return mixed
	*/
	public function groupBy($val)
	{
		$this->group_by[] = $val;
		$this->builder->groupBy($val);
		return $this;
	}

	/**
	* Generates the FROM portion of the query
	*
	* @param string $table
	* @return mixed
	*/
	public function from($table)
	{
		$this->table = $table;
		$this->builder = $this->db->table($table); 
		return $this;
	}

	/**
	* Generates the JOIN portion of the query
	*
	* @param string $table
	* @param string $fk
	* @param string $type
	* @return mixed
	*/
	public function join($table, $fk, $type = NULL)
	{
		$this->joins[] = array($table, $fk, $type);
		$this->builder->join($table, $fk, $type);
		return $this;
	}

	/**
	* Generates the WHERE portion of the query
	*
	* @param mixed $key_condition
	* @param string $val
	* @param bool $backtick_protect
	* @return mixed
	*/
	public function where($key_condition, $val = NULL, $backtick_protect = TRUE)
	{
		$this->where[] = array($key_condition, $val, $backtick_protect);
		$this->builder->where($key_condition, $val, $backtick_protect);
		return $this;
	}

	/**
	* Generates the WHERE portion of the query
	*
	* @param mixed $key_condition
	* @param string $val
	* @param bool $backtick_protect
	* @return mixed
	*/
	public function orWhere($key_condition, $val = NULL, $backtick_protect = TRUE)
	{
		$this->or_where[] = array($key_condition, $val, $backtick_protect);
		$this->builder->orWhere($key_condition, $val, $backtick_protect);
		return $this;
	}
	
	/**
	* Generates the WHERE IN portion of the query
	*
	* @param mixed $key_condition
	* @param string $val
	* @param bool $backtick_protect
	* @return mixed
	*/
	public function whereIn($key_condition, $val = NULL)
	{
		$this->where_in[] = array($key_condition, $val);
		$this->builder->whereIn($key_condition, $val);
		return $this;
	}

	/**
	* Generates the WHERE portion of the query
	*
	* @param mixed $key_condition
	* @param string $val
	* @param bool $backtick_protect
	* @return mixed
	*/
	public function filter($key_condition, $val = NULL, $backtick_protect = TRUE)
	{
		$this->filter[] = array($key_condition, $val, $backtick_protect);
		return $this;
	}

	/**
	* Generates a %LIKE% portion of the query
	*
	* @param mixed $key_condition
	* @param string $val
	* @param bool $backtick_protect
	* @return mixed
	*/
	public function like($key_condition, $val = NULL, $side = 'both')
	{
		$this->like[] = array($key_condition, $val, $side);
		$this->builder->like($key_condition, $val, $side);
		return $this;
	}

	/**
	* Generates the OR %LIKE% portion of the query
	*
	* @param mixed $key_condition
	* @param string $val
	* @param bool $backtick_protect
	* @return mixed
	*/
	public function orLike($key_condition, $val = NULL, $side = 'both')
	{
		$this->or_like[] = array($key_condition, $val, $side);
		$this->builder->orLike($key_condition, $val, $side);
		return $this;
	}

	/**
	* Sets additional column variables for adding custom columns
	*
	* @param string $column
	* @param string $content
	* @param string $match_replacement
	* @return mixed
	*/
	public function addColumn($column, $content, $match_replacement = NULL)
	{
		$this->add_columns[$column] = array('content' => $content, 'replacement' => $this->explode(',', $match_replacement));
		return $this;
	}

	/**
	* Sets additional column variables for editing columns
	*
	* @param string $column
	* @param string $content
	* @param string $match_replacement
	* @return mixed
	*/
	public function editColumn($column, $content, $match_replacement)
	{
		$this->edit_columns[$column][] = array('content' => $content, 'replacement' => $this->explode(',', $match_replacement));
		return $this;
	}

	/**
	* Unset column
	*
	* @param string $column
	* @return mixed
	*/
	public function unsetColumn($column)
	{
		$column = explode(',',$column);
		$this->unset_columns=array_merge($this->unset_columns,$column);
		return $this;
	}

	/**
	* Builds all the necessary query segments and performs the main query based on results set from chained statements
	*
	* @param string $output
	* @param string $charset
	* @return string
	*/
	public function generate($output = 'json', $charset = 'UTF-8')
	{
		if (strtolower($output) == 'json') $this->getPaging();

		$this->getOrdering();
		$this->getFiltering();
		return $this->produceOutput(strtolower($output), strtolower($charset));
	}

	/**
	* Generates the LIMIT portion of the query
	*
	* @return mixed
	*/
	private function getPaging()
	{
		$iStart = $this->request->getPost('start');
		$iLength = $this->request->getPost('length');

		if ($iLength != '' && $iLength != '-1')
			$this->builder->limit($iLength, ($iStart) ? $iStart : 0);
	}

	/**
	* Generates the ORDER BY portion of the query
	*
	* @return mixed
	*/
	private function getOrdering()
	{
		$Data = $this->request->getPost('columns');

		if ($this->request->getPost('order')) {
			foreach ($this->request->getPost('order') as $key) {
				if ($this->checkCType()) {
					$this->builder->orderBy($Data[$key['column']]['data'], $key['dir']);
				} else {
					$this->builder->orderBy($this->columns[$key['column']] , $key['dir']);
				}
			}
		}
	}

	/**
	* Generates a %LIKE% portion of the query
	*
	* @return mixed
	*/
	private function getFiltering()
	{
		$mColArray = $this->request->getPost('columns') ?? []; #sama seperti ( ? : )
		$sWhere = '';
		$search = $this->request->getPost('search');
		$sSearch = $this->db->escapeLikeString(trim($search['value'] ?? ''));//."%' ESCAPE '!'";
		$columns = array_values(array_diff($this->columns, $this->unset_columns));

		if ($sSearch != '') {
			for ($i = 0; $i < count($mColArray); $i++) {
				if ($mColArray[$i]['searchable'] == 'true' && !array_key_exists($mColArray[$i]['data'], $this->add_columns)) {
					if ($this->checkCType()) {
						$sWhere .= $this->select[$mColArray[$i]['data']] . " LIKE '%" . $sSearch . "%' OR ";
					} else {
						$sWhere .= $this->select[$this->columns[$i]] . " LIKE '%" . $sSearch . "%' OR ";
					}
				}
			}
		}

		$sWhere = substr_replace($sWhere, '', -3);

		if($sWhere != '')
			$this->builder->where('(' . $sWhere . ')');

		// TODO : sRangeSeparator
		foreach($this->filter as $val)
			$this->builder->where($val[0], $val[1], $val[2]);
	}

	/**
	* Compiles the select statement based on the other functions called and runs the query
	*
	* @return mixed
	*/
	private function getDisplayResult()
	{
		return $this->builder->select($this->columnstr)->get();
	}

	/**
	* Builds an encoded string data. Returns JSON by default, and an array of aaData if output is set to raw.
	*
	* @param string $output
	* @param string $charset
	* @return mixed
	*/
	private function produceOutput($output, $charset)
	{
		$aaData = [];
		$rResult = $this->getDisplayResult();
		
		if ($output == 'json')
		{
			$iTotal = $this->getTotalResults();
			$iFilteredTotal = $this->getTotalResults(TRUE);
		}

		foreach ($rResult->getResultArray() as $row_key => $row_val)
		{
			$aaData[$row_key] = ($this->checkCType())? $row_val : array_values($row_val);

			foreach ($this->add_columns as $field => $val) {
				if ($this->checkCType()) {
					$aaData[$row_key][$field] = $this->execReplace($val, $aaData[$row_key]);
				} else {
					$aaData[$row_key][] = $this->execReplace($val, $aaData[$row_key]);
				}
			}

			foreach ($this->edit_columns as $modkey => $modval) {
				foreach ($modval as $val) {
					$aaData[$row_key][($this->checkCType())? $modkey : array_search($modkey, $this->columns)] = $this->execReplace($val, $aaData[$row_key]);
				}
			}
			$aaData[$row_key] = array_diff_key($aaData[$row_key], ($this->checkCType())? $this->unset_columns : array_intersect($this->columns, $this->unset_columns));

			if (!$this->checkCType())
				$aaData[$row_key] = array_values($aaData[$row_key]);
		}

		if ($output == 'json')
		{
			if ($this->is_csrf) # jika csrf diaktifkan
			{
				$sOutput = array(
					'draw' => intval($this->request->getPost('draw')),
					'recordsTotal' => $iTotal,
					'recordsFiltered' => $iFilteredTotal,
					'data' => $aaData,
					'csrf_name' => 'X-CSRF-TOKEN', //$this->security->getCSRFTokenName(),
					'csrf_content' => $this->security->getCSRFHash()
				);
			}
			else # default
			{
				$sOutput = array(
					'draw' => intval($this->request->getPost('draw')),
					'recordsTotal' => $iTotal,
					'recordsFiltered' => $iFilteredTotal,
					'data' => $aaData
				);
			}
			
			if ($charset == 'utf-8') {
				return json_encode($sOutput);
			} else {
				return $this->jsonify($sOutput);
			}
		} else {
			return array('aaData' => $aaData);
		}
	}

	/**
	* Get result count
	*
	* @return integer
	*/
	private function getTotalResults($filtering = FALSE)
	{
		if($filtering)
			$this->getFiltering();

		foreach($this->joins as $val)
			$this->builder->join($val[0], $val[1], $val[2]);

		foreach($this->where as $val)
			$this->builder->where($val[0], $val[1], $val[2]);

		foreach($this->or_where as $val)
			$this->builder->orWhere($val[0], $val[1], $val[2]);
		
		foreach($this->where_in as $val)
			$this->builder->whereIn($val[0], $val[1]);

		foreach($this->group_by as $val)
			$this->builder->groupBy($val);

		foreach($this->like as $val)
			$this->builder->like($val[0], $val[1], $val[2]);

		foreach($this->or_like as $val)
			$this->builder->orLike($val[0], $val[1], $val[2]);

		if (strlen($this->distinct) > 0)
		{
			$this->builder->distinct($this->distinct);
			$this->builder->select($this->columns);
		}
		
		//$subquery = $this->builder->getCompiledSelect();
		$subquery = $this->builder
			->select($this->columnstr)
			->getCompiledSelect();
		$countingsql = "SELECT COUNT(*) FROM (" . $subquery . ") SqueryAux";
		$query = $this->db->query($countingsql);
		$result = $query->getRowArray();
		$count = $result['COUNT(*)'];
		return $count;
	}

	/**
	* Runs callback functions and makes replacements
	*
	* @param mixed $custom_val
	* @param mixed $row_data
	* @return string $custom_val['content']
	*/
	private function execReplace($custom_val, $row_data)
	{
		$replace_string = '';
	   
		// Go through our array backwards, else $1 (foo) will replace $11, $12 etc with foo1, foo2 etc
		$custom_val['replacement'] = array_reverse($custom_val['replacement'], true);

		if (isset($custom_val['replacement']) && is_array($custom_val['replacement']))
		{
			//Added this line because when the replacement has over 10 elements replaced the variable "$1" first by the "$10"
			$custom_val['replacement'] = array_reverse($custom_val['replacement'], true);
			foreach ($custom_val['replacement'] as $key => $val)
			{
				$sval = preg_replace("/(?<!\w)([\'\"])(.*)\\1(?!\w)/i", '$2', trim($val));

				if (preg_match('/(\w+::\w+|\w+)\((.*)\)/i', $val, $matches) && is_callable($matches[1]))
				{
					$func = $matches[1];
					$args = preg_split("/[\s,]*\\\"([^\\\"]+)\\\"[\s,]*|" . "[\s,]*'([^']+)'[\s,]*|" . "[,]+/", $matches[2], 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

					foreach ($args as $args_key => $args_val)
					{
						$args_val = preg_replace("/(?<!\w)([\'\"])(.*)\\1(?!\w)/i", '$2', trim($args_val));
						$args[$args_key] = (in_array($args_val, $this->columns))? ($row_data[($this->checkCType())? $args_val : array_search($args_val, $this->columns)]) : $args_val;
					}

					$replace_string = call_user_func_array($func, $args);
				} elseif (in_array($sval, $this->columns)) {
					$replace_string = $row_data[($this->checkCType())? $sval : array_search($sval, $this->columns)];
				} else {
					$replace_string = $sval;
				}
				
				$custom_val['content'] = str_ireplace('$' . ($key + 1), $replace_string, $custom_val['content']);
			}
		}
		
		return $custom_val['content'];
	}

	/**
	* Check column type -numeric or column name
	*
	* @return bool
	*/
	private function checkCType()
	{
		$column = $this->request->getPost('columns');
		if (is_numeric($column[0]['data'] ?? null)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}


	/**
	* Return the difference of open and close characters
	*
	* @param string $str
	* @param string $open
	* @param string $close
	* @return string $retval
	*/
	private function balanceChars($str, $open, $close)
	{
		$openCount = substr_count($str, $open);
		$closeCount = substr_count($str, $close);
		$retval = $openCount - $closeCount;
		return $retval;
	}

	/**
	* Explode, but ignore delimiter until closing characters are found
	*
	* @param string $delimiter
	* @param string $str
	* @param string $open
	* @param string $close
	* @return mixed $retval
	*/
	private function explode($delimiter, $str, $open = '(', $close=')')
	{
		$retval = [];
		$hold = [];
		$balance = 0;
		$parts = explode($delimiter, $str);

		foreach ($parts as $part)
		{
			$hold[] = $part;
			$balance += $this->balanceChars($part, $open, $close);

			if ($balance < 1)
			{
				$retval[] = implode($delimiter, $hold);
				$hold = [];
				$balance = 0;
			}
		}

		if (count($hold) > 0)
			$retval[] = implode($delimiter, $hold);

		return $retval;
	}

	/**
	* Workaround for json_encode's UTF-8 encoding if a different charset needs to be used
	*
	* @param mixed $result
	* @return string
	*/
	private function jsonify($result = FALSE)
	{
		if(is_null($result))
			return 'null';

		if($result === FALSE)
			return 'false';

		if($result === TRUE)
			return 'true';

		if(is_scalar($result))
		{
			if(is_float($result))
				return floatval(str_replace(',', '.', strval($result)));

			if(is_string($result))
			{
				static $jsonReplaces = array(array('\\', '/', '\n', '\t', '\r', '\b', '\f', '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $result) . '"';
			}
			else {
				return $result;
			}
		}

		$isList = TRUE;

		for ($i = 0, reset($result); $i < count($result); $i++, next($result))
		{
			if (key($result) !== $i)
			{
				$isList = FALSE;
				break;
			}
		}

		$json = [];

		if ($isList)
		{
			foreach($result as $value) {
				$json[] = $this->jsonify($value);
			}
			return '[' . join(',', $json) . ']';
		}
		else
		{
			foreach ($result as $key => $value) {
				$json[] = $this->jsonify($key) . ':' . $this->jsonify($value);
			}
			return '{' . join(',', $json) . '}';
		}
	}
	
	 /**
	 * returns the sql statement of the last query run
	 * @return type
	 */
	public function lastQuery()
	{
		return  $this->builder->getLastQuery();
	}
}
/* End of file Titasictables.php */
/* Location: ./app/ThirdParty/Titasictech/Titasictables.php */
