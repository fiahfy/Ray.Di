<?php
namespace Ray\Di;

/**
 * Test class for Module.
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Forge
     */
    protected $module;

    protected $config;

    const NAME = 'user_db';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->module = new Modules\BasicModule();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testConfigureTo()
    {
        $expected = array(AbstractModule::TO_CLASS, 'Ray\Di\Mock\UserDb');
        $actual = $this->module['Ray\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected, $actual);
    }

    public function testConfigureToProvider()
    {
        $module = new Modules\ProviderModule;
        $expected = array(AbstractModule::TO_PROVIDER, 'Ray\Di\Modules\DbProvider');
        $actual = $module['Ray\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected, $actual);
    }

    public function testConfigureToInstance()
    {
        $module = new Modules\InstanceModule;
        $expected = array(AbstractModule::TO_INSTANCE, new Mock\UserDb());
        $actual = $module['Ray\Di\Mock\DbInterface'][Definition::NAME_UNSPECIFIED][AbstractModule::TO];
        $this->assertSame($expected[0], AbstractModule::TO_INSTANCE);
        $this->assertSame('\Ray\Di\Mock\UserDb', $actual[1]);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->module['Ray\Di\Mock\DbInterface']));
    }

    /**
     * @expectedException Ray\Di\Exception\ReadOnly
     */
    public function testOffsetSet()
    {
        $this->module['Ray\Di\DbInterface'] = 'Ray\Di\Mock\DbInterface';
    }

    /**
     * @expectedException Ray\Di\Exception\ReadOnly
     */
    public function testOffsetUnset()
    {
        unset($this->module['Ray\Di\Mock\DbInterface']);
    }

    /**
     * @covers Ray\Di\AbstractModule::__toString
     */
    public function testToString()
    {
        $this->assertTrue(is_string((string)$this->module));
    }

    //     public function restregisterInterceptAnnotation()
    //     {
    //         $module = new Modules\AopModule;
    //         $interceptorClass = 'Ray\Di\SalesTax';
    //         $expected = array(new $interceptorClass);
    //         $this->assertSame($interceptorClass, get_class($module->annotations['SalesTax'][0]));
    //     }

    /**
     * @expectedException Ray\Di\Exception\InvalidToBinding
     */
    public function testToInvalid()
    {
        new Modules\InvalidToModule;
    }

    /**
     * @expectedException Ray\Di\Exception\InvalidProviderBinding
     */
    public function testToProviderInvalid()
    {
        new Modules\InvalidProviderModule;
    }

    public function testToStringInstance()
    {
        $module = new \Ray\Di\Modules\InstanceModule;
        $expected = "bind('')->annotatedWith('id')->toInstance((string)PC6001)
bind('')->annotatedWith('user_name')->toInstance((string)koriym)
bind('')->annotatedWith('user_age')->toInstance((integer)21)
bind('')->annotatedWith('user_gender')->toInstance((string)male)
bind('Ray\Di\Mock\DbInterface')->to('\Ray\Di\Mock\UserDb')
bind('Ray\Di\Mock\UserInterface')->toInstance((object)Ray\Di\Mock\User)\n";
        $this->assertSame($expected, (string)$module);
    }

    public function testToStringInstanceArray()
    {
        $module = new \Ray\Di\Modules\ArrayInstance;
        $expected = "bind('')->annotatedWith('adapters')->toInstance((array)[\"html\",\"http\"])\n";
        $this->assertSame($expected, (string)$module);
    }

    public function testToStringDecoratedModule()
    {
        $module = new \Ray\Di\Modules\BasicModule(new \Ray\Di\Modules\ArrayInstance);
        $expected = "bind('')->annotatedWith('adapters')->toInstance((array)[\"html\",\"http\"])
bind('Ray\Di\Mock\DbInterface')->to('Ray\Di\Mock\UserDb')\n";
        $this->assertSame($expected, (string)$module);
    }

    /**
     * This module binds nothing
     */
    public function testInvokeReturnFalse()
    {
        $module = $this->module;
        $binder = $module('Ray\Di\Tests\RealBillingService');
        $this->assertSame(false, $binder->hasBinding());
    }

    public function testInvokeReturnBinder()
    {
        $module = new \Ray\Di\Modules\AopMatcherModule;;
        $binder = $module('Ray\Di\Tests\RealBillingService');
        $this->assertInstanceOf('\Ray\Aop\Bind', $binder);
    }

    public function testAopAnyMatcherModule()
    {
        $module = new \Ray\Di\Modules\AopAnyMatcherModule;;
        $bind = $module('Ray\$bind\Tests\RealBillingService');
        $this->assertInstanceOf('Ray\Aop\Bind', $bind);
        $interceptors = $bind('any_method_name');
        $this->assertInstanceOf('\Ray\Di\Tests\TaxCharger', $interceptors[0]);
    }

    public function testAopAnnotateMatcherModule()
    {
        $module = new \Ray\Di\Modules\AopAnnotateMatcherModule;
        $bind = $module('Ray\Di\Tests\RealBillingService');
        $result = $bind('chargeOrderWithNoTax');
        $this->assertSame(false, $result);
    }

    public function testAopAnnotateMatcherModuleGetCorrectIntercecptor()
    {
        $module = new \Ray\Di\Modules\AopAnnotateMatcherModule;
        $bind = $module('Ray\Di\Tests\RealBillingService');
        $result = $bind('chargeOrder');
        $this->assertInstanceOf('\Ray\Di\Tests\TaxCharger', $result[0]);
    }

    public function testInstall()
    {
        $module = new \Ray\Di\Modules\InstallModule;
        $this->assertTrue(isset($module->bindings['Ray\\Di\\Mock\\DbInterface']));
        $this->assertTrue(isset($module->bindings['Ray\\Di\\Mock\\LogInterface']));
    }
}
