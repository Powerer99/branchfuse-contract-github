# branchfuse-contract-github

A small PHP project for trying out [BranchFuse](https://github.com/Powerer99/branchfuse) on GitHub Actions. The
Bitbucket twin is `powerer99/branchfuse-contract` on Bitbucket.

`src/PriceCalculator.php` has deliberate bugs, so the PHPUnit suite always has a mix of results: 7 tests pass, 2 fail
and 1 errors (`feature/shipping` adds 3 more passing tests and 1 more failure). Each failure points at a line in
`tests/` or `src/`, which BranchFuse turns into a clickable link.

## Workflow

`.github/workflows/phpunit.yml` (*PHPUnit*) runs the tests. Start it from BranchFuse on any branch, with inputs:

| Input | What it does |
|---|---|
| `test_filter` | Optional: only run matching tests, e.g. `Coupon` |
| `currency` | GBP, USD or EUR. EUR makes one more test fail |
| `verbose` | Lists every test as it runs |

Pushes to `feature/**` branches run it too.

`.github/workflows/passing.yml` (*PHPUnit (passing tests)*) runs the same suite without the tests tagged
`#[Group('known-bugs')]`, so it succeeds. Start it from BranchFuse to see a green run.

## Running the tests locally

```sh
composer install
vendor/bin/phpunit
```
