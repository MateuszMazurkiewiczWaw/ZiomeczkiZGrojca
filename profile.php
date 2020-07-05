<?php
include("includes/header.php");

$message_obj = new Message($con, $userLoggedIn);

if (isset($_GET['profile_username'])) {
    $username = $_GET['profile_username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username = '$username'");
    $user_array = mysqli_fetch_array($user_details_query);

    $num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}

if (isset($_POST['remove_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->removeFriend($username);
}

if (isset($_POST['add_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->sendRequest($username);
}

if (isset($_POST['respond_friend'])) {
    header("Location: requests.php");
}

if(isset($_POST['post_message'])) {
    if(isset($_POST['message_body'])) {
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username, $body, $date);
    }

    $link = '#profileTabs a[href="#messages_div"]';
    echo "<script>
            $(function() {
                $('" . $link . "').tab('show')
            });
          </script>";
}

?>

<style type="text/css">
    .wrapper {
        margin-left: 0px;
        padding-left: 0px;
    }
</style>

<div class="profile_left">
    <img src="<?php echo $user_array['profile_pic']; ?>">

    <div class="profile_info">
        <p><?php echo "Postow: " . $user_array['num_posts']; ?></p>
        <p><?php echo "Lajkow: " . $user_array['num_likes']; ?></p>
        <p><?php echo "Ziomkow: " . $num_friends; ?></p>
    </div>

    <form action="<?php echo $username ?>" method="POST">
        <?php
        $profile_user_obj = new User($con, $username);

        if ($profile_user_obj->isClosed()) {
            header("Location: user_closed.php");
        }

        $logged_in_user_obj = new User($con, $userLoggedIn);
        if ($userLoggedIn != $username) {

            if ($logged_in_user_obj->isFriend($username)) {
                echo '<input type="submit" name="remove_friend" class="danger" value="Usun Ziomala!"><br>';
            } elseif ($logged_in_user_obj->didReceiveRequest($username)) {
                echo '<input type="submit" name="resond_request" class="warning" value="Odpowiedz na zaproszenie!"><br>';
            } elseif ($logged_in_user_obj->didSendRequest($username)) {
                echo '<input type="submit" name="" class="default" value="Zaproszenie wyslane!"><br>';
            } else {
                echo '<input type="submit" name="add_friend" class="success" value="Dodaj Ziomala!"><br>';
            }
        }
        ?>

    </form>

    <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Opublikuj Cos!">

    <?php
    if ($userLoggedIn != $username) {
        echo '<div class="profile_info_bottom">';
        echo $logged_in_user_obj->getMutualFriends($username) . " wspolnych Ziomkow";
        echo '</div>';
    }
    ?>

</div>

<div class="profile_main_column column">

    <ul class="nav nav-tabs" role="tablist" id="profileTabs">
        <li role="presentation" class="active"><a href="#newsfeed_div" aria-controls="newsfeed_div" role="tab"
                                                  data-toggle="tab">ZiomNewsy</a></li>
        <li role="presentation"><a href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Wiadomosci</a>
        </li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade in active" id="newsfeed_div">
            <div class="posts_area"></div>
            <img id="loading" src="assets/images/icons/loading.gif" alt="ladowanie">
        </div>

        <div role="tabpanel" class="tab-pane fade" id="messages_div">
            <?php
            echo "<h4>Ty i <a href='" . $username . "'>" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";
            echo "<div class='loaded_messages' id='scroll_messages'>";
            echo $message_obj->getMessages($username);
            echo "</div>";
            ?>

            <div class="message_post">
                <form action="" method="POST">
                    <textarea name='message_body' id='message_textarea'
                              placeholder='Wpisz swoja wiadomosc...'></textarea>
                    <input type='submit' name='post_message' class='info' id='message_submit' value='Wyslij'>
                </form>
            </div>

            <script>
                var div = document.getElementById("scroll_messages");
                div.scrollTop = div.scrollHeight;
            </script>
        </div>
    </div>

</div>

<!-- Modal https://getbootstrap.com/docs/4.0/components/modal/-->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Opublikuj cos!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                    <span aria-hidden="true">&times;</span>
                </button>

            </div>

            <div class="modal-body">
                <p>To pojawi sie na Twojej stronie profilowej oraz bedzie widoczne w newsach Twoich Ziomeczkow</p>

                <form class="profile_post" action="" method="POST">
                    <div class="form-group">
                        <textarea class="form_control" name="post_body"></textarea>
                        <input type="hidden" name="user_from" value="<?php echo $userLoggedIn; ?>">
                        <input type="hidden" name="user_to" value="<?php echo $username; ?>">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Opublikuj
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var userLoggedIn = '<?php echo $userLoggedIn; ?>';
    var profileUsername = '<?php echo $username; ?>';

    $(document).ready(function () {
        $('#loading').show();

        //AJAX request
        $.ajax({
            url: "includes/handlers/ajax_load_profile_posts.php",
            type: "POST",
            data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
            cache: false,
            success: function (data) {
                $('#loading').hide();
                $('.posts_area').html(data);
            }
        });

        $(window).scroll(function () {
            var height = $('.post_area').height();
            var scroll_top = $(this).scrollTop();
            var page = $('.posts_area').find('.nextPage').val();
            var noMorePosts = $('.posts_area').find('.noMorePosts').val();

            if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
                $('#loading').show();

                var ajaxReq = $.ajax({
                    url: "includes/handlers/ajax_load_profile_posts.php",
                    type: "POST",
                    data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
                    cache: false,
                    success: function (response) {
                        $('.posts_area').find('.nextPage').remove();
                        $('.posts_area').find('.noMorePosts').remove();

                        $('#loading').hide();
                        $('.posts_area').append(response);
                    }
                });
            }

            return false;

        });

    });


</script>

</div>
</body>
</html>

