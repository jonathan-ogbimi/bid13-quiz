<?php
function isValidPhoneNumber($phone_number, $customer_id, $api_key) {
    $api_url = "https://rest-ww.telesign.com/v1/phoneid/$phone_number";
    /* 
    # Change content type to application json
    */
    $headers = [
        "Authorization: Basic " . base64_encode("$customer_id:$api_key"),
        "Content-Type: application/json"
    ];
    /*
    Initialize empty json payload
    Use HTTP POST instead of GET
    POST json payload
    */
    $jsonData = '{}'; // Initialize empty json payload
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);// Use HTTP POST
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // POST json payload
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        return false; // API request failed
    }
    
    $data = json_decode($response, true);
    if (!isset($data['phone_type']['description'])) {// fix to check for phone_type not numbering.phone_type
        return false; // Unexpected API response
    }
    
    $valid_types = ["FIXED_LINE", "MOBILE", "VALID"];
    return in_array(strtoupper($data['phone_type']['description']), $valid_types);// fix to use phone_type.description 
}

// Usage example
$phone_number = "1234567890"; // Replace with actual phone number
$customer_id = "your_customer_id";
$api_key = "your_api_key";
$result = isValidPhoneNumber($phone_number, $customer_id, $api_key);
var_dump($result);
