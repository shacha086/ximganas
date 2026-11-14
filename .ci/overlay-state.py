#!/usr/local/bin/python3.11
"""Inventory and apply file-level FreeBSD checkpoint overlays."""

import fnmatch
import json
import os
from pathlib import Path, PurePosixPath
import shutil
import stat
import sys


ROOTS = (
    "usr/local", "usr/src", "usr/ports", "usr/obj",
    "var/db/pkg", "var/db/ports", "var/cache/ccache",
    "boot/kernel", "boot/modules", "etc/make.conf",
)
NAS = "usr/local/xigmanas"
SVN = f"{NAS}/svn"
STATE = f"{NAS}/state"
TRANSIENT_STATE = {"dirty", "exit-code", "rootfs-sha", "pkg-db-sha"}


def included(path):
    parts = path.split("/")
    if ".ssh" in parts or any(fnmatch.fnmatch(p, "ssh_host_*_key*") for p in parts):
        return False
    if path == "usr/local/etc/ssh" or path.startswith("usr/local/etc/ssh/"):
        return False
    if path.startswith(f"{NAS}/"):
        relative = path[len(NAS) + 1:]
        if relative.split("/")[0] not in ("state", "svn"):
            return False
    if path.startswith(f"{STATE}/"):
        name = path[len(STATE) + 1:]
        if name in TRANSIENT_STATE or name.startswith("installed-"):
            return False
    if path.startswith(f"{SVN}/"):
        relative = path[len(SVN) + 1:].split("/")
        if relative[:2] != ["build", "ports"]:
            return relative == ["build"]
        if len(relative) >= 4:
            return relative[3] == "work" or relative[3].startswith("work-")
    return True


def inventory():
    required_file = Path(f"/{STATE}/required-workdirs")
    required = set(required_file.read_text().splitlines()) if required_file.exists() else set()
    entries = {}

    def visit(path, info=None):
        if not included(path):
            return
        absolute = f"/{path}"
        try:
            info = info or os.lstat(absolute)
        except FileNotFoundError:
            return
        mode = info.st_mode
        if stat.S_ISSOCK(mode):
            return
        if (path.startswith("usr/ports/") and path.endswith("/work") and
                stat.S_ISDIR(mode) and absolute not in required):
            with os.scandir(absolute) as children:
                if any(fnmatch.fnmatch(child.name, ".install_done.*") for child in children):
                    return
        directory = stat.S_ISDIR(mode)
        entries[path] = [
            mode, info.st_uid, info.st_gid, getattr(info, "st_flags", 0),
            0 if directory else info.st_size,
            info.st_mtime_ns, info.st_ctime_ns,
            os.readlink(absolute) if stat.S_ISLNK(mode) else "",
            info.st_rdev,
        ]
        if directory:
            with os.scandir(absolute) as children:
                for child in children:
                    try:
                        child_info = child.stat(follow_symlinks=False)
                    except FileNotFoundError:
                        continue
                    visit(f"{path}/{child.name}", child_info)

    for root in ROOTS:
        visit(root)
    return entries


def write_json(path, value):
    with open(path, "w", encoding="ascii") as stream:
        json.dump(value, stream, separators=(",", ":"), ensure_ascii=True)


def export(baseline, destination):
    before = json.loads(Path(baseline).read_text()) if baseline else {}
    after = inventory()
    changed = sorted(
        (path for path, info in after.items() if before.get(path) != info),
        key=lambda path: (path.count("/"), path),
    )
    removed = set(before.keys() - after.keys())
    removed.update(
        path for path in before.keys() & after.keys()
        if stat.S_IFMT(before[path][0]) != stat.S_IFMT(after[path][0])
    )
    # A removed directory covers its descendants, including type replacements.
    deleted = [
        path for path in sorted(removed)
        if not any(str(parent) in removed for parent in PurePosixPath(path).parents)
    ]
    directory = Path(destination)
    directory.mkdir(parents=True, exist_ok=True)
    write_json(directory / "changed.json", changed)
    write_json(directory / "deleted.json", deleted)
    with open(directory / "files.list", "wb") as stream:
        for path in changed:
            stream.write(os.fsencode(path) + b"\0")
    logical_bytes = sum(
        after[path][4] for path in changed if stat.S_ISREG(after[path][0])
    )
    write_json(directory / "stats.json", {
        "changed": len(changed), "deleted": len(deleted),
        "logical_bytes": logical_bytes, "entries": len(after),
    })
    print(f"OVERLAY changed={len(changed)} deleted={len(deleted)} "
          f"logical_bytes={logical_bytes}", file=sys.stderr)


def validate(path):
    if not isinstance(path, str) or not path or "\0" in path:
        raise ValueError("Invalid overlay path")
    parts = path.split("/")
    if any(part in ("", ".", "..") for part in parts):
        raise ValueError(f"Unsafe overlay path: {path!r}")
    if not any(path == root or path.startswith(f"{root}/") for root in ROOTS):
        raise ValueError(f"Path outside checkpoint roots: {path!r}")
    if not included(path):
        raise ValueError(f"Excluded checkpoint path: {path!r}")
    return Path(f"/{path}")


def check_parents(path):
    for parent in path.parents:
        if parent.is_symlink():
            raise ValueError(f"Overlay parent is a symlink: {parent}")


def clear_flags(path):
    try:
        info = path.lstat()
    except FileNotFoundError:
        return
    if getattr(info, "st_flags", 0):
        if stat.S_ISLNK(info.st_mode):
            os.lchflags(path, 0)
        else:
            os.chflags(path, 0)


def prepare(destination):
    directory = Path(destination)
    deleted = json.loads((directory / "deleted.json").read_text())
    changed = json.loads((directory / "changed.json").read_text())
    if not isinstance(deleted, list) or not isinstance(changed, list):
        raise ValueError("Invalid overlay file lists")
    # Validate the entire layer before deleting anything.
    paths = [(name, validate(name)) for name in deleted]
    targets = [validate(name) for name in changed]
    for name, path in paths:
        if name in ROOTS and name != "etc/make.conf":
            raise ValueError(f"Cannot remove a checkpoint root: {name}")
        check_parents(path)
    for _, path in paths:
        if path.is_dir() and not path.is_symlink():
            for root, dirs, files in os.walk(path, topdown=False, followlinks=False):
                for name in dirs + files:
                    clear_flags(Path(root) / name)
                clear_flags(Path(root))
            shutil.rmtree(path)
        else:
            clear_flags(path)
            path.unlink(missing_ok=True)
    for path in targets:
        check_parents(path)
        clear_flags(path)


def main():
    operation = sys.argv[1]
    if operation == "snapshot":
        entries = inventory()
        write_json(sys.argv[2], entries)
        print(f"OVERLAY baseline_entries={len(entries)}", file=sys.stderr)
    elif operation == "diff":
        export(sys.argv[2], sys.argv[3])
    elif operation == "full":
        export(None, sys.argv[2])
    elif operation == "prepare":
        prepare(sys.argv[2])
    else:
        raise ValueError(f"Unknown operation: {operation}")


if __name__ == "__main__":
    main()
