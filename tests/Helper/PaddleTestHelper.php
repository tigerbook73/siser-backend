<?php

namespace Tests\Helper;

use Paddle\SDK\Entities\DateTime;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class PaddleTestHelper
{
  public static function serialize($data)
  {
    $serializer = new Serializer(
      [
        new BackedEnumNormalizer(),
        new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => DateTime::PADDLE_RFC3339]),
        new JsonSerializableNormalizer(),
        new ObjectNormalizer(nameConverter: new CamelCaseToSnakeCaseNameConverter()),
      ],
      [new JsonEncoder()],
    );

    return $serializer->serialize($data, 'json', [
      AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true,
    ]);
  }
}
