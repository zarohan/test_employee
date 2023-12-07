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