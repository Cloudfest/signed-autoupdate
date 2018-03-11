# Signed Autoupdate

https://docs.google.com/document/d/1iRSns-AMslhaMeXPssv33ko7q07oZSsYtk7fQe0qfAs/edit?usp=sharing

## CLI

The CLI helps you to generate a keypair, to sign a package and to verify you signed package.

### Commands

#### `generator:generate`

To generate a new keypair run:

```bash
$ signer.phar generator:generate [<path>]
```

to get the complete list of parameters use:

```bash
$ signer.phar generator:generate --help
```

#### `signer:sign`

To sign a package, navigate to the package folder and run:

```bash
$ signer.phar signer:sign [options] [--] <path> <key>
```

to get the complete list of parameters use:

```bash
$ signer.phar signer:sign --help
```

#### `signer:sign`

To verify a signed package, run:

```bash
$ signer.phar verifier:verify [<signature>] [<key>] [<list>]
```

to get the complete list of parameters use:

```bash
$ signer.phar verifier:verify --help
```

### Build phar package

To build a new `.phar` package, you have to install [box](https://github.com/box-project/box2#as-a-global-composer-install) and run

```bash
$ box build -v
```

in the root of the `cli` folder.

# WordPress Plugin

## Description

Plugin will inject via add_filter into the download process of a plugin and checks for existence of some files. If 
signatures existing it will try to verify the files signatures. The plugin also allows deletion and editing of public
keys.

So:

- will check for existence of: .well-known/signature.txt, .well-known/publickey.txt, .well-known/list.json
- if existing:
  - and public key is new, stores to trusted store
  - and public key is old, checks against public key the same
  - will block update if not the same keys
  - will verify with signature, public key and the list.json if the package is valid
- the SAU Signatures shows already known signatures for editing / deletion


## Screens

* [Package List View](!/doc/package-list-view.png)
* [New Key Add During First Install](!/doc/new-key-found.png)
* [Reject Installation on Error](!/doc/installation-rejected-key-mismatch.png)