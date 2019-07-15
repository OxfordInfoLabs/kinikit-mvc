<?php


namespace Kinikit\MVC\Response;


class TestJSONObject {

    private $name;
    private $address;
    private $phone;

    /**
     * TestJSONObject constructor.
     * @param $name
     * @param $address
     * @param $phone
     */
    public function __construct($name, $address, $phone) {
        $this->name = $name;
        $this->address = $address;
        $this->phone = $phone;
    }


    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void {
        $this->phone = $phone;
    }


}
