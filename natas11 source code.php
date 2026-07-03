<?

$defaultdata = array( "showpassword"=>"no", "bgcolor"=>"#ffffff"); // default settings that will make cookie

function xor_encrypt($in) { // defiing XOR encrypt function
    $key = '<censored>'; // secret key
    $text = $in; // input text that will be XOR'd
    $outText = ''; 

    // Iterate through each character to XOR
    for($i=0;$i<strlen($text);$i++) {
    $outText .= $text[$i] ^ $key[$i % strlen($key)]; // .= concates string with $outText
    }

    return $outText;
}

function loadData($def) { // calling $def = $defaultdata
    global $_COOKIE;
    $mydata = $def; // if no cookie then $defaultdata will be cookie
    if(array_key_exists("data", $_COOKIE)) { // checks if $_COOKIE has a variable named data
    $tempdata = json_decode(xor_encrypt(base64_decode($_COOKIE["data"])), true); // decrypting cookie
    if(is_array($tempdata) && array_key_exists("showpassword", $tempdata) && array_key_exists("bgcolor", $tempdata)) { //checking decrypted cookie
        if (preg_match('/^#(?:[a-f\d]{6})$/i', $tempdata['bgcolor'])) { // ensures if tempdata has hex color code
        $mydata['showpassword'] = $tempdata['showpassword']; // $tempdata = $mydata
        $mydata['bgcolor'] = $tempdata['bgcolor'];
        }
    }
    }
    return $mydata;  // decrypted cookie
}

function saveData($d) {
    setcookie("data", base64_encode(xor_encrypt(json_encode($d)))); // data = $d, encrypting cookie
}

$data = loadData($defaultdata); // default cookies

if(array_key_exists("bgcolor",$_REQUEST)) {
    if (preg_match('/^#(?:[a-f\d]{6})$/i', $_REQUEST['bgcolor'])) { //updates bgcolor REQUEST
        $data['bgcolor'] = $_REQUEST['bgcolor'];
    }
}

saveData($data);



?>
