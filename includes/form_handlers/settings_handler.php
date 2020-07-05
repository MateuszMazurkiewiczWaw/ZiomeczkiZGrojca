<?php
if (isset($_POST['update_details'])) {

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    $email_check = mysqli_query($con, "SELECT * FROM users WHERE email = '$email'");
    $row = mysqli_fetch_array($email_check);
    $matched_user = $row['username'];

    if (($matched_user == "") || ($matched_user == $userLoggedIn)) {
        $message = "Wartosci zaktualizowane<br><br>";

        $query = mysqli_query($con, "UPDATE users SET first_name = '$first_name', last_name = '$last_name', email = '$email' WHERE username = '$userLoggedIn'");
    } else {
        $message = "Ten adres email jest juz w uzyciu!<br><br>";
    }
} else {
    $message = "";
}

if (isset($_POST['update_password'])) {

    $old_password = strip_tags($_POST['old_password']);
    $new_password_1 = strip_tags($_POST['new_password_1']);
    $new_password_2 = strip_tags($_POST['new_password_2']);

    $password_query = mysqli_query($con, "SELECT password FROM users WHERE username = '$userLoggedIn'");
    $row = mysqli_fetch_array($password_query);
    $db_password = $row['password'];

    if (md5($old_password) == $db_password) {

        if ($new_password_1 == $new_password_2) {

            if (strlen($new_password_2) <= 4) {
                $password_message = "Twoje haslo musi miec wiecej niz 4 znaki!<br><br>";
            } else {
                $new_password_md5 = md5($new_password_1);
                $password_query = mysqli_query($con, "UPDATE users SET password = '$pew_password_md5' WHERE username = '$userLoggedIn'");
                $password_message = "Haslo uzytkownika zostalo zmienione!<br><br>";
            }

        } else {
            $password_message = "Nowe hasla musza byc zgodne!<br><br>";
        }
    } else {
        $password_message = "Stare haslo jest niepoprawne!<br><br>";
    }
} else {
    $password_message = "";
}

if (isset($_POST['close_account'])) {
    header("Location: close_account.php");
}



?>