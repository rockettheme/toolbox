<?php

use PHPUnit\Framework\TestCase;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceIterator;
use RocketTheme\Toolbox\ResourceLocator\RecursiveUniformResourceIterator;

class UniformResourceLocatorTest extends TestCase
{
    /**
     * @var UniformResourceLocator
     */
    static protected $locator;

    public static function setUpBeforeClass(): void
    {
        // Share locator in all tests.
        self::$locator = new UniformResourceLocator(self::base());
    }

    /**
     * @return string
     */
    private static function base()
    {
        return __DIR__ . '/data';
    }

    public function testGetBase()
    {
        $this->assertEquals(__DIR__ . '/data', self::$locator->getBase());
    }

    /**
     * @param $scheme
     * @param $path
     * @param $lookup
     *
     * @dataProvider addPathProvider
     */
    public function testAddPath($scheme, $path, $lookup)
    {
        $locator = self::$locator;

        $this->assertFalse($locator->schemeExists($scheme));

        $locator->addPath($scheme, $path, $lookup);

        $this->assertTrue($locator->schemeExists($scheme));
    }

    public static function addPathProvider() {
        return [
            ['base', '', 'base'],
            ['local', '', 'local'],
            ['override', '', 'override'],
            ['all', '', ['override://all', 'local://all', 'base://all']],
        ];
    }

    /**
     * @depends testAddPath
     */
    public function testGetSchemes()
    {
        $this->assertEquals(
            ['base', 'local', 'override', 'all'],
            self::$locator->getSchemes()
        );
    }

    /**
     * @depends testAddPath
     * @dataProvider getPathsProvider
     */
    public function testGetPaths($scheme, $expected)
    {
        $locator = self::$locator;

        $this->assertEquals($expected, $locator->getPaths($scheme));
    }


    public static function getPathsProvider() {
        return [
            ['base', ['' => ['base']]],
            ['local', ['' => ['local']]],
            ['override', ['' => ['override']]],
            ['all', ['' => [['override', 'all'], ['local', 'all'], ['base', 'all']]]],
            ['fail', []]
        ];
    }

    /**
     * @depends testAddPath
     */
    public function testSchemeExists()
    {
        $locator = self::$locator;

        // Partially tested in addPath() tests.
        $this->assertFalse($locator->schemeExists('foo'));
        $this->assertFalse($locator->schemeExists('file'));
    }

    /**
     * @depends testAddPath
     */
    public function testGetIterator()
    {
        $locator = self::$locator;

        $this->assertInstanceOf(
            UniformResourceIterator::class,
            $locator->getIterator('all://')
        );

        $this->expectException(\InvalidArgumentException::class);
        $locator->getIterator('fail://');
    }

    /**
     * @depends testAddPath
     */
    public function testGetRecursiveIterator()
    {
        $locator = self::$locator;

        $this->assertInstanceOf(
            RecursiveUniformResourceIterator::class,
            $locator->getRecursiveIterator('all://')
        );

        $this->expectException(\InvalidArgumentException::class);
        $locator->getRecursiveIterator('fail://');
    }

    /**
     * @depends testAddPath
     */
    public function testIsStream($uri)
    {
        $locator = self::$locator;

        // Existing file.
        $this->assertEquals(true, $locator->isStream('all://base.txt'));
        // Non-existing file.
        $this->assertEquals(true, $locator->isStream('all://bar.txt'));
        // Unknown uri type.
        $this->assertEquals(false, $locator->isStream('fail://base.txt'));
        // Bad uri.
        $this->assertEquals(false, $locator->isStream('fail://../base.txt'));
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize($uri, $path)
    {
        $locator = self::$locator;

        $this->assertEquals($path, $locator->normalize($uri));
    }

    /**
     * @depends testAddPath
     * @dataProvider findResourcesProvider
     */
    public function testFindResource($uri, $paths)
    {
        $locator = self::$locator;
        $path = $paths ? reset($paths) : false;
        $fullPath = !$path ? false : __DIR__ . "/data/{$path}";

        $this->assertEquals($fullPath, $locator->findResource($uri));
        $this->assertEquals($path, $locator->findResource($uri, false));
    }

    /**
     * @depends testAddPath
     * @dataProvider findResourcesProvider
     */
    public function testFindResources($uri, $paths)
    {
        $locator = self::$locator;

        $this->assertEquals($paths, $locator->findResources($uri, false));
    }

    /**
     * @depends testFindResource
     * @dataProvider findResourcesProvider
     */
    public function testInvoke($uri, $paths)
    {
        $locator = self::$locator;
        $path = $paths ? reset($paths) : false;
        $fullPath = !$path ? false : __DIR__ . "/data/{$path}";

        $this->assertEquals($fullPath, $locator($uri));
    }


    /**
     * A relative path with no scheme resolves against the base, and must stay
     * inside it: anything climbing past the base is refused rather than
     * resolved. Absolute paths, drive letters and streams are untouched.
     */
    public static function normalizeProvider() {
        return [
            ['', self::base()],
            ['./', self::base() . '/'],
            ['././/./', self::base() . '/'],
            ['././/../', false],
            ['/', '/'],
            ['//', '/'],
            ['///', '/'],
            ['/././', '/'],
            ['foo', self::base() . '/foo'],
            ['/foo', '/foo'],
            ['//foo', '/foo'],
            ['/foo/', '/foo/'],
            ['//foo//', '/foo/'],
            ['path/to/file.txt', self::base() . '/path/to/file.txt'],
            ['path/to/../file.txt', self::base() . '/path/file.txt'],
            ['path/to/../../file.txt', self::base() . '/file.txt'],
            ['path/to/../../../file.txt', false],
            ['/path/to/file.txt', '/path/to/file.txt'],
            ['/path/to/../file.txt', '/path/file.txt'],
            ['/path/to/../../file.txt', '/file.txt'],
            ['/path/to/../../../file.txt', false],
            ['c:\\', 'c:/'],
            ['c:\\path\\to\file.txt', 'c:/path/to/file.txt'],
            ['c:\\path\\to\../file.txt', 'c:/path/file.txt'],
            ['c:\\path\\to\../../file.txt', 'c:/file.txt'],
            ['c:\\path\\to\../../../file.txt', false],
            ['stream://path/to/file.txt', 'stream://path/to/file.txt'],
            ['stream://path/to/../file.txt', 'stream://path/file.txt'],
            ['stream://path/to/../../file.txt', 'stream://file.txt'],
            ['stream://path/to/../../../file.txt', false],

        ];
    }
    public static function findResourcesProvider() {
        return [
            ['all://base.txt', ['base/all/base.txt']],
            ['all://base_all.txt', ['override/all/base_all.txt', 'local/all/base_all.txt', 'base/all/base_all.txt']],
            ['all://base_local.txt', ['local/all/base_local.txt', 'base/all/base_local.txt']],
            ['all://base_override.txt', ['override/all/base_override.txt', 'base/all/base_override.txt']],
            ['all://local.txt', ['local/all/local.txt']],
            ['all://local_override.txt', ['override/all/local_override.txt', 'local/all/local_override.txt']],
            ['all://override.txt', ['override/all/override.txt']],
            ['all://missing.txt', []],
            ['all://asdf/../base.txt', ['base/all/base.txt']],
        ];
    }

    /* *
     * @depends testAddPath
     * /
    public function testMergeResources()
    {
        $locator = self::$locator;
    }

    public function testReset()
    {
        $locator = self::$locator;
    }

    public function testResetScheme()
    {
        $locator = self::$locator;
    }
    */
}
