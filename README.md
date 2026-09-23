[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]

<p align="center">
  <a href="https://github.com/jestrella04/Poetainos">
    <img src="public/images/logo.svg" alt="Poetainos logo" width="80" height="80">
  </a>
</p>

<h3 align="center">Poetainos</h3>

<p align="center">
  A virtual community for amateur writers.
  <br>
  <a href="https://poetainos.com">Visit the site</a>
  ·
  <a href="https://github.com/jestrella04/Poetainos/issues">Report a bug</a>
  ·
  <a href="https://github.com/jestrella04/Poetainos/issues">Request a feature</a>
</p>

## Table of Contents

- [About the Project](#about-the-project)
- [Built With](#built-with)
- [Getting Started](#getting-started)
- [Development](#development)
- [Knowledge Graph (graphify)](#knowledge-graph-graphify)
- [Contributing](#contributing)
- [License](#license)

## About the Project

Poetainos is a virtual community for amateur writers. This repository holds the source code that powers [poetainos.com](https://poetainos.com).

## Built With

- [Laravel](https://laravel.com) (PHP 8.5+) with MariaDB
- [Vue 3](https://vuejs.org) + TypeScript
- [Vuetify 4](https://vuetifyjs.com)
- [Inertia.js](https://inertiajs.com) (SPA + SSR)
- [Vite](https://vite.dev)

## Getting Started

### Prerequisites

- PHP 8.5+ and [Composer](https://getcomposer.org)
- Node.js 24.x and npm
- MariaDB

A ready-to-use dev container is also available in [`.devcontainer/`](.devcontainer).

### Installation

```sh
git clone https://github.com/jestrella04/Poetainos.git
cd Poetainos
composer setup
```

`composer setup` installs PHP and Node dependencies, creates `.env` from `.env.example`, generates the app key, runs migrations, installs the Playwright browser, and builds the frontend. Update the database credentials in `.env` before running it (or rerun `php artisan migrate` afterwards).

To load demo data:

```sh
php artisan migrate:fresh --seed
```

## Development

```sh
composer dev
```

Starts the Laravel server (http://localhost:8000), queue worker, log viewer (Pail), and Vite dev server together.

| Command                          | Description                                                         |
| -------------------------------- | ------------------------------------------------------------------- |
| `npm run build`                  | Production build (client + SSR bundles)                             |
| `composer test`                  | Run the Pest test suite                                             |
| `npm run test`                   | Run the Vitest test suite                                           |
| `composer ci:check`              | Lint, format check, static analysis, type check, and frontend tests |
| `composer lint` / `npm run lint` | Fix PHP / JS style issues                                           |

## Knowledge Graph (graphify)

The project uses [graphify](https://github.com/Ruzicka/graphify) to maintain a knowledge graph for codebase exploration. Install it with:

```sh
curl -LsSf https://astral.sh/uv/install.sh | sh
uv tool install graphifyy
graphify install --project   # Register the skill with your AI assistant
```

Query or update the graph:

```sh
graphify query "how are writings published"   # Scoped subgraph for a question
graphify explain "WritingPublisher"             # Focused concept explanation
graphify path "WritingPublisher" "ImageStorage" # Relationship between two nodes
graphify update .                               # Rebuild the graph after code changes
```

The pre-built graph lives in `graphify-out/`.

## Contributing

Contributions are welcome. See the [open issues](https://github.com/jestrella04/Poetainos/issues) for proposed features and known bugs.

1. Fork the project
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push the branch (`git push origin feature/amazing-feature`)
5. Open a pull request

Please make sure `composer ci:check` and `composer test` pass before submitting.

## License

Distributed under the MIT License. See [`LICENSE.txt`](LICENSE.txt) for details.

[contributors-shield]: https://img.shields.io/github/contributors/jestrella04/Poetainos.svg?style=flat-square
[contributors-url]: https://github.com/jestrella04/Poetainos/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/jestrella04/Poetainos.svg?style=flat-square
[forks-url]: https://github.com/jestrella04/Poetainos/network/members
[stars-shield]: https://img.shields.io/github/stars/jestrella04/Poetainos.svg?style=flat-square
[stars-url]: https://github.com/jestrella04/Poetainos/stargazers
[issues-shield]: https://img.shields.io/github/issues/jestrella04/Poetainos.svg?style=flat-square
[issues-url]: https://github.com/jestrella04/Poetainos/issues
[license-shield]: https://img.shields.io/github/license/jestrella04/Poetainos.svg?style=flat-square
[license-url]: https://github.com/jestrella04/Poetainos/blob/master/LICENSE.txt
