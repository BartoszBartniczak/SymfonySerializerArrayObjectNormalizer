<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer;


use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\ArrayObjectSubclass;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\NotArrayObject;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\Person;
use BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures\SerializerDeserializerInterface;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BuiltinType
{
    public function getBuiltinType()
    {
        return "int";

    }
}

class ArrayObjectNormalizerTest extends TestCase
{

    /**
     * @var ArrayObjectNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        $this->normalizer = new ArrayObjectNormalizer();
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::__construct
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
        $this->assertInstanceOf(SerializerAwareInterface::class, $this->normalizer);
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        $this->assertInstanceOf(NormalizerAwareInterface::class, $this->normalizer);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::setSerializer
     */
    public function testSetSerializerThrowsExceptionIfObjectIsNotInstanceOfDenormalizerInterface()
    {

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a serializer that also implements DenormalizerInterface.');

        $serializerInterface = $this->getMockBuilder(SerializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerInterface SerializerInterface */

        $this->normalizer->setSerializer($serializerInterface);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::setSerializer
     */
    public function testSetSerializer()
    {

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'supportsDenormalization'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface->expects($this->at(0))
            ->method('supportsDenormalization')
            ->with('{}', 'Person', 'json')
            ->willReturn(true);
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $this->normalizer->setSerializer($serializerDeserializerInterface);
        $this->normalizer->supportsDenormalization('{}', \ArrayObject::class . '<Person>', 'json');
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::supportsDenormalization
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::checkInstanceOfArrayObject
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::containsSubclass
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::extractClass
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::extractSubclass
     */
    public function testSupportsDenormalization()
    {
        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'supportsDenormalization'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface->expects($this->exactly(2))
            ->method('supportsDenormalization')
            ->with('{}', 'Person', 'json')
            ->willReturn(true);
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $this->normalizer->setSerializer($serializerDeserializerInterface);

        $this->assertTrue($this->normalizer->supportsDenormalization('{}', \ArrayObject::class, 'json'));
        $this->assertTrue($this->normalizer->supportsDenormalization('{}', \ArrayObject::class . "<Person>", 'json'));
        $this->assertTrue($this->normalizer->supportsDenormalization('{}', ArrayObjectSubclass::class, 'json'));
        $this->assertTrue($this->normalizer->supportsDenormalization('{}', ArrayObjectSubclass::class . '<Person>', 'json'));

        $this->assertFalse($this->normalizer->supportsDenormalization('{}', NotArrayObject::class, 'json'));
        $this->assertFalse($this->normalizer->supportsDenormalization('{}', NotArrayObject::class . '<Person>', 'json'));
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::checkRequirements
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testCheckRequirementsThrowsBadMethodCallExceptionIfSerializerIsNotSet()
    {

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Please set a serializer before calling denormalize()!');

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->denormalize('', \ArrayObject::class, 'json');
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::checkRequirements
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testCheckRequirementsThrowsInvalidArgumentExceptionIfDataIsNotAnArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data expected to be an array, string given.');

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObjectNormalizer->denormalize('string', \ArrayObject::class, 'json');
    }


    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::checkRequirements
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testCheckRequirementsThrowsInvalidArgumentExceptionIfDataIsNotInstanceOfArrayObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class should be instance of \ArrayObject');

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObjectNormalizer->denormalize([], NotArrayObject::class, 'json');
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::checkRequirements
     */
    public function testDenormalizeEmptyArray()
    {
        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObject = $arrayObjectNormalizer->denormalize([], \ArrayObject::class, 'json');

        $this->assertInstanceOf(\ArrayObject::class, $arrayObject);
        $this->assertEquals(new \ArrayObject(), $arrayObject);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testDenormalizeArrayOfIntegers()
    {
        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObject = $arrayObjectNormalizer->denormalize([1, 2, 4, 6], \ArrayObject::class, 'json');

        $this->assertInstanceOf(\ArrayObject::class, $arrayObject);
        $this->assertEquals(new \ArrayObject([1, 2, 4, 6]), $arrayObject);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testDenormalizeArrayWithStringKeys()
    {
        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();;
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObject = $arrayObjectNormalizer->denormalize(['a' => 1, 'b' => 2, 'd' => 4, 'f' => 6], \ArrayObject::class, 'json');

        $this->assertInstanceOf(\ArrayObject::class, $arrayObject);
        $this->assertEquals(new \ArrayObject(['a' => 1, 'b' => 2, 'd' => 4, 'f' => 6]), $arrayObject);
    }


    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testDenormalizeSubclass()
    {
        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObject = $arrayObjectNormalizer->denormalize([], ArrayObjectSubclass::class, 'json');

        $this->assertInstanceOf(ArrayObjectSubclass::class, $arrayObject);
        $this->assertEquals(new ArrayObjectSubclass(), $arrayObject);

        $arrayObject = $arrayObjectNormalizer->denormalize([1, 2, 4, 6], ArrayObjectSubclass::class, 'json');
        $this->assertEquals(new ArrayObjectSubclass([1, 2, 4, 6]), $arrayObject);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testDenormalizeArrayOfObjects()
    {
        $einstein = new Person('Albert Einstein');
        $tesla = new Person('Nikola Tesla');

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'denormailize'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface
            ->expects($this->at(0))
            ->method('denormalize')
            ->with(['name' => 'Albert Einstein'], Person::class, 'json', [])
            ->willReturn($einstein);
        $serializerDeserializerInterface
            ->expects($this->at(1))
            ->method('denormalize')
            ->with(['name' => 'Nikola Tesla'], Person::class, 'json', [])
            ->willReturn($tesla);
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObject = $arrayObjectNormalizer->denormalize([['name' => 'Albert Einstein'], ['name' => 'Nikola Tesla']], \ArrayObject::class . '<' . Person::class . '>', 'json');

        $this->assertInstanceOf(\ArrayObject::class, $arrayObject);
        $this->assertEquals(new \ArrayObject([$einstein, $tesla]), $arrayObject);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::containsSubclass
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::extractSubclass
     */
    public function testDenormalizeArrayOfObjectsWithStringKeys()
    {
        $einstein = new Person('Albert Einstein');
        $tesla = new Person('Nikola Tesla');

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'denormailize'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface
            ->expects($this->at(0))
            ->method('denormalize')
            ->with(['name' => 'Albert Einstein'], Person::class, 'json', [])
            ->willReturn($einstein);
        $serializerDeserializerInterface
            ->expects($this->at(1))
            ->method('denormalize')
            ->with(['name' => 'Nikola Tesla'], Person::class, 'json', [])
            ->willReturn($tesla);
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObject = $arrayObjectNormalizer->denormalize(['einstein' => ['name' => 'Albert Einstein'], 'tesla' => ['name' => 'Nikola Tesla']], \ArrayObject::class . '<' . Person::class . '>', 'json');

        $this->assertInstanceOf(\ArrayObject::class, $arrayObject);
        $this->assertEquals(new \ArrayObject(['einstein' => $einstein, 'tesla' => $tesla]), $arrayObject);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testDenormalizeSubclassArrayOfObjects()
    {
        $einstein = new Person('Albert Einstein');
        $tesla = new Person('Nikola Tesla');

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'denormailize'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface
            ->expects($this->at(0))
            ->method('denormalize')
            ->with(['name' => 'Albert Einstein'], Person::class, 'json', [])
            ->willReturn($einstein);
        $serializerDeserializerInterface
            ->expects($this->at(1))
            ->method('denormalize')
            ->with(['name' => 'Nikola Tesla'], Person::class, 'json', [])
            ->willReturn($tesla);
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObject = $arrayObjectNormalizer->denormalize([['name' => 'Albert Einstein'], ['name' => 'Nikola Tesla']], ArrayObjectSubclass::class . '<' . Person::class . '>', 'json');

        $this->assertInstanceOf(ArrayObjectSubclass::class, $arrayObject);
        $this->assertEquals(new ArrayObjectSubclass([$einstein, $tesla]), $arrayObject);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testDenormalizeSubclassArrayOfObjectsWithStringKeys()
    {
        $einstein = new Person('Albert Einstein');
        $tesla = new Person('Nikola Tesla');

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->setMethods([
                'denormailize'
            ])
            ->getMockForAbstractClass();
        $serializerDeserializerInterface
            ->expects($this->at(0))
            ->method('denormalize')
            ->with(['name' => 'Albert Einstein'], Person::class, 'json', [])
            ->willReturn($einstein);
        $serializerDeserializerInterface
            ->expects($this->at(1))
            ->method('denormalize')
            ->with(['name' => 'Nikola Tesla'], Person::class, 'json', [])
            ->willReturn($tesla);
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObject = $arrayObjectNormalizer->denormalize(['einstein' => ['name' => 'Albert Einstein'], 'tesla' => ['name' => 'Nikola Tesla']], ArrayObjectSubclass::class . '<' . Person::class . '>', 'json');

        $this->assertInstanceOf(ArrayObjectSubclass::class, $arrayObject);
        $this->assertEquals(new ArrayObjectSubclass(['einstein' => $einstein, 'tesla' => $tesla]), $arrayObject);

    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::denormalize
     */
    public function testDenormalizeThrowsUnexpectedValueExceptionIfKeyIsNotBuiltInType()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The type of the key "test" must be "int" ("string" given).');

        $serializerDeserializerInterface = $this->getMockBuilder(SerializerDeserializerInterface::class)
            ->getMockForAbstractClass();
        /* @var $serializerDeserializerInterface SerializerDeserializerInterface */

        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setSerializer($serializerDeserializerInterface);
        $arrayObjectNormalizer->denormalize(['test' => new Person('Test')], \ArrayObject::class, 'json', ['key_type' => new BuiltinType()]);
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::supportsNormalization
     */
    public function testSupportsNormalization()
    {

        $arrayObjectNormalizer = new ArrayObjectNormalizer();

        $this->assertTrue($arrayObjectNormalizer->supportsNormalization(new \ArrayObject()));
        $this->assertTrue($arrayObjectNormalizer->supportsNormalization(new ArrayObjectSubclass()));

        $this->assertFalse($arrayObjectNormalizer->supportsNormalization(new NotArrayObject()));
    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::normalize
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::setNormalizer
     */
    public function testNormalize()
    {

        $elements = [1, 3, 5, 7];

        $normalizerInterface = $this->getMockBuilder(NormalizerInterface::class)
            ->setMethods([
                'normalize'
            ])
            ->getMockForAbstractClass();
        $normalizerInterface
            ->expects($this->once())
            ->method('normalize')
            ->with($elements)
            ->willReturn('{1, 3, 5, 7}');
        /* @var $normalizerInterface NormalizerInterface */


        $arrayObjectNormalizer = new ArrayObjectNormalizer();
        $arrayObjectNormalizer->setNormalizer($normalizerInterface);
        $arrayObjectNormalizer->normalize(new \ArrayObject($elements));

    }

    /**
     * @covers \BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer::checkInstanceOfArrayObject
     */
    public function testCheckInstanceOfArrayObject(){

        $this->assertFalse($this->normalizer->supportsDenormalization([], 'int<'.Person::class.'>'));
    }
}
