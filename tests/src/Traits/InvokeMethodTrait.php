<?php

namespace Drupal\Tests\template_whisperer\Traits;

/**
 * Provides a function to invoke protected/private methods of a class.
 */
trait InvokeMethodTrait {

  /**
   * Calls protected/private method of a class.
   *
   * @param object &$object
   *   Instantiated object that we will run method on.
   * @param string $method_name
   *   Method name to call.
   * @param array $parameters
   *   Array of parameters to pass into method.
   * @param array $protected_properties
   *   Array of values that should be set on protected properties.
   *
   * @throws \ReflectionException
   *
   * @return mixed
   *   Method return.
   */
  protected function invokeMethod(&$object, $method_name, array $parameters = [], array $protected_properties = []) {
    $reflection = new \ReflectionClass(\get_class($object));

    foreach ($protected_properties as $property => $value) {
      $property = $reflection->getProperty($property);
      $property->setAccessible(TRUE);
      $property->setValue($object, $value);
    }

    $method = $reflection->getMethod($method_name);
    $method->setAccessible(TRUE);

    return $method->invokeArgs($object, $parameters);
  }

}
