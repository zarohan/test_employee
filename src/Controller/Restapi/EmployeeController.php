<?php

namespace App\Controller\Restapi;

use App\ApiResource\EmployeeResource;
use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;


class EmployeeController extends AbstractController
{
    /** TODO: move to dictionary?*/

    public const MESSAGE_VALIDATION_ERROR = 'Validation error';
    public const MESSAGE_EMPLOYEE_NOT_FOUND = 'Employee not found';
    public const MESSAGE_NO_DATA_PROVIDED = 'No data provided';
    public const MESSAGE_MISSING_FIELD = 'Missing field';
    public const MESSAGE_UNKNOWN_FIELD = 'Unknown field';

    public const LIST_HARD_LIMIT = 10000; // so we do not have db troubles

    public function __construct(
        protected EmployeeRepository $repository,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/restapi/employee/{id<\d+>}', name: 'restapi_employee_get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: EmployeeResource::class)
    )]
    #[OA\Response(
        response: 404,
        description: 'Employee not found',
    )]
    public function get(int $id): Response
    {
        $employee = $this->repository->find($id);

        if ($employee === null) {
            return $this->errorResponse(Response::HTTP_NOT_FOUND, self::MESSAGE_EMPLOYEE_NOT_FOUND);
        }

        $resource = $this->employeeToResource($employee);
        return $this->json($resource);
    }

    #[Route('/restapi/employee', name: 'restapi_employee_list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: EmployeeResource::class))
        )
    )]
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
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Employee created',
        content: new Model(type: EmployeeResource::class)
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: self::MESSAGE_VALIDATION_ERROR,
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'field', type: 'string'),
                            new OA\Property(property: 'message', type: 'string'),
                        ]
                    )),
                ]
            )
        )
    )]
    #[OA\RequestBody(
        description: 'Employee data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'firstname', type: 'string'),
                new OA\Property(property: 'lastname', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(
                    property: 'employedAt',
                    description: 'Date of employment must not be in the past', type: 'string',
                    format: 'date-time',
                    example: '2025-01-01 00:00:00'),
                new OA\Property(property: 'salary', type: 'number', minimum: 100),
            ],
            type: 'object'
        )
    )]
    public function create(Request $request): Response
    {
        /* TODO: add idempotency check */
        $mandatoryFields = ['firstname', 'lastname', 'email', 'employedAt', 'salary'];

        $json = $request->getContent();
        $data = json_decode($json, true) ?? [];

        $errors = $this->validateIncomingValues($data);
        foreach ($mandatoryFields as $field) {
            if (!array_key_exists($field, $data)) {
                $errors []= ['field' => $field, 'message' => self::MESSAGE_MISSING_FIELD];
            }
        }

        if (count($errors) > 0) {
            return $this->errorResponse(Response::HTTP_BAD_REQUEST,
                self::MESSAGE_VALIDATION_ERROR, $errors
            );
        }

        $employee = $this->hydrateEmployeeFromArray($data);
        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $resource = $this->employeeToResource($employee);
        return $this->json($resource, Response::HTTP_CREATED);
    }

    #[Route('/restapi/employee/{id<\d+>}', name: 'restapi_employee_delete', methods: ['DELETE'])]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Employee deleted',
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: self::MESSAGE_EMPLOYEE_NOT_FOUND,
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: self::MESSAGE_EMPLOYEE_NOT_FOUND),
                ]
            )
        )
    )]
    public function delete(int $id): Response
    {
        $employee = $this->repository->find($id);

        if ($employee === null) {
            return $this->errorResponse(Response::HTTP_NOT_FOUND, self::MESSAGE_EMPLOYEE_NOT_FOUND);
        }

        $this->entityManager->remove($employee);
        $this->entityManager->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/restapi/employee/{id<\d+>}', name: 'restapi_employee_update', methods: ['PATCH'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: EmployeeResource::class)
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: self::MESSAGE_EMPLOYEE_NOT_FOUND,
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'message', type: 'string'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: self::MESSAGE_VALIDATION_ERROR,
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'message', type: 'string'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'field', type: 'string'),
                            new OA\Property(property: 'message', type: 'string'),
                        ]
                    )),
                ]
            )
        )
    )]
    #[OA\RequestBody(
        description: 'Employee data',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'firstname', type: 'string'),
                new OA\Property(property: 'lastname', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(
                    property: 'employedAt',
                    description: 'Date of employment must not be in the past',
                    type: 'string',
                    format: 'date-time',
                    example: '2025-01-01 00:00:00'
                ),
                new OA\Property(property: 'salary', type: 'number', minimum: 100),
            ],
            type: 'object'
        )
    )]
    public function update(Request $request, int $id): Response
    {
        $employee = $this->repository->find($id);

        if ($employee === null) {
            return $this->errorResponse(Response::HTTP_NOT_FOUND, self::MESSAGE_EMPLOYEE_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (count($data) === 0) {
            return $this->errorResponse(Response::HTTP_BAD_REQUEST, self::MESSAGE_NO_DATA_PROVIDED);
        }

        if (count($errors = $this->validateIncomingValues($data)) > 0) {
            return $this->errorResponse(Response::HTTP_BAD_REQUEST,
                self::MESSAGE_VALIDATION_ERROR,
                $errors
            );
        }

        $employee = $this->hydrateEmployeeFromArray($data, $employee);

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $resource = $this->employeeToResource($employee);
        return $this->json($resource, Response::HTTP_OK);
    }

    protected function validateIncomingValues(array $data): array
    {
        $errors = [];
        foreach ($data as $key => $value) {
            if (!property_exists(Employee::class, $key)) {
                $errors []= ['field' => $key, 'message' => self::MESSAGE_UNKNOWN_FIELD];
                continue;
            }
            /** OK, this is ugly, but for now I stuck */
            if ($key === 'employedAt') {
                $value = new \DateTimeImmutable($value);
            }
            $violationList = $this->validator->validatePropertyValue(Employee::class, $key, $value);

            $errors = array_merge($errors,
                array_map(
                    fn($violation) => ['field' => $key, 'message' => $violation->getMessage()],
                    iterator_to_array($violationList)
                )
            );
        }

        return $errors;
    }

    protected function errorResponse(int $code, string $message, array $data = []): Response
    {
        // TODO: Create a resource class for error response?
        $responseData = ['message' => $message];
        if (count($data)) {
            $responseData['data'] = $data;
        }
        return $this->json($responseData, $code);
    }

    protected function hydrateEmployeeFromArray(array $data, ?Employee $employee = null): Employee
    {
        $employee = $employee ?: new Employee();

        $employee
            ->setFirstname($data['firstname'] ?? $employee->getFirstname())
            ->setLastname($data['lastname'] ?? $employee->getLastname())
            ->setEmail($data['email'] ?? $employee->getEmail())
            ->setEmployedAt(new \DateTimeImmutable(
                $data['employedAt'] ?? $employee->getEmployedAt()->format('Y-m-d H:i:s')
            ))
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