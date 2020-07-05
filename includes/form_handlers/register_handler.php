<?php

//Declaring variables to prevent errors
$fname = "";
$lname = "";
$email = "";
$email2 = "";
$password = "";
$password2 = "";
$date = "";
$error_array = array(); //Holds error messages

if (isset($_POST['register_button'])) {

    //First name
    $fname = strip_tags($_POST['reg_fname']); //Remove HTML tags
    $fname = str_replace(' ', '', $fname);
    $fname = ucfirst(strtolower($fname)); //Uppercase fist letter
    $_SESSION['reg_fname'] = $fname;

    //Last name
    $lname = strip_tags($_POST['reg_lname']);
    $lname = str_replace(' ', '', $lname);
    $lname = ucfirst(strtolower($lname));
    $_SESSION['reg_lname'] = $lname;

    //Email
    $email = strip_tags($_POST['reg_email']);
    $email = str_replace(' ', '', $email);
    $email = ucfirst(strtolower($email));
    $_SESSION['reg_email'] = $email;

    //Email2
    $email2 = strip_tags($_POST['reg_email2']);
    $email2 = str_replace(' ', '', $email2);
    $email2 = ucfirst(strtolower($email2));
    $_SESSION['reg_email2'] = $email2;

    //Password
    $password = strip_tags($_POST['reg_password']);

    //Password2
    $password2 = strip_tags($_POST['reg_password2']);

    $date = date("Y-m-d");

    if ($email == $email2) {

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = filter_var($email, FILTER_VALIDATE_EMAIL);

            $e_check = mysqli_query($con, "SELECT email FROM users WHERE email = '$email'");

            $num_rows = mysqli_num_rows($e_check);

            if ($num_rows > 0) {
                array_push($error_array, "Podany adres Email jest juz uzywany<br>");
            }
        } else {
            array_push($error_array, "Niepoprawny format adresu Email<br>");
        }
    } else {
        array_push($error_array, "Adresy Email nie sa takie same<br>");
    }

    if (strlen($fname) > 25 || strlen($fname) < 2) {
        array_push($error_array, "Twoje imie musi miec wiecej 2 i mniej niz 25 znakow<br>");
    }

    if (strlen($lname) > 25 || strlen($lname) < 2) {
        array_push($error_array, "Twoje nazwisko musi miec wiecej 2 i mniej niz 25 znakow<br>");
    }

    if ($password != $password2) {
        array_push($error_array, "Hasla nie sa zgodne ze soba<br>");
    } else {
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            array_push($error_array, "Twoje haslo moze zawierac jedynie litery oraz cyfry<br>");
        }
    }

    if (strlen($password) > 30 || strlen($password) < 5) {
        array_push($error_array, "Twoje haslo musi miec wiecej 5 i mniej niz 30 znakow<br>");
    }

    if (empty($error_array)) {
        $password = md5($password); //Encrypted password before sending to database

        $username = strtolower($fname."_".$lname);
        $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username= '$username'");

        $i = 0;
        while (mysqli_num_rows($check_username_query) != 0) {
            $i++;
            $username = $username."_".$i;
            $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username= '$username'");
        }

        $rand = rand(1, 2);

        if($rand == 1) {
            $profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
        } elseif ($rand == 2) {
            $profile_pic = "assets/images/profile_pics/defaults/head_emerald.png";
        }

        $query = mysqli_query($con, "INSERT INTO users 
            (id, first_name, last_name, username, email, password, signup_date, profile_pic, num_posts, num_likes, user_closed, friend_array)
            VALUES ('', '$fname', '$lname', '$username', '$email', '$password', '$date', '$profile_pic', '0', '0', 'no', ',')");
        array_push($error_array, "<span style='color: #14C800'>Wszystko gotowe! Mozesz korzystac z serwisu!</span><br>");

        //Clear session values
        $_SESSION['reg_fname'] = "";
        $_SESSION['reg_lname'] = "";
        $_SESSION['reg_email'] = "";
        $_SESSION['reg_email2'] = "";
    }
}

?>

