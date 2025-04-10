<?php

namespace App\Services\Paddle;

enum PaddleOperation: string
{
  case CREATE = 'create';
  case UPDATE = 'update';
}
