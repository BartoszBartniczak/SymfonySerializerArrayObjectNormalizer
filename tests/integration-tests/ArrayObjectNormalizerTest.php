<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer;


use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\ArrayObjectSubclass;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\Person;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ArrayObjectNormalizerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Serializer
     */
    private $serializer;

    protected function setUp()
    {
        parent::setUp();

        $normalizers = [
            new ArrayObjectNormalizer(),
            new ObjectNormalizer(),
        ];

        $encoders = [
            new JsonEncoder()
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testEmptyArrayDeSerialization()
    {
        $object = new \ArrayObject([]);
        $json = $this->serializer->serialize($object, 'json');
        $this->assertEquals('[]', $json);

        $this->assertEquals($object, $this->serializer->deserialize($json, \ArrayObject::class, 'json'));
    }

    public function testArrayOfIntegersDeSerialization()
    {
        $object = new \ArrayObject([1, 3, 5, 7]);
        $json = $this->serializer->serialize($object, 'json');
        $this->assertEquals('[1,3,5,7]', $json);

        $this->assertEquals($object, $this->serializer->deserialize($json, \ArrayObject::class, 'json'));
    }

    public function testArrayOfIntegersWithStringKeysDeSerialization()
    {
        $object = new \ArrayObject(['a' => 1, 'c' => 3, 'e' => 5, 'g' => 7]);
        $json = $this->serializer->serialize($object, 'json');
        $this->assertEquals('{"a":1,"c":3,"e":5,"g":7}', $json);

        $this->assertEquals($object, $this->serializer->deserialize($json, \ArrayObject::class, 'json'));
    }

    public function testSubclassDeSerialization()
    {
        $object = new ArrayObjectSubclass(['a' => 1, 'c' => 3, 'e' => 5, 'g' => 7]);
        $json = $this->serializer->serialize($object, 'json');
        $this->assertEquals('{"a":1,"c":3,"e":5,"g":7}', $json);

        $this->assertEquals($object, $this->serializer->deserialize($json, ArrayObjectSubclass::class, 'json'));
    }

    public function testArrayOfObjectsDeSerialization()
    {
        $object = new \ArrayObject([
            new Person('Albert Einstein'),
            new Person('Nikola Tesla')
        ]);
        $json = $this->serializer->serialize($object, 'json');
        $this->assertEquals('[{"name":"Albert Einstein"},{"name":"Nikola Tesla"}]', $json);

        $this->assertEquals($object, $this->serializer->deserialize($json, \ArrayObject::class . '<' . Person::class . '>', 'json'));
    }

    public function testArrayOfObjectsWithStringKeysDeSerialization()
    {
        $object = new \ArrayObject([
            'einstein' => new Person('Albert Einstein'),
            'tesla' => new Person('Nikola Tesla')
        ]);
        $json = $this->serializer->serialize($object, 'json');
        $this->assertEquals('{"einstein":{"name":"Albert Einstein"},"tesla":{"name":"Nikola Tesla"}}', $json);

        $this->assertEquals($object, $this->serializer->deserialize($json, \ArrayObject::class . '<' . Person::class . '>', 'json'));
    }

    public function testSubclassArrayOfObjectsDeSerialization(){
        $object = new ArrayObjectSubclass([
            'einstein' => new Person('Albert Einstein'),
            'tesla' => new Person('Nikola Tesla')
        ]);
        $json = $this->serializer->serialize($object, 'json');
        $this->assertEquals('{"einstein":{"name":"Albert Einstein"},"tesla":{"name":"Nikola Tesla"}}', $json);

        $this->assertEquals($object, $this->serializer->deserialize($json, ArrayObjectSubclass::class . '<' . Person::class . '>', 'json'));
    }

}
