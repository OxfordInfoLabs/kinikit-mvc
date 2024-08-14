<?php

namespace Kinikit\MVC\Controllers;


use Kinikit\Core\DependencyInjection\ExampleEnum;
use Kinikit\Core\DependencyInjection\SimpleEnum;
use Kinikit\Core\Exception\StatusException;
use Kinikit\Core\Reflection\TestBackedEnum;
use Kinikit\Core\Reflection\TestEnum;
use Kinikit\MVC\Objects\TestRESTObject;
use Kinikit\MVC\Test\Backed;
use Kinikit\MVC\Test\Unbacked;

/**
 * Class REST
 * @package Kinikit\MVC\Controllers
 *
 * @rateLimited
 */
class REST {

    /**
     * Get a test object by id using a nested url.
     * @http GET /nested/$id
     *
     * @param integer $id
     * @return TestRESTObject
     */
    public function nestedGet($id) {
        return new TestRESTObject("TEST " . $id, "test$id@test.com", "GET NESTED SINGLE");
    }


    /**
     * Create a test object
     *
     * @http POST /nested
     *
     * @param TestRESTObject $testObject
     * @return TestRESTObject
     */
    public function nestedCreate($testObject) {
        $testObject->setLastStatus("NESTED POSTED");
        return $testObject;
    }


    /**
     * Get a test object by id and name using a nested URL.
     *
     * @http GET /nested/$id/$name
     *
     * @param integer $id
     * @param string $name
     */
    public function nestedVariableGet($id, $name) {
        return new TestRESTObject($name, "test$id@test.com", "GET NESTED VARIABLE SINGLE");
    }


    /**
     * Get a test object by id.
     * @http GET /$id
     *
     * @rateLimit 50
     *
     * @param integer $id
     * @return TestRESTObject
     *
     */
    public function get($id) {
        return new TestRESTObject("TEST " . $id, "test$id@test.com", "GET SINGLE");
    }


    /**
     * Test boolean REST method
     *
     * @http GET /true/$boolValue
     *
     * @rateLimitMultiplier 2
     *
     * @param bool $boolValue
     * @return bool
     */
    public function isTrue($boolValue) {
        return $boolValue;
    }


    /**
     * Get only method
     *
     * @http GET /getOnly
     *
     * @rateLimiter Kinikit\MVC\RateLimiter\TestRateLimiter
     *
     * @param $param1
     * @param float $param2
     * @param boolean $param3
     */
    public function getOnly($param1, $param2, $param3) {
        return array($param1, $param2, $param3);
    }

    /**
     * @http POST /getBackedEnum
     *
     * @param Backed $backedParam
     * @return string
     */
    public function getBackedEnum(Backed $backedParam) : string {
        return "There are ".$backedParam->value;
    }

    /**
     * @http POST /getUnbackedEnum
     *
     * @param Unbacked $unbackedParam
     * @return string
     */
    public function getUnbackedEnum(Unbacked $unbackedParam) : string {
        if ($unbackedParam == Unbacked::One){
            return "Singular";
        } else {
            return "Plural";
        }
    }

    /**
     * List all test objects
     *
     * @http GET
     *
     * @return \Kinikit\MVC\Objects\TestRESTObject[]
     */
    public function list() {
        $list = array();
        for ($i = 0; $i < 10; $i++) {
            $list[] = new TestRESTObject("TEST " . $i, "test$i@test.com");
        }

        return $list;
    }


    /**
     * Create a test object
     *
     * @http POST
     *
     * @param TestRESTObject $testObject
     * @return TestRESTObject
     */
    public function create($testObject) {
        $testObject->setLastStatus("POSTED");
        return $testObject;
    }


    /**
     * Update a test object
     *
     * @http PUT /$objectId
     *
     * @param integer $objectId
     * @param TestRESTObject $testObject
     * @return TestRESTObject
     */
    public function update($objectId, $testObject) {
        $testObject->setLastStatus("PUT $objectId");
        return $testObject;
    }


    /**
     * Patch a test object with new values
     *
     * @http PATCH /$objectId
     *
     * @param integer $objectId
     * @param mixed[] $data
     * @return TestRESTObject
     */
    public function patch($objectId, $data) {
        $object = $this->get($objectId);
        $object->bind($data);
        $object->setLastStatus("PATCHED $objectId");
        return $object;
    }


    /**
     * Delete a test object by id
     *
     * @http DELETE /$objectId
     *
     * @param integer $objectId
     * @return TestRESTObject
     */
    public function delete($objectId) {
        $testObject = $this->get($objectId);
        $testObject->setLastStatus("DELETED $objectId");
        return $testObject;
    }


    /**
     * Throws exception function
     *
     * @throws \Exception
     */
    public function throwsException() {
        throw new \Exception("Bad REST Call", 22);
    }


    /**
     * Status exception
     *
     * @throws StatusException
     */
    public function throwsStatusException() {
        throw new StatusException("Should return a custom error response code", 406, 50);
    }


}
