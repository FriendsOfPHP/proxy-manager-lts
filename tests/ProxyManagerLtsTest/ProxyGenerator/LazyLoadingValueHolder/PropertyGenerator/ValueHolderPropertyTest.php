<?php

declare(strict_types=1);

namespace ProxyManagerLtsTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator;

use Laminas\Code\Generator\PropertyGenerator;
use ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty;
use ProxyManagerLtsTest\ProxyGenerator\PropertyGenerator\AbstractUniquePropertyNameTest;
use ReflectionClass;

/**
 * Tests for {@see \ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty}
 *
 * @covers \ProxyManagerLts\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderProperty
 * @group Coverage
 */
final class ValueHolderPropertyTest extends AbstractUniquePropertyNameTest
{
    protected function createProperty(): PropertyGenerator
    {
        return new ValueHolderProperty(new ReflectionClass(self::class));
    }

    /** @group #400 */
    public function testWillDocumentPropertyType(): void
    {
        $docBlock = (new ValueHolderProperty(new ReflectionClass(self::class)))->getDocBlock();

        self::assertNotNull($docBlock);
        self::assertEquals(
            <<<'PHPDOC'
/**
 * @var \ProxyManagerLtsTest\ProxyGenerator\LazyLoadingValueHolder\PropertyGenerator\ValueHolderPropertyTest|null wrapped object, if the proxy is initialized
 */

PHPDOC
            ,
            $docBlock->generate()
        );
    }
}
