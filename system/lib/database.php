<?php

class MySQLiResult
{
	public $num_rows;
	private $fields;
	private $data;

	public function __construct($num_rows, $fields, $data)
	{
		$this->num_rows = $num_rows;
		$this->fields = $fields;
		$this->data = $data;
	}

	public function fetch_assoc()
	{
		if (empty($this->data)) {
			return null;
		}
		return array_shift($this->data);
	}

	public function fetch_array()
	{
		if (empty($this->data)) {
			return null;
		}
		return array_shift($this->data);
	}

	public function free()
	{
		$this->data = [];
	}

	public function num_rows()
	{
		return $this->num_rows;
	}
}

function new_mysqli_stmt_get_result($stmt)
{
	if (!$stmt) {
		throw new Exception('Invalid prepared statement');
	}
	if ($stmt->errno) {
		throw new Exception('Error executing query: ' . $stmt->error);
	}
	$meta = $stmt->result_metadata();
	if (!$meta) {
		return false; // Return false if there is no result set
	}
	$fields = [];
	while ($field = $meta->fetch_field()) {
		$fields[] = $field->name;
	}
	$data = [];
	$stmt->store_result();

	$variables = [];
	foreach ($fields as $field) {
		$variables[] = &${$field};
	}
	call_user_func_array([$stmt, 'bind_result'], $variables);

	while ($stmt->fetch()) {
		$rowData = [];
		foreach ($fields as $field) {
			$rowData[$field] = ${$field};
		}
		$data[] = $rowData;
	}
	return new MySQLiResult($stmt->num_rows, $fields, $data);
}

class DB
{
	private static $host;
	private static $username;
	private static $password;
	public static $dbase;
	private static $connection;
	private static $autoCommit = true;
	private static $transaction_level = 0;

	public static function init($host, $username, $password, $dbase)
	{
		self::$host = $host;
		self::$username = $username;
		self::$password = $password;
		self::$dbase = $dbase;
		self::open_connection();
		
		// Register shutdown function - equivalent to __destruct for static class
		register_shutdown_function([self::class, 'cleanup']);
	}

	public static function open_connection()
	{
		self::$connection = mysqli_connect(self::$host, self::$username, self::$password, self::$dbase, 3306);
		if (mysqli_connect_errno()) {
			die("Database connection failed: " .
				mysqli_connect_error() .
				" (" . mysqli_connect_errno() . ")");
		}
		mysqli_set_charset(self::$connection, "latin1");
		$now = new DateTime();
		$mins = $now->getOffset() / 60;
		$sgn = ($mins < 0 ? -1 : 1);
		$mins = abs($mins);
		$hrs = floor($mins / 60);
		$mins -= $hrs * 60;
		$offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
		self::query("SET time_zone='$offset'");
	}

	public static function close_connection()
	{
		if (isset(self::$connection)) {
			mysqli_close(self::$connection);
			self::$connection = null;
		}
	}

	/**
	 * Cleanup method - equivalent to __destruct for static class
	 * This is automatically called when the script terminates
	 */
	public static function cleanup()
	{
		// Rollback any uncommitted transactions
		if (self::$transaction_level > 0) {
			error_log("Warning: Script ending with uncommitted transaction. Rolling back.");
			while (self::$transaction_level > 0) {
				self::rollback();
			}
		}
		
		// Close the database connection
		self::close_connection();
	}

	public static function query($sql, $params = [])
	{
		$stmt = mysqli_prepare(self::$connection, $sql);
		if ($stmt === false) {
			throw new Exception('Prepare failed: ' . mysqli_error(self::$connection));
		}

		$types = '';
		$formatted_params = [];

		foreach ($params as $key => $param) {
			if ($param === null) {
				$types .= 's'; // MySQL treats NULL as string type in prepared statements
				$params[$key] = null;
			} else {
				switch (gettype($param)) {
					case 'string':
						$types .= 's';
						break;
					case 'integer':
						$types .= 'i';
						break;
					case 'double':
						$types .= 'd';
						break;
					default:
						throw new InvalidArgumentException("Invalid parameter type at index $key: " . gettype($param));
				}
			}
			$formatted_params[] = &$params[$key];
		}

		if (!empty($types)) {
			if (!mysqli_stmt_bind_param($stmt, $types, ...$formatted_params)) {
				throw new Exception('Bind param failed: ' . mysqli_stmt_error($stmt));
			}
		}

		if (!mysqli_stmt_execute($stmt)) {
			$error = mysqli_stmt_error($stmt);
			$errno = mysqli_stmt_errno($stmt);
			throw new Exception("Query execution failed ($errno): $error [SQL: $sql]");
		}

		try {
			$results = new_mysqli_stmt_get_result($stmt);
		} catch (Exception $e) {
			throw new Exception("Failed to get result: " . $e->getMessage());
		}

		// Return boolean for successful write operations without result set
		return $results ?: true;
	}

	public static function updateQuery(string $tableName, array $assoc_array_values, array $conditions = [])
	{
		$sql = "UPDATE `$tableName` SET ";

		$values = [];
		foreach (array_keys($assoc_array_values) as $column) {
			$values[] = "`$column` = ?";
		}

		$sql .= implode(",", $values);

		if (count($conditions)) {
			$sql .= " WHERE ";
			$conditions = array_map(function ($condition) {
				return "`$condition` = ?";
			}, array_keys($conditions));
			$sql .= implode(" AND ", $conditions);
		}

		$query = self::query($sql, array_merge(array_values($assoc_array_values), array_values($conditions)));
		return $query;
	}

	public static function multi_query($sql, $log = false)
	{
		$sql = trim(trim($sql), "; ");
		$sql = <<<SQL
        SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
        SET time_zone = "+00:00";


        /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
        /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
        /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
        /*!40101 SET NAMES utf8mb4 */;

        START TRANSACTION;

        {$sql};

        COMMIT;

        /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
        /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
        /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
        SQL;

		if ($log) {
			var_dump($sql);
		}
		// self::confirm_query($result);
		$result = mysqli_multi_query(self::$connection, $sql);
		self::confirm_query($result);
		while ($result && mysqli_more_results(self::$connection)) {
			$result = mysqli_next_result(self::$connection);
			self::confirm_query($result);
		}

		return $result;
	}

	public static function migrate_query($sql)
	{
		return self::multi_query($sql, true);
	}

	private static function confirm_query($result, $sql = '')
	{
		if (!$result) {
			$error = mysqli_error(self::$connection);
			if ($error) {
				error_log($error);
				if ($sql) var_dump($sql);
				throw new Exception($error);
			}
		}
	}

	public static function escape_value($par)
	{
		if (is_array($par)) {
			return array_map(
				function ($r) {
					return self::escape_value($r);
				},
				$par
			);
		} elseif (is_string($par)) {
			return mysqli_real_escape_string(self::$connection, $par);
		}
		return $par;
	}

	public static function unescape_value($par)
	{
		if (!is_array($par)) {
			return strtr(
				$par,
				[
					'\0' => "\x00",
					'\n' => "\n",
					'\r' => "\r",
					'\\\\' => "\\",
					"\'" => "'",
					'\"' => '"',
					'\Z' => "\x1a"
				]
			);
		} else {
			return array_map(
				function ($r) {
					return self::unescape_value($r);
				},
				$par
			);
		}
	}

	// "database neutral" functions

	public static function fetch_array($result_set)
	{
		return ($result_set->fetch_assoc());
	}

	public static function num_rows($result_set)
	{
		return ($result_set->num_rows());
	}

	public static function insert_id()
	{
		// get the last id inserted over the current db connection
		return mysqli_insert_id(self::$connection);
	}

	public static function affected_rows()
	{
		return mysqli_affected_rows(self::$connection);
	}

	public static function start_transaction()
	{
		if (self::$transaction_level == 0) {
			if (self::$autoCommit) {
				self::autocommit(false);
				mysqli_begin_transaction(self::$connection);
			}
		} else {
			// Create a savepoint for nested transactions
			mysqli_query(self::$connection, "SAVEPOINT transaction_" . self::$transaction_level);
		}
		self::$transaction_level++;
	}

	public static function rollback()
	{
		if (self::$transaction_level > 0) {
			if (self::$transaction_level == 1) {
				mysqli_rollback(self::$connection);
				self::autocommit(true);
			} else {
				// Rollback to the last savepoint for nested transactions
				mysqli_query(self::$connection, "ROLLBACK TO SAVEPOINT transaction_" . (self::$transaction_level - 1));
			}
			self::$transaction_level--;
		}
	}

	public static function commit()
	{
		if (self::$transaction_level > 0) {
			if (self::$transaction_level == 1) {
				mysqli_commit(self::$connection);
				self::autocommit(true);
			} else {
				// Release the last savepoint for nested transactions
				mysqli_query(self::$connection, "RELEASE SAVEPOINT transaction_" . (self::$transaction_level - 1));
			}
			self::$transaction_level--;
		}
	}

	public static function autocommit(bool $value)
	{
		self::$autoCommit = $value;
		mysqli_autocommit(self::$connection, $value);
		return;
	}

	public static function EXPORT_DATABASE($return_content = false, $tables = false, $backup_name = false)
	{
		$host = self::$host;
		$user = self::$username;
		$pass = self::$password;
		$name = self::$dbase;
		set_time_limit(3000);
		$mysqli = new mysqli($host, $user, $pass, $name);
		$mysqli->select_db($name);
		$mysqli->query("SET NAMES 'utf8'");
		$queryTables = $mysqli->query('SHOW TABLES');
		while ($row = $queryTables->fetch_row()) {
			$target_tables[] = $row[0];
		}
		if ($tables !== false) {
			$target_tables = array_intersect($target_tables, $tables);
		}
		$content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+02:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `" . $name . "`\r\n--\r\n\r\n\r\n";
		foreach ($target_tables as $table) {
			if (empty($table)) {
				continue;
			}
			$result = $mysqli->query('SELECT * FROM `' . $table . '`');
			$fields_amount = $result->field_count;
			$rows_num = $mysqli->affected_rows;
			$res = $mysqli->query('SHOW CREATE TABLE ' . $table);
			$TableMLine = $res->fetch_row();
			$content .= "\n\n" . $TableMLine[1] . ";\n\n";
			$TableMLine[1] = str_ireplace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `', $TableMLine[1]);
			for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
				while ($row = $result->fetch_row()) { //when started (and every after 100 command cycle):
					if ($st_counter % 100 == 0 || $st_counter == 0) {
						$content .= "\nINSERT INTO " . $table . " VALUES";
					}
					$content .= "\n(";
					for ($j = 0; $j < $fields_amount; $j++) {
						$row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
						if (isset($row[$j])) {
							$content .= '"' . $row[$j] . '"';
						} else {
							$content .= '""';
						}
						if ($j < ($fields_amount - 1)) {
							$content .= ',';
						}
					}
					$content .= ")";
					//every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
					if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
						$content .= ";";
					} else {
						$content .= ",";
					}
					$st_counter = $st_counter + 1;
				}
			}
			$content .= "\n\n\n";
		}
		$content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
		if ($return_content) {
			return $content;
		}
		$backup_name = $backup_name ? $backup_name : $name . '.(' . date('Y-m-d_H:i:s') . ').sql.gz';
		$content = gzencode($content, 9);
		ob_get_clean();
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary");
		header('Content-Length: ' . (function_exists('mb_strlen') ? mb_strlen($content, '8bit') : strlen($content)));
		header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
		echo $content;
		exit;
	}
}

// Connect to DB
// DB::init(DB_HOST, DB_USER, DB_PASS, DB_NAME);
