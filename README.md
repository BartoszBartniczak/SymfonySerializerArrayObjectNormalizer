BartoszBartniczak\Symfony-Serializer-ArrayObject-Noramlizer
===========================================================
ArrayObject Normalizer for Symfony/Serializer component. This Normalizer works with ArrayObject objects and its subclasses.
------------------------------------------------

### Configuration

```php
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use BartoszBartniczak\SymfonySerializer\Normalizer\ArrayObjectNormalizer;


$normalizers = [
    new ArrayObjectNormalizer(), //add ArrayObjectNoralizer to the normalizers array
    new ObjectNormalizer(),
    ];

$encoders = [
    new JsonEncoder()
];

$serializer = new Serializer($normalizers, $encoders);
```

### Examples

#### Simple ArrayObject (De-)Serialization

```php
$json = $serializer->serialize(new \ArrayObject(['a' => 1, 'c' => 3, 'e' => 5, 'g' => 7]), 'json');
```

In the `$json` variable you should have now this JSON document:

```json
{
  "a": 1,
  "c": 3,
  "e": 5,
  "g": 7
}
```

Now you can deserialize this JSON object back to `\ArrayObject`:

```php
$serializer->deserialize($json, \ArrayObject::class, 'json');
```

#### Array Of Objects (De-)Serialization

If the ArrayObject contains objects of some class, you need to define the type for deserialization.

```php
$arrayOfObjects = new \ArrayObject([
  'einstein' => new Person('Albert Einstein'),
  'tesla' => new Person('Nikola Tesla')
]);

$json = $serializer->serialize($arrayOfObjects, 'json');

// deserialization
$deserializedObject = $serializer->deserialize($json, \ArrayObject::class.'<Person>', 'json');
```

#### Subclasses (extending the \ArrayObject class)

This Normalizer supports inheritance of objects. You can extend the `\ArrayObject` (e.g. for adding some methods) and this Normalizer still will be able to (de-)serialize objects.
 
```php
<?php

class PersonArray extends \ArrayObject{
    
}

$arrayOfObjects = new PersonArray([
  'einstein' => new Person('Albert Einstein'),
  'tesla' => new Person('Nikola Tesla')
]);

$json = $serializer->serialize($arrayOfObjects, 'json');

// deserialization
$deserializedObject = $serializer->deserialize($json, PersonArray::class.'<Person>', 'json');
```

For other examples, you should check out the integration tests.

### Tests

#### Unit tests

To run unit test execute the command:

```bash
php vendor/phpunit/phpunit/phpunit --configuration tests/unit-tests/configuration.xml
```

#### Integration tests

To run integration tests execute the command:

```bash
php vendor/phpunit/phpunit/phpunit --configuration tests/integration-tests/configuration.xml
```