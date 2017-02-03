<?php
/**
 * Created by PhpStorm.
 * User: Bartosz Bartniczak <kontakt@bartoszbartniczak.pl>
 */

namespace BartoszBartniczak\SymfonySerializer\Normalizer\Fixtures;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

interface SerializerDeserializerInterface extends SerializerInterface, DenormalizerInterface
{

}