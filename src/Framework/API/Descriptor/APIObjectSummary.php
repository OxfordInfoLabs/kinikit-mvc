<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 18/10/2018
 * Time: 13:00
 */

namespace Kinikit\MVC\Framework\API\Descriptor;


use Kinikit\Core\Object\DynamicSerialisableObject;


/**
 * Summary class for displaying object summaries
 *
 */
class APIObjectSummary extends DynamicSerialisableObject {

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * APIObjectSummary constructor.
     * @param string $path
     * @param string $name
     */
    public function __construct($path = null, $name = null) {
        parent::__construct(false);

        $this->path = $path;
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }


}