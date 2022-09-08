<?php

declare(strict_types=1);


use Enjoys\Dotenv\EnvCollection;
use PHPUnit\Framework\TestCase;

class EnvCollectionTest extends TestCase
{

    private EnvCollection $collection;

    protected function setUp(): void
    {
        $this->collection = new EnvCollection();
        $this->collection->add('VAR1', 'value');
        $this->collection->add('VAR2', true);
        $this->collection->add('VAR3', null);
    }

    public function testDelete()
    {
        $this->collection->delete('VAR2');
        $this->collection->delete('NOT_ISSET_VAR');
        $this->assertSame(['VAR1', 'VAR3'],  $this->collection->getKeys());
    }

    public function testHas()
    {
        $this->assertTrue($this->collection->has('VAR1'));
        $this->assertTrue($this->collection->has('VAR3'));
        $this->assertFalse($this->collection->has('NOT_ISSET_VAR'));
    }

    public function testGetKeys()
    {
        $this->assertSame(['VAR1', 'VAR2', 'VAR3'],  $this->collection->getKeys());
    }

    public function testGetCollection()
    {
        $this->assertSame(['VAR1' => 'value', 'VAR2' => true, 'VAR3' => null],  $this->collection->getCollection());
    }

    public function testGet()
    {
        $this->assertSame(null,  $this->collection->get('VAR3'));
    }

    public function testAdd()
    {
        $this->collection->add('VAR4', 42);
        $this->assertSame(42,  $this->collection->get('VAR4'));
    }
}
