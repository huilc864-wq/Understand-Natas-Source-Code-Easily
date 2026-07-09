<?php

function debug($msg) { /* {{{ */
    if(array_key_exists("debug", $_GET)) {     //if URL or $_GET has debug parameter, print debug messages
        print "DEBUG: $msg<br>";
    }
}
/* }}} */
function print_credentials() { /* {{{ */        
    if($_SESSION and array_key_exists("admin", $_SESSION) and $_SESSION["admin"] == 1) {
    print "You are an admin. The credentials for the next level are:<br>";
    print "<pre>Username: natas21\n";        /  /print credentials for admin user if the session variable 'admin' is set to 1
    print "Password: <censored></pre>";
    } else {
    print "You are logged in as a regular user. Login as an admin to retrieve credentials for natas21.";
    }
}
/* }}} */

/* we don't need this */
function myopen($path, $name) {
    //debug("MYOPEN $path $name");     //this function's job is to just return true, as we don't need to do anything special when opening a session
    return true;
}

/* we don't need this */
function myclose() {
    //debug("MYCLOSE");
    return true;
}

function myread($sid) {   //this function reads the session data from a file based on the session ID
    debug("MYREAD $sid");  
    if(strspn($sid, "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM-") != strlen($sid)) { //check if the session ID contains only alphanumeric characters and hyphens
    debug("Invalid SID");
        return "";
    }
    $filename = session_save_path() . "/" . "mysess_" . $sid; //get to session folder and append /mysess_ to the session ID to get the session file name
    if(!file_exists($filename)) {  //check if the session file exists, if not return an empty string
        debug("Session file doesn't exist");
        return "";
    }
    debug("Reading from ". $filename);
    $data = file_get_contents($filename);
    $_SESSION = array();   //this line initializes the $_SESSION array to an empty array, which means that any existing session data will be cleared before reading the new data from the session file
    foreach(explode("\n", $data) as $line) {
        debug("Read [$line]");
    $parts = explode(" ", $line, 2);
    if($parts[0] != "") $_SESSION[$parts[0]] = $parts[1];
    }
    return session_encode() ?: "";
}

function mywrite($sid, $data) {
    // $data contains the serialized version of $_SESSION
    // but our encoding is better
    debug("MYWRITE $sid $data");
    // make sure the sid is alnum only!!
    if(strspn($sid, "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM-") != strlen($sid)) {
    debug("Invalid SID");
        return;
    }
    $filename = session_save_path() . "/" . "mysess_" . $sid; // append /mysess_ to the session ID to get the session file name
    $data = "";
    debug("Saving in ". $filename);
    ksort($_SESSION);         //sort the session variables by key in ascending order, so that the session data is always written in a consistent order
    foreach($_SESSION as $key => $value) {
        debug("$key => $value");
        $data .= "$key $value\n";
    }
    file_put_contents($filename, $data);
    chmod($filename, 0600);        //setting the file permissions to 0600, which means that only the owner of the file can read and write to it
    return true;
}

/* we don't need this */
function mydestroy($sid) {
    //debug("MYDESTROY $sid");  //it means that we don't need to do anything special when destroying a session, so we just return true
    return true;
}
/* we don't need this */
function mygarbage($t) {
    //debug("MYGARBAGE $t");  //this function returns true, it means that we don't need to do any garbage collection for the session files
    return true;
}

session_set_save_handler(
    "myopen",
    "myclose",
    "myread",
    "mywrite",             
    "mydestroy", //use custom session handlers for reading and writing session data to files
    "mygarbage");
session_start();

if(array_key_exists("name", $_REQUEST)) {  //this code checks if the "name" parameter is present in the request (either GET or POST). If it is, it sets the session variable "name" to the value of the "name" parameter and prints a debug message indicating that the name has been set.
    $_SESSION["name"] = $_REQUEST["name"];  //set the session variable "name" to the value of the "name" parameter in the request
    debug("Name set to " . $_REQUEST["name"]);
}

print_credentials();

$name = "";
if(array_key_exists("name", $_SESSION)) {
    $name = $_SESSION["name"];  //its job is to retrieve the value of the "name" session variable and store it in the $name variable. If the "name" session variable is not set, it will default to an empty string.
}

?>
