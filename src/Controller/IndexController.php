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
    public function homepage(ValidatorInterface $validator): Response
    {
        $violationList = $validator->validatePropertyValue(Employee::class, 'salary', 123);
        dd($violationList);
        return $this->render('test.html.twig');
    }
}