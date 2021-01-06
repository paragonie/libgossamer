# Libgossamer

[![Build Status](https://travis-ci.org/paragonie/libgossamer.svg?branch=master)](https://travis-ci.org/paragonie/libgossamer)
[![Latest Stable Version](https://poser.pugx.org/paragonie/libgossamer/v/stable)](https://packagist.org/packages/paragonie/libgossamer)
[![Latest Unstable Version](https://poser.pugx.org/paragonie/libgossamer/v/unstable)](https://packagist.org/packages/paragonie/libgossamer)
[![License](https://poser.pugx.org/paragonie/libgossamer/license)](https://packagist.org/packages/paragonie/libgossamer)
[![Downloads](https://img.shields.io/packagist/dt/paragonie/libgossamer.svg)](https://packagist.org/packages/paragonie/libgossamer)

> **Want to learn about the Gossamer project? [*Check out our website!*](https://gossamer.tools)**

Library that provides most of the plumbing for the Gossamer PKI.

Since version 0.4.0 it also bundles a client-side library for retrieving
keys and verifying the signatures of update files.

The code syntax is compatible with PHP 5.3+, but this is only intended for PHP 5.6+,
[as per WordPress's new minimum supported version](https://wordpress.org/news/2019/04/minimum-php-version-update/).

# Getting Started

## Installing

First, obtain the source code from Composer/Packagist, like so:

```
composer require paragonie/libgossamer:^0|^1
```

This will include two components:

1. The library that implements the Gossamer specification.
2. The [Gossamer Client](lib/Client).

The next steps will depend entirely on what you want to do with Gossamer.
Check out the [tutorials](docs/tutorials) directory for specific next steps.

## Other Repositories

* [Gossamer Standalone Server](https://github.com/paragonie/gossamer-server)
* [Gossamer Command Line Interface](https://github.com/paragonie/gossamer-cli)

## Documentation

**[Read the Libgossamer Documentation online](docs).**
