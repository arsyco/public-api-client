<?php

namespace ArrowSphere\PublicApiClient\Tests\Contacts;

use ArrowSphere\PublicApiClient\Contacts\ContactsClient;
use ArrowSphere\PublicApiClient\Exception\NotFoundException;
use ArrowSphere\PublicApiClient\Exception\PublicApiClientException;
use ArrowSphere\PublicApiClient\Tests\AbstractClientTest;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;

/**
 * Class ContactsClientTest
 *
 * @property ContactsClient $client
 */
class ContactsClientTest extends AbstractClientTest
{
    protected const MOCKED_CLIENT_CLASS = ContactsClient::class;

    public function testGetVendorContactsWithMissingVendorParameter(): void
    {
        $this->httpClient->expects(self::never())->method('request');

        $this->expectException(InvalidArgumentException::class);
        $contacts = $this->client->getVendorContacts();
        iterator_to_array($contacts);
    }

    public function testGetVendorContactsWithEmptyVendorParameter(): void
    {
        $this->httpClient->expects(self::never())->method('request');

        $this->expectException(InvalidArgumentException::class);
        $contacts = $this->client->getVendorContacts(['vendor' => '']);
        iterator_to_array($contacts);
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetContactsRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/contacts?vendor=adobe')
            ->willReturn(new Response(200, [], 'OK'));

        $this->client->getContactsRaw(['vendor' => 'adobe']);
    }

    /**
     * @depends testGetContactsRaw
     *
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetVendorContactsWithInvalidResponse(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/contacts?vendor=adobe&per_page=100')
            ->willReturn(new Response(200, [], '{'));

        $this->expectException(PublicApiClientException::class);
        $contacts = $this->client->getVendorContacts(['vendor' => 'adobe']);
        iterator_to_array($contacts);
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetVendorContactsWithPagination(): void
    {
        $response = json_encode([
            'data'       => [],
            'pagination' => [
                'total_page' => 3,
            ],
        ]);

        $this->httpClient
            ->expects(self::exactly(3))
            ->method('request')
            ->withConsecutive(
                [
                    'GET',
                    'https://www.test.com/contacts?vendor=adobe&per_page=100',
                ],
                [
                    'GET',
                    'https://www.test.com/contacts?vendor=adobe&page=2&per_page=100',
                ],
                [
                    'GET',
                    'https://www.test.com/contacts?vendor=adobe&page=3&per_page=100',
                ]
            )
            ->willReturn(new Response(200, [], $response));

        $test = $this->client->getVendorContacts(['vendor' => 'adobe']);
        iterator_to_array($test);
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetVendorContacts(): void
    {
        $response = <<<JSON
{
    "status": 200,
    "data": [
        {
            "firstname": "John",
            "lastname": "Doe",
            "email": "john.doe@example.com",
            "phone": "+1234567890"
        },
        {
            "firstname": "Jane",
            "lastname": "Smith",
            "email": "jane.smith@example.com"
        }
    ],
    "pagination": {
        "per_page": 100,
        "current_page": 1,
        "total_page": 1,
        "total": 2,
        "next": null,
        "previous": null
    }
}
JSON;

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/contacts?vendor=adobe&per_page=100')
            ->willReturn(new Response(200, [], $response));

        $test = $this->client->getVendorContacts(['vendor' => 'adobe']);
        $list = iterator_to_array($test);
        self::assertCount(2, $list);

        $contact = $list[0];
        self::assertEquals('John', $contact->getFirstname());
        self::assertEquals('Doe', $contact->getLastname());
        self::assertEquals('john.doe@example.com', $contact->getEmail());
        self::assertEquals('+1234567890', $contact->getPhone());

        $contact = $list[1];
        self::assertEquals('Jane', $contact->getFirstname());
        self::assertEquals('Smith', $contact->getLastname());
        self::assertEquals('jane.smith@example.com', $contact->getEmail());
        self::assertNull($contact->getPhone());
    }
}
