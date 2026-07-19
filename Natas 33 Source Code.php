<?php // PHP open tag starts the PHP code block
            // graz XeR, the first to solve it! thanks for the feedback!
            // ~morla
            class Executor{ // define a class named Executor
                private $filename=""; // declare a private property $filename, initial value empty string
                private $signature='adeafbadbabec0dedabada55ba55d00d'; // private MD5 signature value
                private $init=False; // private property $init set to False (boolean false)

                function __construct(){ // constructor method called when a new Executor object is created
                    $this->filename=$_POST["filename"]; // set $filename from POST form input named "filename"
                    if(filesize($_FILES['uploadedfile']['tmp_name']) > 4096) { // check uploaded file size; if over 4096 bytes
                        echo "File is too big<br>"; // print an error message for oversized files
                    }
                    else { // otherwise, file size is within limit
                        if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], "/natas33/upload/" . $this->filename)) { // move uploaded temp file to /natas33/upload/ with the provided filename
                            echo "The update has been uploaded to: /natas33/upload/$this->filename<br>"; // success message showing destination path
                            echo "Firmware upgrad initialised.<br>"; // message indicating firmware update initialized
                        }
                        else{ // if moving the uploaded file failed
                            echo "There was an error uploading the file, please try again!<br>"; // upload error message
                        }
                    }
                }

                function __destruct(){ // destructor method called when the Executor object is destroyed at end of script
                    // upgrade firmware at the end of this script

                    // "The working directory in the script shutdown phase can be different with some SAPIs (e.g. Apache)."
                    chdir("/natas33/upload/"); // change the current working directory to /natas33/upload/
                    if(md5_file($this->filename) == $this->signature){ // compute MD5 of the uploaded file and compare to expected signature
                        echo "Congratulations! Running firmware update: $this->filename <br>"; // success message before executing the file
                        passthru("php " . $this->filename); // run the uploaded file as a PHP script using passthru
                    }
                    else{ // if MD5 does not match expected signature
                        echo "Failur! MD5sum mismatch!<br>"; // failure message due to checksum mismatch
                    }
                }
            }
        ?>