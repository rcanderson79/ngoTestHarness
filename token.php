<?php	

	if (isset($_POST['action'])) {
	
		$cr_type = "";
		if ($_POST['action'] == 'gettoken') { 
		
			$cr_type = "POST";
			$cr_url = $_POST["endpoint"];
			$cr_head = array("Authorization: Basic ".$_POST["apikey"], "Content-Type: application/x-www-form-urlencoded");
			$cr_post = http_build_query(array('grant_type' => 'client_credentials'));
	    $cr_json = true;
	
		}
		if ($_POST['action'] == 'createorder') { 

			$cr_type = "POST";
			$cr_url = $_POST["endpoint"];
			$cr_head = array("Authorization: Bearer ".$_POST["token"], "Content-Type: application/vnd.ni-payment.v2+json", "Accept: application/vnd.ni-payment.v2+json");
			$cr_post = $_POST["payload"];
      $cr_json = true;

		}	
		if ($_POST['action'] == 'getorder') { 
		
			$cr_type = "GET";
			$cr_url = $_POST["endpoint"];
			$cr_head = array("Authorization: Bearer ".$_POST["token"]);
			$cr_post = null;
      $cr_json = true;

		}
    if ($_POST['action'] == 'sendcard') {
      
      $cr_type = "PUT";
      $cr_url = $_POST["endpoint"];
			$cr_head = array("Authorization: Bearer ".$_POST["token"], "Content-Type: application/vnd.ni-payment.v2+json", "Accept: application/vnd.ni-payment.v2+json");
			$cr_post = $_POST["payload"];
      $cr_json = true;
      
    }
    if ($_POST['action'] == 'post3DS') {
      
      $cr_type = "POST";
      $cr_url = $_POST["endpoint"];
      $cr_head = array("Content-Type: application/x-www-form-urlencoded");
      $cr_post = http_build_query( array( 'PaReq' => $_POST["PaReq"], 
                                          'TermUrl' => $_POST["TermUrl"],
                                          'MD' => $_POST["MD"] ));
      $cr_json = false;

    }
		if ($cr_type !== "") { echo(invokeCurlRequest($cr_type, $cr_url, $cr_head, $cr_post, $cr_json)); }
		
	}
	
	function invokeCurlRequest($type, $url, $headers, $post, $json) {
	
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		

		if ($type == "POST" || $type == "PUT") {
		
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      
      if ($type == "PUT") {
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        
      }
		
		}

		$server_output = curl_exec ($ch);
    
    if ($json == true) {
      
      $returnData = new StdClass();
      $returnData->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $returnData->response = json_decode($server_output);		
  		curl_close ($ch);
  		return json_encode($returnData);
      
    } else {
      
      $returnData = $server_output;
  		curl_close ($ch);
  		return $returnData;
      
    }
   
	}
		
?>