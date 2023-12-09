<?php

namespace App\Tests;

use App\Controller\Restapi\EmployeeController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EmployeeTest extends WebTestCase
{
    protected function employeeData(): array
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'example@exampl.com',
            'salary' => 1000,
            'employedAt' => '2035-01-01 00:00:00',
        ];
    }

    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/restapi/employee', $this->employeeData());

        $this->assertResponseStatusCodeSame(201);

        $employee = json_decode($client->getResponse()->getContent(), true);
        foreach ($this->employeeData() as $key => $value) {
            $this->assertSame($value, $employee[$key]);
        }

        $client->jsonRequest('GET', '/restapi/employee/' . $employee['id']);
        $this->assertResponseStatusCodeSame(200);

        $client->jsonRequest('PATCH', '/restapi/employee/' . $employee['id'], [
            'firstname' => 'Jim',
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('Jim', json_decode($client->getResponse()->getContent(), true)['firstname']);

        $client->jsonRequest('POST', '/restapi/employee', $this->employeeData());

        $client->jsonRequest('GET', '/restapi/employee');
        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(2, json_decode($client->getResponse()->getContent(), true));

        $client->jsonRequest('DELETE', '/restapi/employee/' . $employee['id']);
        $this->assertResponseStatusCodeSame(204);

        $client->jsonRequest('GET', '/restapi/employee/' . $employee['id']);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testError(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/restapi/employee', [
            'lastname' => '',
            'random' => 'random',
            'email' => 'afsdfsadfasdf',
            'salary' => 10,
            'employedAt' => '1991-08-24 00:00:00',
        ]);
        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($response['message'], EmployeeController::MESSAGE_VALIDATION_ERROR);

        $this->assertCount(6, $response['data']);

        $client->jsonRequest('GET', '/restapi/employee/1');
        $this->assertResponseStatusCodeSame(404);

        $client->jsonRequest('POST', '/restapi/employee', $this->employeeData());
        $employee = json_decode($client->getResponse()->getContent(), true);

        $client->jsonRequest('PATCH', '/restapi/employee/' . $employee['id'], [
            'employedAt' => '1991-08-24 00:00:00',
        ]);
        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(EmployeeController::MESSAGE_VALIDATION_ERROR, $data['message']);
        $this->assertSame('employedAt', $data['data'][0]['field']);

        $client->jsonRequest('PATCH', '/restapi/employee/' . $employee['id'], [
            'salary' => 10,
        ]);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(EmployeeController::MESSAGE_VALIDATION_ERROR, $data['message']);
        $this->assertSame('salary', $data['data'][0]['field']);

        $client->jsonRequest('PATCH', '/restapi/employee/' . $employee['id']);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(EmployeeController::MESSAGE_NO_DATA_PROVIDED, $data['message']);

        $client->jsonRequest('DELETE', '/restapi/employee/' . $employee['id']);
        $this->assertResponseStatusCodeSame(204);

        $client->jsonRequest('GET', '/restapi/employee/' . $employee['id']);
        $this->assertResponseStatusCodeSame(404);

        $client->jsonRequest('PATCH', '/restapi/employee/' . $employee['id']);
        $this->assertResponseStatusCodeSame(404);


        $client->jsonRequest('DELETE', '/restapi/employee/' . $employee['id']);
        $this->assertResponseStatusCodeSame(404);
    }
}
