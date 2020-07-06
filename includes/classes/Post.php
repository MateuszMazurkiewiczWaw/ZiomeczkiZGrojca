<?php
class Post {
    private $user_obj;
    private $con;

    public function __construct($con, $user){
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function submitPost($body, $user_to, $imageName) {
        $body = strip_tags($body); //removes html tags
        $body = mysqli_real_escape_string($this->con, $body);
        $body = str_replace('\r\n', "\n", $body);
        $body = nl2br($body);
        $check_empty = preg_replace('/\s+/', '', $body); //Deltes all spaces

        if($check_empty != "") {

            $body_array = preg_split("/\s+/", $body);

            foreach($body_array as $key => $value) {

                if(strpos($value, "www.youtube.com/watch?v=") !== false) {

                    $link = preg_split("!&!", $value);
                    $value = preg_replace("!watch\?v=!", "embed/", $link[0]);
                    $value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value ."\'></iframe><br>";
                    $body_array[$key] = $value;

                }

            }
            $body = implode(" ", $body_array);


            $date_added = date("Y-m-d H:i:s");
            $added_by = $this->user_obj->getUsername();

            if($user_to == $added_by)
                $user_to = "none";

            $query = mysqli_query($this->con, "INSERT INTO posts VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0', '$imageName')");
            $returned_id = mysqli_insert_id($this->con);

            if($user_to != 'none') {
                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returned_id, $user_to, "like");
            }

            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts='$num_posts' WHERE username='$added_by'");


            $stopWords = "a aby ach acz aczkolwiek aj albo ale alez ależ ani az aż bardziej bardzo beda bedzie bez deda będą bede będę
             będzie bo bowiem by byc być byl byla byli bylo byly był była było były bynajmniej cala cali caly cała cały ci cie ciebie
             cię co cokolwiek cos coś czasami czasem czemu czy czyli daleko dla dlaczego dlatego do dobrze dokad dokąd dosc dość duzo
             dużo dwa dwaj dwie dwoje dzis dzisiaj dziś gdy gdyby gdyz gdyż gdzie gdziekolwiek gdzies gdzieś go i ich ile im inna inne inny
             innych iz iż ja jak jakas jakaś jakby jaki jakichs jakichś jakie jakis jakiś jakiz jakiż jakkolwiek jako jakos jakoś ją je jeden
             jedna jednak jednakze jednakże jedno jego jej jemu jesli jest jestem jeszcze jeśli jezeli jeżeli juz już kazdy każdy kiedy
             kilka kims kimś kto ktokolwiek ktora ktore ktorego ktorej ktory ktorych ktorym ktorzy ktos ktoś która które którego
             której który których którym którzy ku lat lecz lub ma mają mało mam mi miedzy między mimo mna mną mnie moga mogą moi
             moim moj moja moje moze mozliwe mozna może możliwe można mój mu musi my na nad nam nami nas nasi nasz nasza nasze
             naszego naszych natomiast natychmiast nawet nia nią nic nich nie niech niego niej niemu nigdy nim nimi niz niż no
             o obok od około on ona one oni ono oraz oto owszem pan pana pani po pod podczas pomimo ponad poniewaz ponieważ powinien
             powinna powinni powinno poza prawie przeciez przecież przed przede przedtem przez przy roku rowniez również sam
             sama są sie się skad skąd soba sobą sobie sposob sposób swoje ta tak taka taki takie takze także tam te tego tej
             ten teraz też to toba tobą tobie totez toteż totobą trzeba tu tutaj twoi twoim twoj twoja twoje twój twym ty tych tylko
             tym u w wam wami was wasz wasza wasze we według wiele wielu więc więcej wlasnie właśnie wszyscy wszystkich wszystkie
             wszystkim wszystko wtedy wy z za zaden zadna zadne zadnych zapewne zawsze ze zeby zeznowu zł znow znowu znów zostal
             został żaden żadna żadne żadnych że żeby";

            $stopWords = preg_split("/[\s,]+/", $stopWords);

            //Remove all punctionation
            $no_punctuation = preg_replace("/[^a-zA-Z 0-9]+/", "", $body);

            if(strpos($no_punctuation, "height") === false && strpos($no_punctuation, "width") === false
                && strpos($no_punctuation, "http") === false && strpos($no_punctuation, "youtube") === false){
                $keywords = preg_split("/[\s,]+/", $no_punctuation);

                foreach($stopWords as $value) {
                    foreach($keywords as $key => $value2){
                        if(strtolower($value) == strtolower($value2))
                            $keywords[$key] = "";
                    }
                }

                foreach ($keywords as $value) {
                    $this->calculateTrend(ucfirst($value));
                }
            }
        }
    }

    public function calculateTrend($term) {

        if($term != '') {
            $query = mysqli_query($this->con, "SELECT * FROM trends WHERE title='$term'");

            if(mysqli_num_rows($query) == 0)
                $insert_query = mysqli_query($this->con, "INSERT INTO trends(title,hits) VALUES('$term','1')");
            else
                $insert_query = mysqli_query($this->con, "UPDATE trends SET hits=hits+1 WHERE title='$term'");
        }
    }

    public function loadPostsFriends($data, $limit) {

        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();

        if($page == 1)
            $start = 0;
        else
            $start = ($page - 1) * $limit;

        $str = "";
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' ORDER BY id DESC");

        if(mysqli_num_rows($data_query) > 0) {


            $num_iterations = 0;
            $count = 1;

            while($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];
                $imagePath = $row['image'];

                if($row['user_to'] == "none") {
                    $user_to = "";
                }
                else {
                    $user_to_obj = new User($this->con, $row['user_to']);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href='" . $row['user_to'] ."'>" . $user_to_name . "</a>";
                }

                $added_by_obj = new User($this->con, $added_by);
                if($added_by_obj->isClosed()) {
                    continue;
                }

                $user_logged_obj = new User($this->con, $userLoggedIn);
                if($user_logged_obj->isFriend($added_by)){

                    if($num_iterations++ < $start)
                        continue;

                    if($count > $limit) {
                        break;
                    }
                    else {
                        $count++;
                    }

                    if($userLoggedIn == $added_by)
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    else
                        $delete_button = "";

                    $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                    $user_row = mysqli_fetch_array($user_details_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];


                    ?>
                    <script>
                        function toggle<?php echo $id; ?>() {

                            var target = $(event.target);
                            if (!target.is("a")) {
                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if(element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }
                        }

                    </script>
                    <?php

                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s");
                    $start_date = new DateTime($date_time);
                    $end_date = new DateTime($date_time_now);
                    $interval = $start_date->diff($end_date);
                    if($interval->y >= 1) {
                        if($interval->y == 1)
                            $time_message = $interval->y . " rok temu"; //1 year ago
                        else
                            $time_message = $interval->y . " lat temu"; //1+ year ago
                    }
                    else if ($interval->m >= 1) {
                        if($interval->d == 0) {
                            $days = " temu";
                        }
                        else if($interval->d == 1) {
                            $days = $interval->d . " dzien temu";
                        }
                        else {
                            $days = $interval->d . " dni temu";
                        }


                        if($interval->m == 1) {
                            $time_message = $interval->m . " miesiac ". $days;
                        }
                        else {
                            $time_message = $interval->m . " miesiecy ". $days;
                        }

                    }
                    else if($interval->d >= 1) {
                        if($interval->d == 1) {
                            $time_message = "wczoraj";
                        }
                        else {
                            $time_message = $interval->d . " dni temu";
                        }
                    }
                    else if($interval->h >= 1) {
                        if($interval->h == 1) {
                            $time_message = $interval->h . " godzine temu";
                        }
                        else {
                            $time_message = $interval->h . " godziny temu";
                        }
                    }
                    else if($interval->i >= 1) {
                        if($interval->i == 1) {
                            $time_message = $interval->i . " minute temu";
                        }
                        else {
                            $time_message = $interval->i . " minuty temu";
                        }
                    }
                    else {
                        if($interval->s < 30) {
                            $time_message = " przed chwila";
                        }
                        else {
                            $time_message = $interval->s . " sekund temu";
                        }
                    }

                    if($imagePath != "") {
                        $imageDiv = "<div class='postedImage'>
										<img src='$imagePath'>
									</div>";
                    }
                    else {
                        $imageDiv = "";
                    }

                    $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									$imageDiv
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Komentarze($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";
                }

                ?>
                <script>

                    $(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("Jestes pewien ze chcesz usunac ten post?", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

                                if(result)
                                    location.reload();

                            });
                        });


                    });

                </script>
                <?php

            }

            if($count > $limit)
                $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
            else
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;' class='noMorePostsText'> Brak wiecej postow do wyswietlenia! </p>";
        }
        echo $str;
    }

    public function loadProfilePosts($data, $limit) {

        $page = $data['page'];
        $profileUser = $data['profileUsername'];
        $userLoggedIn = $this->user_obj->getUsername();

        if($page == 1)
            $start = 0;
        else
            $start = ($page - 1) * $limit;

        $str = "";
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='none') OR user_to='$profileUser')  ORDER BY id DESC");

        if(mysqli_num_rows($data_query) > 0) {


            $num_iterations = 0;
            $count = 1;

            while($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];


                if($num_iterations++ < $start)
                    continue;

                if($count > $limit) {
                    break;
                }
                else {
                    $count++;
                }

                if($userLoggedIn == $added_by)
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                else
                    $delete_button = "";


                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];


                ?>
                <script>
                    function toggle<?php echo $id; ?>() {

                        var target = $(event.target);
                        if (!target.is("a")) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");

                            if(element.style.display == "block")
                                element.style.display = "none";
                            else
                                element.style.display = "block";
                        }
                    }

                </script>
                <?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time);
                $end_date = new DateTime($date_time_now);
                $interval = $start_date->diff($end_date);
                if($interval->y >= 1) {
                    if($interval->y == 1)
                        $time_message = $interval->y . " rok temu";
                    else
                        $time_message = $interval->y . " lat temu";
                }
                else if ($interval->m >= 1) {
                    if($interval->d == 0) {
                        $days = " temu";
                    }
                    else if($interval->d == 1) {
                        $days = $interval->d . " dzien temu";
                    }
                    else {
                        $days = $interval->d . " dni temu";
                    }


                    if($interval->m == 1) {
                        $time_message = $interval->m . " miesiac". $days;
                    }
                    else {
                        $time_message = $interval->m . " miesiecy". $days;
                    }

                }
                else if($interval->d >= 1) {
                    if($interval->d == 1) {
                        $time_message = " wczoraj";
                    }
                    else {
                        $time_message = $interval->d . " dni temu";
                    }
                }
                else if($interval->h >= 1) {
                    if($interval->h == 1) {
                        $time_message = $interval->h . " godzine temu";
                    }
                    else {
                        $time_message = $interval->h . " godziny temu";
                    }
                }
                else if($interval->i >= 1) {
                    if($interval->i == 1) {
                        $time_message = $interval->i . " minute temu";
                    }
                    else {
                        $time_message = $interval->i . " minuty temu";
                    }
                }
                else {
                    if($interval->s < 30) {
                        $time_message = "przed chwila";
                    }
                    else {
                        $time_message = $interval->s . " sekund temu";
                    }
                }

                $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Komentarze($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";
                ?>

                <script>

                    $(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("Jestes pewien ze chcesz usunac ten post?", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

                                if(result)
                                    location.reload();
                            });
                        });
                    });

                </script>
                <?php
            }

            if($count > $limit)
                $str .= "<input type='hidden' class='nextPage' value='" . ($page + 1) . "'>
							<input type='hidden' class='noMorePosts' value='false'>";
            else
                $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align: center;' class='noMorePostsText'> Brak wiecej postow do wyswietlenia! </p>";
        }
        echo $str;
    }

    public function getSinglePost($post_id) {

        $userLoggedIn = $this->user_obj->getUsername();

        $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id'");

        $str = "";
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' AND id='$post_id'");

        if(mysqli_num_rows($data_query) > 0) {


            $row = mysqli_fetch_array($data_query);
            $id = $row['id'];
            $body = $row['body'];
            $added_by = $row['added_by'];
            $date_time = $row['date_added'];

            if($row['user_to'] == "none") {
                $user_to = "";
            }
            else {
                $user_to_obj = new User($this->con, $row['user_to']);
                $user_to_name = $user_to_obj->getFirstAndLastName();
                $user_to = "to <a href='" . $row['user_to'] ."'>" . $user_to_name . "</a>";
            }

            $added_by_obj = new User($this->con, $added_by);
            if($added_by_obj->isClosed()) {
                return;
            }

            $user_logged_obj = new User($this->con, $userLoggedIn);
            if($user_logged_obj->isFriend($added_by)){


                if($userLoggedIn == $added_by)
                    $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                else
                    $delete_button = "";

                $user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by'");
                $user_row = mysqli_fetch_array($user_details_query);
                $first_name = $user_row['first_name'];
                $last_name = $user_row['last_name'];
                $profile_pic = $user_row['profile_pic'];

                ?>
                <script>
                    function toggle<?php echo $id; ?>(e) {

                        if( !e ) e = window.event;

                        var target = $(e.target);
                        if (!target.is("a")) {
                            var element = document.getElementById("toggleComment<?php echo $id; ?>");

                            if(element.style.display == "block")
                                element.style.display = "none";
                            else
                                element.style.display = "block";
                        }
                    }

                </script>
                <?php

                $comments_check = mysqli_query($this->con, "SELECT * FROM comments WHERE post_id='$id'");
                $comments_check_num = mysqli_num_rows($comments_check);

                //Timeframe
                $date_time_now = date("Y-m-d H:i:s");
                $start_date = new DateTime($date_time);
                $end_date = new DateTime($date_time_now);
                $interval = $start_date->diff($end_date);
                if($interval->y >= 1) {
                    if($interval == 1)
                        $time_message = $interval->y . " rok temu";
                    else
                        $time_message = $interval->y . " lat temu";
                }
                else if ($interval->m >= 1) {
                    if($interval->d == 0) {
                        $days = " temu";
                    }
                    else if($interval->d == 1) {
                        $days = $interval->d . " dzien temu";
                    }
                    else {
                        $days = $interval->d . " dni temu";
                    }


                    if($interval->m == 1) {
                        $time_message = $interval->m . " miesiac". $days;
                    }
                    else {
                        $time_message = $interval->m . " miesiecy". $days;
                    }
                }
                else if($interval->d >= 1) {
                    if($interval->d == 1) {
                        $time_message = "wczoraj";
                    }
                    else {
                        $time_message = $interval->d . " dni tem";
                    }
                }
                else if($interval->h >= 1) {
                    if($interval->h == 1) {
                        $time_message = $interval->h . " godzine temu";
                    }
                    else {
                        $time_message = $interval->h . " godziny temu";
                    }
                }
                else if($interval->i >= 1) {
                    if($interval->i == 1) {
                        $time_message = $interval->i . " minute temu";
                    }
                    else {
                        $time_message = $interval->i . " minuty temu";
                    }
                }
                else {
                    if($interval->s < 30) {
                        $time_message = " przed chwila";
                    }
                    else {
                        $time_message = $interval->s . " sekund temu";
                    }
                }

                $str .= "<div class='status_post' onClick='javascript:toggle$id()'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50'>
								</div>

								<div class='posted_by' style='color:#ACACAC;'>
									<a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;$time_message
									$delete_button
								</div>
								<div id='post_body'>
									$body
									<br>
									<br>
									<br>
								</div>

								<div class='newsfeedPostOptions'>
									Komentarze($comments_check_num)&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$id' scrolling='no'></iframe>
								</div>

							</div>
							<div class='post_comment' id='toggleComment$id' style='display:none;'>
								<iframe src='comment_frame.php?post_id=$id' id='comment_iframe' frameborder='0'></iframe>
							</div>
							<hr>";
                ?>

                <script>

                    $(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function() {
                            bootbox.confirm("Jestes pewien ze chcesz usunac ten post?", function(result) {

                                $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>", {result:result});

                                if(result)
                                    location.reload();
                            });
                        });
                    });

                </script>
                <?php
            }
            else {
                echo "<p>Nie mozesz zobaczyc tego posta poniewaz nie jestes Ziomeczkiem z tym uzyszkodnikiem.</p>";
                return;
            }
        }
        else {
            echo "<p>Nie znaleziono zadanego posta. Byc moze kliknoles na zepsuty link!</p>";
            return;
        }
        echo $str;
    }
}

?>