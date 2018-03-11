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
