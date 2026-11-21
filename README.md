# WooCommerce Card-to-Card (WCCC)

**An early React and TypeScript UI prototype with WooCommerce-oriented frontend assets for manual card-to-card payment workflows.**

## Overview

WooCommerce Card-to-Card (WCCC) is an independently developed project focused on organizing manual card-to-card payment workflows for WooCommerce stores.

In markets where customers commonly pay by transferring money directly to a merchant's bank card, store owners need a structured way to manage payment instructions, submitted transaction information, and payment receipt reviews.

WCCC explores how these workflows can be organized through an administrative interface and supporting frontend components.

## The Problem

Traditional card-to-card payments often require merchants to handle several tasks manually:

- Providing customers with destination bank card information.
- Receiving payment information and transfer receipts.
- Matching submitted payments with the correct orders.
- Reviewing payment evidence.
- Updating order payment status.

Without a structured workflow, these tasks can increase administrative effort and create opportunities for human error.

## Project Approach

WCCC explores a merchant-managed payment workflow.

Its main design objectives include:

- Organizing destination bank card information.
- Supporting customer-submitted payment information.
- Associating payment records with WooCommerce orders.
- Providing administrative interfaces for reviewing payment evidence.
- Supporting payment review decisions by authorized store managers.

These are project objectives and design concepts. They should not be interpreted as confirmation that every workflow is fully implemented in this public snapshot.

## Public Repository Scope

**Public Reference Version: 1.0.65**

This repository contains an early public snapshot of WCCC, including:

- A React and TypeScript administrative UI prototype.
- Reusable interface components.
- Demonstration data and client-side interactions.
- WooCommerce-oriented JavaScript and CSS frontend assets.
- Project configuration and supporting metadata.

### Important Implementation Limitations

This public snapshot does not include the complete PHP implementation required for a fully operational WooCommerce plugin.

In particular:

- The React dashboard demonstrates administrative workflows using client-side state.
- The dashboard does not provide complete server-side payment processing.
- The repository does not include the PHP handlers required to make all WooCommerce AJAX operations functional.
- The project does not directly connect to banking APIs or financial institutions.
- It does not independently verify bank transactions or confirm that funds have been received.

Some WooCommerce-oriented frontend assets are included, but their presence alone does not establish that the corresponding backend functionality is available.

**This repository should be treated as an early UI and frontend reference snapshot, not as a complete installable or production-ready WooCommerce plugin.**

## Payment Verification Model

WCCC is designed around a manual payment verification concept.

Payment confirmation is intended to remain the responsibility of an authorized store manager, who must verify the transaction through the relevant banking channel before approving a payment.

The project does not replace banking infrastructure, financial institutions, or independent transaction verification.

## Demo Data

All customer names, phone numbers, bank card numbers, IBAN-like values, transaction references, device records, HMAC strings, system metrics, and webhook details included in the initial demonstration data are fictional placeholders.

They do not represent real customers, bank accounts, financial transactions, or production credentials.

The dashboard interactions in this public snapshot are demonstrations using client-side state.

They do not provide real server-side payment processing, bank verification, or SMS and webhook infrastructure.

## Technology Stack

The public snapshot uses the following technologies and libraries:

- React 19
- TypeScript 5.7
- Vite 6
- Tailwind CSS 4
- Lucide React
- WooCommerce-oriented JavaScript and CSS assets

Additional dependency and licensing information is available in [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).

## Design Principles

The project is guided by the following design principles:

- **Data integrity:** Payment-related information should be handled consistently.
- **Administrative clarity:** Interfaces should be understandable for store managers.
- **Modularity:** Components should have clearly defined responsibilities.
- **Security awareness:** Payment information and uploaded files require appropriate handling.
- **Maintainability:** Changes should be structured and designed to minimize regressions.
- **WooCommerce compatibility:** Frontend components should consider the WooCommerce environment.

These principles describe the project's design direction and do not imply that all production-level requirements are implemented in this snapshot.

## Development

This repository includes a React and TypeScript frontend project.

The available development scripts are defined in `package.json`:

| Command | Purpose |
|---|---|
| `npm run dev` | Start the Vite development server |
| `npm run build` | Run TypeScript checks and build the frontend |
| `npm run preview` | Preview the production build |
| `npm run lint` | Run TypeScript validation without generating output |

A compatible Node.js and npm environment is required.

The public repository represents an early reference snapshot. Later private development versions and their complete development history are not published here.

## Security and Privacy

Please do not publish real customer payment receipts, bank card numbers, transaction credentials, API keys, personal information, or production logs in public issues or discussions.

Security-related reports should avoid exposing sensitive information.

The sample data included in this repository is fictional and intended only for demonstration purposes.

## Repository Scope and Versioning

This repository intentionally represents the public reference version **1.0.65**.

It does not represent the latest development state or the complete current development history of WCCC.

Later development versions are maintained separately and are not published in this repository.

Please do not treat this snapshot as the latest production release.

## Contributing

Contributions and issue reports concerning the code and documentation available in this public repository are welcome.

Please read [CONTRIBUTING.md](CONTRIBUTING.md) before submitting an issue or pull request.

When reporting problems, use fictional demonstration data and never include real customer or payment information.

## License

The original WCCC source code included in this public version 1.0.65 snapshot is licensed under the MIT License.

See the [LICENSE](LICENSE) file for the complete terms.

### Third-Party Assets

Third-party components, dependencies, and bundled assets retain their respective licenses and copyrights.

The bundled Vazirmatn font files are licensed under the SIL Open Font License 1.1 (OFL-1.1).

The complete font license is available in [assets/fonts/OFL.txt](assets/fonts/OFL.txt).

Additional third-party notices are available in [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).

## Maintainer

**Maintainer:** [@mrstar55](https://github.com/mrstar55)

**Repository:** [woocommerce-card-to-card-v1.0.65](https://github.com/mrstar55/woocommerce-card-to-card-v1.0.65)

---

*WCCC — Exploring structured workflows for manual card-to-card payment management in WooCommerce.*
