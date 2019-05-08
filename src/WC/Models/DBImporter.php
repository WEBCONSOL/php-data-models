<?php

namespace WC\Models;

use mysqli;
use Exception;

class DBImporter {

	private $db;
	private $filename;
	private $username;
	private $password;
	private $database;
	private $host;
	private $tbPfx;

	public function __construct() {}

	public function executeByCredentials($filename, $username, $password, $database, $host, $tbPfx='') {
		//set the varibles to properties
		$this->filename = $filename;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->host = $host;
		$this->tbPfx = $tbPfx;

		//connect to the datase
		$this->connect();

		//open file and import the sql
		$this->openfile();
	}

	private function connect() {
		$this->db = new mysqli($this->host, $this->username, $this->password, $this->database);
		if ($this->db->connect_errno) {
			throw new Exception("Failed to connect to MySQL: " . $this->db->connect_error);
		}
	}

	private function query($query) {

	    if ($this->tbPfx) {
	        $query = str_replace('#__', $this->tbPfx, $query);
        }

		if(!$this->db->query($query)){
			throw new Exception("Error with query: ".$this->db->error."\n");
		}
	}

	private function openfile() {
		try {

			//if file cannot be found throw errror
			if (!file_exists($this->filename)) {
				throw new Exception("Error: File not found.\n");
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