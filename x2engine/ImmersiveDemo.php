<?php

class ImmersiveDemo {

    /** Fields */
    private $dbhost = '127.0.0.1';
    private $dbuser = 'isaiah';
    private $dbpass = 'SV7SOnNyHPyQfR';
    private $dbname = 'isaiah';
    private $last_post = null;
    private $last_location = null;
    private $data = null;

    /** Constructors */
    function __construct() {
        $this->data = new ImmersiveDemoData();
    }

    public static function new_instance() {
        return new self();
    }

    public static function new_instance_with_params($dbhost, $dbuser, $dbpass, $dbname) {
        $instance = new self();
        $instance->dbhost = $dbhost;
        $instance->dbuser = $dbuser;
        $instance->dbpass = $dbpass;
        $instance->dbname = $dbname;
        return $instance;
    }

    /** Runs Demo Queries */
    public function run() {
        $con = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname) or die('Could not connect: ' . mysqli_error($con));
        $this->last_post = mysqli_insert_id($con);
        
        

        for ($i = 0; $i < 20; $i++) {
            /*
              $type = ImmersiveDemo::choose_rand_elem($this->data->types);

              sleep(20);

              switch ($type) {
              case "event":
              $this->post_random_event($con);
              break;
              case "notif":
              $this->post_random_notif($con);
              break;
              }
             * 
             */
        }

        mysqli_close($con);
    }

    /** Actions */
    private function post_random_event($con) {
        $user = ImmersiveDemo::choose_rand_elem(array_values($this->data->users));

        $event_type = ImmersiveDemo::choose_rand_elem($this->data->event_types);
        switch ($event_type) {
            case "post": default:
                $post_message = ImmersiveDemo::choose_rand_elem($this->data->event_posts);

                $this->post_event($con, $user, $post_message);

                for ($i = 0; $i < 2 && ImmersiveDemo::flip_coin(); $i++) {
                    sleep(5);

                    $next_user = ImmersiveDemo::choose_rand_elem(array_values($this->data->users), $user);
                    $comment = ImmersiveDemo::choose_rand_elem($this->data->event_comments, $comment);
                    $this->post_comment($con, $next_user, $comment);
                }
                break;
            case "private":
                $private_message = ImmersiveDemo::choose_rand_elem($this->data->event_posts);

                $key = 0;
                while ($key === 0 || $key === 2 || ImmersiveDemo::key_value_match($this->data->users, $key, $user)) {
                    $key = rand(1, 10);
                }

                $this->post_event_private($con, $key, $user, $private_message);
                break;
            case "location":
                $location = ImmersiveDemo::choose_rand_elem($this->data->event_locations);
                $userId = array_search($user, $this->data->users);

                $this->post_location($con, $userId, $location["lat"], $location["lon"]);

                $this->post_event_location($con, $user, $location["text"]);
                break;
        }
    }

    private function post_random_notif($con) {
        $notif_type = ImmersiveDemo::choose_rand_elem($this->data->notif_types);

        switch ($notif_type) {
            case "custom": default:
                $text = "TEST";
                $this->post_custom_notif($con, $text);
                break;
            case "action_reminder":
                $reminder = ImmersiveDemo::choose_rand_elem($this->data->notif_actionreminder_ids);
                $this->post_actionreminder_notif($con, $reminder);
                break;
            case "action_complete":
                $complete = ImmersiveDemo::choose_rand_elem($this->data->notif_actioncomplete_ids);
                $this->post_actioncomplete_notif($con, $complete);
                break;
        }
    }

    /** Query Functions */
    private function post_event($con, $user, $text) {
        mysqli_query($con, sprintf("INSERT INTO x2_events (type, text, user, timestamp, lastUpdated) VALUES ('feed', '%s', '%s', %s, %s)", $text, $user, time(), time()));
        $this->last_post = mysqli_insert_id($con);
    }

    private function post_event_location($con, $user, $text) {
        mysqli_query($con, sprintf("INSERT INTO x2_events (type, text, user, locationId, timestamp, lastUpdated) VALUES ('feed', '%s', '%s', %s, %s, %s)", $text, $user, $this->last_location, time(), time()));
    }

    private function post_event_private($con, $key, $user, $text) {
        mysqli_query($con, sprintf("INSERT INTO x2_events (type, subtype, visibility, associationId, text, user, timestamp, lastUpdated) VALUES ('feed', 'Social Post', 0, %s, '%s', '%s', %s, %s)", $key, $text, $user, time(), time()));
    }

    private function post_location($con, $userId, $lat, $lon) {
        mysqli_query($con, sprintf("INSERT INTO x2_locations (recordId, recordType, lat, lon, createDate) VALUES (%s, 'User', %s, %s, %s)", $userId, $lat, $lon, time()));
        $this->last_location = mysqli_insert_id($con);
    }

    private function post_comment($con, $user, $text) {
        mysqli_query($con, sprintf("INSERT INTO x2_events (type, text, associationType, associationId, user, timestamp, lastUpdated) VALUES ('comment', '%s', 'Events', %s, '%s', %s, %s)", $text, $this->last_post, $user, time(), time()));
    }

    private function post_custom_notif($con, $text) {
        mysqli_query($con, sprintf("INSERT INTO x2_notifications (type, text, user, createdBy, createDate) VALUES ('custom', '%s', 'admin', 'admin', %s)", $text, time()));
    }

    private function post_actionreminder_notif($con, $modelid) {
        mysqli_query($con, sprintf("INSERT INTO x2_notifications (type, user, modelType, modelId, createdBy, createDate) VALUES ('action_reminder', 'admin', 'Actions', '%s', 'admin', '%s')", $modelid, time()));
    }

    private function post_actioncomplete_notif($con, $modelid) {
        mysqli_query($con, sprintf("INSERT INTO x2_notifications (type, user, modelType, modelId, createdBy, createDate) VALUES ('action_complete', 'admin', 'Actions', '%s', 'admin', '%s')", $modelid, time()));
    }

    /** Helper Functions */
    private static function choose_rand_elem($array, $previous = "") {
        if (count($array) === 0) {
            return "null";
        } else if (count($array) === 1) {
            return $array[0];
        }
        $result = "";
        while ($result == "" || $previous == $result) {
            $result = $array[rand(0, count($array) - 1)];
        }
        return $result;
    }

    private static function flip_coin() {
        return rand(0, 1) === 0 ? true : false;
    }

    private static function key_value_match($array, $key, $value) {
        return $array[$key] == $value;
    }

}

class ImmersiveDemoData {

    public $users = array(
        1 => "admin",
        3 => "chames",
        4 => "ncordova",
        5 => "apelletier",
        6 => "kxu",
        7 => "rpatel",
        8 => "coconner",
        9 => "bto",
        10 => "acarisella",
    );
    public $types = array(
        "event",
        "notif",
    );
    public $event_types = array(
        "post",
        "private",
        "location",
    );
    public $event_posts = array(
        "I love the color scheme of this new update!",
        "The next version of X2CRM is about to be amazing!",
        "I love the UI it\'s really simple to use.",
        "Only halfway through this quarter and we are already at 80 percent of what we did last quarter. Great job, everyone!",
        "Hey all! It\'s a nice day today.",
        "Does anyone want to eat lobster for lunch?",
        "There are some leftover donuts in the fridge. Does anyone want them?",
        "I\'m organizing a company lunch for next Friday. Anybody have requests about where to go? I\'m thinking sushi.",
    );
    public $event_posts_private = array(
        "Hey! You are doing a phenomenal job.",
        "Do you think you could finish the paperwork by 4pm tomorrow?",
        "You left your donuts in the fridge!",
        "Do you want to get lunch later?"
    );
    public $event_comments = array(
        "Definitely!",
        "I disagree.",
        "Yes!",
        "Not really.",
    );
    public $event_locations = array(
        array("text" => "Checking in at Union Square, San Francisco", "lat" => 37.788018, "lon" => -122.407809),
        array("text" => "Checking in at St. Vartans Park, New York", "lat" => 40.745448, "lon" => -73.973961),
    );
    public $notif_types = array(
        "custom",
        "action_complete",
        "action_reminder",
    );
    public $notif_actionreminder_ids = array(
        "3371",
        "3376",
        "3384",
    );
    public $notif_actioncomplete_ids = array(
        "60",
        "3376",
    );

}

$demo = ImmersiveDemo::new_instance();
$demo->run();
