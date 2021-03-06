<?php
include("includes/header.php");

if (isset($_GET['q'])) {
    $query = $_GET['q'];
} else {
    $query = "";
}

if (isset($_GET['type'])) {
    $type = $_GET['type'];
} else {
    $type = "name";
}
?>

<div class="main_column column" id="main_column">
    <?php
    if ($query == "")
        echo "Ziomek, musisz wpisac cos w pole z napisem szukaj.";
    else {
        if ($type = "username") {
            $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed = 'no' LIMIT 8");
        } else {
            $names = explode(" ", $query);

            if (count($names) == 3) {
                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[2]%') AND user_closed = 'no'");
            } elseif (count($names) == 2) {
                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' AND last_name LIKE '$names[1]%') AND user_closed = 'no'");
            } else {
                $usersReturnedQuery = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '$names[0]%' OR last_name LIKE '$names[0]%') AND user_closed = 'no'");
            }
        }

        if (mysqli_num_rows($usersReturnedQuery) == 0) {
            echo "Nie odnaleziono nikogo z " . $type . " podobnego do " . $query;
        } else {
            echo "Znalezionych wynikow: " . mysqli_num_rows($usersReturnedQuery) . "<br/><br/>";
        }

        echo "<p id='grey'>Wyproboj wyszukiwanie po:</p>";
        echo "<a href='search.php?q=" . $query . "&type=name'>Imiona</a>, <a href='search.php?q=" . $query . "&type=username'>Nazwy uzytkownikow</a><br/><br/><hr id='search_hr'/> ";

        while ($row = mysqli_fetch_array($usersReturnedQuery)) {
            $user_obj = new User($con, $user['username']);

            $button = "";
            $mutual_friends = "";

            if ($user['username'] != $row['username']) {

                if ($user_obj->isFriend($row['username'])) {
                    $button = "<input type='submit' name='" . $row['username'] . "' class='danger' value='Usun z Ziomkow'>";
                } elseif ($user_obj->didReceiveRequest($row['username'])) {
                    $button = "<input type='submit' name='" . $row['username'] . "' class='warning' value='Odpowiedz na zaproszenie'>";
                } elseif ($user_obj->didSendRequest($row['username'])) {
                    $button = "<input type='submit' class='default' value='Zaproszenie wyslane'>";
                } else {
                    $button = "<input type='submit' name='" . $row['username'] . "' class='success' value='Zapros do Ziomkow'>";
                }

                $mutual_friends = $user_obj->getMutualFriends($row['username']) . " wspolnych ziomkow";

                if (isset($_POST[$row['username']])) {

                    if ($user_obj->isFriend($row['username'])) {
                        $user_obj->removeFriend($row['username']);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    } elseif ($user_obj->didReceiveRequest($row['username'])) {
                        header("Location: requests.php");
                    } elseif ($user_obj->didSendRequest($row['username'])) {

                    } else {
                        $user_obj->sendRequest($row['username']);
                        header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                    }
                }
            }

            echo "<div class='search_result'>
                    <div class='searchPageFriendButtons'
                        <form action='' method='POST'>
                            " . $button . "
                            <br/>
                        </form>
                    </div>
                    
                    <div class='result_profile_pic'>
                        <a href='" . $row['username'] . "'><img src='" . $row['profile_pic'] . "' style='height: 100px;'></a>
                    </div>
                    
                    <a href='" . $row['username'] . "'>" . $row['first_name'] . " " . $row['last_name'] . "
                        <p id='grey'>" . $row['username'] . "</p>
                    </a>
                    
                    <br/>
                    " . $mutual_friends . "
                    <br/>
                    
                   </div>
                   <hr id='search_hr'/>";
        } //while

    }

    ?>

</div>
