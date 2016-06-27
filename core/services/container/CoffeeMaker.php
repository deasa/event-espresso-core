<?php
namespace EventEspresso\core\services\container;

use EventEspresso\core\exceptions\InvalidClassException;
use EventEspresso\core\exceptions\InvalidIdentifierException;
use EventEspresso\core\services\container\exceptions\InstantiationException;

if ( ! defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}



/**
 * Class CoffeeMaker
 * Abstract Parent class for CoffeeMakers which are responsible
 * for building objects that are requested from the CoffeeShop
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.1
 */
abstract class CoffeeMaker implements CoffeeMakerInterface
{

    /**
     * Indicates that CoffeeMaker should construct a NEW entity instance from the provided arguments (if given)
     */
    const BREW_NEW = 'new';

    /**
     * Indicates that CoffeeMaker should always return a SHARED instance
     */
    const BREW_SHARED = 'shared';

    /**
     * Indicates that CoffeeMaker should only load the file/class/interface but NOT instantiate
     */
    const BREW_LOAD_ONLY = 'load_only';


    /**
     * @var CoffeePotInterface $coffee_pot
     */
    private $coffee_pot;

    /**
     * @var DependencyInjector $injector
     */
    private $injector;



    /**
     * @return array
     */
    public static function getTypes()
    {
        return (array)apply_filters(
            'FHEE__EventEspresso\core\services\container\CoffeeMaker__getTypes',
            array(
                CoffeeMaker::BREW_NEW,
                CoffeeMaker::BREW_SHARED,
                CoffeeMaker::BREW_LOAD_ONLY,
            )
        );
    }



    /**
     * @param $type
     */
    public static function validateType($type)
    {
        $types = CoffeeMaker::getTypes();
        if ( ! in_array($type, $types)) {
            throw new InvalidIdentifierException(
                is_object($type) ? get_class($type) : gettype($type),
                __(
                    'recipe type (one of the class constants on \EventEspresso\core\services\container\CoffeeMaker)',
                    'event_espresso'
                )
            );
        }
        return $type;
    }



    /**
     * CoffeeMaker constructor.
     *
     * @param CoffeePotInterface $coffee_pot
     * @param InjectorInterface  $injector
     */
    public function __construct(CoffeePotInterface $coffee_pot, InjectorInterface $injector)
    {
        $this->coffee_pot = $coffee_pot;
        $this->injector = $injector;
    }



    /**
     * @return \EventEspresso\core\services\container\CoffeePotInterface
     */
    protected function coffeePot()
    {
        return $this->coffee_pot;
    }



    /**
     * @return \EventEspresso\core\services\container\DependencyInjector
     */
    protected function injector()
    {
        return $this->injector;
    }



    /**
     * Examines the constructor to determine which method should be used for instantiation
     *
     * @param \ReflectionClass $reflector
     * @return mixed
     */
    protected function resolveInstantiationMethod(\ReflectionClass $reflector)
    {
        if ($reflector->getConstructor() === null) {
            return 'NewInstance';
        } else if ($reflector->isInstantiable()) {
            return 'NewInstanceArgs';
        } else if (method_exists($reflector->getName(), 'instance')) {
            return 'instance';
        } else if (method_exists($reflector->getName(), 'new_instance')) {
            return 'new_instance';
        } else if (method_exists($reflector->getName(), 'new_instance_from_db')) {
            return 'new_instance_from_db';
        } else {
            throw new InstantiationException($reflector->getName());
        }
    }



    /**
     * Ensures files for classes that are not PSR-4 compatible are loaded
     * and then verifies that classes exist where applicable
     *
     * @param RecipeInterface $recipe
     * @throws InvalidClassException
     */
    protected function resolveClassAndFilepath(RecipeInterface $recipe)
    {
        $paths = $recipe->paths();
        if ( ! empty($paths)) {
            foreach ($paths as $path) {
                if (strpos($path, '*') === false && is_readable($path)) {
                    require_once($path);
                }
            }
        }
        if ($recipe->type() !== CoffeeMaker::BREW_LOAD_ONLY && ! class_exists($recipe->fqcn())) {
            throw new InvalidClassException($recipe->identifier());
        }
    }



}
// End of file CoffeeMaker.php
// Location: /CoffeeMaker.php