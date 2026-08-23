from pathlib import Path
import re

ROOT = Path('docs/06-testing')
TABLE_ROW = re.compile(r'^\s*\|.*\|\s*$')
SEPARATOR = re.compile(r'^\s*\|?\s*:?-{3,}:?\s*(?:\|\s*:?-{3,}:?\s*)+\|?\s*$')


def normalize_tables(text: str) -> tuple[str, int]:
    lines = text.splitlines()
    remove = set()
    in_fence = False
    fence = None
    i = 0

    while i < len(lines):
        stripped = lines[i].lstrip()
        if stripped.startswith('```') or stripped.startswith('~~~'):
            marker = stripped[:3]
            if not in_fence:
                in_fence = True
                fence = marker
            elif marker == fence:
                in_fence = False
                fence = None
            i += 1
            continue

        if not in_fence and SEPARATOR.match(lines[i]):
            # Header row may be separated from the delimiter by accidental blank lines.
            h = i - 1
            while h >= 0 and lines[h].strip() == '':
                h -= 1
            if h >= 0 and TABLE_ROW.match(lines[h]):
                for k in range(h + 1, i):
                    if lines[k].strip() == '':
                        remove.add(k)

                # Continue through the table, allowing accidental blank lines between rows.
                j = i + 1
                while j < len(lines):
                    if lines[j].strip() == '':
                        n = j + 1
                        while n < len(lines) and lines[n].strip() == '':
                            n += 1
                        if n < len(lines) and TABLE_ROW.match(lines[n]):
                            for k in range(j, n):
                                remove.add(k)
                            j = n
                            continue
                        break
                    if TABLE_ROW.match(lines[j]):
                        j += 1
                        continue
                    break
                i = max(i + 1, j)
                continue
        i += 1

    if not remove:
        return text, 0

    result = '\n'.join(line for idx, line in enumerate(lines) if idx not in remove)
    if text.endswith('\n'):
        result += '\n'
    return result, len(remove)


def main() -> None:
    changed_files = 0
    removed_blank_lines = 0
    for path in sorted(ROOT.glob('*.md')):
        # Scope is deliberately limited to the numbered M5 documents 01 through 21.
        try:
            number = int(path.name.split('-', 1)[0])
        except ValueError:
            continue
        if not 1 <= number <= 21:
            continue

        original = path.read_text(encoding='utf-8')
        normalized, removed = normalize_tables(original)
        if normalized != original:
            path.write_text(normalized, encoding='utf-8', newline='\n')
            changed_files += 1
            removed_blank_lines += removed
            print(f'fixed {path}: removed {removed} blank line(s) inside Markdown tables')

    print(f'changed_files={changed_files}')
    print(f'removed_blank_lines={removed_blank_lines}')


if __name__ == '__main__':
    main()
