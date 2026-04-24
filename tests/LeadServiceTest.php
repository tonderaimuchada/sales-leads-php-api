<?php

declare(strict_types=1);

namespace Tests;

use App\Services\LeadService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class LeadServiceTest extends TestCase
{
    private PDO&MockObject $pdoMock;
    private PDOStatement&MockObject $stmtMock;
    private LeadService $service;

    protected function setUp(): void
    {
        $this->pdoMock  = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->service  = new LeadService($this->pdoMock);
    }

    public function testCreateThrowsOnMissingFirstName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('First name is required');

        $this->service->create([
            'last_name' => 'Doe',
            'email'     => 'doe@example.com',
        ], 1);
    }

    public function testCreateThrowsOnInvalidEmail(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid email format');

        $this->service->create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'not-an-email',
        ], 1);
    }

    public function testCreateThrowsOnInvalidStatus(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid status');

        $this->service->create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'status'     => 'INVALID_STATUS',
        ], 1);
    }

    public function testGetByIdThrowsWhenNotFound(): void
    {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn(false);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Lead not found with id: 999');
        $this->expectExceptionCode(404);

        $this->service->getById(999);
    }

    public function testGetByIdReturnsFormattedLead(): void
    {
        $fakeLead = [
            'id' => 1,
            'fullName' => 'Jane Smith',
            'emailAddress' => 'jane@test.com',
            'phoneNumber' => '+27-0001',
            'companyName' => 'Instacom',
            'status' => 'NEW',
            'dateCreated' => '2026-04-23 00:00:00',
            'dateModified' => '2026-04-23 00:00:00',
        ];

        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn($fakeLead);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->service->getById(1);

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Jane Smith', $result['fullName']);
        $this->assertEquals('jane@test.com', $result['emailAddress']);
    }

    public function testUpdateThrowsWhenNoFieldsProvided(): void
    {
        $fakeLead = [
            'id' => 1, 'fullName' => 'Jane Smith',
            'emailAddress' => 'jane@test.com', 'phoneNumber' => null, 'companyName' => null,
            'status' => 'NEW', 'dateCreated' => '2026-04-23 00:00:00', 'dateModified' => '2026-04-23 00:00:00',
        ];

        $this->stmtMock->method('execute')->willReturn(true);
        $this->stmtMock->method('fetch')->willReturn($fakeLead);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No fields to update');

        $this->service->update(1, []);
    }
}
