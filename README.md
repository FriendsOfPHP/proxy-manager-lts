# FriendsOfPHP / Proxy Manager LTS

This package is a fork of the excellent [`ocramius/proxy-manager`](https://github.com/Ocramius/ProxyManager/) library
that adds long term support for a wider range of PHP versions.

Unless they're caused by this very fork, please report issues and submit new features to the origin library.

This fork:
- maintains compatibility with PHP `>=7.1`;
  supporting new versions of PHP is considered as a bugfix;
- won't bump the minimum supported version of PHP in a minor release;
- does not depend on Composer 2, thus can be used with Composer 1 if you need more time to migrate;
- uses a versioning policy that is friendly to progressive migrations
  while providing the latest improvements from the origin lib.
