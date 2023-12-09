<?php

namespace App\ApiResource;

class EmployeeResource
{
    public function __construct(
        public int $id,
        public string $firstname,
        public string $lastname,
        public string $email,
        public string $employedAt,
        public float $salary,
        public string $createdAt,
        public string $updatedAt,
    )
    {
    }
}