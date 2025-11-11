# JPesa PHP SDK (with Laravel support)

A lightweight, Composer-ready PHP SDK for the **JPesa API**: Credit/Debit GWallet, Transaction Info, and KYC.

- **Base URL:** `https://my.jpesa.com/api/`
- **Auth:** Provide your `_key_` (API key).

## Installation

```bash
composer require your-vendor/jpesa-php-sdk
```

## Quick start (vanilla PHP)

```php
use JPesa\SDK\JPesaClient;

$client = new JPesaClient(
    baseUrl: 'https://my.jpesa.com/api/',
    apiKey: getenv('JPESA_API_KEY') ?: 'YOUR_KEY'
);

// Credit
$res = $client->credit([
  'mobile' => '256752567374',
  'amount' => 1000,
  'tx'     => 'ORDER-12345',
]);
```

### Other calls

```php
$client->debit([ 'mobile'=>'2567...', 'amount'=>500, 'tx'=>'PAYOUT-1' ]);

$client->transactionInfo([ 'tid'=>'TXN-ID-ABC' ]);
// or: $client->transactionInfo([ 'pid'=>'PAYMENT-ID-XYZ' ]);
// or: $client->transactionInfo([ 'cur'=>'UGX' ]);

$client->kyc('2567...');
```

## Laravel usage

1. **Publish config** (optional):
```bash
php artisan vendor:publish --tag=config --provider="JPesa\SDK\Laravel\JPesaServiceProvider"
```
This will create `config/jpesa.php`.

2. **Set env**:
```
JPESA_API_KEY=your_live_or_sandbox_key
JPESA_BASE_URL=https://my.jpesa.com/api/
JPESA_TIMEOUT=30
```

3. **Resolve via DI**:
```php
use JPesa\SDK\JPesaClient;

public function charge(JPesaClient $jpesa) {
    $jpesa->credit(['mobile'=>'2567...','amount'=>1000,'tx'=>'ORDER-1']);
}
```

4. **Or use Facade**:
```php
use JPesa\SDK\Laravel\Facades\JPesa;

JPesa::debit(['mobile'=>'2567...','amount'=>500,'tx'=>'PAYOUT-1']);
```

## Webhooks / Callbacks

Pass a `callback` URL in `credit()`/`debit()` if you want JPesa to notify your app.
Always verify fields like `tid` and your `tx` to ensure idempotency.

## Testing

```bash
composer install
composer test
```

Unit tests use Guzzle's MockHandler to simulate API responses.

## License

MIT
