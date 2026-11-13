# XigmaNAS 14.3 CI

## Overlay Branch

`cache-overlay` experiments with file-level checkpoint layers using the real
LiveCD and LiveUSB build, without adding a separate test suite.
It has its own build queue, release namespace, and emergency-cache keys.
The first run copies an existing compatible full checkpoint into an isolated
base release once; it does not rebuild those cached components. This protects
the base from main's independent two-generation cleanup.

Each restore applies the base followed by its overlays and takes a local
metadata inventory before the build. Size, nanosecond modification/change
times, file type, ownership, flags, and link target detect changes within that
same VM; files are not considered changed merely because a new VM restored
them. Each overlay contains changed files and explicit removals, including
directory/file replacements. Rootfs, generated images, checkout metadata,
and image-only installation markers are excluded from new layers.

Unchanged reusable state skips backup. A chain compacts after eight overlays
or when accumulated compressed overlays reach half of its base size.
Cleanup retains the latest two heads and every ancestor they reference.
Draft/incomplete uploads are not restored; failed uploads retain the existing
emergency-cache fallback. A fresh runner still downloads the base and all
layers, but subsequent saves upload only the changed layer.

The repository uses SVN **r10566** from the `14.3.0.5` branch. The imported
revision and URL are recorded in `svn-import.json`. The import workflow
preserves Git patches once r10566 is imported. The one-time switch from
the initial r10655 import is refused if source patches have already been made.

## Normal Use

Push patches to `main`. `Build XigmaNAS` checks out that exact commit and
builds LiveCD and LiveUSB images on a FreeBSD 14.3 VM hosted by GitHub Actions.
The local computer is not used to fetch the complete source tree or compile anything.

Successful builds publish two separate artifacts:

- `xigmanas-LiveCD-amd64-<commit>`: the normal LiveCD ISO.
- `xigmanas-LiveUSB-GPT-amd64-<commit>`: the GPT LiveUSB `.img.gz` disk image.

Each includes its own SHA512 checksums and `build-info.txt`. Artifacts expire
after 14 days. Images are uploaded before checkpoint backup, so they can be
downloaded while backup is still running. Diagnostics are retained for 7 days.

The workflow can also be started manually. Leave `source_sha` empty and
`slice` at `1`. Select `environment` to prepare and cache the toolchain
without building the images.

The selected upstream operations are **Create 'LiveCD' (ISO) file** (`create_iso`,
menu item 13) and **Create 'LiveUSB.img.gz GPT' file** (`create_usb_gpt`, menu
item 12), not LiveCD-Tin or Create All. Both share the same kernel and compiled
components. Upstream still creates the necessary embedded installation image
inside the VM. It is not published as a separate artifact.

## Cached Work

- The VM action caches its download, host packages, and prepared toolchain.
- Durable `ci-cache-*` prereleases store FreeBSD source, the ports tree,
  downloaded distfiles, installed build dependencies, compiler cache,
  kernel objects, and resumable XigmaNAS port work directories.
- Expensive state is saved even when compilation fails, but explicit workflow
  cancellation stops backup and skips its fallback and diagnostic uploads.
  Image assembly alone does not mark the compiled cache as changed. Backups
  are skipped unless reusable source, build configuration, dependencies,
  kernel, or port work changed; partial compilation still marks them changed.
  Installed dependency work directories are discarded unless image assembly
  needs their output; their binaries and package database remain cached.
- A source-only patch reuses the kernel and built ports, then assembles a
  fresh image. Kernel configuration or patch changes rebuild the kernel.
  Each custom port has its own input fingerprint: changing one recipe,
  distinfo, or patch invalidates only that port, including partial work.
  Shared options, base-port overlays, bundled distfiles, build metadata,
  or the effective make configuration invalidate all custom ports.
- Restored legacy checkpoints are fingerprinted before the new Git checkout
  replaces their source metadata, preserving completed and partial builds.
  Each port and the kernel log cache hits and elapsed build time.
- The compiler cache has an 8 GB limit. Root filesystem assembly remains
  fresh for each source commit so removed files cannot leak into new images.
- `freebsd.env` pins both FreeBSD source and ports commits. Changing those
  pins or `prepare.sh` starts a new compatible checkpoint generation.
- Each compatible generation retains the latest two complete checkpoints.
  Large archives are split into assets smaller than 2 GB. SHA256 hashes
  are checked before restoring them.
- Release tags anchor storage on the current default branch; `manifest.json`
  records the actual built source commit. This avoids stale-commit workflow
  permission failures when new commits arrive during compilation.
- Release creation permission is checked before expensive work. Failed release
  uploads additionally save the compressed archive to an emergency Actions cache.
  Restoration selects the newer compatible release or emergency checkpoint.

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
If a newer commit reaches `main`, the older build saves its checkpoint but
does not start more slices. Stale continuation jobs are skipped before
entering the build queue.

The runner's hard time limit, infrastructure loss, or a failed checkpoint
upload and emergency-cache failure cannot guarantee preservation of the current slice. The previous
published checkpoint remains available. Failed or incomplete uploads remain
drafts and are never selected for restoration.

## Maintenance

FreeBSD 14.3 is intentionally retained for compatibility with this branch.
It reached end of support on June 30, 2026. Images produced here inherit
the security and compatibility limitations of that branch.

`build.sh` uses the upstream filesystem, port, bootloader, and image
assembly routines without entering interactive menus or updating SVN.
After the initial import, Git is the only source of XigmaNAS patches.
