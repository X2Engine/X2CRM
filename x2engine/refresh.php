<?php

class ImmersiveDemo {

    /** Fields */
    private $dbhost = '127.0.0.1';
    private $dbuser = 'isaiah';
    private $dbpass = 'SV7SOnNyHPyQfR';
    private $dbname = 'isaiah';
    private $last_post = null;
    private $data = null;

    /** Constructors */
    function __construct() {
        $this->data = new ArrayData;
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
            $type = $this->choose_rand_elem($this->data->types);

            sleep(20);

            switch ($type) {
                case "event":
                    $this->post_random_event($con, $this->data);
                    break;
                case "notif":
                    $this->post_random_notif($con, $this->data);
                    break;
            }
        }

        mysqli_close($con);
    }

    /** Actions */
    private function post_random_event($con, $data) {
        $event_type = $this->choose_rand_elem($data->event_types);
        $user = $this->choose_rand_elem($data->users);

        switch ($event_type) {
            case "post": default:
                $message = $this->choose_rand_elem($data->event_posts);
                $this->post_event($con, $user, $message);

                for ($i = 0; $i < 2 && $this->flip_coin(); $i++) {
                    sleep(5);
                    $next_user = $this->choose_rand_elem($data->users, $user);
                    $comment = $this->choose_rand_elem($data->event_comments, $comment);
                    $this->post_comment($con, $next_user, $comment);
                }
                break;
        }
    }

    private function post_random_notif($con, $data) {
        $notif_type = $this->choose_rand_elem($data->notif_types);

        switch ($notif_type) {
            case "custom": default:
                $text = "TEST";
                $this->post_custom_notif($con, $text);
                break;
            case "action_reminder":
                $reminder = $this->choose_rand_elem($data->notif_actionreminder_ids);
                $this->post_actionreminder_notif($con, $reminder);
                break;
            case "action_complete":
                $complete = $this->choose_rand_elem($data->notif_actioncomplete_ids);
                $this->post_actioncomplete_notif($con, $complete);
                break;
        }
    }

    /** Query Functions */
    private function post_event($con, $user, $text) {
        mysqli_query($con, sprintf("INSERT INTO x2_events (type, text, user, timestamp, lastUpdated) VALUES ('feed', '%s', '%s', %s, %s)", $text, $user, time(), time()));
        $this->last_post = mysqli_insert_id($con);
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
    private function choose_rand_elem($array, $previous = "") {
        if (count($array) === 0) {
            return "null";
        } else if (count($array) === 1) {
            return $array[0];
        }
        $result = "";
        while ($result == "" || $previous == $result) {
            $rand = rand(0, count($array) - 1);
            $result = $array[$rand];
        }
        return $result;
    }

    private function flip_coin() {
        $flip = rand(0, 1);
        return $flip === 0 ? true : false;
    }

}

class ArrayData {

    public $users = array(
        "admin",
        "bto",
        "kxu",
        "apelletier",
        "ncordova",
        "chames",
        "coconner",
        "rpatel",
        "acarisella",
    );
    public $types = array(
        "event",
        "notif",
    );
    public $event_types = array(
        "post",
        "message",
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
    public $event_comments = array(
        "Definitely!",
        "I disagree.",
        "Yes!",
        "Not really.",
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
