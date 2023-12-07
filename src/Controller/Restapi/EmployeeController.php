<?php

namespace App\Controller\Restapi;

use App\ApiResource\EmployeeResource;
use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmployeeController extends AbstractController
{
    public const LIST_HARD_LIMIT = 10000; // so we do not have db troubles

    public function __construct(
        protected EmployeeRepository $repository,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/restapi/employee/{id<\d+>}', name: 'restapi_employee_get', methods: ['GET'])]
    public function get(int $id): Response
    {
        $employee = $this->repository->find($id);

        if ($employee === null) {
            return $this->json(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $resource = $this->employeeToResource($employee);
        return $this->json($resource);
    }

    #[Route('/restapi/employee', name: 'restapi_employee_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $limit = $request->query->getInt('limit', 10);
        $employees = $this->repository->findBy([], ['id' => 'desc'], min($limit, self::LIST_HARD_LIMIT) );

        return $this->json(
            array_map(
                fn(Employee $employee) => $this->employeeToResource($employee), $employees
            )
        );
    }
    #[Route('/restapi/employee', name: 'restapi_employee_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $mandatoryFields = ['firstname', 'lastname', 'email', 'employedAt', 'salary'];

        $json = $request->getContent();
        $errors = $this->validateJsonValues($json);

        foreach ($mandatoryFields as $field) {
            if (!array_key_exists($field, json_decode($json, true))) {
                $errors []= ['field' => $field, 'message' => 'Missing field'];
            }
        }

        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $employee = $this->hydrateEmployeeFromJson($json);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $resource = $this->employeeToResource($employee);
        return $this->json($resource, Response::HTTP_CREATED);
    }

    #[Route('/restapi/employee/{id<\d+>}', name: 'restapi_employee_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        $employee = $this->repository->find($id);

        if ($employee === null) {
            return $this->json(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($employee);
        $this->entityManager->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/restapi/employee/{id<\d+>}', name: 'restapi_employee_update', methods: ['PATCH'])]
    public function update(Request $request, int $id): Response
    {
        $employee = $this->repository->find($id);

        if ($employee === null) {
            return $this->json(['message' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        if (count($errors = $this->validateJsonValues($request->getContent())) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $employee = $this->hydrateEmployeeFromJson($request->getContent(), $employee);

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $resource = $this->employeeToResource($employee);
        return $this->json($resource, Response::HTTP_OK);
    }

    protected function validateJsonValues(string $json): array
    {
        $data = json_decode($json, true);
        $errors = [];
        foreach ($data as $key => $value) {
            if (!property_exists(Employee::class, $key)) {
                $errors []= ['field' => $key, 'message' => 'Unknown field'];
                continue;
            }
            /** OK, this is ugly, but for now I stuck */
            if ($key === 'employedAt') {
                $value = new \DateTimeImmutable($value);
            }
            $violationList = $this->validator->validatePropertyValue(Employee::class, $key, $value);
            $errors = array_merge($errors, array_map(fn($violation) => ['field' => $key, 'message' => $violation->getMessage()], iterator_to_array($violationList)));
        }

        return $errors;
    }

    protected function hydrateEmployeeFromJson(string $json, ?Employee $employee = null): Employee
    {
        $data = json_decode($json, true);
        $employee = $employee ?: new Employee();

        $employee
            ->setFirstname($data['firstname'] ?? $employee->getFirstname())
            ->setLastname($data['lastname'] ?? $employee->getLastname())
            ->setEmail($data['email'] ?? $employee->getEmail())
            ->setEmployedAt(new \DateTimeImmutable($data['employedAt'] ?? $employee->getEmployedAt()->format('Y-m-d H:i:s')))
            ->setSalary($data['salary'] ?? $employee->getSalary());

        return $employee;
    }

    protected function employeeToResource(Employee $employee): EmployeeResource
    {
        return new EmployeeResource(
            id: $employee->getId(),
            firstname: $employee->getFirstname(),
            lastname: $employee->getLastname(),
            email: $employee->getEmail(),
            employedAt: $employee->getEmployedAt()->format('Y-m-d H:i:s'),
            salary: $employee->getSalary(),
            createdAt: $employee->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $employee->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }

}