# PlantUML rendering and verification

Diagram sources are `.puml` files alongside their subject documents. Their `.svg` previews are committed so GitHub can display diagrams directly. The catalog manifest records document/source/image paths and hashes. Hashes detect changes; they are not signed attestations.

## Tooling

This conversion was rendered with **PlantUML 1.2026.8**, Java 25, and the bundled Windows Graphviz reported by PlantUML. Obtain the renderer from the [official release](https://github.com/plantuml/plantuml/releases/tag/v1.2026.8). Keep the jar outside the repository. Other platforms may need Graphviz for supported layout types.

## Commands from the repository root

```powershell
python scripts/docs/diagrams.py --render --jar C:/tools/plantuml-1.2026.8.jar
python scripts/docs/diagrams.py --verify
```

Rendering uses the installed Java runtime, UTF-8, a headless local process, and a recursive input pattern. It sends no diagram sources to a public rendering service. On Linux, use the equivalent paths with Python 3 and Java/Graphviz installed. See the [official command-line reference](https://plantuml.com/command-line).

## Editing and adding diagrams

1. Edit the `.puml` source and retain its original version context.
2. Regenerate previews using the pinned renderer and review the visual result.
3. Update the subject document's preview/source links when a filename changes.
4. For a new diagram, add its document/source/image/title entry to `manifest.json` and the catalog table before rendering.
5. Run verification and check all documentation links before publication.

The renderer refreshes manifest hashes only after successful rendering. Verification checks coverage, safe paths, source boundaries, SVG XML, known error-image markers, recorded hashes, and document references. Review diagrams visually as well; syntax and hashes do not prove the model is correct.

Historical diagram syntax was repaired for Markdown-escaped characters, HTML whitespace entities, mixed sequence participant shapes, trust-zone declarations, and wireframe brace labels. Historical feature plans remain historical; rendering them does not declare those features implemented.
