<?php
require_once 'config.php';

try {
    $adapter->authenticate();
    $token = $adapter->getAccessToken();
    $db = new DB();
    $db->update_access_token(json_encode($token));

    //write json to file
    if (file_put_contents("token.json", json_encode($token)))
        echo "JSON file created successfully...";
    else
        echo "Oops! Error creating json file...";
    echo "Access token inserted successfully.";
}
catch( Exception $e ){
    echo $e->getMessage() ;
}