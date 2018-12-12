<?php
/**
 * Created by PhpStorm.
 * User: nathanalan
 * Date: 14/09/2018
 * Time: 15:47
 */

namespace Kinikit\MVC\Framework;


class Redirection {

    private $redirectURL;
    private $params = array();

    /**
     * Redirection constructor.
     * @param $redirectURL
     * @param $params
     */
    public function __construct($redirectURL, $params = array()) {
        $this->redirectURL = $redirectURL;
        $this->params = $params;
    }


    /**
     * Do the redirect
     */
    public function redirect() {
        $location = $this->redirectURL;

        $params = array();
        foreach ($this->params as $key => $value) {
            $params[] = $key . "=" . $value;
        }

        $paramsString = join("&", $params);
        $location = $location . (strpos($location, "?") ? "&" : "?") . $paramsString;

        header("Location: $location");
        exit();
    }


}