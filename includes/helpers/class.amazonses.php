<?php

class AmazonSES
{
	public $Multithread = false;
	public $CampaignID	= 0;
	public $SubscriberID = 0;
	public $Timezone = '';
	
    public $amazonSES_base_url = "https://email.us-east-1.amazonaws.com";
    public $debug = FALSE;

    public $aws_access_key_id = "";
    public $aws_secret_key = "";

    protected function
    make_required_http_headers () {
        $headers = array();
        
        if($this->Timezone=='' || $this->Timezone==0) date_default_timezone_set('America/New_York');
        else date_default_timezone_set($this->Timezone);
        
        $date_value = date(DATE_RFC2822);
        $headers[] = "Date: {$date_value}";

        $signature = base64_encode(hash_hmac("sha1", 
                                             $date_value,
                                             $this->aws_secret_key,
                                             TRUE));

        $headers[] = 
            "X-Amzn-Authorization: AWS3-HTTPS "
            ."AWSAccessKeyId={$this->aws_access_key_id},"
            ."Algorithm=HmacSHA1,Signature={$signature}";

        $headers[] =
            "Content-Type: application/x-www-form-urlencoded";

        return $headers;
    }

    protected function
    make_query_string
    ($query) {
        $query_str = "";
        foreach ($query as $k => $v)
            { $query_str .= urlencode($k)."=".urlencode($v).'&'; }

        return rtrim($query_str, '&');
    }

    protected function
    parse_amazonSES_error
    ($response) {
        $sxe = simplexml_load_string($response);

        // If the error response can not be parsed properly,
        // then just return the original response content.
        if (($sxe === FALSE) or ($sxe->getName() !== "ErrorResponse"))
            { return $response; }

        return "{$sxe->Error->Code}"
               .(($sxe->Error->Message)?" - {$sxe->Error->Message}":"");
    }

    protected function
    make_request
    ($query) {
    	if(@include('../config.php')) include_once '../config.php';    	
    	$server_path_array1 = explode('/', $_SERVER['SCRIPT_FILENAME']);    	
    	$delimiter = $server_path_array1[count($server_path_array1)-1];
    	if($delimiter=='send-now.php') $delimiter = 'includes';
    	$server_path_array = explode($delimiter, $_SERVER['SCRIPT_FILENAME']);
	    $server_path = $server_path_array[0];
    	if(@include($server_path.'includes/config.php')) include_once $server_path.'includes/config.php';
    	
	    if(isset($dbPort)) $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
	    else $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
	    
        // Prepare headers and query string.
        $request_url = $this->amazonSES_base_url;
        $query_str = $this->make_query_string($query);
        $http_headers = $this->make_required_http_headers();

        if ($this->debug) {
            echo "[AmazonSESDebug] Query Parameters:\n\"";
            print_r($query);
            echo "\"\n";

            printf("[AmazonSES Debug] Http Headers:\n\"%s\"\n",
                                      implode("\n", $http_headers));
            printf("[AmazonSES Debug] Query String:\n\"%s\"\n", $query_str);
        }
        
        //if multithreading is needed,
        if($this->Multithread)
        {
	        //Insert SES query into queue
	        $q8 = 'SELECT count(subscriber_id) FROM queue WHERE campaign_id = '.$this->CampaignID.' AND subscriber_id = '.$this->SubscriberID;
	        $r8 = mysqli_query($mysqli, $q8);
	        if ($r8)
	        {
	        	while($row = mysqli_fetch_array($r8)) $no_of_matching_email_in_queue = $row['count(subscriber_id)'];
	        	
	        	if($no_of_matching_email_in_queue==0)
	        	{
					$q = 'INSERT INTO queue (query_str, campaign_id, subscriber_id) VALUES ("'.addslashes($query_str).'", '.$this->CampaignID.', '.$this->SubscriberID.')';
					mysqli_query($mysqli, $q);
				}
	        }
	        
	        //Update last_campaign in subscribers table
	        $q4 = 'UPDATE subscribers SET last_campaign = '.$this->CampaignID.' WHERE subscribers.id = '.$this->SubscriberID;
			mysqli_query($mysqli, $q4);
	        
	        //Get SES send rate
	        $q2 = 'SELECT send_rate FROM login ORDER BY id ASC LIMIT 1';
	        $r2 = mysqli_query($mysqli, $q2);
	        if ($r2) while($row = mysqli_fetch_array($r2)) $ses_send_rate = $row['send_rate'];
	        
	        //Check if there are more than X (where X is the send rate) emails in queue, if so, send them in parallel to SES
	        $q2 = 'SELECT id, query_str, subscriber_id FROM queue WHERE campaign_id = '.$this->CampaignID.' AND sent = 0 LIMIT '.$ses_send_rate;
	        $r2 = mysqli_query($mysqli, $q2);
	        if ($r2 && mysqli_num_rows($r2) >= $ses_send_rate)
	        {
		        $id_array = array();
		        $time_started = microtime();
		        
		        if (!function_exists('on_request_done'))
				{
					function on_request_done($content, $url, $ch, $callback_data)
					{						
						$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
					    if ($httpcode !== 200) //if fail
					    {						    
							// Pause for one second then retry sending
							sleep(1);
					        $cr = curl_init();
					        curl_setopt($cr, CURLOPT_URL, $callback_data[2]);
					        curl_setopt($cr, CURLOPT_POST, $callback_data[1]);
					        curl_setopt($cr, CURLOPT_POSTFIELDS, $callback_data[1]);
					        curl_setopt($cr, CURLOPT_HTTPHEADER, $callback_data[3]);
					        curl_setopt($cr, CURLOPT_HEADER, TRUE);
					        curl_setopt($cr, CURLOPT_RETURNTRANSFER, TRUE); 
					        curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, 2);
							curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 1);
							curl_setopt($cr, CURLOPT_CAINFO, $server_path.'certs/cacert.pem');
					        $response = curl_exec($cr);
					
					        // Get http status code
					        $response_http_status_code = curl_getinfo($cr, CURLINFO_HTTP_CODE);
					        
					        if($response_http_status_code !== 200)
					        {
					        	$q7 = 'SELECT errors FROM campaigns WHERE id = '.$callback_data[4];
					        	$r7 = mysqli_query($mysqli, $q7);
					        	if ($r7)
					        	{
					        	    while($row = mysqli_fetch_array($r7))
					        	    {
					        			$errors = $row['errors'];
					        			
					        			if($errors=='')
											$val = $callback_data[0].':'.$response_http_status_code;
										else
										{
											$errors .= ','.$callback_data[0].':'.$response_http_status_code;
											$val = $errors;
										}
					        	    }  
					        	}
			
						        //update campaigns' errors column
						        $q6 = 'UPDATE campaigns SET errors = "'.$val.'" WHERE id = '.$callback_data[4];
								mysqli_query($mysqli, $q6);
					        }
					    }
					}
				}
		        
		        $mh = curl_multi_init();
		        $outstanding_requests = array();
			    
	            while($row = mysqli_fetch_array($r2))
	            {
	        		$queue_id = $row['id'];
	        		$queue = stripslashes($row['query_str']);	
	        		$subscriber_id = $row['subscriber_id'];	
					array_push($id_array, $queue_id);
			    	
			        // Prepare curl.
			        $ch = curl_init();
			        
			        curl_setopt($ch, CURLOPT_URL, $request_url);
			        curl_setopt($ch, CURLOPT_POST, $queue);
			        curl_setopt($ch, CURLOPT_POSTFIELDS, $queue);
			        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
			        curl_setopt($ch, CURLOPT_HEADER, TRUE);
			        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
					curl_setopt($ch, CURLOPT_CAINFO, $server_path.'certs/cacert.pem');
			        
			        //add handle
					curl_multi_add_handle($mh, $ch);
					
					$ch_array_key = (int)$ch;

			        $outstanding_requests[$ch_array_key] = array(
			            'url' => $request_url,
			            'callback' => 'on_request_done',
			            'user_data' => array($subscriber_id, $queue, $request_url, $http_headers, $this->CampaignID)
			        );
	            }
				
				$active = null;				
				//execute the handles
				do 
				{
				    $mrc = curl_multi_exec($mh, $active);
				    usleep(1);
				    
				    while ($info=curl_multi_info_read($mh))
		            {	
		            	$ch = $info['handle'];
			            $ch_array_key = (int)$ch;
			            
			            $request = $outstanding_requests[$ch_array_key];
			
			            $url = $request['url'];
			            $content = curl_multi_getcontent($ch);
			            $callback = $request['callback'];
			            $user_data = $request['user_data'];
			            
			            call_user_func($callback, $content, $url, $ch, $user_data);
			            
			            unset($outstanding_requests[$ch_array_key]);
			            
			            curl_multi_remove_handle($mh, $ch);
				    }
				}
				while ($mrc == CURLM_CALL_MULTI_PERFORM || ($active && $mrc == CURLM_OK));
				
				curl_multi_close($mh);
	            				
				//delete emails from queue
				$in_id = implode(',', $id_array);
				$q4 = 'UPDATE queue SET sent = 1, query_str = NULL WHERE id IN ('.$in_id.')';
				mysqli_query($mysqli, $q4);
				
				//increment recipients number in campaigns table
				$q5 = 'UPDATE campaigns SET recipients = recipients+'.count($id_array).' WHERE id = '.$this->CampaignID;
				mysqli_query($mysqli, $q5);
				
				$id_array = array();
				
				//throttling
				$time_taken = microtime() - $time_started;
				$usleep = ceil((1 - $time_taken) * 1000000);
				if($time_taken < 1) usleep($usleep);
	        }
        }
        
        else
        {
        	//Get server path
        	$server_path_array2 = explode('includes/', $_SERVER['SCRIPT_FILENAME']);
		    $server_path2 = $server_path_array2[0];
		    if(count($server_path_array2)==1) $server_path2 = $server_path;
	    
	        // Prepare curl.
	        $cr = curl_init();
	        curl_setopt($cr, CURLOPT_URL, $request_url);
	        curl_setopt($cr, CURLOPT_POST, $query_str);
	        curl_setopt($cr, CURLOPT_POSTFIELDS, $query_str);
	        curl_setopt($cr, CURLOPT_HTTPHEADER, $http_headers);
	        curl_setopt($cr, CURLOPT_HEADER, TRUE);
	        curl_setopt($cr, CURLOPT_RETURNTRANSFER, TRUE); 
	        curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($cr, CURLOPT_CAINFO, $server_path2.'certs/cacert.pem');
	
	        // Make the request and fetch response.
	        $response = curl_exec($cr);
	
	        // Separate header and content.
	        $tmpar = explode("\r\n\r\n", $response, 2);
	        $response_http_headers = $tmpar[0];
	        $response_content = $tmpar[1];
	
	        // Parse the http status code.
	        $tmpar = explode(" ", $response_http_headers, 3);
	        $response_http_status_code = $tmpar[1];
	        
	        //increment recipients number in campaigns table
	        if($this->CampaignID != '')
	        {
				$q5 = 'UPDATE campaigns SET recipients = recipients+1 WHERE id = '.$this->CampaignID;
				mysqli_query($mysqli, $q5);
			}
	    }
    }

    //***********************************************************************
    // Name: send_mail
    // Description:
    //    Send mail using amazonSES. Provide $header, $subject,
    //    $body appropriately. The $recipients and $from are mostly expe-
    //    rimental and unneccessary as documented in the SES api.
    //
    //    Return an array in the form -
    //        array(http_status_code, response_content)
    //    if the http_status_code is something other than "200", then
    //    the response_content is an error message parsed from the response.
    //***********************************************************************
    
    public function
    send_mail
    ($header, $subject, $body,
     $recipients=FALSE, $from=FALSE) {
        // Make sure that there is a blank line between header and body.
        $raw_mail = rtrim($header, "\r\n")."\n\n".$body;

        // Prepare query.
        //*********************************************************//
        $query = array();
        $query["Action"] = "SendRawEmail";

        // Add optional Destination.member.N request parameter.
        if ($recipients) {
            $mcnt = 1;
            foreach ($recipients as $recipient) {
                $query["Destinations.member.{$mcnt}"] = $recipient;
                $mcnt += 1;
            }
        }

        // Add optional Source parameter.
        if ($from)
            { $query["Source"] = $from; }

        // Add mail data.
        $query["RawMessage.Data"] = base64_encode($raw_mail);
        //*********************************************************//

        // Send the mail and forward the result array to the caller.
        return $this->make_request($query);
    }

    public function
    request_verification
    ($email_address) {
        $query = array();

        $query["Action"] = "VerifyEmailAddress";
        $query["EmailAddress"] = $email_address;

        return $this->make_request($query);
    }
}

/* End of file */
