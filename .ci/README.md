# XigmaNAS 14.3 CI

The repository uses SVN **r10566** from the `14.3.0.5` branch. The imported
revision and URL are recorded in `svn-import.json`. The import workflow
preserves Git patches once r10566 is imported. The one-time switch from
the initial r10655 import is refused if source patches have already been made.

## Normal Use

Push patches to `main`. `Build XigmaNAS` checks out that exact commit and
builds a LiveCD ISO on a FreeBSD 14.3 VM hosted by GitHub Actions. The local computer is
not used to fetch the complete source tree or compile anything.

Successful builds publish an `xigmanas-LiveCD-amd64-<commit>` artifact containing
the LiveCD ISO, SHA512 checksums, and `build-info.txt`. Artifacts
expire after 14 days. Diagnostics are retained for 7 days.

The workflow can also be started manually. Leave `source_sha` empty and
`slice` at `1`. Select `environment` to prepare and cache the toolchain
without building the ISO.

The selected upstream operation is **Create 'LiveCD' (ISO) file** (`create_iso`,
menu item 13), not LiveCD-Tin or Create All. The normal LiveCD includes an
embedded installation image, so upstream still creates that necessary
intermediate file inside the VM. It is not published as a separate artifact.

## Cached Work

- The VM action caches its download, host packages, and prepared toolchain.
- Durable `ci-cache-*` prereleases store FreeBSD source, the ports tree,
  downloaded distfiles, installed build dependencies, compiler cache,
  kernel objects, and resumable XigmaNAS port work directories.
- Expensive state is saved even when compilation fails. Completed dependency
  work directories are discarded after installation; their binaries and
  package database remain cached.
- A source-only patch reuses the kernel and built ports, then assembles a
  fresh image. Kernel configuration or patch changes rebuild the kernel.
  Changes under `build/ports` or `build/make.conf` rebuild the custom ports.
- `freebsd.env` pins both FreeBSD source and ports commits. Changing those
  pins or `prepare.sh` starts a new compatible checkpoint generation.
- Each compatible generation retains the latest two complete checkpoints.
  Large archives are split into assets smaller than 2 GB. SHA256 hashes
  are checked before restoring them.

Checkpoints are public release assets. They contain generated build files,
installed dependency binaries, and build configuration. They must never
contain private signing keys, credentials, or other secrets. The exporter
excludes SSH keys, SSH configuration directories, and VM password databases.
Do not add secret-bearing build steps without reviewing this boundary.

## Long Builds

A build slice runs for at most 210 minutes. On a normal timeout, the runner
saves a checkpoint and dispatches another slice for the same Git commit.
At most 12 slices are started automatically. A genuine compiler error stops
the chain and is reported as a failed run.

The runner's hard time limit, infrastructure loss, or a failed checkpoint
upload cannot guarantee preservation of the current slice. The previous
published checkpoint remains available. Failed or incomplete uploads remain
drafts and are never selected for restoration.

## Maintenance

FreeBSD 14.3 is intentionally retained for compatibility with this branch.
It reached end of support on June 30, 2026. Images produced here inherit
the security and compatibility limitations of that branch.

`build.sh` uses the upstream filesystem, port, bootloader, and image
assembly routines without entering interactive menus or updating SVN.
After the initial import, Git is the only source of XigmaNAS patches.
