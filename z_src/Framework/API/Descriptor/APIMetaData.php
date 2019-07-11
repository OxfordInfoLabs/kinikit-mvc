<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 18/10/2018
 * Time: 14:32
 */

namespace Kinikit\MVC\Framework\API\Descriptor;


use Kinikit\Core\Configuration;
use Kinikit\Core\Object\DynamicSerialisableObject;


class APIMetaData extends DynamicSerialisableObject {


    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $title;


    /**
     * @var APIControllerSummary[]
     */
    private $controllerSummaries;


    /**
     * @var APIObjectSummary[]
     */
    private $objectSummaries;


    /**
     * @var APIObjectSummary[]
     */
    private $exceptionSummaries;


    /**
     * @var string[]
     */
    private $globalParameters;


    /**
     * @var string
     */
    private $clientNamespace;


    /**
     * Available clients
     *
     * @var array
     */
    private $availableClients;

    /**
     * Construct an api meta data.
     *
     * APIMetaData constructor.
     * @param null $identifier
     * @param string $title
     * @param APIControllerSummary[] $controllerSummaries
     * @param APIObjectSummary[] $objectSummaries
     * @param null $exceptionSummaries
     * @param string[] $globalParameters
     * @param null $clientNamespace
     * @param null $availableClients
     */
    public function __construct($identifier = null, $title = null, array $controllerSummaries = null, $objectSummaries = null, $exceptionSummaries = null, $globalParameters = null, $clientNamespace = null, $availableClients = null) {

        parent::__construct(false);

        $this->title = $title;
        $this->controllerSummaries = $controllerSummaries;
        $this->objectSummaries = $objectSummaries;
        $this->exceptionSummaries = $exceptionSummaries;
        $this->globalParameters = $globalParameters;
        $this->identifier = $identifier;
        $this->clientNamespace = $clientNamespace;
        $this->availableClients = $availableClients;
        $this->exceptionSummaries = $exceptionSummaries;
    }

    /**
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * @return mixed
     */
    public function getTitle() {
        return $this->title;
    }


    /**
     * @return APIControllerSummary[]
     */
    public function getControllerSummaries() {
        return $this->controllerSummaries;
    }

    /**
     * @return APIObjectSummary[]
     */
    public function getObjectSummaries() {
        return $this->objectSummaries;
    }

    /**
     * @return APIObjectSummary[]
     */
    public function getExceptionSummaries() {
        return $this->exceptionSummaries;
    }


    /**
     * @return string[]
     */
    public function getGlobalParameters() {
        return $this->globalParameters;
    }

    /**
     * @return string
     */
    public function getClientNamespace() {
        return $this->clientNamespace;
    }


    // get the source base path
    public function getClientSourceBasePath() {
        return str_replace("\\", "/", $this->getClientNamespace());
    }

    /**
     * @return array
     */
    public function getAvailableClients() {
        return $this->availableClients;
    }


}