<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IndexController extends AbstractController
{
    #[Route('/')]
    public function homepage(EntityManagerInterface $entityManager, ValidatorInterface $validator, EmployeeRepository $repository): Response
    {

        $data = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'exampol@test.com',
            'employedAt' => new \DateTimeImmutable('2021-01-01'),
            'salary' => 10,
        ];

        $allErrors = [];
        foreach ($data as $key => $value) {
            $violationList = $validator->validatePropertyValue(Employee::class, $key, $value);
            $allErrors = array_merge($allErrors, array_map(fn($violation) => ['field' => $key, 'message' => $violation->getMessage()], iterator_to_array($violationList)));
        }
        dd($allErrors);
        $employee = new Employee();
        $employee->setFirstname('John');
        $employee->setLastname('Doe');
        $employee->setEmail('john@example.com');
        $employee->setEmployedAt(new \DateTimeImmutable('2021-01-01'));
        $employee->setSalary('1000');

        $violationList = $validator->validate($employee);

        $entityManager->persist($employee);
        $entityManager->flush();
dd($violationList);

//        dump($violationList->count());
//        $errors = [];
//        foreach ($violationList as $violation) {
//            $errors []= ['field' => $violation->getPropertyPath(), 'message' => $violation->getMessage()];
//        }
//        dump($errors);
exit;
        return $this->render('test.html.twig');
    }
}