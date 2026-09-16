<?php

namespace ArrowSphere\PublicApiClient\Tests\OrganizationUnit;

use ArrowSphere\PublicApiClient\Exception\NotFoundException;
use ArrowSphere\PublicApiClient\Exception\PublicApiClientException;
use ArrowSphere\PublicApiClient\OrganizationUnit\OrganizationUnitClient;
use ArrowSphere\PublicApiClient\Tests\AbstractClientTest;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;

/**
 * Class OrganizationUnitClientTest
 *
 * @property OrganizationUnitClient $client
 */
class OrganizationUnitClientTest extends AbstractClientTest
{
    protected const MOCKED_CLIENT_CLASS = OrganizationUnitClient::class;

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetOrganizationUnitsRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/organizationUnit/?companyRef=XSP12345')
            ->willReturn(new Response(200, [], 'OK'));

        $this->client->getOrganizationUnitsRaw(['companyRef' => 'XSP12345']);
    }

    /**
     * @depends testGetOrganizationUnitsRaw
     *
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetOrganizationUnitsWithInvalidResponse(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/organizationUnit/?per_page=100')
            ->willReturn(new Response(200, [], '{'));

        $this->expectException(PublicApiClientException::class);
        $organizationUnits = $this->client->getOrganizationUnits();
        iterator_to_array($organizationUnits);
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetOrganizationUnitsWithPagination(): void
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
                    'https://www.test.com/organizationUnit/?per_page=100',
                ],
                [
                    'GET',
                    'https://www.test.com/organizationUnit/?page=2&per_page=100',
                ],
                [
                    'GET',
                    'https://www.test.com/organizationUnit/?page=3&per_page=100',
                ]
            )
            ->willReturn(new Response(200, [], $response));

        $test = $this->client->getOrganizationUnits();
        iterator_to_array($test);
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetOrganizationUnits(): void
    {
        $response = <<<JSON
{
    "status": 200,
    "data": [
        {
            "organizationUnitRef": "XSPOU123456",
            "companyRef": "XSP12345",
            "name": "Administration service",
            "countUsers": 7,
            "countCustomers": 11,
            "countLicenses": 8,
            "countOrders": 8
        }
    ],
    "pagination": {
        "per_page": 100,
        "current_page": 1,
        "total_page": 1,
        "total": 1,
        "next": null,
        "previous": null
    }
}
JSON;

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/organizationUnit/?per_page=100')
            ->willReturn(new Response(200, [], $response));

        $test = $this->client->getOrganizationUnits();
        $list = iterator_to_array($test);
        self::assertCount(1, $list);

        $organizationUnit = $list[0];
        self::assertEquals('XSPOU123456', $organizationUnit->getOrganizationUnitRef());
        self::assertEquals('XSP12345', $organizationUnit->getCompanyRef());
        self::assertEquals('Administration service', $organizationUnit->getName());
        self::assertEquals(7, $organizationUnit->getCountUsers());
        self::assertEquals(11, $organizationUnit->getCountCustomers());
        self::assertEquals(8, $organizationUnit->getCountLicenses());
        self::assertEquals(8, $organizationUnit->getCountOrders());
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testCreateOrganizationUnitRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('POST', 'https://www.test.com/organizationUnit/')
            ->willReturn(new Response(201, [], 'OK'));

        $this->client->createOrganizationUnitRaw('Administration service');
    }

    /**
     * @depends testCreateOrganizationUnitRaw
     *
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testCreateOrganizationUnitWithInvalidResponse(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('POST', 'https://www.test.com/organizationUnit/')
            ->willReturn(new Response(201, [], '{'));

        $this->expectException(PublicApiClientException::class);
        $this->client->createOrganizationUnit('Administration service');
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testCreateOrganizationUnit(): void
    {
        $response = <<<JSON
{
    "status": 201,
    "data": {
        "organizationUnitRef": "XSPOU123456",
        "companyRef": "XSP12345",
        "name": "Administration service"
    }
}
JSON;

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('POST', 'https://www.test.com/organizationUnit/')
            ->willReturn(new Response(201, [], $response));

        $organizationUnit = $this->client->createOrganizationUnit('Administration service');
        self::assertEquals('XSPOU123456', $organizationUnit->getOrganizationUnitRef());
        self::assertEquals('XSP12345', $organizationUnit->getCompanyRef());
        self::assertEquals('Administration service', $organizationUnit->getName());
        self::assertNull($organizationUnit->getCountUsers());
        self::assertNull($organizationUnit->getCountCustomers());
        self::assertNull($organizationUnit->getCountLicenses());
        self::assertNull($organizationUnit->getCountOrders());
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetOrganizationUnitRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456')
            ->willReturn(new Response(200, [], 'OK'));

        $this->client->getOrganizationUnitRaw('XSPOU123456');
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetOrganizationUnitRawWithDetails(): void
    {
        $response = json_encode([
            'data' => [
                'organizationUnitRef' => 'XSPOU123456',
                'name'                => 'Administration service',
            ],
        ]);

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456?withDetails=true')
            ->willReturn(new Response(200, [], $response));

        $this->client->getOrganizationUnit('XSPOU123456', true);
    }

    /**
     * @depends testGetOrganizationUnitRaw
     *
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetOrganizationUnitWithInvalidResponse(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456')
            ->willReturn(new Response(200, [], '{'));

        $this->expectException(PublicApiClientException::class);
        $this->client->getOrganizationUnit('XSPOU123456');
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetOrganizationUnit(): void
    {
        $response = <<<JSON
{
    "status": 200,
    "data": {
        "organizationUnitRef": "XSPOU123456",
        "companyRef": "XSP12345",
        "name": "Administration service",
        "countUsers": 7,
        "countCustomers": 11,
        "countLicenses": 8,
        "countOrders": 8
    }
}
JSON;

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456')
            ->willReturn(new Response(200, [], $response));

        $organizationUnit = $this->client->getOrganizationUnit('XSPOU123456');
        self::assertEquals('XSPOU123456', $organizationUnit->getOrganizationUnitRef());
        self::assertEquals('XSP12345', $organizationUnit->getCompanyRef());
        self::assertEquals('Administration service', $organizationUnit->getName());
        self::assertEquals(7, $organizationUnit->getCountUsers());
        self::assertEquals(11, $organizationUnit->getCountCustomers());
        self::assertEquals(8, $organizationUnit->getCountLicenses());
        self::assertEquals(8, $organizationUnit->getCountOrders());
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testDeleteOrganizationUnitRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('DELETE', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456')
            ->willReturn(new Response(204, [], null));

        $this->client->deleteOrganizationUnitRaw('XSPOU123456');
    }

    /**
     * @depends testDeleteOrganizationUnitRaw
     *
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testDeleteOrganizationUnit(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('DELETE', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456')
            ->willReturn(new Response(204, [], null));

        $this->client->deleteOrganizationUnit('XSPOU123456');
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testUpdateOrganizationUnitRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('PATCH', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456')
            ->willReturn(new Response(200, [], 'OK'));

        $this->client->updateOrganizationUnitRaw('XSPOU123456', 'Sales service');
    }

    /**
     * @depends testUpdateOrganizationUnitRaw
     *
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testUpdateOrganizationUnitWithInvalidResponse(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('PATCH', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456')
            ->willReturn(new Response(200, [], '{'));

        $this->expectException(PublicApiClientException::class);
        $this->client->updateOrganizationUnit('XSPOU123456', 'Sales service');
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testUpdateOrganizationUnit(): void
    {
        $response = <<<JSON
{
    "status": 200,
    "data": {
        "organizationUnitRef": "XSPOU123456",
        "companyRef": "XSP12345",
        "name": "Sales service"
    }
}
JSON;

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('PATCH', 'https://www.test.com/organizationUnit/ouRef/XSPOU123456')
            ->willReturn(new Response(200, [], $response));

        $organizationUnit = $this->client->updateOrganizationUnit('XSPOU123456', 'Sales service');
        self::assertEquals('XSPOU123456', $organizationUnit->getOrganizationUnitRef());
        self::assertEquals('XSP12345', $organizationUnit->getCompanyRef());
        self::assertEquals('Sales service', $organizationUnit->getName());
    }
}
