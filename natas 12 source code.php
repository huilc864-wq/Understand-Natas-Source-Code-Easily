<?php

function genRandomString() {
    $length = 10; // max characters length
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    $string = ""; //empty for purpose

    for ($p = 0; $p < $length; $p++) { // goes through every character
        $string .= $characters[mt_rand(0, strlen($characters)-1)]; // appends from random characters
    }

    return $string; // generated random string
}

function makeRandomPath($dir, $ext) { // pure function 
    do {
    $path = $dir."/".genRandomString().".".$ext; // creates random filename then adds / then $dir then adds extension
    } while(file_exists($path)); // if don't goes to do again
    return $path; //path = extension
}

function makeRandomPathFromFilename($dir, $fn) {
    $ext = pathinfo($fn, PATHINFO_EXTENSION); //outputs by checking filename extension = $ext
    return makeRandomPath($dir, $ext); //calls previous function
}

if(array_key_exists("filename", $_POST)) { //if POST has filename
    $target_path = makeRandomPathFromFilename("upload", $_POST["filename"]); //upload is $dir && filename from POST is $fn now (calling previous function)


        if(filesize($_FILES['uploadedfile']['tmp_name']) > 1000) {
        echo "File is too big";
    } else {
        if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
            echo "The file <a href=\"$target_path\">$target_path</a> has been uploaded";
        } else{
            echo "There was an error uploading the file, please try again!";
        }
    }
} else {
?>
