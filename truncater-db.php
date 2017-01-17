#! /usr/bin/php7.0
<?php
	
	error_reporting(0);
	class Truncater {

		function __construct() {
			echo "[!] Truncater DB v1.1\n[!] Coded by [t]0x1c - github.com/reyvand\n\n";
			if(function_exists('mysqli_init') && extension_loaded('mysqli')) {
				($_SERVER['argc'] == 1) ? $this->getHelp() : $this->indexParam();
			} else {
				echo "[x] nothing mysqli extension detected, please upgrade php or install a php-mysqli extension\n\n";
			}
		}
		function getHelp() {
			echo "[-] invalid options\n[!] usage : php ".basename(__FILE__)." [option] \n[!] try -help for help\n\n";
		}
		function help() {
			echo "[?] Help\n\t -help\t	display this information\n\t -check\t\tsearch for available databases\n\t -h\t	host database server\n\t -u\t	user database server\n\t -p\t	password database server\n\t -d\t	target database\n\n[!] Example\n\t - Check available databases\n\t    $ ./".basename(__FILE__)." -h host -u user -p password -check\n\t - Truncate all table in database\n\t    $ ./".basename(__FILE__)." -h host -u user -p pass -d database\n\t\n";
		}
		function indexParam() {
			$param = $_SERVER['argv'];
			if(array_search('-help', $param)) {
				$this->help();
			} elseif(array_search('-check', $param)) {
				$this->searchDB();
			} elseif(array_search('-d', $param)) {
				$this->pwnDB();
			} else {
				$this->getHelp();
			}
		}
		function getParamValue($param) {
			$x = array_search($param, $_SERVER['argv']);
			if(!empty($x)) {
				return $_SERVER['argv'][$x + 1];
			} else {
				return "";
			}
		}
		function connect($host,$user,$pass) {
			$new_connection = new mysqli($host,$user,$pass);
			echo ($new_connection->connect_error) ? "[x] connection failed\n" : "";
			return $new_connection;
		}
		function collectingDB($connection) {
			$q = $connection->query("SHOW DATABASES");
			$db_available = array();
			while($res = $q->fetch_assoc()) {
				array_push($db_available, $res['Database']);
			}
			return $db_available;
		}
		function showDB($db_available) {
			echo "[+] Available Database on this Server : \n";
			foreach ($db_available as $db) {
				echo " > ".$db."\n";
			}
		}
		function searchDB() {
			$connect = $this->connect($this->getParamValue('-h'),$this->getParamValue('-u'),$this->getParamValue('-p'));
			$db_available = $this->collectingDB($connect);
			$this->showDB($db_available);
			#echo $this->getParamValue('-h').$this->getParamValue('-u').$this->getParamValue('-p');
		}
		function pwnDB() {
			$connect = $this->connect($this->getParamValue('-h'),$this->getParamValue('-u'),$this->getParamValue('-p'));
			$link = $connect->select_db($this->getParamValue('-d'));
			$q_selected_db = $connect->query("SELECT DATABASE()");
			if($res_db = $q_selected_db->fetch_row()) {
				echo "[+] Selected DB : ".$res_db[0]."\n";
				echo "[+] enumerating tables in database ".$res_db[0]." .. \n";
				$available_table = array();
				$q_table = $connect->query("SHOW TABLES IN ".$res_db[0]." ");
				while($res_table = $q_table->fetch_row()) {
					array_push($available_table, $res_table[0]);
				}
				if(count($available_table) > 0) {
					echo "[+] found ".count($available_table)." table(s)\n";
					foreach($available_table as $tb) {
						echo "   - ".$tb." \n";
					}
					echo "[?] truncate all tables in this database ? [y/n]   : ";
					$line = fgets(STDIN);
					if($line = "y") {
						echo "[+] truncating all tables in database ".$res_db[0]." ..\n";
						$c = 0;
						for($i=0; $i<count($available_table); $i++) {
							$q_act = $connect->query("TRUNCATE ".$res_db[0].".".$available_table[$i]." ");
							if($q_act) {
								$c++;
							}
						}
						if($c == count($available_table)) {
							echo "[+] operation succeed\n";
						} else {
							echo "[x] operation failed\n";
						}
					} else {
						echo "[x] quitting ..\n";
					}
				} else {
					echo "[x] no tables available on this database";
				}
			} else {
				echo "[x] Failed to execute query\n";
			}
		}
	}
	$t = new Truncater;
