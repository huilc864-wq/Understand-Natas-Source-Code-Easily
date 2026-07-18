
<?php
    // sry, this is ugly as hell.
    // cheers kaliman ;)
    // - morla

    class Logger{
        private $logFile;   // path to the log file for this logger instance
        private $initMsg;   // message written when logger is created
        private $exitMsg;   // message written when logger is destroyed

        function __construct($file){
            // initialise variables
            $this->initMsg="#--session started--#\n";  // start marker written once
            $this->exitMsg="#--session end--#\n";     // end marker written when object is destroyed
            $this->logFile = "/tmp/natas26_" . $file . ".log";  // log filename includes provided identifier

            // write initial message
            $fd=fopen($this->logFile,"a+");  // open or create file in append mode
            fwrite($fd,$this->initMsg);      // append the start marker
            fclose($fd);                     // close file handle
        }

        function log($msg){
            $fd=fopen($this->logFile,"a+");  // open log file for appending
            fwrite($fd,$msg."\n");           // write the provided message plus newline
            fclose($fd);                     // close file handle
        }

        function __destruct(){
            // write exit message
            $fd=fopen($this->logFile,"a+");  // open log file for appending
            fwrite($fd,$this->exitMsg);      // append the end marker
            fclose($fd);                     // close file handle
        }
    }

    function showImage($filename){
        if(file_exists($filename))         // only output image tag if file exists
            echo "<img src=\"$filename\">";  // display the generated PNG image
    }

    function drawImage($filename){
        $img=imagecreatetruecolor(400,300);  // create a new 400x300 truecolor image
        drawFromUserdata($img);              // draw lines based on GET and cookie data
        imagepng($img,$filename);            // save the image as a PNG file
        imagedestroy($img);                  // free image resources
    }

    function drawFromUserdata($img){
        if( array_key_exists("x1", $_GET) && array_key_exists("y1", $_GET) &&
            array_key_exists("x2", $_GET) && array_key_exists("y2", $_GET)){

            $color=imagecolorallocate($img,0xff,0x12,0x1c);  // create a red color for the line
            imageline($img,$_GET["x1"], $_GET["y1"],
                            $_GET["x2"], $_GET["y2"], $color);  // draw the line from GET params
        }

        if (array_key_exists("drawing", $_COOKIE)){
            $drawing=unserialize(base64_decode($_COOKIE["drawing"]));  // load saved drawing history from cookie
            if($drawing)
                foreach($drawing as $object)
                    if( array_key_exists("x1", $object) &&
                        array_key_exists("y1", $object) &&
                        array_key_exists("x2", $object) &&
                        array_key_exists("y2", $object)){

                        $color=imagecolorallocate($img,0xff,0x12,0x1c);  // create the same red color
                        imageline($img,$object["x1"],$object["y1"],
                                $object["x2"] ,$object["y2"] ,$color);  // draw each saved line

                    }
        }
    }

    function storeData(){
        $new_object=array();              // prepare a new object to store the current line

        if(array_key_exists("x1", $_GET) && array_key_exists("y1", $_GET) &&
            array_key_exists("x2", $_GET) && array_key_exists("y2", $_GET)){
            $new_object["x1"]=$_GET["x1"];  // save x1 coordinate from GET
            $new_object["y1"]=$_GET["y1"];  // save y1 coordinate from GET
            $new_object["x2"]=$_GET["x2"];  // save x2 coordinate from GET
            $new_object["y2"]=$_GET["y2"];  // save y2 coordinate from GET
        }

        if (array_key_exists("drawing", $_COOKIE)){
            $drawing=unserialize(base64_decode($_COOKIE["drawing"]));  // restore existing drawing array if present
        }
        else{
            // create new array
            $drawing=array();              // start with an empty list of drawing objects
        }

        $drawing[]=$new_object;           // append the current line object to the drawing history
        setcookie("drawing",base64_encode(serialize($drawing)));  // store drawing history back in cookie
    }
?>

<h1>natas26</h1>
<div id="content">

Draw a line:<br>
<form name="input" method="get">
X1<input type="text" name="x1" size=2>  <!-- x coordinate of the line start -->
Y1<input type="text" name="y1" size=2>  <!-- y coordinate of the line start -->
X2<input type="text" name="x2" size=2>  <!-- x coordinate of the line end -->
Y2<input type="text" name="y2" size=2>  <!-- y coordinate of the line end -->
<input type="submit" value="DRAW!">     <!-- submit the form to redraw the image -->
</form>

<?php
    session_start();  // start a session so session_id() can be used

    if (array_key_exists("drawing", $_COOKIE) ||
        (   array_key_exists("x1", $_GET) && array_key_exists("y1", $_GET) &&
            array_key_exists("x2", $_GET) && array_key_exists("y2", $_GET))){
        $imgfile="img/natas26_" . session_id() .".png";  // image file name is unique per session
        drawImage($imgfile);  // generate the image file with current and saved lines
        showImage($imgfile);  // output the image in the browser
        storeData();          // save the current line into the cookie history
    }

?>
