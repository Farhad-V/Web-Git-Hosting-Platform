"""Render and verify the checked-in PlantUML documentation catalog."""
import argparse
import hashlib
import json
import os
import subprocess
import xml.etree.ElementTree as ET
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
MANIFEST = ROOT / 'docs/diagrams/manifest.json'

def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()

def resolve(relative):
    path = (ROOT / relative).resolve()
    if not path.is_relative_to(ROOT / 'docs'):
        raise ValueError('Diagram path outside docs: ' + relative)
    return path

def verify(records, hashes=True):
    seen = set()
    for item in records:
        source, image, document = (resolve(item[key]) for key in ('source', 'image', 'document'))
        if source in seen:
            raise ValueError('Duplicate source: ' + item['source'])
        seen.add(source)
        text = source.read_text(encoding='utf-8').strip()
        if not any(text.startswith(start) and text.endswith(end) for start, end in
                   (('@startuml', '@enduml'), ('@startsalt', '@endsalt'))):
            raise ValueError('Invalid source boundaries: ' + item['source'])
        svg = image.read_text(encoding='utf-8')
        xml = ET.fromstring(svg)
        if xml.tag != '{http://www.w3.org/2000/svg}svg':
            raise ValueError('Invalid SVG: ' + item['image'])
        if any(marker in svg for marker in ('Syntax Error', 'An error has occured', 'java.lang.StringIndexOutOfBoundsException')):
            raise ValueError('PlantUML error preview: ' + item['image'])
        doc = document.read_text(encoding='utf-8')
        for path in (source, image):
            relative = os.path.relpath(path, document.parent).replace('\\', '/')
            if '](' + relative + ')' not in doc:
                raise ValueError('Missing document link: ' + relative)
        if hashes:
            for key, path in (('source_sha256', source), ('image_sha256', image)):
                if item.get(key) != digest(path):
                    raise ValueError('Changed diagram; regenerate and verify: ' + str(path))
    actual = {path.resolve() for path in (ROOT / 'docs').rglob('*.puml')}
    if actual != seen:
        raise ValueError('Catalog source coverage mismatch')
    if list((ROOT / 'docs').rglob('*.mmd')):
        raise ValueError('Superseded Mermaid sources remain')
    for document in (ROOT / 'docs').rglob('*.md'):
        if '```mermaid' in document.read_text(encoding='utf-8'):
            raise ValueError('Unconverted Mermaid block: ' + str(document))

def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--render', action='store_true')
    parser.add_argument('--verify', action='store_true')
    parser.add_argument('--jar', type=Path)
    args = parser.parse_args()
    records = json.loads(MANIFEST.read_text(encoding='utf-8'))
    if args.render:
        if not args.jar or not args.jar.is_file():
            parser.error('--render requires an existing official PlantUML --jar')
        subprocess.run(['java', '-Djava.awt.headless=true', '-jar', str(args.jar.resolve()),
                        '-tsvg', '-charset', 'UTF-8', 'docs/**/*.puml'], cwd=ROOT, check=True)
        for item in records:
            image = resolve(item['image'])
            image.write_bytes(image.read_bytes().replace(b'\r\n', b'\n'))
        verify(records, hashes=False)
        for item in records:
            item['source_sha256'] = digest(resolve(item['source']))
            item['image_sha256'] = digest(resolve(item['image']))
        MANIFEST.write_text(json.dumps(records, indent=2) + '\n', encoding='utf-8', newline='')
    verify(records)
    print(f'Verified {len(records)} PlantUML sources, SVG previews, hashes, and document references.')

if __name__ == '__main__':
    main()
