<?php

declare(strict_types=1);

namespace MNarushevich\AuditLogs\Services\AWS;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Facades\Config;

final class DynamoDBService
{
    private string $tableName;

    public function __construct(
        private readonly DynamoDbClient $client,
        private readonly Marshaler $marshaler,
    ) {
        $this->tableName = Config::get('auditlogs.log_table');
    }

    public function createTable(): void
    {
        try {
            $this->client->createTable([
                'TableName' => $this->tableName,
                'KeySchema' => [
                    ['AttributeName' => 'id', 'KeyType' => 'HASH'], // Partition key
                ],
                'AttributeDefinitions' => [
                    ['AttributeName' => 'id', 'AttributeType' => 'S'],
                ],
                'ProvisionedThroughput' => [
                    'ReadCapacityUnits' => 5,
                    'WriteCapacityUnits' => 5,
                ],
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function insertItem(array $data): void
    {
        $data['id'] = uniqid(); // Generate unique ID
        $item = $this->marshaler->marshalItem($data);

        $this->client->putItem([
            'TableName' => $this->tableName,
            'Item' => $item,
        ]);
    }

    public function getItem($id): ?array
    {
        $response = $this->client->getItem([
            'TableName' => $this->tableName,
            'Key' => $this->marshaler->marshalItem(['id' => $id]),
        ]);

        return isset($response['Item']) ? $this->marshaler->unmarshalItem($response['Item']) : null;
    }

    public function deleteItem($id): void
    {
        $this->client->deleteItem([
            'TableName' => $this->tableName,
            'Key' => $this->marshaler->marshalItem(['id' => $id]),
        ]);
    }
}
