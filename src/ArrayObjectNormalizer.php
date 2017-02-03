<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer;


use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ArrayObjectNormalizer implements DenormalizerInterface, SerializerAwareInterface, NormalizerInterface, NormalizerAwareInterface
{


    /**
     * @var SerializerInterface|DenormalizerInterface
     */
    protected $serializer;

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * ArrayObjectNormalizer constructor.
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof DenormalizerInterface) {
            throw new InvalidArgumentException('Expected a serializer that also implements DenormalizerInterface.');
        }

        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if (!$this->checkInstanceOfArrayObject($type)) {
            return false;
        }

        if ($this->containsSubclass($type)) {
            $subtype = $this->extractSubclass($type);

            return (bool)preg_match('/^[a-zA-Z0-9_\x7f-\xff\\\\]+<[a-zA-Z0-9_\x7f-\xff\\\\]+>$/', $type) && $this->serializer->supportsDenormalization($data, $subtype, $format);
        } else {
            return (bool)preg_match('/^[a-zA-Z0-9_\x7f-\xff\\\\]+$/', $type);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedValueException
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $this->checkRequirements($data, $class);

        if ($this->containsSubclass($class)) {
            $subclass = $this->extractSubclass($class);
        }

        $serializer = $this->serializer;

        $builtinType = isset($context['key_type']) ? $context['key_type']->getBuiltinType() : null;

        foreach ($data as $key => $value) {
            if (null !== $builtinType && !call_user_func('is_' . $builtinType, $key)) {
                throw new UnexpectedValueException(sprintf('The type of the key "%s" must be "%s" ("%s" given).', $key, $builtinType, gettype($key)));
            }

            if(isset($subclass)) {
                $data[$key] = $serializer->denormalize($value, $subclass, $format, $context);
            }
        }

        $className = $this->extractClass($class);
        return new $className($data);
    }

    protected function extractSubclass($class)
    {
        preg_match('/<[a-zA-Z0-9_\x7f-\xff\\\\,]+>/', $class, $match);
        $subclass = substr($match[0], 1, -1);

        return $subclass;
    }

    protected function extractClass($class)
    {
        preg_match('/^[a-zA-Z0-9_\x7f-\xff\\\\]+/', $class, $match);
        return $match[0];
    }

    protected function checkInstanceOfArrayObject($type)
    {
        $class = $this->extractClass($type);

        if ($class === \ArrayObject::class) {
            return true;
        }

        if(!class_exists($class)){
            return false;
        }

        $parents = class_parents($class);

        return isset($parents[\ArrayObject::class]);
    }

    public function normalize($object, $format = null, array $context = array())
    {
        return $this->normalizer->normalize($object->getArrayCopy());
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \ArrayObject;
    }

    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * @param $data
     * @param $class
     */
    protected function checkRequirements($data, $class)
    {
        if ($this->serializer === null) {
            throw new BadMethodCallException('Please set a serializer before calling denormalize()!');
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Data expected to be an array, ' . gettype($data) . ' given.');
        }

        if (!$this->checkInstanceOfArrayObject($class)) {
            throw new InvalidArgumentException('Class should be instance of \ArrayObject');
        }
    }

    private function containsSubclass($type)
    {
        return (bool)preg_match('/^[a-zA-Z0-9_\x7f-\xff\\\\]+<[a-zA-Z0-9_\x7f-\xff\\\\,]+>$/', $type);
    }


}