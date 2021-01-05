# Gossamer Tutorial #1: Configuring the Gossamer Client

## Introduction

Libgossamer bundles a client-side interface that will query one of two
sources of truth (depending on your preferences):

* In a **Local Trust** configuration, it queries the local database.
  This assumes that the [Synchronizer](../reference/Synchronizer.md)
  is running in the background to keep the local database up-to-date.
* In a **Federated Trust** configuration, it queries a remote server.
  See [Gossamer Server](https://github.com/paragonie/gossamer-server)
  for the source code and documentation for running a remote server.

In either setup, the query will be directed at a data store that is kept
in sync with the transactions published on the cryptographic ledger.

Thus, regardless of your local configuration, you **SHOULD** always see
the same data.

This document will walk you through setting up the Gossamer Client
with either of your desired trust modes.

## Step One: Choosing Your Trust Mode

There are two Trust Modes built into libgossamer:

* **Local Trust**: Perform all the Gossamer protocol steps locally,
  including the storage of verification keys and update metadata.
  * Pro: Less attack surface
  * Con: More disk space and bandwidth necessary
* **Federated Trust**: All the Gossamer protocol steps are performed
  by the Gossamer Server, and your local software queries that server.
  * Pro: Don't need to store everything locally (this was intended
    for managed hosting services who might have thousands of customers
    running the same software)
  * Con: More attack surface
    * This can be easily mitigated by having canary nodes configured use
      Local Trust that also query the Gossamer Server and scream loudly 
      if there's a discrepancy that's not explained by Synchronizer lag.
      
There are valid reasons to use either trust mode, but in the spirit of the
open source software community, any software that integrates with Gossamer
**MUST** provide end users the option to use Local Trust, even if it's not 
the default setting.

### Local Trust

You first need to instantiate the [LocalTrust](../reference/Client/TrustMode/LocalTrust.md) 
class with a [database adapter](../reference/DbInterface.md) (e.g.
[the PDO adapter](../reference/Db/PDO.md)).

```php
<?php
use ParagonIE\Gossamer\Db\PDO as PDOAdapter;
use ParagonIE\Gossamer\Client\TrustMode\LocalTrust;

$db = new PDOAdapter(new PDO('sqlite:/path/to/database.sql'));
$myTrustMode = new LocalTrust($db);
```

Note: We also provide another adapter that wraps the `wpdb` class built into
WordPress.

Once you have `$myTrustMode` populated, you'll want to move onto Step Two.

### Federated Trust

You first need to instantiate the [FederatedTrust](../reference/Client/TrustMode/FederatedTrust.md)
class with an [HTTP adapter](../reference/HttpInterface.md) (e.g.
[the Guzzle adapter](../reference/Http/Guzzle.md)).

You also need to know the URL of the server you're querying.

```php
<?php
use ParagonIE\Gossamer\Http\Guzzle as GuzzleAdapter;
use ParagonIE\Gossamer\Client\TrustMode\FederatedTrust;

$url = 'https://gossamer-server.example.com/gossamer-api';
$http = new GuzzleAdapter();
$myTrustMode = new FederatedTrust($http, $url);
```

Note: We also provide another adapter that wraps the `WpHttp` class built
into WordPress.

Once you have `$myTrustMode` populated, you'll want to move onto Step Two.

## Step Two: Configure Your Attestation Policy

**Note:** If you aren't planning to use third-party attestations in your
automated "should we install this update?" decision-making, you can move 
onto Step Three.

----

An [`AttestPolicy`](../reference/Client/AttestPolicy.md) object contains
one or more top-level policy rules (classes that implement 
[`PolicyRuleInterface`](../reference/Client/PolicyRuleInterface.md)).

These rules can be totally arbitrary. We include [some basic rules](../reference/Client/PolicyRules)
with libgossamer.

* `AttestedAt` returns TRUE if there is an attestation from the list of 
  trusted third-party providers with a specific attestation (e.g.
  `spot-check`). It defaults to needing 1, but you can configure any
  minimum threshold (e.g. "at least 3 of these 7 must have spot-checked
  the code").
* `GroupAnd` is instantiated by one or more rules, which ALL must return TRUE
  for `GroupAnd` to return TRUE.
* `GroupOr` is instantiated by one or more rules. If any of these rules return
  TRUE, then `GroupOr` will return TRUE as well.

This is best illustrated by example.

### AttestPolicy Example

Consider the following set of rules for a hypothetical policy:

    (
        One of the following trusted providers must have verified
        that this build was reproducible from the source code:
        
        ["reproduced-bot"]
    ) AND (
        (
            Two of the following trusted providers must have
            evaluated this at the `spot-check` level or higher:
            
            ["paragonie", "symfony", "laravel", "ncc"]
        ) OR (
            One of the following trusted providers must have
            evaluated this at the `code-review` level or higher:
            
            ["paragonie", "ncc"]
        )
    ) AND (
        None of the following trusted providers have issued a vote
        *against* installing this update:
        
        ["jedisct1", "muglug"]
    )

Translated into code, this attestation policy will look like this:

```php
<?php
use \ParagonIE\Gossamer\Client\AttestPolicy;
use \ParagonIE\Gossamer\Client\PolicyRules\AttestedAt;
use \ParagonIE\Gossamer\Client\PolicyRules\AttestedAtOrAbove;
use \ParagonIE\Gossamer\Client\PolicyRules\GroupAnd;
use \ParagonIE\Gossamer\Client\PolicyRules\GroupOr;
use \ParagonIE\Gossamer\Client\PolicyRules\Not;

$myAttestPolicy = (new AttestPolicy())
    ->addRule(
        new GroupAnd(
            new AttestedAt(
                AttestPolicy::REPRODUCED,
                ['reproduced-bot']
            ),
            new GroupOr(
                new AttestedAtOrAbove(
                    AttestPolicy::SPOT_CHECK,
                    ['paragonie', 'symfony', 'laravel', 'ncc'],
                    2
                ),
                new AttestedAtOrAbove(
                    AttestPolicy::CODE_REVIEW,
                    ['paragonie', 'ncc']
                )
            ),
            new Not(
                new AttestedAt(
                    AttestPolicy::VOTE_AGAINST,
                    ['jedisct1', 'muglug']
                )
            )
        )
    );
```

Now that you have `$myAttestPolicy` configured, you can move onto the
next step.

## Step Three: Instantiate Your Gossamer Client

### Minimalistic

At minimum, the Gossamer Client needs the Trust Mode we're operating
under to function:

```php
<?php
use ParagonIE\Gossamer\Client\GossamerClient;
use ParagonIE\Gossamer\Client\TrustModeInterface;
/**
 * @var TrustModeInterface $myTrustMode 
 */

$gossamer = new GossamerClient($myTrustMode);
```

If you also provided an attestation policy above, it gets passed as
the second constructor argument.

```php
<?php
use ParagonIE\Gossamer\Client\GossamerClient;
use ParagonIE\Gossamer\Client\TrustModeInterface;
use ParagonIE\Gossamer\Client\AttestPolicy;
/**
 * @var TrustModeInterface $myTrustMode
 * @var AttestPolicy $myAttestPolicy 
 */

$gossamer = new GossamerClient(
    $myTrustMode,
    $myAttestPolicy
);
```

If you're using a specific signature algorithm, pass the
identifier as the third argument to the constructor:

```php
<?php
use ParagonIE\Gossamer\Client\GossamerClient;
use ParagonIE\Gossamer\Client\TrustModeInterface;
use ParagonIE\Gossamer\Client\AttestPolicy;
use ParagonIE\Gossamer\Release\Common;
/**
 * @var TrustModeInterface $myTrustMode
 * @var AttestPolicy $myAttestPolicy 
 */

$gossamer = new GossamerClient(
    $myTrustMode,
    $myAttestPolicy,
    Common::SIGN_ALG_ED25519_SHA384
);
```

The signature algorithm **MUST** be hard-coded and not negoatiated
at runtime.

## Step Four: Using the Client

### Fetching Verification Keys

```php
<?php
use ParagonIE\Gossamer\Client\GossamerClient;
/**
 * @var GossamerClient $gossamer
 */

$keys = $gossamer->getVerificationKeys('paragonie');
```

This will return an array of encoded Ed25519 public keys currently
trusted for the provider.

### Validating an Update

```php
<?php
use ParagonIE\Gossamer\Client\GossamerClient;
use Psr\Http\Message\StreamInterface;
/**
 * @var GossamerClient $gossamer
 * @var string|resource|StreamInterface $file
 */

// Get the update record data:
$record = $gossamer->getUpdate('symfony/polyfill-php80', 'v1.20.0');

// If it's valid...
if ($record->isFileValid($file)) {
    // Install it!
    my_custom_installer($file);
}
```
