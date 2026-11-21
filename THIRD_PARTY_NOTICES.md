# Third-Party Notices

This document identifies the direct third-party dependencies and bundled font assets used by the public WCCC version 1.0.65 snapshot.

The listed version numbers are the version ranges declared in `package.json`, not necessarily the exact versions resolved during the original build.

## 1. Runtime Dependencies

| Package | Declared Version | License | Upstream |
|---|---|---|---|
| React | ^19.0.0 | MIT | https://github.com/facebook/react |
| React DOM | ^19.0.0 | MIT | https://github.com/facebook/react |
| clsx | ^2.1.1 | MIT | https://github.com/lukeed/clsx |
| lucide-react | ^0.475.0 | ISC | https://github.com/lucide-icons/lucide |
| tailwind-merge | ^3.0.1 | MIT | https://github.com/dcastil/tailwind-merge |

## 2. Development Dependencies

| Package | Declared Version | License | Upstream |
|---|---|---|---|
| @tailwindcss/vite | ^4.0.0 | MIT | https://github.com/tailwindlabs/tailwindcss |
| @types/node | ^22.13.4 | MIT | https://github.com/DefinitelyTyped/DefinitelyTyped |
| @types/react | ^19.0.8 | MIT | https://github.com/DefinitelyTyped/DefinitelyTyped |
| @types/react-dom | ^19.0.3 | MIT | https://github.com/DefinitelyTyped/DefinitelyTyped |
| @vitejs/plugin-react | ^4.3.4 | MIT | https://github.com/vitejs/vite-plugin-react |
| tailwindcss | ^4.0.0 | MIT | https://github.com/tailwindlabs/tailwindcss |
| typescript | ^5.7.3 | Apache-2.0 | https://github.com/microsoft/TypeScript |
| vite | ^6.1.0 | MIT | https://github.com/vitejs/vite |

## 3. Bundled Font Assets

### Vazirmatn

The repository includes the following font files:

- `assets/fonts/vazirmatn.woff2`
- `assets/fonts/vazirmatn-bold.woff2`

Font family: Vazirmatn

License: SIL Open Font License 1.1 (OFL-1.1)

The complete font license is provided in:

`assets/fonts/OFL.txt`

Original project:
https://github.com/rastikerdar/vazirmatn

## 4. External Resources

The demonstration interface references externally hosted resources, including:

- Google Fonts for Vazirmatn font loading.
- Unsplash-hosted placeholder imagery.

These resources are not redistributed as locally bundled third-party image files by this repository. Their respective providers' terms apply.

## 5. Dependency Scope and Limitations

This inventory covers the direct dependencies declared in `package.json`.

The repository does not currently include a package lockfile. Consequently, the exact resolved versions and complete licenses of transitive dependencies are not enumerated here.

Users building or redistributing the project should review the license information supplied with their installed dependency tree.

## 6. License Independence

The WCCC source files are licensed under the MIT License as described in the root `LICENSE` file.

Third-party dependencies and bundled font assets retain their respective copyrights and licenses.

Nothing in the WCCC license overrides the terms of third-party licenses.

---

Maintained by: mrstar55

Project: WooCommerce Card-to-Card (WCCC)

Public reference version: 1.0.65
