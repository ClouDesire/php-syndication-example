<?php

class CloudesireAPIclient {
    private $_token;
    private $_baseUrl;
    private $_username;
    private $_password;
    
    //client constructor
    function __construct($baseUrl, $username, $password) {
        
        $this->_baseUrl  = $baseUrl;
        $this->_username = $username;
        $this->_password = $password;
        $this->_token    = $this->APILogin();
    }
    
    //Open CURL connection
    private function CURLconnection($url, $curl_options) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        
        if (is_array($curl_options) && count($curl_options)) {
            foreach($curl_options as $opt_name => $opt_value) {
                curl_setopt($ch, $opt_name, $opt_value);
            }
        }
        
        $result = curl_exec($ch);

        curl_close($ch); 
        
        return $result;
    }
    
    //builds the CURL headers array
    private function CURLheaders($isJson) {
        $headers = array();
        
        if ($isJson)
            $headers[] = 'Content-Type: application/json';
        
        $headers[] = 'CMW-Auth-Token: ' . $this->_token;
        $headers[] = 'CMW-Auth-User: '. $this->_username;
        
        return $headers;
    }
    
    //performs a POST or PATCH via CURL
    private function PostOrPatchData($url, $data, $mode) {
        
        //additional CURL options
        $curl_options = array(
            CURLOPT_CUSTOMREQUEST => $mode,
            CURLOPT_POSTFIELDS  => json_encode($data),
            CURLOPT_HTTPHEADER  => $this->CURLheaders(true)
        );
        
        return $this->CURLconnection($url, $curl_options);
    }
    
    //POST data via CURL
    private function PostData($url, $data) {
        return $this->PostOrPatchData($url, $data, 'POST');
    }
    
    //PATCH data via CURL
    private function PatchData($url, $data) {
        return $this->PostOrPatchData($url, $data, 'PATCH');
    }
    
    //API authentication
    private function APILogin() {
        
        $url = $this->_baseUrl . '/login?expire=false';
        
        $authString = $this->_username . ":" . $this->_password;
        
        $curl_options = array(
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD  => $authString
        );
        
        $result = $this->CURLconnection($url, $curl_options);

        if ($result) {
            return json_decode($result);
        } else return false;
    }

    //provides subcription's instructions
    public function SetSubsctiptionInstructions($entityUrl, $instructions) {
        
        if (!$this->_token) {
            return false;
        }

        $url = $this->_baseUrl . $entityUrl . "/instructions";
        
        return $this->PostData($url, $instructions);
    }

    //provides subcription's endpoints
    public function SetSubsctiptionEndpoints($entityUrl, $endpoints) {
        
        if (!$this->_token) {
            return false;
        }

        $url = $this->_baseUrl . $entityUrl . "/endpoints";
        
        return $this->PostData($url, $endpoints);
    }

    //sets subcription 'deploymentStatus' to DEPLOYED or UNDEPLOYED
    public function UpdateSubsctiptionStatus($entityUrl, $status) {
        
        if (!$this->_token) {
            return false;
        }

        $newStatus = array(
           'deploymentStatus' => $status
        );

        $url = $this->_baseUrl . $entityUrl;
        
        return $this->PatchData($url, $newStatus);
    }

    //gets subscription details
    public function GetSubscription($entityUrl)
    {
        if (!$this->_token) {
            return false;
        }
        
        $url = $this->_baseUrl . $entityUrl;
        
        $curl_options = array(
            CURLOPT_HTTPHEADER  => $this->CURLheaders(false)
        );
        
        return json_decode($this->CURLconnection($url, $curl_options));
    }
}


