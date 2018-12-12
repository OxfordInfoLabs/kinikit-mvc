<?php

namespace Kinikit\MVC\Framework\Controller;

/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 15/10/2018
 * Time: 11:19
 */
class TestREST extends RESTService {

    /**
     * Get a test object by id.
     * @http GET /$id
     *
     * @param integer $id
     * @return \Kinikit\MVC\Framework\Controller\TestRESTObject
     */
    public function get($id) {
        return new TestRESTObject($id, "TEST " . $id, "test$id@test.com", "GET SINGLE");
    }


    /**
     * Get the count of objects
     *
     * @http GET /count
     * @return integer
     */
    public function count() {
        return 50;
    }


    /**
     * List all test objects
     *
     * @http GET
     *
     * @return \Kinikit\MVC\Framework\Controller\TestRESTObject[]
     */
    public function list() {
        $list = array();
        for ($i = 0; $i < 10; $i++) {
            $list[] = new TestRESTObject("$i", "TEST " . $i, "test$i@test.com");
        }

        return $list;
    }


    /**
     * Create a test object
     *
     * @http POST
     *
     * @param \Kinikit\MVC\Framework\Controller\TestRESTObject $testObject
     * @return \Kinikit\MVC\Framework\Controller\TestRESTObject
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
     * @param \Kinikit\MVC\Framework\Controller\TestRESTObject $testObject
     * @return \Kinikit\MVC\Framework\Controller\TestRESTObject
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
     * @return \Kinikit\MVC\Framework\Controller\TestRESTObject
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
     * @return \Kinikit\MVC\Framework\Controller\TestRESTObject
     */
    public function delete($objectId) {
        $testObject = $this->get($objectId);
        $testObject->setLastStatus("DELETED $objectId");
        return $testObject;
    }


    /**
     * Get a test object by id using a nested url.
     * @http GET /nested/$id
     *
     * @param integer $id
     * @return \Kinikit\MVC\Framework\Controller\TestRESTObject
     */
    public function nestedGet($id) {
        return new TestRESTObject($id, "TEST " . $id, "test$id@test.com", "GET NESTED SINGLE");
    }


    /**
     * Get a nested count
     *
     * @http GET /nested/count
     *
     * @return integer
     */
    public function nestedCount(){
        return 100;
    }


    /**
     * Create a test object
     *
     * @http POST /nested
     *
     * @param \Kinikit\MVC\Framework\Controller\TestRESTObject $testObject
     * @return \Kinikit\MVC\Framework\Controller\TestRESTObject
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
        return new TestRESTObject($id, $name, "test$id@test.com", "GET NESTED VARIABLE SINGLE");
    }

}