<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Object\SerialisableObject;
use Kinikit\Core\Util\Annotation\ClassAnnotationParser;
use Kinikit\Core\Util\Annotation\ClassAnnotations;
use Kinikit\Core\Util\Serialisation\XML\XMLToObjectConverter;
use Kinikit\MVC\Exception\InvalidControllerInterceptorException;

/**
 * Worker class to evaluate any controller interceptors which are defined for a given controller.
 *
 * @author mark
 *
 */
class ControllerInterceptorEvaluator extends SerialisableObject {

    private $interceptors = array();
    private static $instance;


    /**
     * Construct an evaluator statically from a configuration file.
     *
     * @param string $configFile
     * @return ControllerInterceptorEvaluator
     */
    public static function getInstance($forceReload = false) {
        if ($forceReload || !ControllerInterceptorEvaluator::$instance) {

            $evaluator = new ControllerInterceptorEvaluator();
            foreach (SourceBaseManager::instance()->getSourceBases() as $sourceBase) {

                if (file_exists($sourceBase . "/Config/controller-interceptors.xml")) {
                    $converter = new XMLToObjectConverter (array("ControllerInterceptors" => "Kinikit\MVC\Framework\ControllerInterceptorEvaluator",
                        "Interceptor" => "Kinikit\MVC\Framework\ControllerInterceptorDefinition"));
                    $newInstance = $converter->convert(file_get_contents($sourceBase . "/Config/controller-interceptors.xml"));

                    // Merge controller interceptors
                    if ($newInstance instanceof ControllerInterceptorEvaluator)
                        $evaluator->setInterceptors(array_merge($evaluator->getInterceptors(), $newInstance->getInterceptors()));
                }


            }


            ControllerInterceptorEvaluator::$instance = $evaluator;
        }

        return ControllerInterceptorEvaluator::$instance;
    }

    /**
     * @return the $interceptors
     */
    public function getInterceptors() {
        return $this->interceptors ? $this->interceptors : array();
    }

    /**
     * @param $interceptors the $interceptors to set
     */
    public function setInterceptors($interceptors) {

        // Handle single objects (convert to arrays)
        if ($interceptors && !is_array($interceptors)) {
            $interceptors = array($interceptors);
        }

        foreach ($interceptors as $interceptor) {
            if (!($interceptor instanceof ControllerInterceptorDefinition))
                throw new InvalidControllerInterceptorException ($interceptor->getClassName());
        }

        $this->interceptors = $interceptors;
    }

    /**
     * Evaluate any interceptors for the controller and method.  This will evaluate any statically defined
     * ones in the XML file as well as any found in the controller itself.
     *
     * @param Controller $controllerInstance
     * @param string $methodName
     * @param ClassAnnotations $annotations
     * @return bool
     */
    public function evaluateInterceptorsForControllerMethod($controllerInstance, $methodName, $annotations = null) {

        $controllerClass = get_class($controllerInstance);

        if (!$annotations) {
            $annotations = ClassAnnotationParser::instance()->parse($controllerClass);
        }

        $interceptors = array();
        foreach ($this->interceptors as $interceptor) {

            if ($interceptor->getControllers() == "*"
                || ($interceptor->getControllers() == $controllerClass)
                || (is_array($interceptor->getControllers()) && in_array($controllerClass, $interceptor->getControllers()))) {

                $interceptors[] = $interceptor;
            }
        }

        $classInterceptors = $annotations->getClassAnnotationForMatchingTag("interceptor");
        if ($classInterceptors) {
            foreach ($classInterceptors->getValues() as $interceptorClass) {
                $interceptors[] = new ControllerInterceptorDefinition($interceptorClass);
            }
        }

        // Evaluate each one in turn
        foreach ($interceptors as $interceptor) {
            $interceptorClass = $interceptor->getClassName();
            $interceptor = new $interceptorClass();
            $result = $interceptor->beforeMethod($controllerInstance, $methodName, $annotations);
            if (!$result) return false;
        }


        return true;
    }

}

?>