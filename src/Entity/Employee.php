<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual('now')]
    private ?\DateTimeImmutable $employedAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 16, scale: 4)]
    #[Assert\GreaterThanOrEqual(100)]
    #[Assert\Type(type: 'numeric')]
    private ?float $salary = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEmployedAt(): ?\DateTimeImmutable
    {
        return $this->employedAt;
    }

    public function setEmployedAt(\DateTimeImmutable $employedAt): static
    {
        $this->employedAt = $employedAt;

        return $this;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }

    public function setSalary(string $salary): static
    {
        $this->salary = $salary;

        return $this;
    }

//    #[Assert\Callback]
//    public function validateEmployedAt(ExecutionContextInterface $context,  $payload): void
//    {
//        if ($this->employedAt->getTimestamp() < time() ) {
//            $context->buildViolation('Employee cannot be employed in the past')
//                ->atPath('employedAt')
//                ->addViolation();
//        }
//    }
}
