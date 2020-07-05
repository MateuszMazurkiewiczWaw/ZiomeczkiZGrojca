<?php
require 'config/config.php';
require 'includes/form_handlers/register_handler.php';
require 'includes/form_handlers/login_handler.php';
?>

<DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Witaj w serwisie Ziomeczki z Grojca</title>
        <link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="assets/js/register.js"></script>
    </head>
    <body>

    <?php
    if(isset($_POST['register_button'])) {
        echo '
        <script>
        
        $(document).ready(function() {
          $("#first").hide();
          $("#second").show();
        });
        
        </script>
        ';
    }

    ?>


    <div class="wrapper">

        <div class="login_box">

            <div class="login_header">
                <h1>Ziomeczki z Grójca!</h1>
                Zaloguj sie lub zarejestruj ponizej
            </div>

            <div id="first">

                <form action="register.php" method="POST">
                    <input type="email" name="log_email" placeholder="Email" value="<?php
                    if (isset($_SESSION['log_email'])) {
                        echo $_SESSION['log_email'];
                    } ?>" required>
                    <br>
                    <input type="password" name="log_password" placeholder="Haslo">
                    <br>
                    <?php if (in_array("Adres email lub haslo sa niepoprawne<br>", $error_array)) echo "Adres email lub haslo sa niepoprawne<br>" ?>
                    <input type="submit" name="login_button" value="Zaloguj">
                    <br>
                    <a href="#" id="signup" class="signup">Nie masz jeszcze konta? Zarejestruj sie tutaj!</a>
                </form>

            </div>

            <div id="second">

                <form action="register.php" method="POST">
                    <input type="text" name="reg_fname" placeholder="Imie" value="<?php
                    if (isset($_SESSION['reg_fname'])) {
                        echo $_SESSION['reg_fname'];
                    } ?>" required>
                    <br>
                    <?php if (in_array("Twoje imie musi miec wiecej 2 i mniej niz 25 znakow<br>", $error_array)) echo "Twoje imie musi miec wiecej 2 i mniej niz 25 znakow<br>"; ?>

                    <input type="text" name="reg_lname" placeholder="Nazwisko" value="<?php
                    if (isset($_SESSION['reg_lname'])) {
                        echo $_SESSION['reg_lname'];
                    } ?>" required>
                    <br>
                    <?php if (in_array("Twoje nazwisko musi miec wiecej 2 i mniej niz 25 znakow<br>", $error_array)) echo "Twoje nazwisko musi miec wiecej 2 i mniej niz 25 znakow<br>"; ?>


                    <input type="email" name="reg_email" placeholder="Email" value="<?php
                    if (isset($_SESSION['reg_email'])) {
                        echo $_SESSION['reg_email'];
                    } ?>" required>
                    <br>

                    <input type="email" name="reg_email2" placeholder="Potwierdz Email" value="<?php
                    if (isset($_SESSION['reg_email2'])) {
                        echo $_SESSION['reg_email2'];
                    } ?>" required>
                    <br>
                    <?php if (in_array("Podany adres Email jest juz uzywany<br>", $error_array)) echo "Podany adres Email jest juz uzywany<br>";
                    else if (in_array("Niepoprawny format adresu Email<br>", $error_array)) echo "Niepoprawny format adresu Email<br>";
                    elseif (in_array("Adresy Email nie sa takie same<br>", $error_array)) echo "Adresy Email nie sa takie same<br>"; ?>


                    <input type="password" name="reg_password" placeholder="Haslo" required>
                    <br>
                    <input type="password" name="reg_password2" placeholder="Potwierdz Haslo" required>
                    <br>
                    <?php if (in_array("Hasla nie sa zgodne ze soba<br>", $error_array)) echo "Hasla nie sa zgodne ze soba<br>";
                    else if (in_array("Twoje haslo moze zawierac jedynie litery oraz cyfry<br>", $error_array)) echo "Twoje haslo moze zawierac jedynie litery oraz cyfry<br>";
                    elseif (in_array("Twoje haslo musi miec wiecej 5 i mniej niz 30 znakow<br>", $error_array)) echo "Twoje haslo musi miec wiecej 5 i mniej niz 30 znakow<br>"; ?>


                    <input type="submit" name="register_button" value="Zarejestruj">
                    <br>

                    <?php if (in_array("<span style='color: #14C800'>Wszystko gotowe! Mozesz korzystac z serwisu!</span><br>", $error_array)) echo "<span style='color: #14C800'>Wszystko gotowe! Mozesz korzystac z serwisu!</span><br>"; ?>
                    <a href="#" id="signin" class="signin">Masz juz konto? Zaloguj sie tutaj!</a>
                </form>

            </div>
        </div>
    </div>
    </body>
    </html>
</DOCTYPE>