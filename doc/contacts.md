# Contacts Client

## General information

The `/contacts` endpoint was originally designed to support full CRUD operations on a company's
own (internal) contacts.  
**For now, however, this client only implements retrieval of vendor (external) contacts** — i.e. contacts declared by 
a vendor (such as Adobe) for an end customer, selected via the mandatory `vendor` parameter and the `customerRef` optional parameter.

This client replaces the `PartnersClient::getContactsRaw()` method (now deprecated), which
returned an untyped payload.

## Entities

### VendorContact

A vendor contact is managed by the `VendorContact` entity.

| Field     | Type             | Example               | Description                                                                |
| --------- | ---------------- | --------------------- | --------------------------------------------------------------------------- |
| firstname | `string`         | John                  | The contact's first name.                                                   |
| lastname  | `string`         | Doe                   | The contact's last name.                                                    |
| email     | `string`         | john.doe@example.com  | The contact's email address.                                                |
| phone     | `string` or `null` | +1234567890         | The contact's phone number. May be absent if not provided by the vendor.    |

## Usage

You can get it through the main entry point `PublicApiClient` and its method `getContactsClient()`,
or instantiate it directly as follows:

```php
<?php

use ArrowSphere\PublicApiClient\Contacts\ContactsClient;

const URL = 'https://your-url-to-arrowsphere.example.com';
const API_KEY = 'your API key in ArrowSphere';

$client = (new ContactsClient())
    ->setUrl(URL)
    ->setApiKey(API_KEY);

foreach ($client->getVendorContacts(['vendor' => 'VIP-MP']) as $contact) {
    echo $contact->getEmail() . PHP_EOL;
}
```

### GetContacts

> **Note:** only vendor (external) contacts can currently be retrieved; the `vendor` parameter is mandatory for the typed method.  
> You should also pass the `customerRef` parameter if you want to filter the contacts for a specific end customer.

The "GetContacts" endpoint lists the vendor contacts of an end customer.

The `ContactsClient::getVendorContacts(array $parameters)` method returns a `Generator` of
`VendorContact`, handling the pagination transparently. The `$parameters` array **must** include a
`vendor` key (e.g. `['vendor' => 'VIP-MP']`); an `InvalidArgumentException` is thrown if it is
missing or empty.

The `ContactsClient::getContactsRaw(array $parameters)` method returns a `string` that is the JSON
containing a single page of results.
