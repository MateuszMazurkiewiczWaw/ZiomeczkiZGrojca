<?php
include("includes/header.php");

if (isset($_POST['cancel'])) {
    header("Location: settings.php");
}

if (isset($_POST['close_account'])) {
    $close_query = mysqli_query($con, "UPDATE users SET user_closed = 'yes' WHERE username = '$userLoggedIn'");
    session_destroy();
    header("Location: register.php");
}

?>

<div class="main_column column">

    <h4>Zamknij konto</h4>

    Czy jestes pewien ze chcesz zawiesic swoje konto?<br><br>
    Zamkniecie Twojego konta sprawi ze Twoj profil oraz wszystkie aktywnosci w stosunku do innych Ziomeczkow zostana
    ukryte i beda niedostepne.<br><br>
    Mozesz odwiesic swoje konto kazdej chwili poprzez ponowne zalogowanie sie.<br><br>

    <form action="close_account.php" method="POST">
        <input type="submit" name="close_account" id="close_account" value="Potwierdzam zamkniecie!" class="danger setting_submit">
        <input type="submit" name="update_details" id="cancel" value="Anuluj!" class="info setting_submit">
    </form>


</div>

