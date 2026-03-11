# Platoon Go Rewrite Plan

## Overview

This document outlines a plan for rewriting the Platoon Laravel package as a standalone Go CLI application. The goal is twofold: produce a useful tool that works without requiring a Laravel project, and serve as a structured learning path for Go.

---

## What We're Building

**Platoon** is a zero-downtime deployment tool. Today it lives inside Laravel and delegates SSH work to Laravel Envoy. The Go rewrite will:

- Run as a standalone binary (`platoon`) — no PHP, no Laravel, no Envoy required
- Read a single `platoon.yaml` config file (replacing `config/platoon.php`)
- SSH directly to target servers and run deployment commands (replacing Envoy)
- Expose the same top-level commands: `deploy`, `releases list`, `releases set`, `releases rollback`, `targets`, `cleanup`

---

## Go Concepts Introduced (Learning Path)

Each phase is designed to teach one or more core Go ideas:

| Phase | Go Concepts |
|-------|-------------|
| 1 | Modules, packages, `main`, CLI with Cobra |
| 2 | Structs, interfaces, methods, YAML parsing |
| 3 | `os/exec`, error handling, custom error types |
| 4 | `golang.org/x/crypto/ssh`, goroutines, channels |
| 5 | `text/template`, variadic functions, `io.Writer` |
| 6 | Testing with `testing`, table-driven tests, mocks |
| 7 | Embedding, `embed.FS`, build constraints |

---

## Proposed Directory Structure

```
platoon-go/
├── main.go                  # Entry point
├── go.mod
├── go.sum
├── platoon.yaml.example     # Example config
├── cmd/                     # CLI command definitions (Cobra)
│   ├── root.go              # Root command, global flags, config loading
│   ├── deploy.go            # platoon deploy [target]
│   ├── targets.go           # platoon targets
│   ├── cleanup.go           # platoon cleanup [--keep N]
│   └── releases/
│       ├── releases.go      # platoon releases (subcommand group)
│       ├── list.go          # platoon releases list [target]
│       ├── set.go           # platoon releases set <release> [target]
│       └── rollback.go      # platoon releases rollback [target]
├── internal/
│   ├── config/
│   │   ├── config.go        # Config structs and YAML loading
│   │   └── validate.go      # Config validation
│   ├── target/
│   │   └── target.go        # Target struct: paths, commands, hooks
│   ├── ssh/
│   │   └── client.go        # SSH session: run remote commands
│   ├── deploy/
│   │   └── deployer.go      # Deployment orchestration (the 10-step flow)
│   ├── releases/
│   │   └── releases.go      # Release listing, activation, rollback logic
│   └── tags/
│       └── expander.go      # Hook tag expansion (@php, @artisan, etc.)
└── testdata/
    └── platoon.yaml         # Fixture config for tests
```

---

## Phase 1 — Project Scaffold & CLI

**Goal:** Get a working binary that parses commands.

**Tasks:**
1. `go mod init github.com/yourname/platoon`
2. Add [Cobra](https://github.com/spf13/cobra) as the CLI framework
3. Create `main.go` → calls `cmd.Execute()`
4. Implement `cmd/root.go` with a `--config` flag (default: `platoon.yaml`) and `--target` flag
5. Add stub implementations of all commands so `platoon --help` and each subcommand produce useful output

**Go concepts:** modules (`go.mod`), packages, `func main()`, `cobra.Command`, flags

---

## Phase 2 — Configuration

**Goal:** Load and validate `platoon.yaml`.

**Config file format (YAML):**
```yaml
default: staging
repo: git@github.com:yourorg/yourapp.git

targets:
  common:
    php: /usr/bin/php
    composer: /usr/bin/composer
    keep: 2

  staging:
    host: staging.example.com
    port: 22
    username: deploy
    root: /var/www/myapp
    branch: main
    migrate: true
    assets:
      - .env.staging:.env

  production:
    host: prod.example.com
    username: deploy
    root: /var/www/myapp
    branch: main
    migrate: true
```

**Tasks:**
1. Define `Config`, `TargetConfig` structs in `internal/config/config.go`
2. Load with `gopkg.in/yaml.v3`
3. Implement `Validate()` — return structured errors for missing required fields
4. Merge `common` target settings into each named target (mirroring existing behavior)
5. Write table-driven tests for validation

**Go concepts:** structs, struct tags (`yaml:"..."`), pointer receivers, multiple return values `(T, error)`, `errors.New`, table-driven tests

---

## Phase 3 — Target & Path Resolution

**Goal:** Implement the `Target` abstraction that computes all deployment paths and command strings.

**Key paths to compute (given `root: /var/www/myapp`):**
```
/var/www/myapp/releases/           # all releases
/var/www/myapp/releases/<ts>/      # current release dir
/var/www/myapp/live                # symlink → active release
/var/www/myapp/.env                # shared env file
/var/www/myapp/storage             # shared storage
```

**Tasks:**
1. Implement `internal/target/target.go` with a `Target` struct
2. Add methods: `ReleasePath()`, `LivePath()`, `EnvPath()`, `StoragePath()`, `PHPCmd()`, `ArtisanCmd()`, `ComposerCmd()`
3. Implement `internal/tags/expander.go` — replace `@php`, `@artisan`, `@composer`, `@base`, `@release` in hook strings
4. Write unit tests for path computation and tag expansion

**Go concepts:** methods on structs, `path/filepath`, `fmt.Sprintf`, value vs pointer receivers

---

## Phase 4 — SSH Client

**Goal:** Run commands on remote servers over SSH.

**Tasks:**
1. Implement `internal/ssh/client.go` using `golang.org/x/crypto/ssh`
2. Support key-based auth (read from `~/.ssh/id_rsa` by default, or configurable path)
3. Expose two functions:
   - `Run(cmd string) error` — run a command, stream stdout/stderr to terminal
   - `RunOutput(cmd string) (string, error)` — capture output (for release listing)
4. Support custom SSH port
5. Handle connection errors gracefully with descriptive messages

**Go concepts:** interfaces (`ssh.AuthMethod`), `io.Reader`/`io.Writer`, `defer`, goroutines + `sync.WaitGroup` for output streaming

---

## Phase 5 — Deployment Orchestration

**Goal:** Implement the 10-step deployment flow in pure Go.

**The 10 steps (mirroring existing `deploy.blade.php`):**

| Step | Where | What |
|------|-------|-------|
| `build` | local | Run any local build hooks |
| `install` | remote | `git clone --branch <branch> <repo> <release_path>` |
| `prep` | remote | Symlink `.env` and `storage` into release |
| `composer` | remote | Ensure composer is available |
| `dependencies` | remote | `composer install` in release dir |
| `assets` | local→remote | SCP/rsync asset files to target |
| `database` | remote | `php artisan migrate --force` (if `migrate: true`) |
| `live` | remote | Atomically switch `live` symlink to new release |
| `cleanup` | remote | Remove old releases beyond `keep` count |
| `finish` | remote | Run any finish hooks |

**Tasks:**
1. Implement `internal/deploy/deployer.go` with a `Deployer` struct
2. Execute each step in sequence, calling user-configured hooks before/after
3. On any failure, print which step failed and exit non-zero (no automatic rollback — that's explicit via `releases rollback`)
4. Print step progress to stdout as deployment runs

**Go concepts:** structs with method sets, `os/exec` for local commands, error wrapping with `fmt.Errorf("step %s: %w", step, err)`, `errors.Is`/`errors.As`

---

## Phase 6 — Release Management Commands

**Goal:** Implement `releases list`, `releases set`, `releases rollback`, and `cleanup`.

**Tasks:**

`releases list`:
- SSH to target, `ls -1 <releases_dir>` + read `live` symlink
- Print table: release timestamp, status (active/inactive), age

`releases set <release>`:
- Verify release directory exists on target
- Atomically update `live` symlink: `ln -sfn <release_path> <live_path>.new && mv -T <live_path>.new <live_path>`

`releases rollback`:
- Find second-most-recent release
- Call the same symlink swap as `releases set`

`cleanup --keep N`:
- List releases sorted by name (timestamp order)
- Delete all but the N most recent (keeping active release safe)

**Go concepts:** `sort.Strings`, `strings.TrimSpace`, `os/exec`, handling command-line flags with Cobra

---

## Phase 7 — Polish & Distribution

**Goal:** Make the tool production-ready and distributable.

**Tasks:**
1. Embed the example config with `//go:embed` so `platoon init` can scaffold a `platoon.yaml`
2. Add `--dry-run` flag to `deploy` and `cleanup` (print commands without executing)
3. Add colored output with `github.com/fatih/color` or similar
4. Cross-compile for Linux/macOS/Windows with `GOOS`/`GOARCH`
5. Write a `Makefile` with `build`, `test`, `lint`, `release` targets
6. Add GitHub Actions workflow for CI and release binary uploads

**Go concepts:** `embed.FS`, build tags, `runtime.GOOS`, cross-compilation

---

## Key Dependency Choices

| Need | Package |
|------|---------|
| CLI framework | `github.com/spf13/cobra` |
| YAML parsing | `gopkg.in/yaml.v3` |
| SSH | `golang.org/x/crypto/ssh` |
| Colored output | `github.com/fatih/color` |
| Testing | stdlib `testing` + `github.com/stretchr/testify` |

---

## What Changes From the PHP Version

| PHP / Laravel | Go |
|---------------|----|
| `config/platoon.php` | `platoon.yaml` |
| Laravel Artisan commands | Cobra CLI commands |
| Laravel Envoy (Blade templates + PHP SSH) | `golang.org/x/crypto/ssh` directly |
| `PlatoonServiceProvider` | `cmd/root.go` wires everything together |
| `ConfigValidator` (Laravel Validator) | Custom `Validate()` returning `[]error` |
| `TagExpander` | `internal/tags/expander.go` |
| Composer autoload | Go module system |

---

## Suggested Order of Work

1. Phase 1 — Scaffold + CLI (get `platoon --help` working)
2. Phase 2 — Config loading + validation (tests first)
3. Phase 3 — Target paths and tag expansion (tests first)
4. Phase 4 — SSH client (can be tested manually against a VM)
5. Phase 5 — Deploy orchestration (wire it all together)
6. Phase 6 — Release management commands
7. Phase 7 — Polish and distribution

Each phase builds directly on the previous one, and each introduces new Go idioms in a manageable scope.
