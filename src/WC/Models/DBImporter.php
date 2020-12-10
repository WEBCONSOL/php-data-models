<?php

namespace WC\Models;

use Exception;
use mysqli;
use RuntimeException;

class DBImporter {

    /**
     * @var mysqli
     */
	private $db;
	private $filename;
	private $username;
	private $password;
	private $database;
	private $host;
	private $tbPfx;
	private $port = 3006;

	public function __construct() {}

	public function executeByCredentials($filename, $username, $password, $database, $host, $port=3006, $tbPfx='') {
		//set the varibles to properties
		$this->filename = $filename;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->host = $host;
		$this->port = $port;
		$this->tbPfx = $tbPfx;

		//connect to the datase
		$this->connect();

		//open file and import the sql
		$this->openfile();
	}

	private function connect() {
		$this->db = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
		if ($this->db->connect_errno) {
			throw new RuntimeException("Failed to connect to MySQL: " . $this->db->connect_error);
		}
	}

	private function query($query) {

	    if ($this->tbPfx) {
	        $query = str_replace('#__', $this->tbPfx, $query);
        }

		if(!$this->db->query($query)){
			throw new RuntimeException("Error with query: ".$this->db->error."\n");
		}
	}

	private function openfile() {
		try {

			//if file cannot be found throw errror
			if (!file_exists($this->filename)) {
				throw new RuntimeException("Error: File not found.\n");
			}

			// Read in entire file
			$fp = fopen($this->filename, 'r');

			// Temporary variable, used to store current query
			$templine = '';

			// Loop through each line
			while (($line = fgets($fp)) !== false) {

				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || $line == '') {
					continue;
				}

				// Add this line to the current segment
				$templine .= $line;

				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					$this->query($templine);

					// Reset temp variable to empty
					$templine = '';
				}
			}

			//close the file
		   fclose($fp);
		   $this->db->close();

		} catch(Exception $e) {
			echo "Error importing: ".$e->getMessage()."\n";
		}
	}
}