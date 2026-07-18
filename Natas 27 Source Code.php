<?php

// Level author and difficulty (comment from the original developer)
// morla / 10111

// The database is reset every 5 minutes.
// This prevents permanent changes to the users table.

// Database schema:
//
// CREATE TABLE users (
//     username VARCHAR(64),
//     password VARCHAR(64)
// );
//
// Both username and password can store up to 64 characters.


/*
|--------------------------------------------------------------------------
| Function: checkCredentials()
|--------------------------------------------------------------------------
| Checks whether the supplied username AND password match a record.
*/
function checkCredentials($link,$usr,$pass){

    // Escape special characters to prevent SQL Injection.
    $user = mysqli_real_escape_string($link, $usr);
    $password = mysqli_real_escape_string($link, $pass);

    // Look for a user whose username AND password match.
    // NOTE: Uses the raw (escaped) input $user without trimming or truncation.
    //       This is a different canonical form than createUser() uses when inserting.
    $query = "SELECT username FROM users
              WHERE username='$user'
              AND password='$password'";

    // Execute the SQL query.
    $res = mysqli_query($link, $query);

    // If at least one row exists, credentials are correct.
    if(mysqli_num_rows($res) > 0){
        return True;
    }

    // Otherwise login failed.
    return False;
}


/*
|--------------------------------------------------------------------------
| Function: validUser()
|--------------------------------------------------------------------------
| Checks whether a username already exists.
*/
function validUser($link,$usr){

    // Escape special characters.
    $user = mysqli_real_escape_string($link, $usr);

    // Search for the username.
    // NOTE: Again uses the raw (escaped) input without trimming or truncation.
    //       validUser() and checkCredentials() use the same raw form, but createUser()
    //       will store a *truncated* form. This mismatch is critical.
    $query = "SELECT * FROM users
              WHERE username='$user'";

    // Execute query.
    $res = mysqli_query($link, $query);

    // Make sure query executed successfully.
    if($res){

        // If one or more rows exist,
        // then the username already exists.
        if(mysqli_num_rows($res) > 0){
            return True;
        }
    }

    // Username not found.
    return False;
}


/*
|--------------------------------------------------------------------------
| Function: dumpData()
|--------------------------------------------------------------------------
| Returns every column belonging to the user.
|--------------------------------------------------------------------------
*/
function dumpData($link,$usr){

    // Remove whitespace from beginning/end of username.
    // BUG/CONFUSION: this call does nothing because trim() return value is ignored.
    //                The next line *does* use trim($usr) correctly, so this line is redundant.
    trim($usr);

    // Escape special characters.
    // NOTE: Here they *do* trim before escaping, so dumpData() uses a trimmed canonical form.
    //       That is different from validUser()/checkCredentials() which used the raw form.
    $user = mysqli_real_escape_string($link, trim($usr));

    // Fetch all columns for this user.
    // Because dumpData() trims but validUser() did not, the application can take different
    // branches (create vs authenticate) while later lookups use a different canonical form.
    $query = "SELECT * FROM users
              WHERE username='$user'";

    $res = mysqli_query($link, $query);

    if($res){

        if(mysqli_num_rows($res) > 0){

            // Read each returned row.
            while($row = mysqli_fetch_assoc($res)){

                // fetch_assoc() returns an associative array.
                // Example:
                //
                // Array
                // (
                //     [username] => admin
                //     [password] => secret
                // )

                // Old code:
                // return print_r($row);

                // print_r(..., true) returns the string
                // instead of printing it immediately.
                return print_r($row, true);
            }
        }
    }

    return False;
}


/*
|--------------------------------------------------------------------------
| Function: createUser()
|--------------------------------------------------------------------------
| Creates a new user.
|--------------------------------------------------------------------------
*/
function createUser($link, $usr, $pass){

    // Reject usernames with leading/trailing whitespace.
    //
    // Example:
    // "admin " != "admin"
    //
    // trim() removes whitespace.
    if($usr != trim($usr)){

        echo "Go away hacker";

        return False;
    }

    // Username length is limited to 64 characters.
    // IMPORTANT: createUser truncates the username to 64 chars BEFORE inserting.
    //            validUser() and checkCredentials() do NOT truncate before checking.
    //            This means two different raw inputs can map to the same stored username.
    $user = mysqli_real_escape_string(
        $link,
        substr($usr,0,64)
    );

    // Password also limited to 64 characters.
    $password = mysqli_real_escape_string(
        $link,
        substr($pass,0,64)
    );

    // Insert new user into database.
    $query = "INSERT INTO users(username,password)
              VALUES('$user','$password')";

    mysqli_query($link,$query);

    // If at least one row was inserted,
    // account creation succeeded.
    if(mysqli_affected_rows($link) > 0){
        return True;
    }

    return False;
}


/*
|--------------------------------------------------------------------------
| Main Program
|--------------------------------------------------------------------------
*/

// Check whether both username and password
// were submitted by the user.
if(
    array_key_exists("username", $_REQUEST) &&
    array_key_exists("password", $_REQUEST)
){

    // Connect to MySQL server.
    $link = mysqli_connect(
        'localhost',
        'natas27',
        '<censored>'
    );

    // Select database.
    mysqli_select_db($link,'natas27');

    // Does this username already exist?
    // NOTE: validUser() uses the raw input (escaped) and does NOT trim or truncate.
    //       If validUser() returns False, the code will call createUser().
    if(validUser($link,$_REQUEST["username"])){

        // Existing user.
        // Verify password.
        if(checkCredentials(
            $link,
            $_REQUEST["username"],
            $_REQUEST["password"]
        )){

            // Login successful.
            echo "Welcome "
                 . htmlentities($_REQUEST["username"])
                 . "!<br>";

            echo "Here is your data:<br>";

            // Retrieve user data.
            // NOTE: dumpData() trims before lookup, so it may find a different row
            //       than the one validUser() considered when deciding to create vs login.
            $data = dumpData(
                $link,
                $_REQUEST["username"]
            );

            // Escape HTML before printing.
            print htmlentities($data);
        }

        else{

            // Username exists but password is wrong.
            echo "Wrong password for user: "
                 . htmlentities($_REQUEST["username"])
                 . "<br>";
        }

    }

    else{

        // Username doesn't exist.

        // Create a new account.
        // Because createUser() truncates to 64 chars, the inserted username may
        // collide with an existing stored username even though validUser() thought
        // the username did not exist (different canonical forms).
        if(createUser(
            $link,
            $_REQUEST["username"],
            $_REQUEST["password"]
        )){

            echo "User "
                 . htmlentities($_REQUEST["username"])
                 . " was created!";
        }

    }

    // Close database connection.
    mysqli_close($link);

}

else{

// If no username/password were submitted,
// display the login form.
?>

<form action="index.php" method="POST">

Username:
<input name="username"><br>

Password:
<input name="password" type="password"><br>

<input type="submit" value="login" />

</form>

<?php
}
?>
