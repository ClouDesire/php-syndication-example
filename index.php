<?php
//Cloudesire API client to be used
require 'APIClient.php';

//Cloudesire API endpoint

//STAGING VENDORS ENVIRONMENT
define('API_BASE_URL', 'https://staging-vendors.cloudesire.com/api/');

//PRODUCTION ENVIRONMENT
// define('API_BASE_URL', 'https://backend.cloudesire.com/api/');

//specify your vendor credentials here
//remember to change them when you are in the production environment
define('API_USERNAME', 'your-username');
define('API_PASSWORD', 'your-password');

//read POST data
$event = json_decode(file_get_contents('php://input'), true);

//any events received?
if (is_array($event)) {
    
    $entity    = $event['entity'];
    $entityURL = $event['entityUrl'];
    $id        = $event['id'];
    $type      = $event['type'];
    
    if ($id) {
        
        //I'm focusing only on Subscriptions' related events
        
        if ($entity == 'Subscription' && ($type == 'MODIFIED' || $type == 'CREATED')) {
            
            //instantiate the Cloudesire API Client
            $apiClient = new CloudesireAPIClient(API_BASE_URL, API_USERNAME, API_PASSWORD);

            //invoke the Cloudesire API in order to retrieve the subscription details
            //subscription data will be returned as object
            $subscription_data = $apiClient->GetSubscription($entityURL);
                
            if (is_object($subscription_data)) {
		
                $paid = $subscription->paid;
                $status = $subscription->deploymentStatus;

                
                //unprovisioning
                if ($status == 'UNDEPLOY_SENT') {

                    /*
                    NOTE:
                    here you should disable the related tenant information in your database...
                    */

                    //calls Cloudesire API in order to set the Subscription to 'undeployed'
                    $responseUndeployed = $apiClient->UpdateSubsctiptionStatus($entityURL, 'UNDEPLOYED');
                    //here you should manage exceptions, checking if $responseUndeployed is not false...

                }

                //provisioning
                if ($status == 'PENDING' && $paid == true) {

                    /*
                    NOTE:
                    here you should retrieve the customer data and create his tenant in your database
                    */

                    //endpoints need to be provided in this way
                    $endpoints[] = array(
                        'endpoint' => 'https://www.yourapp.com/login', //please provide your app endpoint URL here
                        'description' => 'Login Page',
                        'category'=> 'APP'
                    );
                    
                    //calls Cloudesire API in order to provide endpoints
                    $responseEndpoints = $apiClient->SetSubsctiptionEndpoints($entityURL, $endpoints);
                    //here you should manage exceptions, checking if $responseEndpoints is not false...


                    //instructions need to be provided in this way
                    $instructions = array(
                        'en' => 'Demo English Instructions',
                        'it' => 'Istruzioni esemplificative in italiano'
                    );
                    //calls Cloudesire API in order to provide instructions
                    $responseInstructions = $apiClient->SetSubsctiptionInstructions($entityURL, $instructions);
                    //here you should manage exceptions, checking if $responseInstructions is not false...


                    //calls Cloudesire API in order to provide set the Subscription to 'deployed'
                    $responseDeployed = $apiClient->UpdateSubsctiptionStatus($entityURL, 'DEPLOYED');
                    //here you should manage exceptions, checking if $responseDeployed is not false...

                }
                    
            } else {
              header('HTTP/ 433 no subscription data received');
              exit();
            }  
        } else {
            //I'm ignoring (on purpose) all other events.... 
        }
    }
    else {
        header('HTTP/ 433 identifier missing');
        exit();
    }
    
    //default answer
    header('HTTP/1.1 204 - No content');
    
}

?>
