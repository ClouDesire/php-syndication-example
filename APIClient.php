<?php

class CloudesireAPIClient {
    private $token;
    private $baseUrl;
    private $username;
    private $password;
    
    //client constructor
    function __construct($baseUrl, $username, $password) {
        
        if ($baseUrl && $username && $password) {
            $this->baseUrl  = $baseUrl;
            $this->username = $username;
            $this->password = $password;
        } else throw new Exception('baseurl, username, password cannot be empty');
        
        $token = $this->APILogin();
        
        if (!$token)
            throw new Exception('invalid API credentials');
        else $this->token = $token;
    }
    
    //Open CURL connection
    private function CURLConnection($url, $curl_options) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        
        if (is_array($curl_options) && count($curl_options)) {
            foreach($curl_options as $opt_name => $opt_value) {
                curl_setopt($ch, $opt_name, $opt_value);
            }
        }
        
        $result = curl_exec($ch);
        
        $curl_error = curl_error($ch);
        
        curl_close($ch); 
        
        $error = ($curl_error != '' || $result === false);
        
        return array(
            'curl_error_string' => $curl_error,
            'error' => $error,
            'result' => $result
        );
    }
    
    //builds the CURL headers array
    private function CURLHeaders($isJson) {
        $headers = array();
        
        if ($isJson)
            $headers[] = 'Content-Type: application/json';
        
        $headers[] = 'CMW-Auth-Token: ' . $this->token;
        $headers[] = 'CMW-Auth-User: '. $this->username;
        
        return $headers;
    }
    
    //performs a POST or PATCH via CURL
    private function PostOrPatchData($url, $data, $mode) {
        
        //additional CURL options
        $curl_options = array(
            CURLOPT_CUSTOMREQUEST => $mode,
            CURLOPT_POSTFIELDS  => json_encode($data),
            CURLOPT_HTTPHEADER  => $this->CURLHeaders(true)
        );
        
        return $this->CURLConnection($url, $curl_options);
    }
    
    //POST data via CURL
    private function PostData($url, $data) {
        return $this->PostOrPatchData($url, $data, 'POST');
    }
    
    //PATCH data via CURL
    private function PatchData($url, $data) {
        return $this->PostOrPatchData($url, $data, 'PATCH');
    }
    
    //chacks CURL results and builds responses
    private function Response($curlResponse) {
        if (!$curlResponse['error']) {
           $response = $curlResponse['response'];
           return json_decode($response);
        } else return false; 
    }
    
    //API authentication
    private function APILogin() {
        $url = $this->baseUrl . '/login?expire=false';
        
        $authString = $this->username . ":" . $this->password;
        
        $curl_options = array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD  => $authString
        );
        
        return $this->Response($this->CURLConnection($url, $curl_options));
     }
    
    
    //provides subcription's instructions
    public function SetSubsctiptionInstructions($entityUrl, $instructions) {
        $url = $this->baseUrl . $entityUrl . "/instructions";
        
        return $this->Response($this->PostData($url, $instructions));
    }

    //provides subcription's endpoints
    public function SetSubsctiptionEndpoints($entityUrl, $endpoints) {
        $url = $this->baseUrl . $entityUrl . "/endpoints";
        
        return $this->Response($this->PostData($url, $endpoints));
    }

    //sets subcription 'deploymentStatus' to DEPLOYED or UNDEPLOYED
    public function UpdateSubsctiptionStatus($entityUrl, $status) {
        $newStatus = array(
           'deploymentStatus' => $status
        );

        $url = $this->baseUrl . $entityUrl;
        
        return $this->Response($this->PatchData($url, $newStatus));
    }

    //gets subscription details
    public function GetSubscription($entityUrl)
    {
        $url = $this->baseUrl . $entityUrl;
        
        $curl_options = array(
            CURLOPT_HTTPHEADER  => $this->CURLHeaders(false)
        );
        
        return $this->Response($this->CURLConnection($url, $curl_options));
    }
}


