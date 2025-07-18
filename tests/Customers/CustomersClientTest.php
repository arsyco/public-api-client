<?php

namespace ArrowSphere\PublicApiClient\Tests\Customers;

use ArrowSphere\PublicApiClient\Customers\CustomersClient;
use ArrowSphere\PublicApiClient\Customers\Entities\Customer;
use ArrowSphere\PublicApiClient\Customers\Entities\CustomersResponse;
use ArrowSphere\PublicApiClient\Customers\Entities\Gdap;
use ArrowSphere\PublicApiClient\Customers\Request\ExportCustomersRequest;
use ArrowSphere\PublicApiClient\Customers\Request\MigrationRequest;
use ArrowSphere\PublicApiClient\Customers\Request\ProvisionRequest;
use ArrowSphere\PublicApiClient\Customers\Request\SubEntities\CustomerFilters;
use ArrowSphere\PublicApiClient\Exception\EntityValidationException;
use ArrowSphere\PublicApiClient\Exception\NotFoundException;
use ArrowSphere\PublicApiClient\Exception\PublicApiClientException;
use ArrowSphere\PublicApiClient\Tests\AbstractClientTest;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;

/**
 * Class CustomersClientTest
 *
 * @property CustomersClient $client
 */
class CustomersClientTest extends AbstractClientTest
{
    protected const MOCKED_CLIENT_CLASS = CustomersClient::class;

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetCustomersRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers?abc=def&ghi=0')
            ->willReturn(new Response(200, [], 'OK USA'));

        $this->client->getCustomersRaw([
            'abc' => 'def',
            'ghi' => false,
        ]);
    }

    /**
     * @depends testGetCustomersRaw
     *
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetCustomersWithInvalidResponse(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers?abc=def&ghi=0&per_page=100')
            ->willReturn(new Response(200, [], '{'));

        $this->expectException(PublicApiClientException::class);
        $customers = $this->client->getCustomers([
            'abc' => 'def',
            'ghi' => false,
        ]);
        iterator_to_array($customers);
    }

    /**
     * @throws EntityValidationException
     * @throws GuzzleException
     * @throws PublicApiClientException
     */
    public function testGetCustomersWithPagination(): void
    {
        $response = json_encode([
            'data'       => [
                'customers' => [],
            ],
            'pagination' => [
                'total_page' => 3,
            ],
        ]);

        $this->httpClient
            ->expects(self::exactly(3))
            ->method('request')
            ->withConsecutive(
                [
                    'get',
                    'https://www.test.com/customers?abc=def&ghi=0&per_page=100',
                ],
                [
                    'get',
                    'https://www.test.com/customers?abc=def&ghi=0&page=2&per_page=100',
                ],
                [
                    'get',
                    'https://www.test.com/customers?abc=def&ghi=0&page=3&per_page=100',
                ]
            )
            ->willReturn(new Response(200, [], $response));

        $test = $this->client->getCustomers([
            'abc' => 'def',
            'ghi' => false,
        ]);
        iterator_to_array($test);
    }

    /**
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetCustomers(): void
    {
        $response = <<<JSON
{
  "status": 200,
  "data": {
    "customers": [
      {
        "AddressLine1": "1007 Mountain Drive",
        "AddressLine2": "Wayne Manor",
        "BillingId": "123",
        "City": "Gotham City",
        "CompanyName": "Wayne industries",
        "Contact": {
          "Email": "test@example.com",
          "FirstName": "Bruce",
          "LastName": "Wayne",
          "Phone": "1-800-555-1234"
        },
        "CountryCode": "US",
        "Details": [],
        "DeletedAt": null,
        "EmailContact": "nobody@example.com",
        "Headcount": null,
        "InternalReference": "",
        "ReceptionPhone": "1-800-555-1111",
        "Ref": "COMPANY12345",
        "Reference": "XSP12345",
        "State": "NJ",
        "TaxNumber": "",
        "WebsiteUrl": "https:\/\/www.dccomics.com",
        "Zip": "12345"
      },
      {
        "AddressLine1": "855 Main Street",
        "AddressLine2": "Police Department",
        "BillingId": "456",
        "City": "Central City",
        "CompanyName": "Central City Police Department",
        "Contact": {
          "Email": "test2@example.com",
          "FirstName": "Barry",
          "LastName": "Allen",
          "Phone": "1-800-555-5678"
        },
        "CountryCode": "US",
        "Details": [],
        "DeletedAt": null,
        "EmailContact": "nobody@example.com",
        "Headcount": null,
        "InternalReference": "",
        "ReceptionPhone": "1-800-555-1111",
        "Ref": "COMPANY12346",
        "Reference": "XSP12346",
        "State": "MO",
        "TaxNumber": "",
        "WebsiteUrl": "https:\/\/www.dccomics.com",
        "Zip": "12346"
      }
    ]
  },
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
            ->with('get', 'https://www.test.com/customers?abc=def&ghi=0&per_page=100')
            ->willReturn(new Response(200, [], $response));

        $test = $this->client->getCustomers([
            'abc' => 'def',
            'ghi' => false,
        ]);
        $list = iterator_to_array($test);
        self::assertCount(2, $list);

        /** @var Customer $customer */
        $customer = array_shift($list);
        self::assertInstanceOf(Customer::class, $customer);
        self::assertSame('1007 Mountain Drive', $customer->getAddressLine1());
        self::assertSame('Wayne Manor', $customer->getAddressLine2());
        self::assertSame('123', $customer->getBillingId());
        self::assertSame('Gotham City', $customer->getCity());
        self::assertSame('Wayne industries', $customer->getCompanyName());
        self::assertSame('test@example.com', $customer->getContact()->getEmail());
        self::assertSame('Bruce', $customer->getContact()->getFirstName());
        self::assertSame('Wayne', $customer->getContact()->getLastName());
        self::assertSame('1-800-555-1234', $customer->getContact()->getPhone());
        self::assertSame('US', $customer->getCountryCode());
        self::assertNull($customer->getDeletedAt());
        self::assertSame('nobody@example.com', $customer->getEmailContact());
        self::assertNull($customer->getHeadcount());
        self::assertSame('', $customer->getInternalReference());
        self::assertSame('1-800-555-1111', $customer->getReceptionPhone());
        self::assertSame('COMPANY12345', $customer->getRef());
        self::assertSame('XSP12345', $customer->getReference());
        self::assertSame('NJ', $customer->getState());
        self::assertSame('', $customer->getTaxNumber());
        self::assertSame('https://www.dccomics.com', $customer->getWebsiteUrl());

        /** @var Customer $customer */
        $customer = array_shift($list);
        self::assertInstanceOf(Customer::class, $customer);
        self::assertSame('855 Main Street', $customer->getAddressLine1());
        self::assertSame('Police Department', $customer->getAddressLine2());
        self::assertSame('456', $customer->getBillingId());
        self::assertSame('Central City', $customer->getCity());
        self::assertSame('Central City Police Department', $customer->getCompanyName());
        self::assertSame('test2@example.com', $customer->getContact()->getEmail());
        self::assertSame('Barry', $customer->getContact()->getFirstName());
        self::assertSame('Allen', $customer->getContact()->getLastName());
        self::assertSame('1-800-555-5678', $customer->getContact()->getPhone());
        self::assertSame('US', $customer->getCountryCode());
        self::assertNull($customer->getDeletedAt());
        self::assertSame('nobody@example.com', $customer->getEmailContact());
        self::assertNull($customer->getHeadcount());
        self::assertSame('', $customer->getInternalReference());
        self::assertSame('1-800-555-1111', $customer->getReceptionPhone());
        self::assertSame('COMPANY12346', $customer->getRef());
        self::assertSame('XSP12346', $customer->getReference());
        self::assertSame('MO', $customer->getState());
        self::assertSame('', $customer->getTaxNumber());
        self::assertSame('https://www.dccomics.com', $customer->getWebsiteUrl());
    }

    /**
     * @return void
     *
     * @throws EntityValidationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetCustomersPage(): void
    {
        $data = [
            'customers' => [
                [
                    'AddressLine1' => '1007 Mountain Drive',
                    'AddressLine2' => 'Wayne Manor',
                    'BillingId' => '123',
                    'City' => 'Gotham City',
                    'CompanyName' => 'Wayne industries',
                    'Contact' => [
                        'Email' => 'test@example.com',
                        'FirstName' => 'Bruce',
                        'LastName' => 'Wayne',
                        'Phone' => '1-800-555-1234',
                    ],
                    'CountryCode' => 'US',
                    'Details' => [],
                    'DeletedAt' => null,
                    'EmailContact' => 'nobody@example.com',
                    'Headcount' => null,
                    'InternalReference' => '',
                    'ReceptionPhone' => '1-800-555-1111',
                    'Ref' => 'COMPANY12345',
                    'Reference' => 'XSP12345',
                    'State' => 'NJ',
                    'TaxNumber' => '',
                    'WebsiteUrl' => 'https:\/\/www.dccomics.com',
                    'Zip' => '12345',
                ],
                [
                    'AddressLine1' => '855 Main Street',
                    'AddressLine2' => 'Police Department',
                    'BillingId' => '456',
                    'City' => 'Central City',
                    'CompanyName' => 'Central City Police Department',
                    'Contact' => [
                        'Email' => 'test2@example.com',
                        'FirstName' => 'Barry',
                        'LastName' => 'Allen',
                        'Phone' => '1-800-555-5678',
                    ],
                    'CountryCode' => 'US',
                    'Details' => [],
                    'DeletedAt' => null,
                    'EmailContact' => 'nobody@example.com',
                    'Headcount' => null,
                    'InternalReference' => '',
                    'ReceptionPhone' => '1-800-555-1111',
                    'Ref' => 'COMPANY12346',
                    'Reference' => 'XSP12346',
                    'State' => 'MO',
                    'TaxNumber' => '',
                    'WebsiteUrl' => 'https:\/\/www.dccomics.com',
                    'Zip' => '12346',
                ],
            ],
        ];
        $pagination = [
            'pagination' => [
                'per_page' => 100,
                'current_page' => 1,
                'total_page' => 1,
                'total' => 2,
                'next' => '',
                'previous' => '',
            ],
        ];
        $result = [
            'status' => 200,
            'data' => $data,
        ] + $pagination;
        $response = json_encode($result);

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers?abc=def&ghi=0&per_page=100')
            ->willReturn(new Response(200, [], $response));

        $test = $this->client->getCustomersPage([
            'abc' => 'def',
            'ghi' => false,
        ]);

        $customers = $test->getCustomers();
        $customer = array_shift($customers);
        self::assertInstanceOf(CustomersResponse::class, $test);
        self::assertSame('1007 Mountain Drive', $customer->getAddressLine1());
        self::assertSame('Wayne Manor', $customer->getAddressLine2());
        self::assertSame('123', $customer->getBillingId());
        self::assertSame('Gotham City', $customer->getCity());
        self::assertSame('Wayne industries', $customer->getCompanyName());
        self::assertSame('test@example.com', $customer->getContact()->getEmail());
        self::assertSame('Bruce', $customer->getContact()->getFirstName());
        self::assertSame('Wayne', $customer->getContact()->getLastName());
        self::assertSame('1-800-555-1234', $customer->getContact()->getPhone());
        self::assertSame('US', $customer->getCountryCode());
        self::assertNull($customer->getDeletedAt());
        self::assertSame('nobody@example.com', $customer->getEmailContact());
        self::assertNull($customer->getHeadcount());
        self::assertSame('', $customer->getInternalReference());
        self::assertSame('1-800-555-1111', $customer->getReceptionPhone());
        self::assertSame('COMPANY12345', $customer->getRef());
        self::assertSame('XSP12345', $customer->getReference());
        self::assertSame('NJ', $customer->getState());
        self::assertSame('', $customer->getTaxNumber());
        self::assertSame('https:\/\/www.dccomics.com', $customer->getWebsiteUrl());

        $pagination = $test->getPagination();
        self::assertSame(1, $pagination->getCurrentPage());
        self::assertSame(100, $pagination->getPerPage());
        self::assertSame(1, $pagination->getTotalPage());
        self::assertSame(2, $pagination->getTotal());
        self::assertSame('', $pagination->getNextPage());
        self::assertSame('', $pagination->getPreviousPage());
    }

    /**
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws EntityValidationException
     * @throws GuzzleException
     */
    public function testCreateCustomer(): void
    {
        $payload = [
            'AddressLine1'      => '1007 Mountain Drive',
            'AddressLine2'      => 'Wayne Manor',
            'BillingId'         => '',
            'City'              => 'Gotham City',
            'CompanyName'       => 'Wayne industries',
            'Contact'           => [
                'Email'     => 'test@example.com',
                'FirstName' => 'Bruce',
                'LastName'  => 'Wayne',
                'Phone'     => '1-800-555-1234',
            ],
            'CountryCode'       => 'US',
            'Details'           => [
                'DomainName' => 'https://www.dccomics.com',
                'Migration'  => false,
            ],
            'EmailContact'      => 'nobody@example.com',
            'Headcount'         => null,
            'InternalReference' => '',
            'ReceptionPhone'    => '1-800-555-1111',
            'Ref'               => 'COMPANY12345',
            'State'             => 'NJ',
            'TaxNumber'         => '',
            'WebsiteUrl'        => 'https://www.dccomics.com',
            'Zip'               => '12345',
            'OrganizationUnit'  => null,
        ];

        $customer = new Customer($payload);

        $response = <<<JSON
{
    "status": 201,
    "data": {
        "reference": "XSP123456"
    }
}
JSON;

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('post', 'https://www.test.com/customers?abc=def&ghi=0', [
                'headers' => [
                    'apiKey' => '123456',
                    'Content-Type' => 'application/json',
                    'User-Agent' => $this->userAgentHeader,
                ],
                'body'    => json_encode($payload),
            ])
            ->willReturn(new Response(200, [], $response));

        $reference = $this->client->createCustomer($customer, [
            'abc' => 'def',
            'ghi' => false,
        ]);

        self::assertSame('XSP123456', $reference);
    }

    /**
     * @return void
     *
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetInvitationRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers/invitations/ABCD12345?abc=def&ghi=0')
            ->willReturn(new Response(200, [], 'OK USA'));

        $this->client->getInvitationRaw(
            'ABCD12345',
            [
                'abc' => 'def',
                'ghi' => false,
            ]
        );
    }

    /**
     * @return void
     *
     * @throws EntityValidationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetInvitationWithInvalidResponse(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers/invitations/ABCD12345?abc=def&ghi=0')
            ->willReturn(new Response(200, [], '{'));

        $this->expectException(PublicApiClientException::class);
        $this->client->getInvitation(
            'ABCD12345',
            [
                'abc' => 'def',
                'ghi' => false,
            ]
        );
    }

    /**
     * @return void
     *
     * @throws EntityValidationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetInvitation(): void
    {
        $response = <<<JSON
{
  "status": 200,
  "data": {
    "code": "ABCD12345",
    "createdAt": "2021-12-25 23:59:51",
    "updatedAt": "2022-01-01 12:23:34",
    "contact": {
      "username": "aaaabbbb-cccc-dddd-eeee-ffff00001111",
      "email": "noreply@example.com",
      "firstName": "Bruce",
      "lastName": "Wayne"
    },
    "company": {
      "reference": "ABC123"
    },
    "policy": "admin"
  }
}
JSON;

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers/invitations/ABCD12345?abc=def&ghi=0')
            ->willReturn(new Response(200, [], $response));

        $invitation = $this->client->getInvitation(
            'ABCD12345',
            [
                'abc' => 'def',
                'ghi' => false,
            ]
        );

        self::assertSame('ABCD12345', $invitation->getcode());
        self::assertSame('2021-12-25 23:59:51', $invitation->getCreatedAt());
        self::assertSame('2022-01-01 12:23:34', $invitation->getUpdatedAt());
        self::assertSame('aaaabbbb-cccc-dddd-eeee-ffff00001111', $invitation->getContact()->getUsername());
        self::assertSame('noreply@example.com', $invitation->getContact()->getEmail());
        self::assertSame('Bruce', $invitation->getContact()->getFirstName());
        self::assertSame('Wayne', $invitation->getContact()->getLastName());
        self::assertSame('ABC123', $invitation->getCompany()->getReference());
        self::assertSame('admin', $invitation->getPolicy());
    }

    /**
     * @return void
     *
     * @throws EntityValidationException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testCreateInvitation(): void
    {
        $payload = [
            'contactId' => 12345,
            'policy' => 'admin',
        ];

        $response = <<<JSON
{
    "status": 201,
    "data": {
        "code": "ABCD12345",
        "createdAt": "2021-12-25 23:59:51",
        "updatedAt": "2022-01-01 12:23:34",
        "contact": {
            "username": "aaaabbbb-cccc-dddd-eeee-ffff00001111",
            "email": "noreply@example.com",
            "firstName": "Bruce",
            "lastName": "Wayne"
        },
        "company": {
            "reference": "ABC123"
        },
        "policy": "admin"
    }
}
JSON;

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('post', 'https://www.test.com/customers/invitations?abc=def&ghi=0', [
                'headers' => [
                    'apiKey' => '123456',
                    'Content-Type' => 'application/json',
                    'User-Agent' => $this->userAgentHeader,
                ],
                'body'    => json_encode($payload),
            ])
            ->willReturn(new Response(200, [], $response));

        $invitation = $this->client->createInvitation(12345, 'admin', [
            'abc' => 'def',
            'ghi' => false,
        ]);

        self::assertSame('ABCD12345', $invitation->getcode());
        self::assertSame('2021-12-25 23:59:51', $invitation->getCreatedAt());
        self::assertSame('2022-01-01 12:23:34', $invitation->getUpdatedAt());
        self::assertSame('aaaabbbb-cccc-dddd-eeee-ffff00001111', $invitation->getContact()->getUsername());
        self::assertSame('noreply@example.com', $invitation->getContact()->getEmail());
        self::assertSame('Bruce', $invitation->getContact()->getFirstName());
        self::assertSame('Wayne', $invitation->getContact()->getLastName());
        self::assertSame('ABC123', $invitation->getCompany()->getReference());
    }

    /**
     * @return void
     *
     * @throws NotFoundException
     * @throws PublicApiClientException
     * @throws GuzzleException
     */
    public function testGetGdapListRaw(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers/XSP123456/relationships?abc=def&ghi=0')
            ->willReturn(new Response(200, [], 'OK USA'));

        $this->client->getGdapListRaw(
            'XSP123456',
            [
                'abc' => 'def',
                'ghi' => false,
            ]
        );
    }

    /**
     * @return void
     *
     * @throws EntityValidationException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetGdapListWithInvalidResponse(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers/XSP123456/relationships?abc=def&ghi=0')
            ->willReturn(new Response(200, [], '{'));

        $this->expectException(PublicApiClientException::class);

        $gdap = $this->client->getGdapList(
            'XSP123456',
            [
                'abc' => 'def',
                'ghi' => false,
            ]
        );
        iterator_to_array($gdap);
    }

    /**
     * @return void
     *
     * @throws EntityValidationException
     * @throws NotFoundException
     * @throws PublicApiClientException
     */
    public function testGetGdapList(): void
    {
        $expectedGdapList = [
            [
                'id'             => '123',
                'displayName'    => 'Gdap test',
                'status'         => 'Pending Approval',
                'startDate'      => '2024-01-01',
                'endDate'        => '2024-06-30',
                'duration'       => 'P180T',
                'durationInDays' => '180',
                'autoExtend'     => 'P180T',
                'approvalLink'   => 'https://www.approval-link.com',
                'privileges'     => [
                    [
                        "name" => "Directory Readers",
                        "description" => "Can read basic directory information. Commonly used to grant directory read access to applications and guests.",
                    ], [
                        "name" => "Directory Writers",
                        "description" => "Can read and write basic directory information. For granting access to applications, not intended for users.",
                    ],
                ],
                'securityGroups' => [
                    [
                        "name"   => "HelpdeskAgents",
                        "status" => "active",
                    ], [
                        "name" => "AdminAgents",
                    ],
                ],
            ], [
                'id'             => '456',
                'displayName'    => 'Gdap test 2',
                'status'         => 'Validated',
                'startDate'      => '2024-05-12',
                'endDate'        => '2024-11-11',
                'duration'       => 'P180T',
                'durationInDays' => '180',
                'autoExtend'     => 'P180T',
                'approvalLink'   => 'https://www.some-other-approval-link.com',
                'privileges'     => [
                    [
                        "name" => "Privileged Authentication Administrator",
                        "description" => "Can access to view, set, and reset authentication method information for any user (admin or non-admin).",
                    ],
                ],
                'securityGroups' => [
                    [
                        "name"   => "HelpdeskAgents",
                    ], [
                        "name" => "AdminAgents",
                        "status" => "active",
                    ],
                ],
            ],
        ];

        $gdapResult1 = json_encode([
            'data' => [
                'relationRecord' => [$expectedGdapList[0]],
                'paginationToken' => 'A1B2C3D4',
            ],
        ]);

        $gdapResult2 = json_encode([
            'data' => [
                'relationRecord' => [$expectedGdapList[1]],
            ],
        ]);

        $this->httpClient
            ->expects(self::exactly(2))
            ->method('request')
            ->withConsecutive(
                [
                    'get',
                    'https://www.test.com/customers/XSP123456/relationships?abc=def&ghi=0',
                ],
                [
                    'get',
                    'https://www.test.com/customers/XSP123456/relationships?abc=def&ghi=0&paginationToken=A1B2C3D4',
                ]
            )
            ->will(self::onConsecutiveCalls(
                new Response(200, [], $gdapResult1),
                new Response(200, [], $gdapResult2)
            ));

        $test = $this->client->getGdapList(
            'XSP123456',
            [
                'abc' => 'def',
                'ghi' => false,
            ]
        );

        $gdapList = [];

        /** @var Gdap $gdap */
        foreach (iterator_to_array($test) as $gdap) {
            $gdapList[] = $gdap->jsonSerialize();
        }

        self::assertSame($expectedGdapList, $gdapList);
    }

    public function testGetProvisionInvalid(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers/XSP123456/provision?program=MSCP')
            ->willReturn(new Response(200, [], '{'));

        $this->expectException(PublicApiClientException::class);

        $res = $this->client->getProvision(
            'XSP123456',
            'MSCP'
        );
    }

    public function testGetProvision(): void
    {
        $body = [
            'status' => 200,
            'data' => [
                'status' => 'Fulfilled',
                'message' => 'The company was provisioned successfully',
                'attributes' => [
                    [
                        'name' => 'domain',
                        'value' => 'mydomain.onmicrosoft.com',
                    ],
                ],
            ],
        ];
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('get', 'https://www.test.com/customers/XSP123456/provision?program=MSCP')
            ->willReturn(new Response(200, [], json_encode($body)));

        $res = $this->client->getProvision(
            'XSP123456',
            'MSCP'
        );

        self::assertSame('Fulfilled', $res->getStatus());
        self::assertSame('The company was provisioned successfully', $res->getMessage());
        self::assertCount(1, $res->getAttributes());
        self::assertSame('domain', $res->getAttributes()[0]->getName());
        self::assertSame('mydomain.onmicrosoft.com', $res->getAttributes()[0]->getValue());

        self::assertSame($body['data'], $res->jsonSerialize());
    }

    public function testPostProvision(): void
    {
        $payload = [
            'program' => 'MSCP',
            'attributes' => [
                [
                    'name' => 'domain',
                    'value' => 'mydomain.onmicrosoft.com',
                ],
            ],
        ];

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('post', 'https://www.test.com/customers/XSP123456/provision', [
                'headers' => [
                    'apiKey' => '123456',
                    'Content-Type' => 'application/json',
                    'User-Agent' => $this->userAgentHeader,
                ],
                'body' => json_encode($payload),
            ])
            ->willReturn(new Response(202, []));

        $res = $this->client->postProvision(
            'XSP123456',
            new ProvisionRequest($payload)
        );

        self::assertSame('', $res);
    }

    public function testPostMigration(): void
    {
        $payload = [
            'program' => 'MSCP',
            'attributes' => [
                [
                    'name' => 'domain',
                    'value' => 'mydomain.onmicrosoft.com',
                ],
            ],
        ];

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('post', 'https://www.test.com/customers/XSP123456/migration', [
                'headers' => [
                    'apiKey' => '123456',
                    'Content-Type' => 'application/json',
                    'User-Agent' => $this->userAgentHeader,
                ],
                'body' => json_encode($payload),
            ])
            ->willReturn(new Response(202, []));

        $res = $this->client->postMigration(
            'XSP123456',
            new MigrationRequest($payload)
        );

        self::assertSame('', $res);
    }

    public function testCancelMigration(): void
    {
        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('delete', 'https://www.test.com/customers/XSP123456/migration?program=MSCP', [
                'headers' => [
                    'apiKey' => '123456',
                    'Content-Type' => 'application/json',
                    'User-Agent' => $this->userAgentHeader,
                ],
            ])
            ->willReturn(new Response(200, [], 'ok'));

        $res = $this->client->cancelMigration('XSP123456', 'MSCP');

        self::assertSame('ok', $res);
    }

    public function testExportCustomersCompany(): void
    {
        $payload = [
            'filters' => [
                CustomerFilters::COLUMN_COMPANY_NAME => 'Wayne industries',
                CustomerFilters::COLUMN_COUNTRY_NAME => 'Fran',
            ],
        ];
        $customerFilters = new ExportCustomersRequest($payload);

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('post', 'https://www.test.com/customers/initiate-export', [
                'headers' => [
                    'apiKey' => '123456',
                    'Content-Type' => 'application/json',
                    'User-Agent' => $this->userAgentHeader,
                ],
                'body' => json_encode($customerFilters->jsonSerialize()),
            ])
            ->willReturn(new Response(200, [], 'ok'));

        $res = $this->client->postExportCustomers($customerFilters);

        self::assertSame('ok', $res);
    }
}
