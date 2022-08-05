<?php

require_once('./vendor/autoload.php');

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Annotation\DiscriminatorMap;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: [
        'cat' => Cat::class,
        'dog' => Dog::class,
    ],
)]
interface Animal {}

abstract class AbstractEntity {}

class Cat extends AbstractEntity implements Animal {}

class Dog extends AbstractEntity implements Animal {}

class Human extends AbstractEntity
{
    public Animal $pet;

    public function __construct(Animal $pet)
    {
        $this->pet = $pet;
    }
}

$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));

$discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

$serializer = new Serializer(
    [new ObjectNormalizer($classMetadataFactory, null, null, null, $discriminator)],
    ['json' => new JsonEncoder()]
);

$human = new Human(pet: new Cat());

$serialized = $serializer->serialize($human, 'json');

var_dump($serialized);

$repository = $serializer->deserialize($serialized, Human::class, 'json');

var_dump($repository);
