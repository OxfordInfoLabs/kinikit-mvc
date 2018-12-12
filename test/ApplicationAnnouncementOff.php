<?php


use Kinikit\Core\Announcement;

class ApplicationAnnouncement implements Announcement {

    public static $run = false;

    /**
     * Primary logic
     */
    public function announce() {
        ApplicationAnnouncement::$run = true;
    }


}


?>