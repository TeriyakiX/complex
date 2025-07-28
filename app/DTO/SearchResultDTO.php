<?php

namespace App\DTO;

class SearchResultDTO
{
public function __construct(
public string $type,
public string $message,
public mixed $data,
public array $links = [],
public array $meta = [],
) {}
}
