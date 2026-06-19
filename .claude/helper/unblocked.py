#!/usr/bin/env python3
"""
Listet alle ungeblockten Tickets (Open + Draft) gegliedert nach Status.
Cross-checkt Status zwischen ticket-files und README (README ist Source-of-Truth
für Done-Status).

Usage:  python3 .claude/helper/unblocked.py
"""

import re
import glob
import os
import sys

TICKETS_DIR = '.claude/tickets'

if not os.path.isdir(TICKETS_DIR):
    print(f'Run from project root (kein {TICKETS_DIR}/)', file=sys.stderr)
    sys.exit(1)


def load_done_from_readme() -> set:
    readme = open(f'{TICKETS_DIR}/README.md').read()
    done = set()
    for line in readme.split('\n'):
        if '| Done' in line:
            m = re.search(r'\|\s+(\d+[a-z]?)-', line)
            if m:
                done.add(m.group(1))
    return done


def parse_ticket(path: str) -> dict:
    num = re.match(r'(\d+[a-z]?)', os.path.basename(path)).group(1)
    txt = open(path).read()
    m = re.search(r'^\*\*Status:\*\*\s*([^\n(]+?)(?:\s*\(|$)', txt, re.M)
    status = m.group(1).strip() if m else '?'
    m = re.search(r'^\*\*Effort:\*\*\s*(\S+)', txt, re.M)
    effort = m.group(1) if m else '?'
    m = re.search(r'^\*\*Depends on:\*\*(.*)', txt, re.M)
    deps_line = m.group(1) if m else ''
    deps = re.findall(r'T-(\d+[a-z]?)', deps_line)
    title = open(path).readline().strip().lstrip('# ')
    title = re.sub(rf'^T-{num}[:\s]+', '', title)
    return {
        'num': num,
        'status': status,
        'effort': effort,
        'deps': deps,
        'title': title,
    }


def main():
    done = load_done_from_readme()
    files = [
        f for f in glob.glob(f'{TICKETS_DIR}/*.md')
        if not f.endswith('README.md')
    ]
    tickets = []
    for f in files:
        t = parse_ticket(f)
        if t['num'] in done:
            t['status'] = 'Done'
        tickets.append(t)

    def is_unblocked(deps: list) -> bool:
        return all(d in done for d in deps)

    open_unblocked = [t for t in tickets if t['status'] == 'Open' and is_unblocked(t['deps'])]
    draft_unblocked = [t for t in tickets if t['status'] == 'Draft' and is_unblocked(t['deps'])]

    open_unblocked.sort(key=lambda t: t['num'])
    draft_unblocked.sort(key=lambda t: t['num'])

    print('=== UNGEBLOCKT — Open ===')
    print(f'({len(open_unblocked)})')
    for t in open_unblocked:
        print(f"  T-{t['num']} [{t['effort']}]  {t['title']}")

    print()
    print('=== UNGEBLOCKT — Draft ===')
    print(f'({len(draft_unblocked)})')
    for t in draft_unblocked:
        print(f"  T-{t['num']} [{t['effort']}]  {t['title']}")

    print()
    print('=== STATS ===')
    print(f'Done:  {len(done)}')
    print(f'Open:  {sum(1 for t in tickets if t["status"] == "Open")}')
    print(f'Draft: {sum(1 for t in tickets if t["status"] == "Draft")}')


if __name__ == '__main__':
    main()
