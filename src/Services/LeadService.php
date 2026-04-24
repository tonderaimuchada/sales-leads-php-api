<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class LeadService
{
    public function __construct(private readonly PDO $db) {}

    public function getAll(array $filters = [], int $page = 0, int $size = 10): array
    {
        $conditions = ['1=1'];
        $params     = [];

        if (!empty($filters['start_date'])) {
            $conditions[] = 'dateCreated >= :start_date';
            $params['start_date'] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $conditions[] = 'dateCreated <= :end_date';
            $params['end_date'] = $filters['end_date'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'status = :status';
            $params['status'] = $filters['status'];
        }

        $where  = implode(' AND ', $conditions);
        $offset = $page * $size;

        // Count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM leads WHERE {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Data
        $stmt = $this->db->prepare(
            "SELECT * FROM leads WHERE {$where} ORDER BY created_at DESC LIMIT :size OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('size', $size, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $leads = $stmt->fetchAll();

        return [
            'content'        => array_map([$this, 'formatLead'], $leads),
            'total_elements' => $total,
            'total_pages'    => (int)ceil($total / $size),
            'page'           => $page,
            'size'           => $size,
        ];
    }

    public function getById(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM leads WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $lead = $stmt->fetch();

        if (!$lead) {
            throw new \RuntimeException("Lead not found with id: {$id}", 404);
        }

        return $this->formatLead($lead);
    }

    public function create(array $data, int $userId): array
    {
        $this->validate($data, true);

        $stmt = $this->db->prepare(
            'INSERT INTO leads (fullName, emailAddress, phoneNumber, companyName, status, dateCeated, dateModified)
             VALUES (:fullName, :emailAddress, :phoneNumber, :companyName, :status, NOW(), null)
             RETURNING *'
        );

        $stmt->execute([
            'fullName' => $data['fullName'],
            'emailAddress' => $data['emailAddress'],
            'phoneNumber' => $data['phoneNumber'] ?? null,
            'companyName' => $data['companyName'] ?? null,
            'status' => $data['status'] ?? 'NEW',
        ]);

        return $this->formatLead($stmt->fetch());
    }

    public function update(int $id, array $data): array
    {
        $this->getById($id); // throws 404 if not found
        $this->validate($data, false);

        $fields = [];
        $params = ['id' => $id];
        $allowed = ['fullName', 'emailAddress', 'phoneNumber', 'companyName', 'status'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            throw new \RuntimeException('No fields to update', 400);
        }

        $fields[] = 'updated_at = NOW()';
        $set = implode(', ', $fields);

        $stmt = $this->db->prepare("UPDATE leads SET {$set} WHERE id = :id RETURNING *");
        $stmt->execute($params);

        return $this->formatLead($stmt->fetch());
    }

    public function delete(int $id): void
    {
        $this->getById($id); // throws 404 if not found
        $stmt = $this->db->prepare('DELETE FROM leads WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function validate(array $data, bool $isCreate): void
    {
        $errors = [];

        if ($isCreate) {
            if (empty($data['fullName']))  $errors[] = 'Full name is required';
            if (empty($data['emailAddress']))      $errors[] = 'Email address is required';
        }

        if (!empty($data['emailAddress']) && !filter_var($data['emailAddress'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address format';
        }

        $validStatuses = ['NEW', 'CONTACTED', 'QUALIFIED', 'LOST', 'WON'];
        if (!empty($data['status']) && !in_array($data['status'], $validStatuses, true)) {
            $errors[] = 'Invalid status. Must be one of: ' . implode(', ', $validStatuses);
        }

        if (!empty($errors)) {
            throw new \RuntimeException(implode(', ', $errors), 422);
        }
    }

    private function formatLead(array $lead): array
    {
        return [
            'id' => (int)$lead['id'],
            'fullName' => $lead['fullName'],
            'emailAddress' => $lead['emailAddress'],
            'phoneNumber' => $lead['phoneNumber'],
            'companyName' => $lead['companyName'],
            'status' => $lead['status'],
            'dateCreated' => $lead['dateCreated'],
            'dateModified' => $lead['dateModified'],
        ];
    }
}
