<?php

	/**
	* db.php
	*/
	class Database{
		protected $pdo;

		function __construct($dbConfig)
		{
			$this->pdo = $this->getConnection($dbConfig);	
		}

		private function getConnection($config) {
			try {
				$pdo = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbName'] . '', $config['dbUser'], $config['dbPassword']);
				return $pdo;

			} catch (PDOException $e) {
				print "Connection Error!: " . $e->getMessage() . " \n";
				die();
			}
		}

		public function login($username, $password){
			$response = array();
			$encPwd = md5($password); // You can use more secure password encryption method
			$sql = "SELECT id, firstname, lastname FROM tbl_login WHERE username = :username AND password = :password";
			$res = $this->pdo->prepare($sql);
			$res->bindParam(':username', $username);
			$res->bindParam(':password', $encPwd);
			$res->execute();
			$record = $res->fetch(PDO::FETCH_ASSOC);
			if (!empty($record)) {
				$token = $this->getRandom(50);
				$updateSql = "UPDATE tbl_login SET token = :token WHERE id = :id";
				$updateRes = $this->pdo->prepare($updateSql);
				$updateRes->bindParam(':token', $token);
				$updateRes->bindParam(':id', $record['id']);
				$updateRes->execute();
				
				$response['status'] = 'success';
				$response['token'] = $token;
				$response['data'] = array( 'firstname' => $record['firstname'], 'lastname' => $record['lastname']);
			
			}else{
				$response['status'] = 'fail';
			}

			return $response;
		}

		private function verifyToken($token){
			$sql = "SELECT id FROM tbl_login WHERE token = :token";
			$res = $this->pdo->prepare($sql);
			$res->bindParam(':token', $token);
			$res->execute();
			$record = $res->fetch(PDO::FETCH_ASSOC);
			if (!empty($record)) {
				return $record['id'];
			}else{
				return false;
			}
		}

		public function logout($token){
			$key = $this->verifyToken($token);
			if (!empty($key)) {
				$updateSql = "UPDATE tbl_login SET token = null WHERE token = :token";
				$updateRes = $this->pdo->prepare($updateSql);
				$updateRes->bindParam(':token', $token);
				$updateRes->execute();
				
				return array('status' => 'success', 'message' => '');
			}else{
				return array('status' => 'fail', 'message' => 'Invalid Token');
			}
		}

		public function getLeaves($token){
			$key = $this->verifyToken($token);
			if (!empty($key)) {
				$sql = "SELECT id, leave_date, reason, created_date FROM tbl_leaves WHERE user_id = :user_id";
				$res = $this->pdo->prepare($sql);
				$res->bindParam(':user_id', $key);
				$res->execute();
				$records = $res->fetchAll(PDO::FETCH_ASSOC);
				if (!empty($records)) {
					return array('status' => 'success', 'message' => '', 'leaves' => $records);
				}else{
					return array('status' => 'success', 'message' => '', 'leaves' => array());
				}
			}else{
				return array('status' => 'fail', 'message' => 'Invalid Token');
			}
			
		}

		private function getRandom($length){
			$char = array_merge(range(0,9), range('A', 'Z'), range('a', 'z'));
	        $code = '';
	        for($i=0; $i < $length; $i++) {
	            $code .= $char[mt_rand(0, count($char) - 1)];
	        }
	        return $code;
		}
	}