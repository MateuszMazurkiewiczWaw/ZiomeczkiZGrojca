<?php

class Notification
{
    private $user_obj;
    private $con;

    public function __construct($con, $user)
    {
        $this->con = $con;
        $this->user_obj = new User($con, $user);
    }

    public function getUnreadNumber()
    {
        $userLoggedIn = $this->user_obj->getUsername();
        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE viewed = 'no' AND user_to = '$userLoggedIn'");
        return mysqli_num_rows($query);
    }

    public function getNotifications($data, $limit)
    {
        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();
        $return_string = "";

        if ($page == 1) {
            $start = 0;
        } else {
            $start = ($page - 1) * $limit;
        }

        $set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET viewed = 'yes' WHERE user_to '$userLoggedIn'");

        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to = '$userLoggedIn' ORDER BY ID DESC");

        if (mysqli_num_rows($query) == 0) {
            echo "Brak wiecej powiadomien do wyswietlenia!";
            return;
        }

        $num_iterations = 0;
        $count = 1;

        while ($row = mysqli_fetch_array($query)) {

            if ($num_iterations++ < $start)
                continue;

            if ($count > $limit)
                break;
            else
                $count++;

            $user_from = $row['user_from'];

            $user_data_query = mysqli_query($this->con, "SELECT * FROM users WHERE username = '$user_from'");
            $user_data = mysqli_fetch_array($user_data_query);

            //Timeframe
            $date_time_now = date("Y-m-d H:i:s");
            $start_date = new DateTime($row['datetime']);
            $end_date = new DateTime($date_time_now);
            $interval = $start_date->diff($end_date);

            if ($interval->y >= 1) {
                if ($interval == 1) {
                    $time_message = $interval->y . " rok temu";
                } else {
                    $time_message = $interval->y . " lat temu";
                }
            } elseif ($interval->m >= 1) {
                if ($interval->d == 0) {
                    $days = " temu";
                } elseif ($interval->d == 1) {
                    $days = $interval->d . " dzien temu";
                } else {
                    $days = $interval->d . " dni temu";
                }

                if ($interval->m == 1) {
                    $time_message = $interval->m . " miesiac" . $days;
                } else {
                    $time_message = $interval->m . " miesiecy" . $days;
                }
            } elseif ($interval->d >= 1) {
                if ($interval->d == 1) {
                    $time_message = " wczoraj";
                } else {
                    $time_message = $interval->d . " dni temu";
                }
            } elseif ($interval->h >= 1) {
                if ($interval->h == 1) {
                    $time_message = $interval->h . " godzine temu";
                } else {
                    $time_message = $interval->h . " godziny temu";
                }
            } elseif ($interval->i >= 1) {
                if ($interval->i == 1) {
                    $time_message = $interval->i . " minute temu";
                } else {
                    $time_message = $interval->i . " minuty temu";
                }
            } else {
                if ($interval->s < 30) {
                    $time_message = " przed chwila";
                } else {
                    $time_message = $interval->s . " sekund temu";
                }
            }


            $opened = row['opened'];
            $style = ($row['opened'] == 'no') ? "background-color: #DDEDFF;" : "";

            $return_string .= "<a href='" . $row['link'] . "'>
                                    <div class='resultDisplay resultDisplayNotification' style='" . $style . "'></div>
                                        <div class='notificationsProfilePic'>
                                            <img src='" . $user_data['profile_pic'] . "'
                                        </div>
                                        <p class='timestamp_smaller' id='grey'>" . $time_message . "</p>" . $row['message'] . "
                                    </div>
                               </a>";
        }

        if ($count > $limit) {
            $return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'>
                                    <input type='hidden' class='noMoreDropdownData' value='false'>";
        } else {
            $return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align: center;'>Brak wiecej powiadomien do wyswietlenia!</p>";
        }

        return $return_string;
    }

    public function insertNotification($post_id, $user_to, $type)
    {
        $userLoggedIn = $this->user_obj->getUsername();
        $userLoggedInName = $this->user_obj->getFirstAndLastName();

        $date_time = date("Y-m-d H:i:s");
        $message = "";

        switch ($type) {
            case 'comment':
                $message = $userLoggedInName . " skomentowal Twoj post";
                break;
            case 'like':
                $message = $userLoggedInName . " polubil Twoj post";
                break;
            case 'profile_post':
                $message = $userLoggedInName . " napisal posta na Twoim profilu";
                break;
            case 'comment_non_owner':
                $message = $userLoggedInName . " dodal komentarz do posta ktory wczesniej skomentowales";
                break;
            case 'profile_comment':
                $message = $userLoggedInName . " dodal komentarz do posta na Twoim profilu";
                break;
        }

        $link = "post.php?id=" . $post_id;

        $insert_query = mysqli_query($this->con, "INSERT INTO notofications VALUES ('', '$user_to', '$userLoggedIn', '$message', '$link', '$date_time', 'no', 'no')");
    }

}

?>