<?php

namespace atoum\atoum\tests\units\php84;

use atoum\atoum;
use atoum\atoum\tests\units\php84\fixtures\TemperatureWithHooks;

/**
 * Test demonstrating property hooks with atoum
 *
 * @requires PHP >= 8.4
 */
class TemperatureWithHooksTest extends atoum
{
    /**
     * Test basic property assignment and reading
     */
    public function testBasicPropertyUsage()
    {
        $this
            ->given($temp = new TemperatureWithHooks())
            ->when($temp->celsius = 0)
            ->then
                ->float($temp->celsius)->isEqualTo(0)
                ->float($temp->fahrenheit)->isEqualTo(32)
                ->float($temp->kelvin)->isEqualTo(273.15)
        ;
    }

    /**
     * Test temperature conversions using property hooks
     */
    public function testTemperatureConversions()
    {
        $this
            ->given($temp = new TemperatureWithHooks())

            // Set via Celsius, check all scales
            ->when($temp->celsius = 100)
            ->then
                ->float($temp->celsius)->isEqualTo(100)
                ->float($temp->fahrenheit)->isEqualTo(212) // Boiling point
                ->float($temp->kelvin)->isEqualTo(373.15)

            // Set via Fahrenheit, check Celsius
            ->when($temp->fahrenheit = 32)
            ->then
                ->float($temp->celsius)->isEqualTo(0) // Freezing point
                ->float($temp->fahrenheit)->isEqualTo(32)
        ;
    }

    /**
     * Test validation in set hook
     */
    public function testValidationBelowAbsoluteZero()
    {
        $this
            ->given($temp = new TemperatureWithHooks())
            ->exception(function () use ($temp) {
                $temp->celsius = -300; // Below absolute zero
            })
                ->isInstanceOf(\ValueError::class)
                ->hasMessage('Temperature cannot be below absolute zero (-273.15°C)')
        ;
    }

    /**
     * Test that absolute zero itself is valid
     */
    public function testAbsoluteZeroIsValid()
    {
        $this
            ->given($temp = new TemperatureWithHooks())
            ->when($temp->celsius = -273.15)
            ->then
                ->float($temp->celsius)->isEqualTo(-273.15)
                ->float($temp->kelvin)->isEqualTo(0)
        ;
    }

    /**
     * Test scale property with transformation hook
     */
    public function testScaleProperty()
    {
        $this
            ->given($temp = new TemperatureWithHooks())

            // Test uppercase transformation
            ->when($temp->scale = 'celsius')
            ->then
                ->string($temp->scale)->isEqualTo('CELSIUS')

            // Test with mixed case
            ->when($temp->scale = 'FaHrEnHeIt')
            ->then
                ->string($temp->scale)->isEqualTo('FAHRENHEIT')
        ;
    }

    /**
     * Test scale validation
     */
    public function testInvalidScale()
    {
        $this
            ->given($temp = new TemperatureWithHooks())
            ->exception(function () use ($temp) {
                $temp->scale = 'invalid';
            })
                ->isInstanceOf(\ValueError::class)
                ->message->contains('Invalid scale')
        ;
    }

    /**
     * Test read-only property (kelvin has only get hook)
     */
    public function testReadOnlyKelvin()
    {
        $this
            ->given($temp = new TemperatureWithHooks(25))
            ->then
                ->float($temp->kelvin)->isEqualTo(298.15)

            // Note: In PHP 8.4, trying to set a property with only get hook
            // will result in an error
            ->exception(function () use ($temp) {
                $temp->kelvin = 300;
            })
                ->isInstanceOf(\Error::class)
        ;
    }

    /**
     * Test getValue method integration
     */
    public function testGetValueMethod()
    {
        $this
            ->given($temp = new TemperatureWithHooks(100))
            ->when($temp->scale = 'celsius')
            ->then
                ->float($temp->getValue())->isEqualTo(100)

            ->when($temp->scale = 'fahrenheit')
            ->then
                ->float($temp->getValue())->isEqualTo(212)

            ->when($temp->scale = 'kelvin')
            ->then
                ->float($temp->getValue())->isEqualTo(373.15)
        ;
    }

    /**
     * Test helper methods
     */
    public function testHelperMethods()
    {
        $this
            ->given($temp = new TemperatureWithHooks(-10))
            ->then
                ->boolean($temp->isFreezing())->isTrue()
                ->boolean($temp->isBoiling())->isFalse()

            ->given($temp2 = new TemperatureWithHooks(150))
            ->then
                ->boolean($temp2->isFreezing())->isFalse()
                ->boolean($temp2->isBoiling())->isTrue()
        ;
    }

    /**
     * Test mocking a class with property hooks
     * This demonstrates atoum's support for PHP 8.4 property hooks
     */
    public function testMockingWithPropertyHooks()
    {
        // Note: This test will only work if running on PHP 8.4+
        // and the mock generator supports property hooks

        if (version_compare(PHP_VERSION, '8.4.0', '<')) {
            $this->skip('This test requires PHP 8.4+');
        }

        $this
            ->given($mock = new \mock\examples\php84\TemperatureWithHooks())

            // Mock the celsius property get hook
            ->and($this->calling($mock)->__get_celsius = 25.0)

            ->then
                ->float($mock->celsius)->isEqualTo(25.0)
                ->mock($mock)
                    ->call('__get_celsius')->once()
        ;
    }

    /**
     * Test that mock controller tracks property hook calls
     */
    public function testMockTracksPropertyHookCalls()
    {
        if (version_compare(PHP_VERSION, '8.4.0', '<')) {
            $this->skip('This test requires PHP 8.4+');
        }

        $this
            ->given($mock = new \mock\examples\php84\TemperatureWithHooks())

            // Mock the set hook behavior
            ->and($this->calling($mock)->__set_celsius = null)

            ->when($mock->celsius = 42.0)

            ->then
                ->mock($mock)
                    ->call('__set_celsius')
                        ->withArguments(42.0)
                        ->once()
        ;
    }
}
