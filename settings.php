<?php
include("includes/header.php");
include("includes/form_handlers/settings_handler.php");
?>

<div class="main_column column">

    <h4>Ustawienia Konta</h4>
    <?php
    echo "<img src='" . $user['profile_pic'] . "' id='small_profile_pic'>"
    ?>
    <br/>
    <a href="upload.php">Zaladuj nowe zdjecie profilowe</a><br/><br/><br/>

    Zmodyfikuj ustawienia i kliknij na 'Zaktualizuj wartosci'

    <?php
    $user_data_query = mysqli_query($con, "SELECT first_name, last_name, email FROM users WHERE username = '$userLoggedIn'");
    $row = mysqli_fetch_array($user_data_query);

    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $email = $row['email'];
    ?>


    <form action="settings.php" method="POST">
        Imie: <input type="text" name="first_name" value="<?php echo $first_name; ?>" id="settings_input"><br/>
        Nazwisko: <input type="text" name="last_name" value="<?php echo $last_name; ?>" id="settings_input"><br/>
        Adres email: <input type="text" name="email" value="<?php echo $email; ?>" id="settings_input"><br/>

        <?php echo $message; ?>

        <input type="submit" name="update_details" id="save_details" value="Zaktualizuj wartosci" class="info setting_submit"><br/>
    </form>

    <h4>Zmiana hasla</h4>

    <form action="settings.php" method="POST">
        Stare haslo: <input type="password" name="old_password" id="settings_input"><br/>
        Nowe haslo: <input type="password" name="new_password_1" id="settings_input"><br/>
        Potwierdz nowe haslo: <input type="password" name="new_password_2" id="settings_input"><br/>

        <?php echo $password_message; ?>

        <input type="submit" name="update_password" id="save_details" value="Zaktualizuj haslo" class="info setting_submit"><br/>
    </form>

    <h4>Zamknij / usuj konto</h4>

    <form action="settings.php" method="POST">
        <input type="submit" name="close_account" id="close_account" value="Zamknij konto" class="danger setting_submit"><br/>
    </form>

</div>
















