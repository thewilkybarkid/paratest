<?php

declare(strict_types=1);

namespace ParaTest\Runners\PHPUnit;

class ConfigurationTest extends \TestBase
{
    /**
     * @var string
     */
    protected $path;
    /**
     * @var Configuration
     */
    protected $config;

    public function setUp(): void
    {
        $this->path = realpath(PARATEST_ROOT . '/phpunit.xml.dist');
        $this->config = new Configuration($this->path);
    }

    public function testToStringReturnsPath()
    {
        $this->assertEquals($this->path, (string) $this->config);
    }

    public function test_getSuitesShouldReturnCorrectNumberOfSuites()
    {
        $suites = $this->config->getSuites();
        $this->assertCount(2, $suites);

        return $suites;
    }

    public function test_hasSuites()
    {
        $actual = $this->config->hasSuites();
        $this->assertTrue($actual);
    }

    public function testGlobbingSupport()
    {
        $basePath = getcwd() . DS;
        $configuration = new Configuration($this->fixture('phpunit-globbing.xml'));
        /** @var SuitePath[][] $suites */
        $suites = $configuration->getSuites();
        $this->assertEquals($basePath . 'test' . DS . 'fixtures' . DS . 'globbing-support-tests' . DS . 'some-dir', $suites['ParaTest Fixtures'][0]->getPath());
        $this->assertEquals($basePath . 'test' . DS . 'fixtures' . DS . 'globbing-support-tests' . DS . 'some-dir2', $suites['ParaTest Fixtures'][1]->getPath());

        return $suites;
    }

    /**
     * @depends test_getSuitesShouldReturnCorrectNumberOfSuites
     *
     * @param mixed $suites
     */
    public function testSuitesContainSuiteNameAtKey($suites)
    {
        $this->assertArrayHasKey('ParaTest Unit Tests', $suites);
        $this->assertArrayHasKey('ParaTest Functional Tests', $suites);

        return $suites;
    }

    /**
     * @depends testSuitesContainSuiteNameAtKey
     *
     * @param mixed $suites
     */
    public function testSuitesContainPathAsValue($suites)
    {
        $basePath = getcwd() . DS;
        $unitSuite = $suites['ParaTest Unit Tests'];
        $this->assertIsArray($unitSuite);
        $this->assertCount(1, $unitSuite);
        $unitSuitePath = $unitSuite[0];
        $this->assertInstanceOf(SuitePath::class, $unitSuitePath);
        $this->assertEquals($basePath . 'test' . DS . 'unit', $unitSuitePath->getPath());
        $functionalSuite = $suites['ParaTest Functional Tests'];
        $this->assertIsArray($functionalSuite);
        $this->assertCount(1, $functionalSuite);
        $functionalSuitePath = $functionalSuite[0];
        $this->assertInstanceOf(SuitePath::class, $functionalSuitePath);
        $this->assertEquals($basePath . 'test' . DS . 'functional', $functionalSuitePath->getPath());
    }

    public function testGetEnvironmentVariables()
    {
        $this->assertCount(2, $this->config->getEnvironmentVariables());
        $this->assertArrayHasKey('APP_ENV', $this->config->getEnvironmentVariables());
        $this->assertArrayHasKey('CACHE_DRIVER', $this->config->getEnvironmentVariables());

        $config = new Configuration(realpath(__DIR__ . '/phpunit.xml.dist'));
        $this->assertCount(0, $config->getEnvironmentVariables());
    }

    public function testLoadConfigEvenIfLibXmlEntityLoaderIsDisabled()
    {
        $before = libxml_disable_entity_loader();
        $e = null;

        try {
            $this->config = new Configuration($this->path);
        } catch (\Exception $exc) {
            $e = $exc;
        }

        libxml_disable_entity_loader($before);

        $this->assertNull($e, 'Could not instantiate Configuration: ' . ($e instanceof \Exception ? $e->getMessage() : 'no error given'));
    }
}
